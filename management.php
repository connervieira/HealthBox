<?php
include "./config.php";

$force_login_redirect = true;
include $healthbox_config["auth"]["provider"]["core"];

if (in_array($username, $healthbox_config["auth"]["access"]["admin"]) == false) {
    if ($healthbox_config["auth"]["access"]["mode"] == "whitelist") {
        if (in_array($username, $healthbox_config["auth"]["access"]["whitelist"]) == false) { // Check to make sure this user is not in blacklist.
            echo "<p>You are not permitted to access this utility.</p>";
            exit();
        }
    } else if ($healthbox_config["auth"]["access"]["mode"] == "blacklist") {
        if (in_array($username, $healthbox_config["auth"]["access"]["blacklist"]) == true) { // Check to make sure this user is not in blacklist.
            echo "<p>You are not permitted to access this utility.</p>";
            exit();
        }
    } else {
        echo "<p>The configured access mode is invalid.</p>";
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>HealthBox - Management</title>
        <link rel="stylesheet" href="./assets/styles/main.css">
        <link rel="stylesheet" href="./assets/fonts/lato/latofonts.css">
    </head>
    <body>
        <main>
            <div class="navbar" role="navigation">
                <a class="button" role="button" href="./index.php">Back</a>
            </div>
            <h1><span style="color:#ff55aa">Health</span><span style="padding:3px;border-radius:10px;background:#ff55aa;">Box</span></h1>
            <h2>Management</h2>

            <hr>
            <h3>Manage Data</h3><br>
            <a class="button" role="button" href="./dataexport.php">Export&nbsp;Data</a>
            <a class="button" role="button" href="./dataimport.php">Import&nbsp;Data</a>
        </main>
    </body>
</html>
