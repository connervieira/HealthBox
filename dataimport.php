<?php
include "./config.php";

$force_login_redirect = true;
include $healthbox_config["auth"]["provider"]["core"];

if (in_array($username, $healthbox_config["auth"]["access"]["admin"]) == false) {
    if ($healthbox_config["auth"]["access"]["mode"] == "whitelist") {
        if (in_array($username, $healthbox_config["auth"]["access"]["whitelist"]) == false) { // Check to make sure this user is not in blacklist.
            echo "<p>You are not permitted to access this utility.</p>";
            exit();
        }
    } else if ($healthbox_config["auth"]["access"]["mode"] == "blacklist") {
        if (in_array($username, $healthbox_config["auth"]["access"]["blacklist"]) == true) { // Check to make sure this user is not in blacklist.
            echo "<p>You are not permitted to access this utility.</p>";
            exit();
        }
    } else {
        echo "<p>The configured access mode is invalid.</p>";
        exit();
    }
}

include "./fooddata.php";
include "./servicedata.php";
include "./healthdata.php";
include "./metrics.php";
$food_data = load_food();
$service_data = load_servicedata();
$health_data = load_healthdata();
$metrics = load_metrics();

function validate_food($food) {
    global $food_data;
    foreach (array_keys($food) as $element) {
        if ($element == "service") {
            // Skip validation of the service, since the service was overwritten in the previous step.
        } else if ($element == "nutrients") {
            foreach (array_keys($food[$element]) as $nutrient) {
                if (in_array($nutrient, array_keys($food_data["metadata"]["nutrients"]))) { // Validate that this nutrient ID exists in the food database.
                    if (floatval($food[$element][$nutrient]) >= 0 and floatval($food[$element][$nutrient]) < 1000000) { // Validate that this nutrient value is a reasonable value.
                        // This value is valid.
                    } else {
                        echo "<p>One or more of the nutrient values is invalid.</p>";
                        return false;
                    }
                } else {
                    echo "<p>One or more of the nutrient IDs is not recognized.</p>";
                    return false;
                }
            }
        } else if ($element == "serving") {
            if (sizeof(array_keys($food[$element])) == 2) { // Check to make sure the service size contains the number of expected elements.
                if (in_array("size", array_keys($food[$element])) and in_array("unit", array_keys($food[$element]))) { // Check to make sure the service size contains the expected elements.
                    if (gettype($food[$element]["size"]) == "double" or gettype($food[$element]["size"]) == "integer") {
                        if ($food[$element]["size"] > 0 or $food[$element]["size"] < 100000) {
                            // This value is valid.
                        } else {
                            echo "<p>The serving size is out of bounds.</p>";
                            return false;
                        }
                    } else { 
                        echo "<p>The serving size is invalid.</p>";
                        return false;
                    }
                } else { 
                    echo "<p>The serving is invalid.</p>";
                    return false;
                }
            } else { 
                echo "<p>The serving contains unexpected keys.</p>";
                return false;
            }
        } else { // This value is not a hardcoded value.
            if (in_array($element, array_keys($food_data["metadata"]["values"]))) {
                if (gettype($food[$element]) == "string") {
                    if ($food_data["metadata"]["values"][$element]["type"] == "str") {
                        if (preg_replace("/[^a-zA-Z0-9\-_ \']/", '', $food[$element]) == $food[$element]) {
                            // This value is valid.
                        } else {
                            echo "<p>Invalid string found.</p>";
                            return false;
                        }
                    } else {
                        echo "<p>Mis-matched datatype found (string).</p>";
                        return false;
                    }
                } else if (gettype($food[$element]) == "bool"){
                    if ($food_data["metadata"]["values"][$element]["type"] == "bool") {
                        // This value is valid.
                    } else {
                        echo "<p>Mis-matched datatype found (bool).</p>";
                        return false;
                    }
                } else if (gettype($food[$element]) == "double") {
                    if ($food_data["metadata"]["values"][$element]["type"] == "float") {
                        // This value is valid.
                    } else {
                        echo "<p>Mis-matched datatype found (bool).</p>";
                        return false;
                    }
                } else if (gettype($food[$element]) == "int"){
                    if ($food_data["metadata"]["values"][$element]["type"] == "int") {
                        // This value is valid.
                    } else {
                        echo "<p>Mis-matched datatype found (bool).</p>";
                        return false;
                    }
                }
            } else {
                echo $element;
                echo "<p>One or more foods contains an unrecognized value ID.</p>";
                return false;
            }
        }
    }

    return true;
}
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>HealthBox - Data Import</title>
        <link rel="stylesheet" href="./assets/styles/main.css">
        <link rel="stylesheet" href="./assets/fonts/lato/latofonts.css">
    </head>
    <body>
        <main>
            <div class="navbar" role="navigation">
                <a class="button" role="button" href="./management.php">Back</a>
            </div>
            <h1><span style="color:#ff55aa">Health</span><span style="padding:3px;border-radius:10px;background:#ff55aa;">Box</span></h1>
            <h2>Data Import</h2>

            <hr>
            <?php
            $service = $_POST["service"]; // This is the service being used to import the data.
            $uploaded_file_path = $_FILES['file']['tmp_name']; // This is the uploaded file, containing data to import.
            $uploaded_file_size = filesize($uploaded_file_path); // Calculate the file size of the uploaded file.

            if ($uploaded_file_size > 0) { // Check to see if a file has been uploaded.
                if (check_permissions_action($service, "data-writeall", $service_data) and check_permissions_action($service, "foods-add", $service_data) and check_permissions_action($service, "foods-edit", $service_data)) { // Check to make sure the selected service has all required permissions to import data.
                    if ($uploaded_file_size < 10 * 1000 * 1000) { // Check to make sure the uploaded file is less than 10MB.
                        $uploaded_file_contents = file_get_contents($uploaded_file_path); // Read the contents of the uploaded file.
                        if (is_json($uploaded_file_contents) == true) { // Check to make sure the data uploaded is valid JSON.
                            $imported_data = json_decode($uploaded_file_contents, true); // Load the imported JSON data.
                            if (sizeof(array_keys($imported_data)) == 2) { // Check to make sure there are exactly the number of expected keys in the imported data.
                                if (in_array("food", array_keys($imported_data)) and in_array("data", array_keys($imported_data))) { // Check to make sure the keys in the imported data are the ones expected.

                                    echo "<h3>Food Validation</h3>";
                                    $food_valid = true;
                                    foreach (array_keys($imported_data["food"]) as $food) {
                                        $service_associated_user = find_serviceid($imported_data["food"][$food]["service"], $service_data);
                                        if ($service_associated_user == $username) { // Leave the service identifier unchanged, since it matches a service associated with this user.
                                        } else { // This service ID was either not found, or is not associated with this user.
                                            $imported_data["food"][$food]["service"] = $service; // Replace the service associated with this data with the service used to import the data.
                                        }
                                        if (validate_food($imported_data["food"][$food]) == false) {
                                            $food_valid = false;
                                            echo "<p>'" . htmlspecialchars($food) . "' is invalid.</p>";
                                        }
                                    }
                                    if ($food_valid == true) {
                                        echo "<p><b>Food validated</b></p>";
                                    }




                                    echo "<br><h3>Health Validation</h3>";
                                    $data_valid = true;
                                    foreach (array_keys($imported_data["data"]) as $category) {
                                        if (in_array($category, array_keys($metrics))) { // Check to make sure this category exists on this instance.
                                            foreach (array_keys($imported_data["data"][$category]) as $metric) {
                                                if (in_array($metric, array_keys($metrics[$category]["metrics"]))) { // Check to make sure this metric exists on this instance.
                                                    foreach (array_keys($imported_data["data"][$category][$metric]) as $datapoint) {
                                                        if ($datapoint == intval($datapoint)) { // Check to make sure the timestamp is the expected type.
                                                            if ($datapoint > 0 and $datapoint < time() + 315360000) { // Make sure the timestamp is between 1970 and 10 years in the future.
                                                                $service_associated_user = find_serviceid($imported_data["data"][$category][$metric][$datapoint]["service"], $service_data);
                                                                if ($service_associated_user == $username) { // Leave the service identifier unchanged, since it matches a service associated with this user.
                                                                } else { // This service ID was either not found, or is not associated with this user.
                                                                    $imported_data["data"][$category][$metric][$datapoint]["service"] = $service; // Replace the service associated with this data with the service used to import the data.
                                                                }
                                                                $submission_data = $imported_data["data"][$category][$metric][$datapoint]["data"];
                                                                $value_index = 0;
                                                                foreach (array_keys($imported_data["data"][$category][$metric][$datapoint]["data"]) as $key) { // Iterate through each value associated with this datapoint.
                                                                    if (in_array($key, $metrics[$category]["metrics"][$metric]["keys"])) { // Check to see if this value exists in the keys for this metric on this instance.
                                                                        $validation_rule = $metrics[$category]["metrics"][$metric]["validation"][$value_index];

                                                                        if ($validation_rule == "int") { // int: A positive whole number.
                                                                            if (floatval($submission_data[$key]) != round(floatval($submission_data[$key]))) { // Check to see if this value is not a whole number.
                                                                                echo "'" . htmlspecialchars($category) . ">" . htmlspecialchars($metric) . ">" . htmlspecialchars($datapoint) . ">" . htmlspecialchars($key) . "'" . " is an invalid '" . $validation_rule . "'";
                                                                                $data_valid = false;
                                                                            }
                                                                            if ($submission_data[$key] < 0) { // Check to see if this value is negative.
                                                                                echo "'" . htmlspecialchars($category) . ">" . htmlspecialchars($metric) . ">" . htmlspecialchars($datapoint) . ">" . htmlspecialchars($key) . "'" . " is an invalid '" . $validation_rule . "'";
                                                                                $data_valid = false;
                                                                            }
                                                                        } else if ($validation_rule == "float") { // float: A positive decimal number
                                                                            $submission_data[$key] = floatval($submission_data[$key]); // Convert this value to a floating point number.
                                                                            if ($submission_data[$key] < 0) { // Check to see if this value is negative.
                                                                                echo "'" . htmlspecialchars($category) . ">" . htmlspecialchars($metric) . ">" . htmlspecialchars($datapoint) . ">" . htmlspecialchars($key) . "'" . " is an invalid '" . $validation_rule . "'";
                                                                                $data_valid = false;
                                                                            }
                                                                        } else if ($validation_rule == "start_time" or $validation_rule == "end_time") { // start_time: A Unix timestamp before end_time (integer). /// end_time: A Unix timestamp after start_time (integer).
                                                                            $start_time_index = array_search("start_time", $metrics[$category]["metrics"][$metric]["validation"]);
                                                                            $end_time_index = array_search("end_time", $metrics[$category]["metrics"][$metric]["validation"]);

                                                                            $start_time_key = array_keys($submission_data)[$start_time_index];
                                                                            $end_time_key = array_keys($submission_data)[$end_time_index];

                                                                            if (floatval($submission_data[$start_time_key]) != round(floatval($submission_data[$start_time_key]))) { // Check to see if the start time is not a whole number.
                                                                                echo "'" . htmlspecialchars($category) . ">" . htmlspecialchars($metric) . ">" . htmlspecialchars($datapoint) . ">" . htmlspecialchars($key) . "'" . " is an invalid '" . $validation_rule . "'";
                                                                                $data_valid = false;
                                                                            } else if (floatval($submission_data[$end_time_key]) != round(floatval($submission_data[$end_time_key]))) { // Check to see if the end time is not a whole number.
                                                                                echo "'" . htmlspecialchars($category) . ">" . htmlspecialchars($metric) . ">" . htmlspecialchars($datapoint) . ">" . htmlspecialchars($key) . "'" . " is an invalid '" . $validation_rule . "'";
                                                                                $data_valid = false;
                                                                            }

                                                                            $submission_data[$start_time_key] = intval($submission_data[$start_time_key]); // Convert the end time to an integer.
                                                                            $submission_data[$end_time_key] = intval($submission_data[$end_time_key]); // Convert the end time to an integer.

                                                                            if ($submission_data[$start_time_key] <= 0) { // Check to see if the start time is not a positive number.
                                                                                echo "'" . htmlspecialchars($category) . ">" . htmlspecialchars($metric) . ">" . htmlspecialchars($datapoint) . ">" . htmlspecialchars($key) . "'" . " is an invalid '" . $validation_rule . "'";
                                                                                $data_valid = false;
                                                                            } else if ($submission_data[$end_time_key] <= 0) { // Check to see if the end time is not a positive number.
                                                                                echo "'" . htmlspecialchars($category) . ">" . htmlspecialchars($metric) . ">" . htmlspecialchars($datapoint) . ">" . htmlspecialchars($key) . "'" . " is an invalid '" . $validation_rule . "'";
                                                                                $data_valid = false;
                                                                            }

                                                                            if ($submission_data[$start_time_key] >= $submission_data[$end_time_key]) { // Check to see if the start time occurs after the end time.
                                                                                echo "'" . htmlspecialchars($category) . ">" . htmlspecialchars($metric) . ">" . htmlspecialchars($datapoint) . ">" . htmlspecialchars($key) . "'" . " is an invalid '" . $validation_rule . "'";
                                                                                $data_valid = false;
                                                                            }
                                                                        } else if ($validation_rule == "datetime") { // datetime: A Unix timestamp
                                                                            if (floatval($submission_data[$key]) != round(floatval($submission_data[$key]))) { // Check to see if this value is not a whole number.
                                                                                echo "'" . htmlspecialchars($category) . ">" . htmlspecialchars($metric) . ">" . htmlspecialchars($datapoint) . ">" . htmlspecialchars($key) . "'" . " is an invalid '" . $validation_rule . "'";
                                                                                $data_valid = false;
                                                                            }
                                                                            $submission_data[$key] = intval($submission_data[$key]); // Convert this value to an integer.
                                                                            if ($submission_data[$key] <= 0) { // Check to see if this value is not a positive number.
                                                                                echo "'" . htmlspecialchars($category) . ">" . htmlspecialchars($metric) . ">" . htmlspecialchars($datapoint) . ">" . htmlspecialchars($key) . "'" . " is an invalid '" . $validation_rule . "'";
                                                                                $data_valid = false;
                                                                            }
                                                                        } else if ($validation_rule == "short_string") { // short_string: A string under 20 characters. (Allowed characters: a-zA-Z0-9 '_-())
                                                                            $submission_data[$key] = strval($submission_data[$key]); // Convert this value to a string.
                                                                            if (strlen($submission_data[$key]) > 20) { // Check to see if this value is too long.
                                                                                echo "'" . htmlspecialchars($category) . ">" . htmlspecialchars($metric) . ">" . htmlspecialchars($datapoint) . ">" . htmlspecialchars($key) . "'" . " is an invalid '" . $validation_rule . "'";
                                                                                $data_valid = false;
                                                                            }
                                                                            if ($submission_data[$key] != preg_replace("/[^a-zA-Z0-9 '_\-()]/", '', $submission_data[$key])) { // Check to see if this value contains disallowed characters.
                                                                                echo "'" . htmlspecialchars($category) . ">" . htmlspecialchars($metric) . ">" . htmlspecialchars($datapoint) . ">" . htmlspecialchars($key) . "'" . " is an invalid '" . $validation_rule . "'";
                                                                                $data_valid = false;
                                                                            }
                                                                        } else if ($validation_rule == "long_string") { // long_string: A string under 150 characters. (Allowed characters: a-zA-Z0-9 '_-())
                                                                            $submission_data[$key] = strval($submission_data[$key]); // Convert this value to a string.
                                                                            if (strlen($submission_data[$key]) > 150) { // Check to see if this value is too long.
                                                                                echo "'" . htmlspecialchars($category) . ">" . htmlspecialchars($metric) . ">" . htmlspecialchars($datapoint) . ">" . htmlspecialchars($key) . "'" . " is an invalid '" . $validation_rule . "'";
                                                                                $data_valid = false;
                                                                            }
                                                                            if ($submission_data[$key] != preg_replace("/[^a-zA-Z0-9 '_\-()]/", '', $submission_data[$key])) { // Check to see if this value contains disallowed characters.
                                                                                echo "'" . htmlspecialchars($category) . ">" . htmlspecialchars($metric) . ">" . htmlspecialchars($datapoint) . ">" . htmlspecialchars($key) . "'" . " is an invalid '" . $validation_rule . "'";
                                                                                $data_valid = false;
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
                                                                                echo "'" . htmlspecialchars($category) . ">" . htmlspecialchars($metric) . ">" . htmlspecialchars($datapoint) . ">" . htmlspecialchars($key) . "'" . " is an invalid '" . $validation_rule . "'";
                                                                                $data_valid = false;
                                                                            }
                                                                        } else if ($validation_rule == "sex") { // sex: A 1 character string: M, F, or I
                                                                            $submission_data[$key] = strval($submission_data[$key]); // Convert this value to a string.
                                                                            $submission_data[$key] = strtoupper($submission_data[$key]); // Convert this value to uppercase.
                                                                            $submission_data[$key] = $submission_data[$key][0]; // Take only the first character of this value.

                                                                            if ($submission_data[$key] !== "M" and $submission_data[$key] !== "F" and $submission_data[$key] !== "I") { // Check to see if this value is not any of the expected values.
                                                                                echo "'" . htmlspecialchars($category) . ">" . htmlspecialchars($metric) . ">" . htmlspecialchars($datapoint) . ">" . htmlspecialchars($key) . "'" . " is an invalid '" . $validation_rule . "'";
                                                                                $data_valid = false;
                                                                            }
                                                                        } else if ($validation_rule == "sexuality") { // sexuality: A 1 character string: S, G, B, or A
                                                                            $submission_data[$key] = strval($submission_data[$key]); // Convert this value to a string.
                                                                            $submission_data[$key] = strtoupper($submission_data[$key]); // Convert this value to uppercase.
                                                                            $submission_data[$key] = $submission_data[$key][0]; // Take only the first character of this value.

                                                                            if ($submission_data[$key] !== "S" and $submission_data[$key] !== "G" and $submission_data[$key] !== "B" and $submission_data[$key] !== "A") { // Check to see if this value is not any of the expected values.
                                                                                echo "'" . htmlspecialchars($category) . ">" . htmlspecialchars($metric) . ">" . htmlspecialchars($datapoint) . ">" . htmlspecialchars($key) . "'" . " is an invalid '" . $validation_rule . "'";
                                                                                $data_valid = false;
                                                                            }
                                                                        } else if ($validation_rule == "temperature") { // temperature: A positive or negative float, above -273
                                                                            $submission_data[$key] = floatval($submission_data[$key]);
                                                                            if ($submission_data[$key] <= -273) {
                                                                                echo "'" . htmlspecialchars($category) . ">" . htmlspecialchars($metric) . ">" . htmlspecialchars($datapoint) . ">" . htmlspecialchars($key) . "'" . " is an invalid '" . $validation_rule . "'";
                                                                                $data_valid = false;
                                                                            }
                                                                        } else if ($validation_rule == "percentage") { // percentage: A decimal number ranged 0 to 1, inclusively.
                                                                            $submission_data[$key] = floatval($submission_data[$key]);
                                                                            if ($submission_data[$key] < 0 or $submission_data[$key] > 1) {
                                                                                echo "'" . htmlspecialchars($category) . ">" . htmlspecialchars($metric) . ">" . htmlspecialchars($datapoint) . ">" . htmlspecialchars($key) . "'" . " is an invalid '" . $validation_rule . "'";
                                                                                $data_valid = false;
                                                                            }
                                                                        } else if ($validation_rule == "side") { // side: A 1 character string: L or R
                                                                            $submission_data[$key] = strval($submission_data[$key]); // Convert this value to a string.
                                                                            $submission_data[$key] = strtoupper($submission_data[$key]); // Convert this value to uppercase.
                                                                            $submission_data[$key] = $submission_data[$key][0]; // Take only the first character of this value.

                                                                            if ($submission_data[$key] !== "L" and $submission_data[$key] !== "R") { // Check to see if this value is not any of the expected values.
                                                                                echo "'" . htmlspecialchars($category) . ">" . htmlspecialchars($metric) . ">" . htmlspecialchars($datapoint) . ">" . htmlspecialchars($key) . "'" . " is an invalid '" . $validation_rule . "'";
                                                                                $data_valid = false;
                                                                            }
                                                                        } else if ($validation_rule == "foodid") { // foodid: A food ID that exists in the food database.
                                                                            $submission_data[$key] = strval($submission_data[$key]); // Convert this value to a string.
                                                                            if (strlen($submission_data[$key]) > 150) { // Check to see if this value is excessively long.
                                                                                echo "'" . htmlspecialchars($category) . ">" . htmlspecialchars($metric) . ">" . htmlspecialchars($datapoint) . ">" . htmlspecialchars($key) . "'" . " is an invalid '" . $validation_rule . "'";
                                                                                $data_valid = false;
                                                                            }
                                                                            if ($submission_data[$key] != preg_replace("/[^a-zA-Z0-9 '_\-()]/", '', $submission_data[$key])) { // Check to see if this value contains disallowed characters.
                                                                                echo "'" . htmlspecialchars($category) . ">" . htmlspecialchars($metric) . ">" . htmlspecialchars($datapoint) . ">" . htmlspecialchars($key) . "'" . " is an invalid '" . $validation_rule . "'";
                                                                                $data_valid = false;
                                                                            }
                                                                            if (in_array($submission_data[$key], array_keys($food_data["entries"][$associated_user]["foods"])) == false) { // Check to see if this food ID is not in the food database.
                                                                                echo "'" . htmlspecialchars($category) . ">" . htmlspecialchars($metric) . ">" . htmlspecialchars($datapoint) . ">" . htmlspecialchars($key) . "'" . " is an invalid '" . $validation_rule . "'";
                                                                                $data_valid = false;
                                                                            }
                                                                        } else if ($validation_rule == "mealid") { // mealid: A string that combines a date (YYYY-MM-DD) and meal number, where 0 is a snack (1 for breakfast, 2 for lunch, 3 for dinner) separated by a comma. For example, dinner on May 5th would be "2024-05-21,3".
                                                                            if ($submission_data[$key] != preg_replace("/[^0-9,\-]/", '', $submission_data[$key])) { // Check to see if this value contains disallowed characters.
                                                                                echo "'" . htmlspecialchars($category) . ">" . htmlspecialchars($metric) . ">" . htmlspecialchars($datapoint) . ">" . htmlspecialchars($key) . "'" . " is an invalid '" . $validation_rule . "'";
                                                                                $data_valid = false;
                                                                            }
                                                                            $meal_components = explode(",", $submission_data[$key]); // Split this value into its components (date and meal number).
                                                                            if (sizeof($meal_components) > 2) {
                                                                                echo "'" . htmlspecialchars($category) . ">" . htmlspecialchars($metric) . ">" . htmlspecialchars($datapoint) . ">" . htmlspecialchars($key) . "'" . " is an invalid '" . $validation_rule . "'";
                                                                                $data_valid = false;
                                                                            } else if (sizeof($meal_components) < 2) {
                                                                                echo "'" . htmlspecialchars($category) . ">" . htmlspecialchars($metric) . ">" . htmlspecialchars($datapoint) . ">" . htmlspecialchars($key) . "'" . " is an invalid '" . $validation_rule . "'";
                                                                                $data_valid = false;
                                                                            }
                                                                            $date = explode("-", $meal_components[0]);
                                                                            $meal = intval($meal_components[1]);
                                                                            if (!preg_match("/^[0-9]{4}-[0-9]{2}-[0-9]{2}$/", $meal_components[0])) {
                                                                                echo "'" . htmlspecialchars($category) . ">" . htmlspecialchars($metric) . ">" . htmlspecialchars($datapoint) . ">" . htmlspecialchars($key) . "'" . " is an invalid '" . $validation_rule . "'";
                                                                                $data_valid = false;
                                                                            } else if (checkdate($date[1], $date[2], $date[0]) == false) {
                                                                                echo "'" . htmlspecialchars($category) . ">" . htmlspecialchars($metric) . ">" . htmlspecialchars($datapoint) . ">" . htmlspecialchars($key) . "'" . " is an invalid '" . $validation_rule . "'";
                                                                                $data_valid = false;
                                                                            }
                                                                            if ($meal < 0) {
                                                                                echo "'" . htmlspecialchars($category) . ">" . htmlspecialchars($metric) . ">" . htmlspecialchars($datapoint) . ">" . htmlspecialchars($key) . "'" . " is an invalid '" . $validation_rule . "'";
                                                                                $data_valid = false;
                                                                            } else if ($meal > 9) {
                                                                                echo "'" . htmlspecialchars($category) . ">" . htmlspecialchars($metric) . ">" . htmlspecialchars($datapoint) . ">" . htmlspecialchars($key) . "'" . " is an invalid '" . $validation_rule . "'";
                                                                                $data_valid = false;
                                                                            }
                                                                        } else if ($validation_rule == "mood") { // mood: An integer ranging from -5 to 5.
                                                                            if (floatval($submission_data[$key]) != round(floatval($submission_data[$key]))) { // Check to see if this value is not a whole number.
                                                                                echo "'" . htmlspecialchars($category) . ">" . htmlspecialchars($metric) . ">" . htmlspecialchars($datapoint) . ">" . htmlspecialchars($key) . "'" . " is an invalid '" . $validation_rule . "'";
                                                                                $data_valid = false;
                                                                            }
                                                                            $submission_data[$key] = intval($submission_data[$key]); // Convert this value to an integer.
                                                                            if ($submission_data[$key] < -5) {
                                                                                echo "'" . htmlspecialchars($category) . ">" . htmlspecialchars($metric) . ">" . htmlspecialchars($datapoint) . ">" . htmlspecialchars($key) . "'" . " is an invalid '" . $validation_rule . "'";
                                                                                $data_valid = false;
                                                                            } else if ($submission_data[$key] > 5) {
                                                                                echo "'" . htmlspecialchars($category) . ">" . htmlspecialchars($metric) . ">" . htmlspecialchars($datapoint) . ">" . htmlspecialchars($key) . "'" . " is an invalid '" . $validation_rule . "'";
                                                                                $data_valid = false;
                                                                            }
                                                                        }
                                                                    } else {
                                                                        $data_valid = false;
                                                                    }
                                                                    $value_index += 1;
                                                                }
                                                            } else {
                                                                echo "<p>One or more datapoints has an invalid key (out of bounds).</p>";
                                                                $data_valid = false;
                                                            }
                                                        } else {
                                                            echo "<p>One or more datapoints has an invalid key (invalid type).</p>";
                                                            $data_valid = false;
                                                        }
                                                    }
                                                } else {
                                                    echo "<p>The '" . htmlspecialchars($category) . ">" . htmlspecialchars($metric) . "' metric does not exist on this instance.</p>";
                                                    $data_valid = false;
                                                }
                                            }
                                        } else {
                                            echo "<p>The '" . htmlspecialchars($category) . "' category does not exist on this instance.</p>";
                                            $data_valid = false;
                                        }
                                    }
                                    if ($data_valid == true) {
                                        echo "<p><b>Data validated</b></p>";
                                    }

                                    echo "<hr style=\"width:70%;\">";

                                    if ($food_valid == true and $data_valid == true) {
                                        //foreach (array_keys($imported_data[
                                        foreach (array_keys($imported_data["food"]) as $food_id) {
                                            $food_data["entries"][$username]["foods"][$food_id] = $imported_data["food"][$food_id];
                                        }
                                        foreach (array_keys($imported_data["data"]) as $category) {
                                            foreach (array_keys($imported_data["data"][$category]) as $metric) {
                                                foreach (array_keys($imported_data["data"][$category][$metric]) as $datapoint) {
                                                    $health_data[$username][$category][$metric][$datapoint] = $imported_data["data"][$category][$metric][$datapoint];
                                                }
                                            }
                                        }
                                        $health_data = clean_database($health_data); // Remove any empty metrics, categories, and users.

                                        save_food($food_data);
                                        save_healthdata($health_data);
                                        echo "<p>Import success</p>";
                                    } else {
                                        echo "<p>Import failed</p>";
                                    }
                                } else {
                                    echo "<p>The imported data does not contain the expected keys.</p>";
                                }
                            } else {
                                echo "<p>The imported data contains unexpected keys.</p>";
                            }
                        } else {
                            echo "<p>The uploaded file does not contain valid JSON data.</p>";
                            echo "<a class=\"button\" href=\"./dataimport.php\">Back</a>";
                        }
                    } else {
                        echo "<p>The uploaded file must be smaller than 10MB</p>";
                        echo "<a class=\"button\" href=\"./dataimport.php\">Back</a>";
                    }
                } else {
                    echo "<p>The selected service does not have the required permissions to import data.</p>";
                    echo "<p>Please grant this service the following actions:</p>";
                    echo "<ul>";
                    echo "    <li>data-writeall</li>";
                    echo "    <li>foods-add</li>";
                    echo "    <li>foods-edit</li>";
                    echo "</ul>";
                    echo "<a class=\"button\" href=\"./manageservices.php\">Manage Services</a>";
                    echo "<a class=\"button\" href=\"./dataimport.php\">Back</a>";
                }
            } else {
                if (time() - $_GET["confirm"] < 60) {
                    echo "<form enctype=\"multipart/form-data\" method=\"post\">";
                    echo "    <label for=\"service\">Service: </label>";
                    echo "    <select id=\"service\" name=\"service\">";
                    foreach (array_keys($service_data[$username]) as $key) {
                        echo "<option value=\"" . $key. "\" ";
                        if ($_GET["service"] == $key) { echo "selected"; }
                        echo ">" . $service_data[$username][$key]["name"] . " (" . substr($key, 0, 6) . ")</option>";
                    }
                    echo "    </select><br>";
                    echo "    <label for=\"file\">File:</label> <input class=\"button\" type=\"file\" id=\"file\" name=\"file\" accept=\"application/JSON\"><br>";
                    echo "    <input class=\"button\" type=\"submit\">";
                    echo "</form>";
                } else {
                    echo "<div style=\"text-align:left;\">";
                    echo "    <p>Notice: This tool will import food and health data from the file you import in the next step. This process can not be undone, and has the potential to overwrite existing information associated with your account. If your account has data that you don't want to lose, you should export it using the <a href=\"./dataexport.php\">export tool</a> before overwriting it.</p>";
                    echo "    <p>Additionally, this import tool requires a service with the following permissions:</p>";
                    echo "    <ul>";
                    echo "        <li>data-writeall</li>";
                    echo "        <li>foods-add</li>";
                    echo "        <li>foods-edit</li>";
                    echo "    </ul>";
                    echo "    <p>All imported data will be associated with this service, unless the imported data contains a service ID that is already associated with your account. Services can not be transferred between instances.</p>";
                    echo "</div>";
                    echo "<a class='button' href='?confirm=" . time() ."'>Confirm</a>";
                }
            }
            ?>
        </main>
    </body>
</html>
