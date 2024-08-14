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

include "./servicedata.php";
include "./healthdata.php";

$service_data = load_servicedata();
$metrics = load_metrics();
$health_data = load_healthdata();


$selected = $_GET["selected"];
$selected = strtolower($selected);
$selected = preg_replace("/[^a-f0-9]/", '', $selected);
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>HealthBox - Update Service</title>
        <link rel="stylesheet" href="./assets/styles/main.css">
        <link rel="stylesheet" href="./assets/fonts/lato/latofonts.css">
    </head>
    <body>
        <main>
            <div class="navbar" role="navigation">
                <a class="button" role="button" href="manageservices.php">Back</a>
            </div>
            <h1>HealthBox</h1>
            <h2>Update Service</h2>

            <hr>

            <h3>Control Access</h3>
            <?php
            $service_id = $_POST["service"];
            $service_id = strtolower($service_id);
            $service_id = preg_replace("/[^a-f0-9]/", '', $service_id);

            if ($_POST["submit"] == "Update Access") { // Check to see if the form has been submitted.
                $permission = explode(" - ", $_POST["metric"]);
                if (sizeof($permission) == 2) {
                    $category = preg_replace("/[^a-zA-Z0-9 \-_]/", '', $permission[0]); // Sanitize the category.
                    $metric = preg_replace("/[^a-zA-Z0-9 \-_]/", '', $permission[1]); // Sanitize the metric.
                    if (in_array($category, array_keys($metrics))) {
                        if (in_array($metric, array_keys($metrics[$category]["metrics"]))) {
                            // The category-metric pair has been validated.
                        } else {
                            echo "<p>The specified metric does not exist.</p>";
                            exit();
                        }
                    } else {
                        echo "<p>The specified category does not exist.</p>";
                        exit();
                    }
                } else {
                    echo "<p>The specified category/metric pair is invalid.</p>";
                    exit();
                }
                if (in_array($username, array_keys($service_data))) { // Check to see if this user hasn't yet been added to the service database.
                    if (in_array($service_id, array_keys($service_data[$username]))) {
                    } else {
                        echo "<p>The specified service does not exist.</p>";
                    }
                } else {
                    echo "<p>You have no services registered</p>";
                    exit();
                }

                $permission = preg_replace("/[^a-z]/", '', $_POST["permission"]);
                $action = preg_replace("/[^a-z]/", '', $_POST["action"]);

                if (!in_array("access", array_keys($service_data[$username][$service_id]["permissions"]))) { // Check to see if this service does not contain access permission data.
                    $service_data[$username][$service_id]["permissions"]["access"] = array(); // Initialize this service's access permissions.
                }
                if (!in_array($category, array_keys($service_data[$username][$service_id]["permissions"]["access"]))) {
                    $service_data[$username][$service_id]["permissions"]["access"][$category] = array(); // Initialize this category for this service.
                }
                if (!in_array($metric, array_keys($service_data[$username][$service_id]["permissions"]["access"][$category]))) {
                    $service_data[$username][$service_id]["permissions"]["access"][$category][$metric] = array("r" => false, "w" => false); // Initialize this metric for this category.
                }
                if ($permission == "read") {
                    if ($action == "grant") {
                        $service_data[$username][$service_id]["permissions"]["access"][$category][$metric]["r"] = true;
                    } else if ($action == "revoke") {
                        $service_data[$username][$service_id]["permissions"]["access"][$category][$metric]["r"] = false;
                    } else {
                        echo "<p>Unrecognized action.</p>";
                    }
                } else if ($permission == "write") {
                    if ($action == "grant") {
                        $service_data[$username][$service_id]["permissions"]["access"][$category][$metric]["w"] = true;
                    } else if ($action == "revoke") {
                        $service_data[$username][$service_id]["permissions"]["access"][$category][$metric]["w"] = false;
                    } else {
                        echo "<p>Unrecognized action.</p>";
                    }
                }

                if ($service_data[$username][$service_id]["permissions"]["access"][$category][$metric]["w"] == false and $service_data[$username][$service_id]["permissions"]["access"][$category][$metric]["r"] == false) { // Check to see if both actions for this metric are false.
                    unset($service_data[$username][$service_id]["permissions"]["access"][$category][$metric]); // Delete this metric from the permissions data.
                    if (sizeof($service_data[$username][$service_id]["permissions"]["access"][$category]) <= 0) { // Check to see if this category is now empty.
                        unset($service_data[$username][$service_id]["permissions"]["access"][$category]);
                    }
                }

                echo "<pre>";
                print_r($service_data);

                save_servicedata($service_data);
                echo "<p>Successfully updated permissions.</p>";
            }
            ?>
            <form method="POST">
                <label for="service">Service ID: </label><input type="text" id="service" name="service" max="50" pattern="[a-f0-9]{1,50}" value="<?php echo $selected; ?>" required><br>
                <label for="metric">Metric: </label><select id="metric" name="metric" required>
                    <?php
                    foreach (array_keys($metrics) as $category) {
                        foreach (array_keys($metrics[$category]["metrics"]) as $metric) {
                            echo "<option value=\"$category - $metric\">$category - $metric</option>";
                        }
                    }
                    ?>
                </select><br>
                <label for="action">Action: </label><select id="action" name="action" required>
                    <option value="grant">Grant</option>
                    <option value="revoke">Revoke</option>
                </select><br>
                <label for="permission">Permission: </label><select id="permission" name="permission" required>
                    <option value="read">Read</option>
                    <option value="write">Write</option>
                </select><br>
                <input class="button" name="submit" id="submit" type="submit" value="Update Access">
            </form>

            <hr>
            <h3>Control Actions</h3>
            <?php
            $service_id = $_POST["service"];
            $service_id = strtolower($service_id);
            $service_id = preg_replace("/[^a-f0-9]/", '', $service_id);

            if ($_POST["submit"] == "Update Actions") { // Check to see if the form has been submitted.
                if (in_array($username, array_keys($service_data))) { // Check to see if this user hasn't yet been added to the service database.
                    if (in_array($service_id, array_keys($service_data[$username]))) { // Check to make sure the specified service ID exists.
                    } else {
                        echo "<p>The specified service does not exist.</p>";
                    }
                } else {
                    echo "<p>You have no services registered</p>";
                    exit();
                }

                $permission = preg_replace("/[^a-z\-_]/", '', $_POST["permission"]);
                if (!in_array($permission, $available_permissions)) { // Verify that the specified permission is a valid option.
                    echo "<p>The specified permission is invalid.</p>";
                    exit();
                }
                $action = preg_replace("/[^a-z]/", '', $_POST["action"]);
                if ($action !== "grant" and $action !== "revoke") {
                    echo "<p>The specified action is invalid.</p>";
                    exit();
                }

                if (!in_array("action", array_keys($service_data[$username][$service_id]["permissions"]))) { // Check to see if this service does not contain action permission data.
                    $service_data[$username][$service_id]["permissions"]["action"] = array(); // Initialize this service's action permissions.
                }
                if (!in_array($permission, array_keys($service_data[$username][$service_id]["permissions"]["action"]))) {
                    $service_data[$username][$service_id]["permissions"]["action"][$permission] = false; // Initialize the permission for this service.
                }
                if ($action == "grant") {
                    $service_data[$username][$service_id]["permissions"]["action"][$permission] = true;
                } else if ($action == "revoke") {
                    unset($service_data[$username][$service_id]["permissions"]["action"][$permission]);
                }

                save_servicedata($service_data);
                echo "<p>Successfully updated permissions.</p>";
            }
            ?>
            <form method="POST">
                <label for="service">Service ID: </label><input type="text" id="service" name="service" max="50" pattern="[a-f0-9]{1,50}" value="<?php echo $selected; ?>" required><br>
                <label for="permission">Permission: </label><select id="permission" name="permission" required>
                    <?php
                    foreach ($available_permissions as $permission) {
                        echo "<option value=\"" . $permission . "\">" . $permission . "</option>";
                    }
                    ?>
                </select><br>
                <label for="action">Action: </label><select id="action" name="action" required>
                    <option value="grant">Grant</option>
                    <option value="revoke">Revoke</option>
                </select><br>
                <input class="button" name="submit" id="submit" type="submit" value="Update Actions">
            </form>



            <hr>
            <h3>View Permissions</h3>
            <?php
                foreach (array_keys($service_data[$username]) as $service) {
                    echo "<div class='buffer'>";
                    echo "<h4><a href='?selected=$service'>" . $service . "</a></h4>";
                    echo "<h5>Access</h5>";
                    if (in_array("access", array_keys($service_data[$username][$service]["permissions"]))) {
                        foreach (array_keys($service_data[$username][$service]["permissions"]["access"]) as $category) {
                            foreach (array_keys($service_data[$username][$service]["permissions"]["access"][$category]) as $metric) {
                                echo "<p style='margin-top:2px;margin-bottom:2px;'><b>$category>$metric</b> -";
                                if ($service_data[$username][$service]["permissions"]["access"][$category][$metric]["r"] == true) {
                                    echo " read";
                                }
                                if ($service_data[$username][$service]["permissions"]["access"][$category][$metric]["w"] == true) {
                                    echo " write";
                                }
                                echo "</p>";
                            }
                        }
                    }
                    echo "<br><h5>Actions</h5>";
                    if (in_array("action", array_keys($service_data[$username][$service]["permissions"]))) {
                        foreach (array_keys($service_data[$username][$service]["permissions"]["action"]) as $action) {
                            echo "<p style='margin-top:2px;margin-bottom:2px;'><b>$action</b></p>";
                        }
                    }
                    echo "</div>";
                }
            ?>
        </main>
    </body>
</html>
