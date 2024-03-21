<?php
include "./config.php";

$servicedata_database_filepath = $healthbox_config["database_location"] . "/services.json";

function load_servicedata() {
    global $servicedata_database_filepath;

    if (file_exists($servicedata_database_filepath) == false) {
        $servicedata_raw = "{}";

        $encoded_servicedata = json_encode(json_decode($servicedata_raw, true), (JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT));
        if ($encoded_servicedata == "null") {
            echo "<p>Failed to initialize service data. It is possible the JSON string is malformed.</p>";
            return false;
        }
        file_put_contents($servicedata_database_filepath, $encoded_servicedata); // Set the contents of the database file to the placeholder data.
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


?>
