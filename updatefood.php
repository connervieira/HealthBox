<?php
$food_id = $_GET["id"];
$serving_size = $_GET["servingsize"];
$serving_unit = $_GET["servingunit"];
$service_id = $_GET["service"];
$values = array();

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

include "./servicedata.php";
$services = load_servicedata();

$associated_user = find_serviceid($service_id, $services); // Search for the provided service ID in the service database.
if ($associated_user == false) {
    echo "{'error': {'id': 'invalid_service', 'reason': 'not_found', 'description': 'The specified service identifier does not exist.'}}";
    exit();
}

include "./fooddata.php";
$food_data = load_food();

foreach (array_keys($food_data["metadata"]["values"]) as $value) {
    $values[$value] = $_GET[$value];
}



if ($food_id != preg_replace("/[^a-zA-Z0-9\-_]/", '', $food_id)) { // Check to see if the provided food_id contains disallowed values.
    echo "{'error': {'id': 'invalid_id', 'reason': 'disallowed_characters', 'description': 'The submitted food_id is invalid because it contains disallowed characters.'}}";
    echo "<p>The provided food ID contains disallowed characters.</p>";
    exit();
} else if (strlen($food_id) >= 100) { // Check of the provided food_id is excessively long.
    echo "{'error': {'id': 'invalid_id', 'reason': 'too_long', 'description': 'The submitted food_id is invalid because it is excessively long.'}}";
    exit();
}


foreach (array_keys($food_data["metadata"]["values"]) as $value) {
    if ($food_data["metadata"]["values"][$value]["type"] == "str") {
        $values[$value] = strval($values[$value]);
        if (strlen($values[$value]) == 0) { // Check to see if this value is not set.
            if ($food_data["metadata"]["values"][$value]["required"] == true) { // Check to see if this value is required.
                echo "{'error': {'id': 'missing_required_data', 'value': '" . $value . "', 'description': 'The submission is missing a required value for this food.'}}";
                exit();
            } else { // Otherwise, this value is not required.
                unset($values[$value]);
            }
        } else { // This value is set.
            if ($values[$value] != preg_replace("/[^a-zA-Z0-9\-_]/", '', $values[$value])) { // Check to see if the provided value contains disallowed values.
                echo "{'error': {'id': 'invalid_value', 'value': '" . $value . "', 'reason': 'disallowed_characters', 'description': 'The submitted " . $value . " is invalid because it contains disallowed characters.'}}";
                exit();
            } else if (strlen($values[$value]) >= 100) { // Check of the provided value is excessively long.
                echo "{'error': {'id': 'invalid_value', 'value': '" . $value . "', 'reason': 'too_long', 'description': 'The submitted " . $value . " is invalid because it is excessively long.'}}";
                exit();
            }
        }
    } else if ($food_data["metadata"]["values"][$value]["type"] == "bool") {
        $values[$value] = strtolower(strval($values[$value]));
        if (strlen($values[$value]) == 0) { // Check to see if this value is not set.
            if ($food_data["metadata"]["values"][$value]["required"] == true) { // Check to see if this value is required.
                echo "{'error': {'id': 'missing_required_data', 'value': '" . $value . "', 'description': 'The submission is missing a required value for this food.'}}";
                exit();
            } else { // Otherwise, this value is not required.
                unset($values[$value]);
            }
        } else { // This value is set.
            if ($values[$value] == "t" or $values[$value] == "y" or $values[$value] == "1") { // Check to see if this value represents true.
                $values[$value] = true;
            } else if ($values[$value] == "f" or $values[$value] == "n" or $values[$value] == "0") { // Check to see if this value represents false.
                $values[$value] = false;
            } else {
                echo "{'error': {'id': 'invalid_value', 'value': '" . $value . "', 'description': 'The submitted '' is invalid because it is not a boolean.'}}";
                exit();
            }
        }
    }
}


