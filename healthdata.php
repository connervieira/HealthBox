<?php
include "./config.php";
include "./metrics.php";

$healthdata_database_filepath = $healthbox_config["database_location"] . "/data.json";

function load_healthdata() {
    global $healthdata_database_filepath;

    if (file_exists($healthdata_database_filepath) == false) {
        file_put_contents($healthdata_database_filepath, "{}"); // Set the contents of the database file to the placeholder data.
    }

    if (file_exists($healthdata_database_filepath) == true) {
        $health_data = json_decode(file_get_contents($healthdata_database_filepath), true);
        return $health_data;
    } else {
        echo "<p>Failed to create health data database file.</p>";
        return false;
    }
}


function save_healthdata($data) {
    global $healthdata_database_filepath;

    $encoded_healthdata = json_encode($data, (JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT));
    file_put_contents($healthdata_database_filepath, $encoded_healthdata); // Set the contents of the database file to the supplied data.
}


?>
