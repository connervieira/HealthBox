<?php

$healthbox_config_filepath = "./config.json";

if (is_writable(dirname($healthbox_config_filepath)) == false) {
    echo "<p class=\"error\">The directory '" . realpath(dirname($healthbox_config_filepath)) . "' is not writable to PHP.</p>";
    exit();
}

// Load and initialize the database.
if (file_exists($healthbox_config_filepath) == false) { // Check to see if the database file doesn't exist.
    $healthbox_configuration_database_file = fopen($healthbox_config_filepath, "w") or die("Unable to create configuration database file."); // Create the file.

    $healthbox_config["auth"]["access"]["whitelist"] = [];
    $healthbox_config["auth"]["access"]["blacklist"] = [];
    $healthbox_config["auth"]["access"]["admin"] = ["admin"];
    $healthbox_config["auth"]["access"]["mode"] = "whitelist";
    $healthbox_config["auth"]["provider"]["core"] = "../dropauth/authentication.php";
    $healthbox_config["auth"]["provider"]["signin"] = "../dropauth/signin.php";
    $healthbox_config["auth"]["provider"]["signout"] = "../dropauth/signout.php";
    $healthbox_config["auth"]["provider"]["signup"] = "../dropauth/signup.php";
    $healthbox_config["database_location"] = "/var/www/protected/healthbox/";

    fwrite($healthbox_configuration_database_file, json_encode($healthbox_config, JSON_UNESCAPED_SLASHES)); // Set the contents of the database file to the placeholder configuration.
    fclose($healthbox_configuration_database_file); // Close the database file.
}

if (file_exists($healthbox_config_filepath) == true) { // Check to see if the item database file exists. The database should have been created in the previous step if it didn't already exists.
    $healthbox_config = json_decode(file_get_contents($healthbox_config_filepath), true); // Load the database from the disk.
} else {
    echo "<p class=\"error\">The configuration database failed to load.</p>"; // Inform the user that the database failed to load.
    exit(); // Terminate the script.
}


if (!function_exists("save_config")) { // Check to see if the save_config function needs to be created.
    function save_config($healthbox_config) {
        global $healthbox_config_filepath;
        file_put_contents($healthbox_config_filepath, json_encode($healthbox_config, JSON_UNESCAPED_SLASHES));
    }
}

if (!function_exists("is_json")) { // Check to see if the is_json function needs to be created.
    function is_json($string) {
        json_decode($string);
        return (json_last_error() == JSON_ERROR_NONE);
    }
}

?>
