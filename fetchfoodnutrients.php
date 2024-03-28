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
if (!in_array("foods-fetch-nutrients", array_keys($services[$associated_user][$service_id]["permissions"]["action"])) or $services[$associated_user][$service_id]["permissions"]["action"]["foods-fetch-nutrients"] == false) { // Check to see if this service has permission to fetch food nutrients.
    echo "{'error': {'id': 'invalid_service', 'reason': 'permission_denied', 'description': 'The specified service identifier does not have permission to fetch food nutrient information.'}}";
    exit();
}

include "./fooddata.php";
$food_data = load_food();


if ($food_id != preg_replace("/[^a-zA-Z0-9\-_]/", '', $food_id)) { // Check to see if the provided food_id contains disallowed values.
    echo "{'error': {'id': 'invalid_id', 'description': 'The submitted food_id is invalid because it contains disallowed characters.'}}";
    echo "<p>The provided food ID contains disallowed characters.</p>";
    exit();
} else if (strlen($food_id) >= 100) { // Check of the provided food_id is excessively long.
    echo "{'error': {'id': 'invalid_id', 'description': 'The submitted food_id is invalid because it is excessively long.'}}";
    exit();
}

if (!in_array($food_id, array_keys($food_data["entries"][$associated_user]["foods"]))) {
    echo "{'error': {'id': 'invalid_id', 'description': 'The specified food_id does not exist in this user's food database.'}}";
    exit();
}

unset($food_data["entries"][$associated_user]["foods"][$food_id]["service"]);
echo json_encode($food_data["entries"][$associated_user]["foods"][$food_id]);
?>
