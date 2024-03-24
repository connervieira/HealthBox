<?php
include "./metrics.php";
include "./servicedata.php";
include "./healthdata.php";
include "./fooddata.php";

$category_id = $_GET["category"];
$metric_id = $_GET["metric"];
$service_id = $_GET["service"];

// Authenticate using provided service ID.
$service_id = strtolower($service_id);
if ($service_id != preg_replace("/[^a-f0-9]/", '', $service_id)) { // Check to see if the service identifier contains disallowed characters.
    echo "{'error': {'id': 'invalid_service', 'reason': 'disallowed_characters', 'description': 'The service identifier contains invalid characters.'}}";
    exit();
}
if (strlen($service_id) > 100) { // Check to see if the service identifier is excessively long.
    echo "{'error': {'id': 'invalid_service', 'reason': 'too_long', 'description': 'The service identifier is excessively long.'}}";
    exit();
} else if (strlen($service_id) < 8) {
    echo "{'error': {'id': 'invalid_service', 'reason': 'too_short', 'description': 'The service identifier is too short.'}}";
    exit();
}
$services = load_servicedata();
$associated_user = find_serviceid($service_id, $services); // Search for the provided service ID in the service database.
if ($associated_user == false) {
    echo "{'error': {'id': 'invalid_service', 'reason': 'not_found', 'description': 'The specified service identifier does not exist.'}}";
    exit();
}

$food_data = load_food();

$metrics = load_metrics();
$health_data = load_healthdata();


if (in_array($category_id, array_keys($metrics)) == false) { // Check to see if the submitted category ID does not exist in the metrics database.
    echo "{'error': {'id': 'invalid_category', 'description': 'The specified category ID does not exist.'}}";
    exit();
}
if (in_array($metric_id, array_keys($metrics[$category_id]["metrics"])) == false) { // Check to see if the submitted metric ID does not exist in the metrics database.
    echo "{'error': {'id': 'invalid_metric', 'description': 'The specified metric ID does not exist.'}}";
    exit();
}

if (in_array($associated_user, array_keys($health_data)) == false) { // Check to see if this user hasn't yet been initialized in the health data.
    $health_data[$associated_user] = array(); // Initialize this user.
}
if (in_array($category_id, array_keys($health_data[$associated_user])) == false) { // Check to see if this category hasn't yet been added to to this user's health data.
    $health_data[$associated_user][$category_id] = array(); // Initialize this category.
}
if (in_array($metric_id, array_keys($health_data[$associated_user][$category_id])) == false) { // Check to see if this category hasn't yet been added to to this user's health data.
    $health_data[$associated_user][$category_id][$metric_id] = array(); // Initialize this metric.
}


$submission_data = array();
foreach ($metrics[$category_id]["metrics"][$metric_id]["keys"] as $key) { // Iterate through each metric to see if they have been submitted.
    if (isset($_GET["key-" . $key])) { // Check to see if this key exists in the submission data.
        $submission_data[$key] = $_GET["key-" . $key];
    } else {
        $submission_data[$key] = ""; // Use a blank placeholder for now.
    }
}


$all_required_metrics_present = true; // This will be switched to `false` if a missing value is found.
for ($i = 0; $i < sizeof($submission_data); $i++) { // Run once for each key in this metric.
    if (strval($_GET["key-" . $metrics[$category_id]["metrics"][$metric_id]["keys"][$i]]) == "" and $metrics[$category_id]["metrics"][$metric_id]["requirements"][$i] == true) { // Check to see if this value is required but was left blank.
        $all_required_metrics_present = false;
    }
}
if ($all_required_metrics_present == false) {
    echo "{'error': {'id': 'missing_required_data', 'description': 'The submission is missing required data for this metric.'}}";
    exit();
}


$datapoint_time = time();

