<?php
include "./metrics.php";
include "./servicedata.php";
include "./healthdata.php";
include "./fooddata.php";

$category_id = $_GET["category"];
$metric_id = $_GET["metric"];
$service_id = $_GET["service"];

$start_time = $_GET["start"];
$end_time = $_GET["end"];

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


// Verify that the permissions of the specified service ID allow it to write to this metric.
if (in_array("data-readall", array_keys($services[$associated_user][$service_id]["permissions"]["action"])) and $services[$associated_user][$service_id]["permissions"]["action"]["data-readall"] == true) { // Check to see if this service has the override permission to read all metrics.
    $access = true;
} else { // Otherwise, check to see if this service has permission to read this specific metric.
    $access = check_permissions_access($service_id, $category_id, $metric_id, "r", $services);
}
if ($access == false) {
    echo "{'error': {'id': 'permission_denied', 'description': 'The specified service does not have permission to read the specified metric.'}}";
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


$start_time = intval($start_time);
$end_time = intval($end_time);

if ($start_time == 0 and $end_time == 0) {
    $datapoints_to_display = array_keys($health_data[$associated_user][$category_id][$metric_id]);
} else {
    if ($start_time <= 0) {
        echo "{'error': {'id': 'invalid_start_time', 'description': 'The start time must be a positive number.'}}";
    }
    if ($end_time <= $start_time) {
        echo "{'error': {'id': 'invalid_end_time', 'description': 'The end time must occur after the start time.'}}";
    }

    $all_datapoint_keys = array_keys($health_data[$associated_user][$category_id][$metric_id]);
    $datapoints_to_display = array();
    foreach ($all_datapoint_keys as $datapoint_key) {
        if ($datapoint_key >= $start_time and $datapoint_key <= $end_time) { // Check to see if this datapoint is between the start and end times.
            array_push($datapoints_to_display, $datapoint_key);
        }
    }
}

$display = array(); // This is the actual data that will be returned.
foreach ($datapoints_to_display as $key) { // Iterate through each datapoint that needs to be displayed.
    $display[$key] = $health_data[$associated_user][$category_id][$metric_id][$key]; // Add this datapoint to the data to display.
}


echo json_encode($display); // Return the data.
?>
