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

include "./healthdata.php";
include "./metrics.php";
include "./servicedata.php";

$health_data = load_healthdata();
$metrics = load_metrics();
$service_data = load_servicedata();


?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>HealthBox - View Data</title>
        <link rel="stylesheet" href="./assets/styles/main.css">
        <link rel="stylesheet" href="./assets/fonts/lato/latofonts.css">
    </head>
    <body>
        <main>
            <div class="navbar" role="navigation">
                <a class="button" role="button" href="managedata.php">Back</a>
            </div>
            <h1>HealthBox</h1>
            <h2>View Data</h2>

            <hr>

            <?php
            $category_id = $_GET["category"];
            $metric_id = $_GET["metric"];

            if ($category_id == "" and $metric_id == "") {
                echo "<p>No metric is currently selected. Click the button below to return to the data management page.</p>";
                echo "<a class='button' href='managedata.php'>Manage Data</a>";
                header("Location: ./managedata.php");
                exit();
            }

            echo "<h3>" . $metrics[$category_id]["name"] . " - " . $metrics[$category_id]["metrics"][$metric_id]["name"] . "</h3>";

            if (in_array($username, array_keys($health_data))) {
                if (in_array($category_id, array_keys($health_data[$username]))) {
                    if (in_array($metric_id, array_keys($health_data[$username][$category_id]))) {

                        $sorted_keys = array_keys($health_data[$username][$category_id][$metric_id]);
                        asort($sorted_keys);
                        if (sizeof($sorted_keys) > 0) {
                            foreach ($sorted_keys as $key) {
                                echo "<div class=\"buffer\">";
                                echo "<h4>" . date("Y-m-d H:i:s", $key) . " UTC <a class='button' href='deletedata.php?category=" . $category_id . "&metric=" . $metric_id . "&datapoint=" . $key . "'>Delete</a></h4>";
                                foreach ($health_data[$username][$category_id][$metric_id][$key]["data"] as $component_key => $component_value) {
                                    echo "<p style='margin-bottom:0px;margin-top:1px;'>" . $component_key . ": " . $component_value . "</p>";
                                }
                                echo "</div>";
                            }
                        } else {
                            echo "<p>There are no datapoints associated with this metric.</p>";
                        }
                    } else {
                        echo "<p>There are no health metrics associated with this category.</p>";
                    }
                } else {
                    echo "<p>There are no health categories associated with your account.</p>";
                }
            } else {
                echo "<p>There is no health data associated with your account.</p>";
            }
            ?>
        </main>
    </body>
</html>
