<?php
include "./metrics.php";
include "./servicedata.php";
include "./healthdata.php";
include "./fooddata.php";

$category_id = $_GET["category"];
$metric_id = $_GET["metric"];
$datapoint_id = $_GET["datapoint"];
$service_id = $_GET["service"];

// Authenticate using provided service ID.
$service_id = strtolower($service_id);
if ($service_id != preg_replace("/[^a-f0-9]/", '', $service_id)) { // Check to see if the service identifier contains disallowed characters.
    echo "{\"error\": {\"id\": \"invalid_serviceV, \"invalid_metric\": \"disallowed_characters\", \"description\": \"The service identifier contains invalid characters.\"}}";
    exit();
}
if (strlen($service_id) > 100) { // Check to see if the service identifier is excessively long.
    echo "{\"error\": {\"id\": \"invalid_service\", \"invalid_metric\": \"too_long\", \"description\": \"The service identifier is excessively long.\"}}";
    exit();
} else if (strlen($service_id) < 8) {
    echo "{\"error\": {\"id\": \"invalid_service\", \"invalid_metric\": \"too_short\", \"description\": \"The service identifier is too short.\"}}";
    exit();
}
$services = load_servicedata();
$associated_user = find_serviceid($service_id, $services); // Search for the provided service ID in the service database.
if ($associated_user == false) {
    echo "{\"error\": {\"id\": \"invalid_service\", \"invalid_metric\": \"not_found\", \"description\": \"The specified service identifier does not exist.\"}}";
    exit();
}

$food_data = load_food();
$metrics = load_metrics();
$health_data = load_healthdata();


if (in_array($category_id, array_keys($metrics)) == false) { // Check to see if the submitted category ID does not exist in the metrics database.
    echo "{\"error\": {\"id\": \"invalid_category\", \"description\": \"The specified category ID does not exist.\"}}";
    exit();
}
if (in_array($metric_id, array_keys($metrics[$category_id]["metrics"])) == false) { // Check to see if the submitted metric ID does not exist in the metrics database.
    echo "{\"error\": {\"id\": \"invalid_metric\", \"description\": \"The specified metric ID does not exist.\"}}";
    exit();
}


// Verify that the permissions of the specified service ID allow it to write to this metric.
if (check_permissions_action($service_id, "data-writeall", $services) == true) {
    $access = true;
} else { // Otherwise, check to see if this service can access this specific metric.
    $access = check_permissions_access($service_id, $category_id, $metric_id, "w", $services);
}
if ($access == false) {
    echo "{\"error\": {\"id\": \"invalid_service\", \"invalid_metric\": \"permission_denied\", \"description\": \"The specified service identifier does not have permission to write to the specified metric.\"}}";
    exit();
}



if (in_array($associated_user, array_keys($health_data))) { // Check to see if this user exists in the health data.
    if (in_array($category_id, array_keys($health_data[$associated_user]))) { // Check to see if this category exists in this user's health data.
        if (in_array($metric_id, array_keys($health_data[$associated_user][$category_id]))) { // Check to see if this metric exists in this user's health data.
            if (in_array($datapoint_id, array_keys($health_data[$associated_user][$category_id][$metric_id]))) { // Check to see if this datapoint exists in this user's health data.
                $health_data = delete_datapoint($health_data, $associated_user, $category_id, $metric_id, $datapoint_id);
            } else {
                echo "{\"error\": {\"id\": \"not_found\", \"description\": \"The specified datapoint does not exist.\"}}";
                exit();
            }
        } else {
            echo "{\"error\": {\"id\": \"not_found\", \"description\": \"The specified datapoint does not exist.\"}}";
            exit();
        }
    } else {
        echo "{\"error\": {\"id\": \"not_found\", \"description\": \"The specified datapoint does not exist.\"}}";
        exit();
    }
} else {
    echo "{\"error\": {\"id\": \"not_found\", \"description\": \"The specified datapoint does not exist.\"}}";
    exit();
}

save_healthdata($health_data);
echo "{\"success\": {\"description\": \"Datapoint \"$category_id - $metric_id - $datapoint_id\" has been deleted.\"}}";
?>
