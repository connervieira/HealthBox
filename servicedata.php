<?php
include "./config.php";
$available_permissions = array("foods-add", "foods-edit", "foods-delete", "foods-fetch-all", "foods-fetch-list", "foods-fetch-nutrients", "data-readall", "data-writeall");

$servicedata_database_filepath = $healthbox_config["database_location"] . "/services.json";

function load_servicedata() {
    global $servicedata_database_filepath;

    if (file_exists($servicedata_database_filepath) == false) {
        file_put_contents($servicedata_database_filepath, "{}"); // Set the contents of the database file to placeholder data.
    }

    if (file_exists($servicedata_database_filepath) == true) {
        $service_data = json_decode(file_get_contents($servicedata_database_filepath), true);
        return $service_data;
    } else {
        echo "<p>Failed to create service data database file.</p>";
        return false;
    }
}


function save_servicedata($data) {
    global $servicedata_database_filepath;

    $encoded_servicedata = json_encode($data, (JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT));
    if (!file_put_contents($servicedata_database_filepath, $encoded_servicedata)) { // Set the contents of the database file to the supplied data.
        echo "<p>Failed to save service database</p>";
    }
}


function find_serviceid($service_id_search, $service_data) {
    foreach (array_keys($service_data) as $user) { // Iterate through each user in the service database.
        foreach (array_keys($service_data[$user]) as $key) { // Iterate through each of this user's service IDs.
            if ($key == $service_id_search) {
                return $user;
            }
        }
    }
    return false;
}

function check_permissions_access($service, $category, $metric, $action, $data) {
    $user = find_serviceid($service, $data);
    if ($user !== false) { // Check to make sure an associated user was found.
        if (in_array($category, array_keys($data[$user][$service]["permissions"]["access"]))) { // Check to see if this category is set for this service.
            if (in_array($metric, array_keys($data[$user][$service]["permissions"]["access"][$category]))) { // Check to see if this metric is set for this service.
                if (in_array($action, array_keys($data[$user][$service]["permissions"]["access"][$category][$metric]))) { // Check to see if this action is set for this service.
                    if ($data[$user][$service]["permissions"]["access"][$category][$metric][$action] == true) { // Check to see if this action is granted.
                        return true;
                    } else {
                        return false;
                    }
                } else { // The specified action is not set for this service.
                    return false;
                }
            } else { // The specified metric is not set for this service.
                return false;
            }
        } else { // The specified category is not set for this service.
            return false;
        }
    } else { // The specified service does not exist.
        echo "The specified service ID could not be found.";
        return false;
    }
}


?>
