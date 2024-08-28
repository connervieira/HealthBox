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
        <script src="./assets/js/Chart.js"></script> 
    </head>
    <body>
        <main>
            <div class="navbar" role="navigation">
                <a class="button" role="button" href="managedata.php">Back</a>
            </div>
            <h1>HealthBox</h1>
            <h2>Graph Data</h2>
            <noscript><p class='error'>This page requires JavaScript to display graphs.</p></noscript>

            <hr>

            <a class="button" role="button" href="graphdata.php">Reset</a><br><br>
            <?php
            if ($_GET["submit"] == "Continue" or $_GET["submit"] == "Submit") { // Check to see if the form has been submitted.
                $category = $_GET["category"];
                $metric = $_GET["metric"];
                $x_axis = $_GET["x_axis"];
                $y_axis = $_GET["y_axis"];
                $start_time = $_GET["start_time"];
                $end_time = $_GET["end_time"];
                if (in_array($category, array_keys($metrics))) { // Check to see if the category exists.
                    if (in_array($metric, array_keys($metrics[$category]["metrics"]))) { // Check to see if the metric exists.
                        if (isset($health_data[$username][$category][$metric])) { // Check to see if this user has datapoints of this metric.
                            if (in_array($x_axis, $metrics[$category]["metrics"][$metric]["keys"]) and in_array($y_axis, $metrics[$category]["metrics"][$metric]["keys"])) { // Check to make sure both the X-axis and Y-axis are valid values.
                                $start_time = strtotime($start_time);
                                $end_time = strtotime($end_time);
                                if ($start_time < $end_time) { // Check to make sure the start time is before the end time.
                                    $x_labels = array();
                                    $x_datapoints = array();
                                    $y_datapoints = array();
                                    $x_datatype = $metrics[$category]["metrics"][$metric]["validation"][array_search($x_axis, $metrics[$category]["metrics"][$metric]["keys"])];
                                    foreach ($health_data[$username][$category][$metric] as $key => $datapoint) { // Iterate through each datapoint.
                                        if ($start_time <= $key and $key <= $end_time) { // Check to see if this datapoint is between the start and end times.
                                            if (isset($datapoint["data"][$x_axis])) { // Check to make sure the X-axis is set.
                                                array_push($x_datapoints, $datapoint["data"][$x_axis]); // Add this X-axis value to the list of values.
                                                if (in_array($x_datatype, array("datetime", "start_time", "end_time"))) { // Check to see if the x_axis value is time.
                                                    array_push($x_labels, date("Y-m-d H:i:s", $datapoint["data"][$x_axis])); // Convert the timestamp to a date for the label.
                                                } else {
                                                    array_push($x_labels, $datapoint["data"][$x_axis]); // Just use the same X value as the label.
                                                }
                                                if (isset($datapoint["data"][$y_axis])) { // Check to see if the Y-axis is set.
                                                    array_push($y_datapoints, $datapoint["data"][$y_axis]); // Add this Y-axis value to the list of values.
                                                } else {
                                                    array_push($y_datapoints, 0); // Add a zero in place of this Y-axis value.
                                                }
                                            }
                                        }
                                    }
                                    if (sizeof($x_datapoints) == sizeof($y_datapoints) and sizeof($x_labels) == sizeof($x_datapoints)) {
                                        if (sizeof($x_datapoints) > 0) { // Check to make sure there is at least one datapoint collected.
                                            echo "<canvas id=\"graph\"></canvas>";
                                            echo "<script>
                                                const xLabels = " . json_encode($x_labels) . ";
                                                const xValues = " . json_encode($x_datapoints) . ";
                                                const yValues = " . json_encode($y_datapoints) . ";

                                                new Chart(\"graph\", {
                                                    type: \"line\",
                                                        data: {
                                                            labels: xLabels,
                                                            datasets: [{
                                                                label: \"" . $metrics[$category]["metrics"][$metric]["name"] . "\",
                                                                backgroundColor:\"rgba(255,0,0,1.0)\",
                                                                borderColor: \"rgba(255,0,0,0.1)\",
                                                                data: yValues
                                                            }]
                                                        },
                                                        options:{
                                                            scales: {
                                                                x: {
                                                                    title: {
                                                                        display: true,
                                                                        text: '" . $x_axis . "'
                                                                    }
                                                                },
                                                                y: {
                                                                    title: {
                                                                        display: true,
                                                                        text: '" . $y_axis . "'
                                                                    }
                                                                }
                                                            }

                                                        }
                                                    }); 
                                            </script>
                                            ";
                                        } else {
                                            echo "<p>Your query returned 0 datapoints.</p>";
                                        }
                                        exit();
                                    } else {
                                        echo "<p class=\"error\">The number of X-datapoints, Y-datapoints, or X-labels differ. This is a bug, and should never occur.</p>";
                                        exit();
                                    }
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
                        echo "<option value=\"" . $category . "\"";
                        if ($_GET["category"] == $category) { echo " selected"; }
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
                                if ($_GET["metric"] == $metric_id) { echo " selected"; }
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
                        $graphable_values = array("int", "float", "start_time", "end_time", "datetime", "percentage", "temperature", "mood");
                        if (in_array($metric_id, array_keys($metrics[$category_id]["metrics"]))) { // Check to make sure the selected metric actually exists in the database.
                            $final_step = true;
                            echo "<label for='x_axis'>X-Axis</label>: <select id='x_axis' name='x_axis'>";
                            $displayed_keys = 0; // This will keep track of how many keys are displayed for sake of alerting the user when there are less than 2 supported keys.
                            foreach (array_keys($metrics[$category_id]["metrics"][$metric_id]["keys"]) as $key) {
                                if (in_array($metrics[$category_id]["metrics"][$metric_id]["validation"][$key], $graphable_values)) {
                                    $displayed_keys += 1;
                                    $value_name = $metrics[$category_id]["metrics"][$metric_id]["keys"][$key];
                                    echo "<option value=\"" . $value_name . "\"";
                                    if ($metrics[$category_id]["metrics"][$metric_id]["validation"][$key] == "datetime") {
                                        echo " selected";
                                    }
                                    echo ">" . $value_name . "</option>";
                                }
                            }
                            echo "</select><br>";
                            if ($displayed_keys < 2) {
                                echo "<p>The selected metric does not have 2 or more numerical values that can be graphed.</p>";
                                exit();
                            }
                            echo "<label for='y_axis'>Y-Axis</label>: <select id='y_axis' name='y_axis'>";
                            foreach (array_keys($metrics[$category_id]["metrics"][$metric_id]["keys"]) as $key) {
                                $value_name = $metrics[$category_id]["metrics"][$metric_id]["keys"][$key];
                                if (in_array($metrics[$category_id]["metrics"][$metric_id]["validation"][$key], $graphable_values)) {
                                    echo "<option value='$value_name'>$value_name</option>";
                                }
                            }
                            echo "</select><br>";

                            $earliest_datapoint_timestamp = min(array_keys($health_data[$username][$category_id][$metric_id])); // Determine the earliest datapoint for this metric.
                            $latest_datapoint_timestamp = max(array_keys($health_data[$username][$category_id][$metric_id])); // Determine the latest datapoint for this metric.
                            echo "<label for='start_time'>Start Time</label>: <input id='start_time' name='start_time' type='datetime-local' autocomplete='off' value='" . date("Y-m-d\TH:i:s", $earliest_datapoint_timestamp) . "'> UTC<br>";
                            echo "<label for='end_time'>End Time</label>: <input id='end_time' name='end_time' type='datetime-local' autocomplete='off' value='" . date("Y-m-d\TH:i:s", $latest_datapoint_timestamp) . "'> UTC<br>";
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
