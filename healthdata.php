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

    if ($encoded_healthdata == null or !isset($encoded_healthdata) or strval($encoded_healthdata) == "null") {
        echo "<p>Failed to encode health data.</p>";
        exit();
    }
    if (!file_put_contents($healthdata_database_filepath, $encoded_healthdata)) { // Set the contents of the database file to the supplied data.
        echo "<p>Failed to save health data.</p>";
        exit();
    }
}


function delete_datapoint($health_data, $user, $category, $metric, $datapoint) {
    unset($health_data[$user][$category][$metric][$datapoint]); // Remove this datapoint from the health database.
    if (sizeof($health_data[$user][$category][$metric]) == 0) { // Check to see if this metric is now empty.
        unset($health_data[$user][$category][$metric]); // Remove this metric from the health database.
        if (sizeof($health_data[$user][$category]) == 0) { // Check to see if this category is now empty.
            unset($health_data[$user][$category]); // Remove this category from the health database.
            if (sizeof($health_data[$user]) == 0) { // Check to see if this user is now empty.
                unset($health_data[$user]); // Remove this user from the health database.
            }
        }
    }

    return $health_data;
}

?>
