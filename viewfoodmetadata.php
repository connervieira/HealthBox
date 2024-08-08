<?php
include "./config.php";

$force_login_redirect = true;
include $healthbox_config["auth"]["provider"]["core"];

if (in_array($username, $healthbox_config["auth"]["access"]["admin"]) == false) {
    if ($healthbox_config["auth"]["access"]["mode"] == "whitelist") {
        if (in_array($username, $healthbox_config["auth"]["access"]["whitelist"]) == false) { // Check to make sure this user is in the whitelist.
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

include "./fooddata.php";

$food_data = load_food();


?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>HealthBox - Manage Data</title>
        <link rel="stylesheet" href="./assets/styles/main.css">
        <link rel="stylesheet" href="./assets/fonts/lato/latofonts.css">
    </head>
    <body>
        <main>
            <div class="navbar" role="navigation">
                <a class="button" role="button" href="index.php">Back</a>
            </div>
            <h1>HealthBox</h1>
            <h2>View Food Information</h2>

            <div style="text-align:left;">
                <hr><h3>Values</h3>
                <p style="margin-top:0px;">This is a description of each food data value understood by this instance of HealthBox.</p>
                <ul>
                    <?php
                    foreach (array_keys($food_data["metadata"]["values"]) as $value) {
                        echo "<li><b>" . $value . "</b>: " . $food_data["metadata"]["values"][$value]["name"] . " - " . $food_data["metadata"]["values"][$value]["type"];
                        if ($food_data["metadata"]["values"][$value]["required"] == true) {
                            echo " (required)";
                        }
                        echo "</li>";
                    }
                    ?>
                </ul>


                <hr><h3>Nutrients</h3>
                <p style="margin-top:0px;">This is a comprehensive list of nutrients supported by this instance of HealthBox.</p>
                <?php
                foreach (array_keys($food_data["metadata"]["nutrients"]) as $nutrient) {
                    echo "<h4 style='margin-bottom:0px;'>" . $food_data["metadata"]["nutrients"][$nutrient]["name"]. "</h4>";
                    echo "<p style='margin-top:0px;'><b>" . $nutrient . "</b>: " . $food_data["metadata"]["nutrients"][$nutrient]["unit"] . "</p>";
                }
                ?>
            </div>
        </main>
    </body>
</html>