$serving_size = floatval($serving_size);
if ($serving_size <= 0) {
    echo "{'error': {'id': 'invalid_servingsize', 'description': 'The submitted serving_size is invalid because it is not a positive number.'}}";
    exit();
} else if ($serving_size > 10000) {
    echo "{'error': {'id': 'invalid_servingsize', 'description': 'The submitted serving_size is invalid because it is excessively large.'}}";
    exit();
}
if ($serving_unit != preg_replace("/[^a-z ]/", '', $serving_unit)) { // Check to see if the provided serving_unit contains disallowed values.
    echo "{'error': {'id': 'invalid_servingunit', 'description': 'The submitted serving_unit is invalid because it contains disallowed characters.'}}";
    exit();
} else if (strlen($serving_unit) >= 20) { // Check of the provided serving_unit is excessively long.
    echo "{'error': {'id': 'invalid_servingunit', 'description': 'The submitted serving_unit is invalid because it is excessively long.'}}";
    exit();
}


if (!in_array($associated_user, array_keys($food_data["entries"]))) { // Check to see if this user doesn't yet exist in the food database.
    $food_data["entries"][$associated_user] = array();
}
if (!in_array("foods", array_keys($food_data["entries"][$associated_user]))) { // Check to see if this user doesn't yet exist in the food database.
    $food_data["entries"][$associated_user]["foods"] = array();
}

if (in_array($food_id, array_keys($food_data["entries"][$associated_user]["foods"]))) { // Check to see if this food already exists.
    // Check to see if this service has permission to overwrite foods.
    if (!in_array("permissions", array_keys($services[$associated_user][$service_id])) or !in_array("action", array_keys($services[$associated_user][$service_id]["permissions"])) or !in_array("foods-edit", array_keys($services[$associated_user][$service_id]["permissions"]["action"])) or $services[$associated_user][$service_id]["permissions"]["action"]["foods-edit"] == false) {
        echo "{'error': {'id': 'invalid_service', 'reason': 'permission_denied', 'description': 'The specified service identifier does not have permission to edit foods.'}}";
        exit();
    }
} else {
    // Check to see if this service has permission to create new foods.
    if (!in_array("foods-add", array_keys($services[$associated_user][$service_id]["permissions"]["action"])) or $services[$associated_user][$service_id]["permissions"]["action"]["foods-add"] == false) {
        echo "{'error': {'id': 'invalid_service', 'reason': 'permission_denied', 'description': 'The specified service identifier does not have permission to add foods.'}}";
        exit();
    }
}


$food_data["entries"][$associated_user]["foods"][$food_id] = array(); // Initialize this new food.
foreach(array_keys($values) as $value) {
    $food_data["entries"][$associated_user]["foods"][$food_id][$value] = $values[$value]; // This is the service that updated this food.
}
$food_data["entries"][$associated_user]["foods"][$food_id]["service"] = $service_id; // This is the service that last updated this food.
$food_data["entries"][$associated_user]["foods"][$food_id]["serving"] = array();
$food_data["entries"][$associated_user]["foods"][$food_id]["serving"]["size"] = $serving_size;
$food_data["entries"][$associated_user]["foods"][$food_id]["serving"]["unit"] = $serving_unit;
$food_data["entries"][$associated_user]["foods"][$food_id]["nutrients"] = array(); // Initialize this new food.


foreach (array_keys($food_data["metadata"]["nutrients"]) as $nutrient) { // Iterate through each nutrient in the database.
    $nutrient_input = $_GET[$nutrient];
    if ($nutrient_input != "") { // Check to see if this nutrient has been filled out.
        $nutrient_input = floatval($nutrient_input);
        if ($nutrient_input < 0) {
            echo "{'error': {'id': 'invalid_value', 'value': '" . $nutrient . "', 'description': 'The submitted value for '" . $nutrient . "' is invalid because it is a negative number.'}}";
        }
        $food_data["entries"][$associated_user]["foods"][$food_id]["nutrients"][$nutrient] = $nutrient_input;
    }
}

save_food($food_data);
echo "{'success': {'description': 'A new food has been added as \"" . $food_id . "\".'}}";
?>
