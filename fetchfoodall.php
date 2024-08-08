<?php
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
if (check_permissions_action($service_id, "foods-fetch-all", $services) == false) {
    echo "{'error': {'id': 'invalid_service', 'reason': 'permission_denied', 'description': 'The specified service identifier does not have permission to fetch food nutrient information.'}}";
    exit();
}

include "./fooddata.php";
$food_data = load_food();


foreach (array_keys($food_data["entries"][$associated_user]["foods"]) as $food) { // Iterate over each food in this user's food database.
    unset($food_data["entries"][$associated_user]["foods"][$food]["service"]); // Strip the service information from this food.
}


echo json_encode($food_data["entries"][$associated_user]["foods"]);
?>
