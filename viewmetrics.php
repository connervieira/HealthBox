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

include "./metrics.php";

$metrics = load_metrics();


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
            <h2>View Metrics</h2>

            <div style="text-align:left;">
                <hr><h3>Validation</h3>
                <p style="margin-top:0px;">This is a description of each validation rule supported by HealthBox.</p>
                <ul>
                    <li><b>int</b>: A positive whole number</li>
                    <li><b>float</b>: A positive decimal number</li>
                    <li><b>start_time</b>: A Unix timestamp before end_time (integer)</li>
                    <li><b>end_time</b>: A Unix timestamp after start_time (integer)</li>
                    <li><b>datetime</b>: A Unix timestamp (integer)</li>
                    <li><b>short_string</b>: A string under 20 characters (Allowed characters: a-zA-Z0-9 '_-())</li>
                    <li><b>long_string</b>: A string under 150 characters (Allowed characters: a-zA-Z0-9 '_-())</li>
                    <li><b>boolean</b>: A 'true' or 'false' value</li>
                    <li><b>sex</b>: A 1 character string: M, F, or I</li>
                    <li><b>sexuality</b>: A 1 character string: S, G, B, or A</li>
                    <li><b>temperature</b>: A positive or negative float, above -273</li>
                    <li><b>percentage</b>: A decimal number ranged 0 to 1, inclusively</li>
                    <li><b>side</b>: A 1 character string: L or R</li>
                    <li><b>foodid</b>: A food ID that exists in the food database.</li>
                    <li><b>mealid</b>: A string that combines a date (YYYY-MM-DD) and meal number separated by a comma, where 0 is a snack (not associated with a specific meal), 1 for breakfast, 2 for lunch, 3 for dinner, etc. For example, dinner on May 5th would be "2024-05-21,3".</li>
                </ul>


                <hr><h3>Metrics</h3>
                <p style="margin-top:0px;">This is a comprehensive list of all metrics supported by this HealthBox instance.</p>
                <?php
                foreach (array_keys($metrics) as $category) {
                    echo "<h3>" . $category . "</h3>";
                    echo "<div style=\"padding-left:2%;\">";
                    foreach (array_keys($metrics[$category]["metrics"]) as $metric) {
                        echo "<h4 style='margin-bottom:0px;'>" . $metric . "</h4>";
                        echo "<div style=\"padding-left:2%;\">";
                        echo "    <ul style=\"margin-top:0px;\">";
                        echo "        <li>Description: " . $metrics[$category]["metrics"][$metric]["description"] . "</li>";
                        echo "        <li>Keys: " . sizeof($metrics[$category]["metrics"][$metric]["keys"]) . "</li>";
                        echo "        <ul>";
                        for ($i = 0; $i < sizeof($metrics[$category]["metrics"][$metric]["keys"]); $i++) {
                            echo "<li><b>" . $metrics[$category]["metrics"][$metric]["keys"][$i] . "</b>: " . $metrics[$category]["metrics"][$metric]["validation"][$i];
                            if ($metrics[$category]["metrics"][$metric]["requirements"][$i]) {
                                echo " (required)";
                            }
                            echo "</li>";
                        }
                        echo "        </ul>";
                        echo "    </ul>";
                        echo "</div>";
                    }
                    echo "</div>";
                }
                ?>
            </div>
        </main>
    </body>
</html>
