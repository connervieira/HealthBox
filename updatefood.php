<?php
$food_id = $_GET["id"];
$food_name = $_GET["name"];
$serving_size = $_GET["servingsize"];
$serving_unit = $_GET["servingunit"];
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

include "./servicedata.php";
$services = load_servicedata();

$associated_user = find_serviceid($service_id, $services); // Search for the provided service ID in the service database.
if ($associated_user == false) {
    echo "{'error': {'id': 'invalid_service', 'reason': 'not_found', 'description': 'The specified service identifier does not exist.'}}";
    exit();
}

include "./fooddata.php";
$food_data = load_food();



if ($food_id != preg_replace("/[^a-zA-Z0-9\-_]/", '', $food_id)) { // Check to see if the provided food_id contains disallowed values.
    echo "{'error': {'id': 'invalid_id', 'reason': 'disallowed_characters', 'description': 'The submitted food_id is invalid because it contains disallowed characters.'}}";
    echo "<p>The provided food ID contains disallowed characters.</p>";
    exit();
} else if (strlen($food_id) >= 100) { // Check of the provided food_id is excessively long.
    echo "{'error': {'id': 'invalid_id', 'reason': 'too_long', 'description': 'The submitted food_id is invalid because it is excessively long.'}}";
    exit();
}
if ($food_name != preg_replace("/[^a-zA-Z0-9\-_]/", '', $food_name)) { // Check to see if the provided food_name contains disallowed values.
    echo "{'error': {'id': 'invalid_value', 'value': 'name', 'reason': 'disallowed_characters', 'description': 'The submitted food_name is invalid because it contains disallowed characters.'}}";
    exit();
} else if (strlen($food_name) >= 100) { // Check of the provided food_name is excessively long.
    echo "{'error': {'id': 'invalid_value', 'value': 'name', 'reason': 'too_long', 'description': 'The submitted food_name is invalid because it is excessively long.'}}";
    exit();
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
} else if (strlen($food_name) >= 20) { // Check of the provided serving_unit is excessively long.
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
    if (!in_array("foods-edit", array_keys($services[$associated_user][$service_id]["permissions"]["action"])) or $services[$associated_user][$service_id]["permissions"]["action"]["foods-edit"] == false) {
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
$food_data["entries"][$associated_user]["foods"][$food_id]["service"] = $service_id; // This is the service that updated this food.
$food_data["entries"][$associated_user]["foods"][$food_id]["name"] = $food_name;
$food_data["entries"][$associated_user]["foods"][$food_id]["serving"] = array();
$food_data["entries"][$associated_user]["foods"][$food_id]["serving"]["size"] = $serving_size;
$food_data["entries"][$associated_user]["foods"][$food_id]["serving"]["unit"] = $serving_unit;
$food_data["entries"][$associated_user]["foods"][$food_id]["nutrients"] = array(); // Initialize this new food.

foreach (array_keys($food_data["metadata"]["values"]["nutrients"]) as $nutrient) { // Iterate through each nutrient in the database.
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
