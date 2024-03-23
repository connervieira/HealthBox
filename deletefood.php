<?php
$food_id = $_GET["food"];
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
    exit();
} else if (strlen($food_id) >= 100) { // Check of the provided food_id is excessively long.
    echo "{'error': {'id': 'invalid_id', 'reason': 'too_long', 'description': 'The submitted food_id is invalid because it is excessively long.'}}";
    exit();
}

if (in_array($associated_user, array_keys($food_data["entries"])) and sizeof(array_keys($food_data["entries"][$associated_user]) > 0) { // Check to see if this ID exists in this users foods.
    if (in_array($food_id, array_keys($food_data["entries"][$associated_user]))) { // Check to see if this ID exists in this users foods.
        unset($food_data["entries"][$associated_user][$food_id]); // Remove this food.
        if (sizeof($food_data["entries"][$associated_user]) <= 0) { // Check to see if this users list of foods is now empty.
            unset($food_data["entries"][$associated_user]); // Remove this user from the food database.
        }
        save_food($food_data);
        echo "{'success': {'description': 'A the \"" . $food_id . "\" food has been removed.'}}";
    } else {
        echo "{'error': {'id': 'invalid_id', 'reason': 'not_found', 'description': 'The submitted food_id does not exist in the database.'}}";
        exit();
    }
    echo "<p>You have no foods in the food database.</p>";
    echo "{'error': {'id': 'invalid_id', 'reason': 'not_found', 'description': 'There are no foods associated with this user in the database.'}}";
    exit();
}
?>