for ($i = 0; $i < sizeof($submission_data); $i++) { // Validate each submitted value for this metric.
    $validation_rule = $metrics[$category_id]["metrics"][$metric_id]["validation"][$i];

    $key = array_keys($submission_data)[$i];

    if ($validation_rule == "int") { // int: A positive whole number.
        if (floatval($submission_data[$key]) != round(floatval($submission_data[$key]))) { // Check to see if this value is not a whole number.
            echo "{'error': {'id': 'invalid_value', 'value': '" . $key ."', 'description': 'The data submitted for " . $key . " is invalid because it is not a whole number.'}}";
            exit();
        }
        $submission_data[$key] = intval($submission_data[$key]); // Convert this value to an integer.
        if ($submission_data[$key] < 0) { // Check to see if this value is negative.
            echo "{'error': {'id': 'invalid_value', 'value': '" . $key ."', 'description': 'The data submitted for " . $key . " is invalid because it is a negative number.'}}";
            exit();
        }
    } else if ($validation_rule == "float") { // float: A positive decimal number
        $submission_data[$key] = floatval($submission_data[$key]); // Convert this value to a floating point number.
        if ($submission_data[$key] < 0) { // Check to see if this value is negative.
            echo "{'error': {'id': 'invalid_value', 'value': '" . $key ."', 'description': 'The data submitted for " . $key . " is invalid because it is a negative number.'}}";
            exit();
        }
    } else if ($validation_rule == "start_time" or $validation_rule == "end_time") { // start_time: A Unix timestamp before end_time (integer). /// end_time: A Unix timestamp after start_time (integer).
        $start_time_index = array_search("start_time", $metrics[$category_id]["metrics"][$metric_id]["validation"]);
        $end_time_index = array_search("end_time", $metrics[$category_id]["metrics"][$metric_id]["validation"]);

        $start_time_key = array_keys($submission_data)[$start_time_index];
        $end_time_key = array_keys($submission_data)[$end_time_index];

        if (floatval($submission_data[$start_time_key]) != round(floatval($submission_data[$start_time_key]))) { // Check to see if the start time is not a whole number.
            echo "{'error': {'id': 'invalid_value', 'value': '" . $start_time_key ."', 'description': 'The data submitted for " . $start_time_key . " is invalid because it is not a whole number.'}}";
            exit();
        } else if (floatval($submission_data[$end_time_key]) != round(floatval($submission_data[$end_time_key]))) { // Check to see if the end time is not a whole number.
            echo "{'error': {'id': 'invalid_value', 'value': '" . $end_time_key ."', 'description': 'The data submitted for " . $end_time_key . " is invalid because it is not a whole number.'}}";
            exit();
        }

        $submission_data[$start_time_key] = intval($submission_data[$start_time_key]); // Convert the end time to an integer.
        $submission_data[$end_time_key] = intval($submission_data[$end_time_key]); // Convert the end time to an integer.

        if ($submission_data[$start_time_key] <= 0) { // Check to see if the start time is not a positive number.
            echo "{'error': {'id': 'invalid_value', 'value': '" . $start_time_key . "', 'description': 'The data submitted for " . $start_time_key . " is invalid because it is not a positive number.'}}";
            exit();
        } else if ($submission_data[$end_time_key] <= 0) { // Check to see if the end time is not a positive number.
            echo "{'error': {'id': 'invalid_value', 'value': '" . $end_time_key . "', 'description': 'The data submitted for " . $end_time_key . " is invalid because it is not a positive number.'}}";
            exit();
        }

        if ($submission_data[$start_time_key] >= $submission_data[$end_time_key]) { // Check to see if the start time occurs after the end time.
            echo "{'error': {'id': 'invalid_value', 'value': '" . $start_time_key ."', 'description': 'The data submitted for " . $start_time_key . " is invalid because it occurs after the value for " . $end_time_key . ".'}}";
            exit();
        }
    } else if ($validation_rule == "datetime") { // datetime: A Unix timestamp
        if (floatval($submission_data[$key]) != round(floatval($submission_data[$key]))) { // Check to see if this value is not a whole number.
            echo "{'error': {'id': 'invalid_value', 'value': '" . $key . "', 'description': 'The data submitted for " . $key . " is invalid because it is not a whole number.'}}";
            exit();
        }
        $submission_data[$key] = intval($submission_data[$key]); // Convert this value to an integer.
        if ($submission_data[$key] <= 0) { // Check to see if this value is not a positive number.
            echo "{'error': {'id': 'invalid_value', 'value': '" . $key . "', 'description': 'The data submitted for " . $key . " is invalid because it is not a positive number.'}}";
            exit();
        }
    } else if ($validation_rule == "short_string") { // short_string: A string under 20 characters. (Allowed characters: a-zA-Z0-9 '_-())
        $submission_data[$key] = strval($submission_data[$key]); // Convert this value to a string.
        if (strlen($submission_data[$key]) > 20) { // Check to see if this value is too long.
            echo "{'error': {'id': 'invalid_value', 'value': '" . $key . "', 'description': 'The data submitted for " . $key . " is invalid because it is over 20 characters long.'}}";
            exit();
        }
        if ($submission_data[$key] != preg_replace("/[^a-zA-Z0-9 '_\-()]/", '', $submission_data[$key])) { // Check to see if this value contains disallowed characters.
            echo "{'error': {'id': 'invalid_value', 'value': '" . $key . "', 'description': 'The data submitted for " . $key . " is invalid because it contains disallowed characters.'}}";
            exit();
        }
    } else if ($validation_rule == "long_string") { // long_string: A string under 150 characters. (Allowed characters: a-zA-Z0-9 '_-())
        $submission_data[$key] = strval($submission_data[$key]); // Convert this value to a string.
        if (strlen($submission_data[$key]) > 150) { // Check to see if this value is too long.
            echo "{'error': {'id': 'invalid_value', 'value': '" . $key . "', 'description': 'The data submitted for " . $key . " is invalid because it is over 150 characters long.'}}";
            exit();
        }
        if ($submission_data[$key] != preg_replace("/[^a-zA-Z0-9 '_\-()]/", '', $submission_data[$key])) { // Check to see if this value contains disallowed characters.
            echo "{'error': {'id': 'invalid_value', 'value': '" . $key . "', 'description': 'The data submitted for " . $key . " is invalid because it contains disallowed characters.'}}";
            exit();
        }
    } else if ($validation_rule == "boolean") { // boolean: A 'true' or 'false' value
        $submission_data[$key] = strval($submission_data[$key]); // Convert this value to a string.
        $submission_data[$key] = strtolower($submission_data[$key]); // Convert this value to lowercase.
        $submission_data[$key] = $submission_data[$key][0]; // Take only the first character of this value.

        if ($submission_data[$key] == "t" or $submission_data[$key] == "y" or $submission_data[$key] == "1") { // Check to see if this value is a string representing 'true'.
            $submission_data[$key] = true; 
        } else if ($submission_data[$key] == "f" or $submission_data[$key] == "n" or $submission_data[$key] == "0") { // Check to see if this value is a string representing 'false'.
            $submission_data[$key] = false; 
        } else {
            echo "{'error': {'id': 'invalid_value', 'value': '" . $key . "', 'description': 'The data submitted for " . $key . " is invalid because it does not appear to be a boolean.'}}";
            exit();
        }
    } else if ($validation_rule == "sex") { // sex: A 1 character string: M, F, or I
        $submission_data[$key] = strval($submission_data[$key]); // Convert this value to a string.
        $submission_data[$key] = strtoupper($submission_data[$key]); // Convert this value to uppercase.
        $submission_data[$key] = $submission_data[$key][0]; // Take only the first character of this value.

        if ($submission_data[$key] !== "M" and $submission_data[$key] !== "F" and $submission_data[$key] !== "I") { // Check to see if this value is not any of the expected values.
            echo "{'error': {'id': 'invalid_value', 'value': '" . $key . "', 'description': 'The data submitted for " . $key . " is invalid because it is not a valid option.'}}";
            exit();
        }
    } else if ($validation_rule == "sexuality") { // sexuality: A 1 character string: S, G, B, or A
        $submission_data[$key] = strval($submission_data[$key]); // Convert this value to a string.
        $submission_data[$key] = strtoupper($submission_data[$key]); // Convert this value to uppercase.
        $submission_data[$key] = $submission_data[$key][0]; // Take only the first character of this value.

        if ($submission_data[$key] !== "S" and $submission_data[$key] !== "G" and $submission_data[$key] !== "B" and $submission_data[$key] !== "A") { // Check to see if this value is not any of the expected values.
            echo "{'error': {'id': 'invalid_value', 'value': '" . $key . "', 'description': 'The data submitted for " . $key . " is invalid because it is not a valid option.'}}";
            exit();
        }
    } else if ($validation_rule == "temperature") { // temperature: A positive or negative float, above -273
        $submission_data[$key] = floatval($submission_data[$key]);
        if ($submission_data[$key] <= -273) {
            echo "{'error': {'id': 'invalid_value', 'value': '" . $key . "', 'description': 'The data submitted for " . $key . " is invalid because it is below absolute zero.'}}";
            exit();
        }
    } else if ($validation_rule == "percentage") { // percentage: A decimal number ranged 0 to 1, inclusively.
        $submission_data[$key] = floatval($submission_data[$key]);
        if ($submission_data[$key] < 0 or $submission_data[$key] > 1) {
            echo "{'error': {'id': 'invalid_value', 'value': '" . $key . "', 'description': 'The data submitted for " . $key . " is invalid because it is outside of the expected range.'}}";
            exit();
        }
    } else if ($validation_rule == "side") { // side: A 1 character string: L or R
        $submission_data[$key] = strval($submission_data[$key]); // Convert this value to a string.
        $submission_data[$key] = strtoupper($submission_data[$key]); // Convert this value to uppercase.
        $submission_data[$key] = $submission_data[$key][0]; // Take only the first character of this value.

        if ($submission_data[$key] !== "L" and $submission_data[$key] !== "R") { // Check to see if this value is not any of the expected values.
            echo "{'error': {'id': 'invalid_value', 'value': '" . $key . "', 'description': 'The data submitted for " . $key . " is invalid because it is not a valid option.'}}";
            exit();
        }
    } else if ($validation_rule == "vehicleid") { // vehicleid: A vehicle ID that exists in the vehicle database.
        $submission_data[$key] = strval($submission_data[$key]); // Convert this value to a string.
        if (strlen($submission_data[$key]) > 150) { // Check to see if this value is excessively long.
            echo "{'error': {'id': 'invalid_value', 'value': '" . $key . "', 'description': 'The data submitted for " . $key . " is invalid because it is excessively long.'}}";
            exit();
        }
        if ($submission_data[$key] != preg_replace("/[^a-zA-Z0-9 '_\-()]/", '', $submission_data[$key])) { // Check to see if this value contains disallowed characters.
            echo "{'error': {'id': 'invalid_value', 'value': '" . $key . "', 'description': 'The data submitted for " . $key . " is invalid because it contains disallowed characters.'}}";
            exit();
        }
        // TODO: Check to see if this ID exists in the vehicle database.
    } else if ($validation_rule == "foodid") { // foodid: A food ID that exists in the food database.
        $submission_data[$key] = strval($submission_data[$key]); // Convert this value to a string.
        if (strlen($submission_data[$key]) > 150) { // Check to see if this value is excessively long.
            echo "{'error': {'id': 'invalid_value', 'value': '" . $key . "', 'description': 'The data submitted for " . $key . " is invalid because it is excessively long.'}}";
            exit();
        }
        if ($submission_data[$key] != preg_replace("/[^a-zA-Z0-9 '_\-()]/", '', $submission_data[$key])) { // Check to see if this value contains disallowed characters.
            echo "{'error': {'id': 'invalid_value', 'value': '" . $key . "', 'description': 'The data submitted for " . $key . " is invalid because it contains disallowed characters.'}}";
            exit();
        }
        if (in_array($submission_data[$key], array_keys($food_data["entries"][$associated_user]["foods"])) == false) { // Check to see if this food ID is not in the food database.
            echo "{'error': {'id': 'invalid_value', 'value': '" . $key . "', 'description': 'The data submitted for " . $key . " is invalid because the specified ID does not exist in the food database.'}}";
            exit();
        }
    } else if ($validation_rule == "mealid") { // mealid: A string that combines a date (YYYY-MM-DD) and meal number, where 0 is a snack (1 for breakfast, 2 for lunch, 3 for dinner) separated by a comma. For example, dinner on May 5th would be "2024-05-21,3".
        if ($submission_data[$key] != preg_replace("/[^0-9,\-]/", '', $submission_data[$key])) { // Check to see if this value contains disallowed characters.
            echo "{'error': {'id': 'invalid_value', 'value': '" . $key . "', 'description': 'The data submitted for " . $key . " is invalid because it contains disallowed characters.'}}";
            exit();
        }
        $meal_components = explode(",", $submission_data[$key]); // Split this value into its components (date and meal number).
        if (sizeof($meal_components) > 2) {
            echo "{'error': {'id': 'invalid_value', 'value': '" . $key . "', 'description': 'The data submitted for " . $key . " is invalid because it contains more than 1 comma.'}}";
            exit();
        } else if (sizeof($meal_components) < 2) {
            echo "{'error': {'id': 'invalid_value', 'value': '" . $key . "', 'description': 'The data submitted for " . $key . " is invalid because it does not contain a comma.'}}";
            exit();
        }
        $date = explode("-", $meal_components[0]);
        $meal = intval($meal_components[1]);
        if (!preg_match("/^[0-9]{4}-[0-9]{2}-[0-9]{2}$/", $meal_components[0])) {
            echo "{'error': {'id': 'invalid_value', 'value': '" . $key . "', 'description': 'The data submitted for " . $key . " is invalid because the date is not in the expected format (YYYY-MM-DD).'}}";
            exit();
        } else if (checkdate($date[1], $date[2], $date[0]) == false) {
            echo "{'error': {'id': 'invalid_value', 'value': '" . $key . "', 'description': 'The data submitted for " . $key . " is invalid because the date is invalid.'}}";
            exit();
        }
        if ($meal < 0) {
            echo "{'error': {'id': 'invalid_value', 'value': '" . $key . "', 'description': 'The data submitted for " . $key . " is invalid because the meal number is a negative number.'}}";
            exit();
        } else if ($meal > 9) {
            echo "{'error': {'id': 'invalid_value', 'value': '" . $key . "', 'description': 'The data submitted for " . $key . " is invalid because the meal number is excessively high.'}}";
            exit();
        }
    }
}


if (in_array("time", $submission_data)) { // Check to see if there is a timestamp in this submission.
    $datapoint_time = $submission_data[$key]; // Make this timestamp the key for this entry.
    unset($submission_data[$key]); // Remove the timestamp from the submission data.
}

foreach ($health_data[$associated_user][$category_id][$metric_id] as $datapoint) { // Iterate over each existing datapoint for this metric.
    if ($datapoint["data"] == $submission_data) { // Check to see if this datapoint exact matches the data we are about to submit.
        echo "{'error': {'id': 'duplicate', 'description': 'The data submitted is an exact duplicate of an existing datapoint.'}}";
        exit();
    }
}

$health_data[$associated_user][$category_id][$metric_id][$datapoint_time] = array(); // Initialize this datapoint.
$health_data[$associated_user][$category_id][$metric_id][$datapoint_time]["service"] = $service_id;
$health_data[$associated_user][$category_id][$metric_id][$datapoint_time]["data"] = $submission_data; // Initialize this datapoint.

save_healthdata($health_data);
echo "{'success': {'description': 'A new datapoint has been added under " . $category_id . ">" . $metric_id . ">" . $datapoint_time . ".'}}";
?>
