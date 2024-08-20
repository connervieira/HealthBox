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
            <h2>Manage Data</h2>

            <?php
            if (!in_array($username, array_keys($service_data)) or sizeof($service_data[$username]) <= 0) { // Check to see if this user is not yet in the service database.
                echo "<p>You have no services registered with HealthBox!</p>";
                echo "<p>You need to register a service to submit data.</p>";
                exit();
            }
            ?>
            <hr>

            <h3>Register Data</h3>
            <a class="button" role="button" href="managedata.php">Reset</a><br><br>
            <?php
            if ($_GET["submit"] == "Continue" or $_GET["submit"] == "Submit") { // Check to see if the form has been submitted.
                if (in_array($username, array_keys($health_data)) == false) { // Check to see if this user hasn't yet been added to the health database.
                    $health_data[$username] = array();
                }
                if (sizeof($health_data[$username]) >= 10) { // Check to see if this user already has an excessive amount of health categories logged.
                    echo "<p>You have exceeded the maximum number of health categories that can be recorded.</p>";
                    echo "<a class=\"button\" role=\"button\" href=\"managedata.php\">Back</a>";
                    exit();
                }

                $category = $_GET["category"];
                $metric = $_GET["metric"];
                $service = $_GET["service"];

                if (in_array($category, array_keys($health_data[$username]))) {
                    if (sizeof($health_data[$username][$category]) >= 1000) { // Check to see if this user already has an excessive amount of health metrics logged.
                        echo "<p>You have exceeded the maximum number of health categories that can be recorded.</p>";
                        echo "<a class=\"button\" role=\"button\" href=\"managedata.php\">Back</a>";
                        exit();
                    }
                    if (in_array($metric, array_keys($health_data[$username][$category]))) {
                        if (sizeof($health_data[$username][$category][$metric]) >= 100000) { // Check to see if this user already has an excessive amount of health datapoints logged.
                            echo "<p>You have exceeded the maximum number of health categories that can be recorded.</p>";
                            echo "<a class=\"button\" role=\"button\" href=\"managedata.php\">Back</a>";
                            exit();
                        }
                    }
                }


                if (strlen($category) > 0 and strlen($metric) > 0 and $_GET["submit"] == "Submit") { // Check to see if the completed form has been submitted.
                    $get_data = "?service=" . $service . "&category=" . $category . "&metric=" . $metric;
                    for ($i = 0; $i < sizeof($metrics[$category]["metrics"][$metric]["keys"]); $i++) { // Validate each submitted value for this metric.
                        $key = $metrics[$category]["metrics"][$metric]["keys"][$i];
                        $validation = $metrics[$category]["metrics"][$metric]["validation"][$i];
                        if ($validation == "start_time" or $validation == "end_time" or $validation == "datetime") { // Time-base datatypes.
                            $value_date = $_GET["key-" . $key . "_date"];
                            $value_time = $_GET["key-" . $key . "_time"];
                            $value = strtotime($value_date . " " . $value_time);
                        } else if ($validation == "boolean") { // Boolean values.
                            if (isset($_GET["key-" . $key])) {
                                $value = "t";
                            } else {
                                $value = "f";
                            }
                        } else { // All other datatypes.
                            $value = $_GET["key-" . $key];
                        }
                        $get_data = $get_data . "&key-" . $key . "=" . $value; // Append this key to the GET data.
                    }

                    echo "<p>Request URL: <a href='./submit.php" . $get_data . "'>" . "./submit.php" . $get_data . "</a></p>";
                }

            }
            ?>
            <form method="GET">
                <label for="service">Service: </label>
                <select id="service" name="service">
                    <?php
                    foreach (array_keys($service_data[$username]) as $key) {
                        echo "<option value=\"" . $key. "\" ";
                        if ($_GET["service"] == $key) { echo "selected"; }
                        echo ">" . $service_data[$username][$key]["name"] . " (" . substr($key, 0, 6) . ")</option>";
                    }
                    ?>
                </select><br>
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
                        foreach (array_keys($metrics[$category_id]["metrics"]) as $metric) {
                            echo "<option value='" . $metric . "'";
                            if ($_GET["metric"] == $metric) { echo "selected"; }
                            echo ">" . $metrics[$category_id]["metrics"][$metric]["name"] . "</option>";
                        }
                    } else {
                        echo "<p class='error'>The selected category does not exist.</p>";
                        exit();
                    }
                    echo "</select><br>";

                    if (isset($_GET["metric"])) { // Check to see if a metric has been selected.
                        $metric_id = $_GET["metric"];
                        if (in_array($_GET["metric"], array_keys($metrics[$category_id]["metrics"]))) { // Check to make sure the selected metric actually exists in the database.
                            $keys_displayed = true;
                            echo "<br>";
                            for ($i = 0; $i < sizeof($metrics[$category_id]["metrics"][$metric_id]["keys"]); $i++) { // Validate each submitted value for this metric.
                                $key = $metrics[$category_id]["metrics"][$metric_id]["keys"][$i];
                                $validation = $metrics[$category_id]["metrics"][$metric_id]["validation"][$i];
                                $required = $metrics[$category_id]["metrics"][$metric_id]["requirements"][$i];
                                if ($required == true) { echo "<b>"; } // If this field is required, display it in bold font.
                                if ($validation == "datetime" or $validation == "start_time" or $validation == "end_time") { // Check to see if this value is a timestamp, since these require multiple fields.
                                    echo "<label for='" . $key . "'>" . $key . " (Date)</label>: <input id='key-" . $key . "_date' name='key-" . $key . "_date' type='date' autocomplete='off'";
                                    if ($required == true) { echo " required"; }
                                    echo "><br>";
                                    echo "<label for='" . $key . "'>" . $key . " (Time)</label>: <input id='key-" . $key . "_time' name='key-" . $key . "_time' type='time' autocomplete='off'";
                                    if ($required == true) { echo " required"; }
                                    echo "><br>";
                                } else if ($validation == "sex" or $validation == "sexuality" or $validation == "side") {
                                    echo "<label for='key-" . $key . "'>" . $key . "</label><select id='key-" . $key . "' name='key-" . $key . "'>";
                                    if ($validation == "sex") {
                                        echo "<option value='M'>Male</option>";
                                        echo "<option value='F'>Female</option>";
                                        echo "<option value='I'>Intersex</option>";
                                    } else if ($validation == "sexuality") {
                                        echo "<option value='S'>Straight</option>";
                                        echo "<option value='G'>Gay</option>";
                                        echo "<option value='B'>Bisexual</option>";
                                        echo "<option value='A'>Asexual</option>";
                                    } else if ($validation == "side") {
                                        echo "<option value='L'>Left</option>";
                                        echo "<option value='R'>Right</option>";
                                    }
                                    echo "</select>";
                                } else { // All other input types.
                                    echo "<label for='" . $key . "'>" . $key . "</label>: <input ";
                                    if ($validation == "int") {
                                        echo "min='1' type='number' step='1'";
                                    } else if ($validation == "float") { echo "min='1' type='number'";
                                    } else if ($validation == "temperature") { echo "min='-273' type='number'";
                                    } else if ($validation == "percentage") { echo "min='0' max='1' step='0.01' type='number'";
                                    } else if ($validation == "short_string") { echo "maxlength='20' pattern=\"[a-zA-Z0-9 '_\-\(\)]{0,20}\"";
                                    } else if ($validation == "long_string") { echo "maxlength='150' pattern=\"[a-zA-Z0-9 '_\-\(\)]{0,150}\"";
                                    } else if ($validation == "long_string") { echo "maxlength='150' pattern=\"[a-zA-Z0-9 '_\-\(\)]{0,150}\"";
                                    } else if ($validation == "boolean") { echo "type='checkbox'";
                                    }
                                    if ($required == true) { echo " required "; }
                                    echo " id='key-" . $key . "' name='key-" . $key . "' autocomplete='off'><br>";
                                }
                                if ($required == true) { echo "</b>"; }
                            }
                            echo "<br>";
                        } else {
                            echo "<p class='error'>The selected metric does not exist.</p>";
                            exit();
                        }
                    }
                }
                if ($keys_displayed == true) {
                    echo "<input class=\"button\" name=\"submit\" id=\"submit\" type=\"submit\" value=\"Submit\">";
                } else {
                    echo "<input class=\"button\" name=\"submit\" id=\"submit\" type=\"submit\" value=\"Continue\">";
                }
                ?>
            </form>

            <hr>
            <h3>View Data</h3>

            <?php
            if (in_array($username, array_keys($health_data)) and sizeof($health_data[$username]) > 0) {
                foreach (array_keys($health_data[$username]) as $category) {
                    echo "<div class=\"buffer\">";
                    echo "<h4>" . $metrics[$category]["name"] . "</h4>";
                    foreach (array_keys($health_data[$username][$category]) as $metric) {
                        echo "<div class=\"buffer\">";
                        echo "<h5>" . $metrics[$category]["metrics"][$metric]["name"] . "</h5>";
                        echo "<p><a href='viewdata.php?category=" . $category . "&metric=" . $metric . "'>View " . sizeof($health_data[$username][$category][$metric]) . " Datapoints</a></p>";
                        echo "</div>";
                    }
                    echo "</div>";
                }
            } else {
                echo "<p>There are no health data points associated with your account.</p>";
            }
            ?>
        </main>
    </body>
</html>
