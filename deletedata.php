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
        <title>HealthBox - Delete Data</title>
        <link rel="stylesheet" href="./assets/styles/main.css">
        <link rel="stylesheet" href="./assets/fonts/lato/latofonts.css">
    </head>
    <body>
        <main>
            <div class="navbar" role="navigation">
                <a class="button" role="button" href="viewdata.php">Back</a>
            </div>
            <h1>HealthBox</h1>
            <h2>Delete Data</h2>

            <hr>

            <?php

            $category_id = $_GET["category"];
            $metric_id = $_GET["metric"];
            $datapoint_id = $_GET["datapoint"];

            if (in_array($username, array_keys($health_data))) { // Check to see if this username is in the health data.
                if (in_array($category_id, array_keys($health_data[$username]))) { // Check to see if this category is in this user's data.
                    if (in_array($metric_id, array_keys($health_data[$username][$category_id]))) { // Check to see if this metric is in this category.
                        if (in_array($datapoint_id, array_keys($health_data[$username][$category_id][$metric_id]))) { // Check to see if this datapoint is in this metric.
                            if (time() - intval($_GET["confirm"]) < 0) { // Check to see if the confirmation timestamp is in the future.
                                echo "<p>The confirmation timestamp is in the future. If you clicked an external link to get here it is possible someone is attempting to manipulate you into deleting datapoint '<b>" . $category_id . ">" . $metric_id . ">" . $datapoint_id . "</b>'. No data has been affected.</p>";
                            } else if (time() - intval($_GET["confirm"]) < 30) { // Check to see if the confirmation timestamp is less than 30 seconds old.
                                unset($health_data[$username][$category_id][$metric_id][$datapoint_id]); // Remove this datapoint from the health database.
                                if (sizeof($health_data[$username][$category_id][$metric_id]) == 0) { // Check to see if this metric is now empty.
                                    unset($health_data[$username][$category_id][$metric_id]); // Remove this metric from the health database.
                                    if (sizeof($health_data[$username][$category_id]) == 0) { // Check to see if this category is now empty.
                                        unset($health_data[$username][$category_id]); // Remove this category from the health database.
                                        if (sizeof($health_data[$username]) == 0) { // Check to see if this user is now empty.
                                            unset($health_data[$username]); // Remove this user from the health database.
                                        }
                                    }
                                }
                                save_healthdata($health_data); // Write the modified health database to disk.
                                echo "<p>Deleted datapoint '<b>" . $category_id . ">" . $metric_id . ">" . $datapoint_id . "</b>'</p>";
                            } else {
                                echo "<p>Are you sure you would like to delete datapoint '<b>" . $category_id . ">" . $metric_id . ">" . $datapoint_id . "</b>'?</p>";
                                echo "<a class='button' href='?category=" . $category_id . "&metric=" . $metric_id . "&datapoint=" . $datapoint_id . "&confirm=" . time() . "'>Confirm</a>";
                            }
                        } else {
                            echo "<p>The specified datapoint doesn't exist.</p>";
                        }
                    } else {
                        echo "<p>There is no health data associated with this metric in your account.</p>";
                    }
                } else {
                    echo "<p>There is no health data associated with this category in your account.</p>";
                }
            } else {
                echo "<p>There is no health data associated with your account.</p>";
            }
            ?>
        </main>
    </body>
</html>
