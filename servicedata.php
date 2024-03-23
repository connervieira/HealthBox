<?php
include "./config.php";

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


?>
