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
if (!in_array("foods-delete", array_keys($services[$associated_user][$service_id]["permissions"]["action"])) or $services[$associated_user][$service_id]["permissions"]["action"]["foods-delete"] == false) { // Check to see if this service has permission to delete foods.
    echo "{'error': {'id': 'invalid_service', 'reason': 'permission_denied', 'description': 'The specified service identifier does not have permission to delete foods.'}}";
    exit();
}

include "./fooddata.php";
include "./healthdata.php";
include "./metrics.php";
$food_data = load_food();
$health_data = load_healthdata();
$metrics = load_metrics();


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


// Remove any datapoints that reference this food ID.
$healthdata_modified = false; // This will be switched to true if the health data has been modified (and needs to be saved).
if (in_array($associated_user, array_keys($health_data))) { // Check to see if this user exists in the health database.
    foreach (array_keys($metrics) as $category) { // Iterate through each category.
        foreach (array_keys($metrics[$category]["metrics"]) as $metric) { // Iterate through each metric.
            $validation_index = 0;
            foreach ($metrics[$category]["metrics"][$metric]["validation"] as $validation) { // Iterate through each validation rule.
                $corresponding_key_name = $metrics[$category]["metrics"][$metric]["keys"][$validation_index];
                if ($validation == "foodid") { // Check to see if this metric makes use of food IDs.
                    if (isset($health_data[$associated_user][$category][$metric])) { // Check to see if this user has any datapoints for this metric.
                        foreach (array_keys($health_data[$associated_user][$category][$metric]) as $datapoint) { // Iterate through each validation rule.
                            if ($health_data[$associated_user][$category][$metric][$datapoint]["data"][$corresponding_key_name] == $food_id) { // Check to see if the food connected to this datapoint is the same as the food we are currently deleting.
                                $healthdata_modified = true;
                                $health_data = delete_datapoint($health_data, $associated_user, $category, $metric, $datapoint); // Erase this datapoint.
                            }
                        }
                    }
                }
                $validation_index = $validation_index + 1;
            }
        }
    }
}
if ($healthdata_modified == true) {
    save_healthdata($health_data);
}

unset($food_data["entries"][$associated_user]["foods"][$food_id]);
save_food($food_data);
echo "{'success': {'description': 'A the \"" . $food_id . "\" food has be deleted.'}}";
?>
