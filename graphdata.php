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
        <title>HealthBox - Graph Data</title>
        <link rel="stylesheet" href="./assets/styles/main.css">
        <link rel="stylesheet" href="./assets/fonts/lato/latofonts.css">
    </head>
    <body>
        <main>
            <div class="navbar" role="navigation">
                <a class="button" role="button" href="managedata.php">Back</a>
            </div>
            <h1>HealthBox</h1>
            <h2>Graph Data</h2>

            <hr>

            <h3>Select Metric</h3>
            <a class="button" role="button" href="graphdata.php">Reset</a><br><br>
            <?php
            if ($_GET["submit"] == "Continue" or $_GET["submit"] == "Submit") { // Check to see if the form has been submitted.
                $category = $_GET["category"];
                $metric = $_GET["metric"];
                $start_time = $_GET["start_time"];
                $end_time = $_GET["end_time"];
                if () { // TODO: Check to see if the category exists.
                    if () { // TODO: Check to see if the metric exists.
                        if () { // TODO: Check to see if this user has datapoints of this metric.
                            if () { // TODO: Check to make sure the start time is before the end time.
                                // TODO: Collect all datapoints between the start and end times.
                                if () { // TODO: Check to make sure there is at least one datapoint collected.
                                    // TODO: Organize the datapoints.
                                }
                            }
                        }
                    }
                }
            }
            ?>
            <form method="GET">
                <label for="category">Category: </label>
                <select id="category" name="category" <?php if (isset($_GET["category"])) { echo " readonly";}?>>
                    <?php
                    foreach (array_keys($metrics) as $category) {
                        echo "<option value=\"" . $category . "\" ";
                        if ($_GET["category"] == $category) { echo "selected"; }
                        echo ">" . $metrics[$category]["name"] . "</option>";
                    }
                    ?>
                </select><br>
                <?php
                if (isset($_GET["category"])) { // Check to see if a category has been selected.
                    $category_id = $_GET["category"];
                    echo '<label for="metric">Metric: </label><select id="metric" name="metric">';
                    if (in_array($category_id, array_keys($metrics))) { // Check to make sure the selected category actually exists in the database.
                        foreach (array_keys($metrics[$category_id]["metrics"]) as $metric_id) {
                            if (isset($health_data[$username][$category_id][$metric_id]) and sizeof($health_data[$username][$category_id][$metric_id]) > 0) { // Check to see if there is at least one datapoint associated with this metric.
                                echo "<option value='" . $metric_id . "'";
                                if ($_GET["metric"] == $metric) { echo "selected"; }
                                echo ">" . $metrics[$category_id]["metrics"][$metric_id]["name"] . "</option>";
                            }
                        }
                    } else {
                        echo "<p class='error'>The selected category does not exist.</p>";
                        exit();
                    }
                    echo "</select><br>";

                    if (isset($_GET["metric"])) { // Check to see if a metric has been selected.
                        $metric_id = $_GET["metric"];
                        if (in_array($_GET["metric"], array_keys($metrics[$category_id]["metrics"]))) { // Check to make sure the selected metric actually exists in the database.
                            $final_step = true;
                            echo "<label for='start_time'>Start Time</label>: <input id='start_time' name='start_time' type='datetime-local' autocomplete='off'><br>";
                            echo "<label for='end_time'>End Time</label>: <input id='end_time' name='end_time' type='datetime-local' autocomplete='off'><br>";
                        } else {
                            echo "<p class='error'>The selected metric does not exist.</p>";
                            exit();
                        }
                    }
                }
                if ($final_step == true) {
                    echo "<input class=\"button\" name=\"submit\" id=\"submit\" type=\"submit\" value=\"Submit\">";
                } else {
                    echo "<input class=\"button\" name=\"submit\" id=\"submit\" type=\"submit\" value=\"Continue\">";
                }
                ?>
            </form>
        </main>
    </body>
</html>
