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
include "./fooddata.php";

$service_data = load_servicedata();
$health_data = load_healthdata();
$food_data = load_food();

?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>HealthBox - Manage Services</title>
        <link rel="stylesheet" href="./assets/styles/main.css">
        <link rel="stylesheet" href="./assets/fonts/lato/latofonts.css">
    </head>
    <body>
        <main>
            <div class="navbar" role="navigation">
                <a class="button" role="button" href="index.php">Back</a>
            </div>
            <h1>HealthBox</h1>
            <h2>Manage Services</h2>

            <hr>

            <h3>Register Service</h3>
            <?php
            if ($_POST["submit"] == "Register") { // Check to see if the form has been submitted.
                if (in_array($username, array_keys($service_data)) == false) { // Check to see if this user hasn't yet been added to the service database.
                    $service_data[$username] = array();
                }
                if (sizeof($service_data[$username]) >= 100) { // Check to see if this user already has an excessive amount of services registered.
                    echo "<p>You have exceeded the maximum number of services that can be registered.</p>";
                    echo "<a class=\"button\" role=\"button\" href=\"manageservices.php\">Back</a>";
                    exit();
                }

                $existing_keys = array(); // This is a placeholder array that will hold all keys currently registered in the service database.
                foreach ($service_data as $user) { // Iterate through each user in the service database.
                    foreach (array_keys($user) as $key) { // Iterate over each of this user's keys.
                        array_push($existing_keys, $key); // Add this key to the complete list of keys.
                    }
                }

                $new_key = bin2hex(random_bytes(12));
                $attempts = 0; // This will keep track of how many attempts at making a new key have been made.
                while (in_array($new_key, $existing_keys)) { // Endlessly generate keys until we find one that isn't already in the existing list of keys.
                    if ($attempts >= 10000) { // Check to see if an excessive number of attempts have been made.
                        echo "<p>A new key could not be generated.</p>";
                        echo "<a class=\"button\" role=\"button\" href=\"manageservices.php\">Back</a>";
                        exit();
                    }
                    $new_key = bin2hex(random_bytes(12));
                    $attempts++; // Increment the attempts counter.
                }

                $service_name = $_POST["name"];
                if ($service_name != preg_replace("/[^a-zA-Z0-9 '_\-]/", '', $service_name)) { // Check to see if the provided service name comtains disallowed values.
                    echo "<p>The provided service name contains disallowed characters.</p>";
                    echo "<a class=\"button\" role=\"button\" href=\"manageservices.php\">Back</a>";
                    exit();
                } else if (strlen($service_name) >= 100) { // Check of the provided service name is excessively long.
                    echo "<p>The provided service name is excessively long.</p>";
                    echo "<a class=\"button\" role=\"button\" href=\"manageservices.php\">Back</a>";
                    exit();
                }
                $service_name = preg_replace("/[^a-zA-Z0-9 '_\-]/", '', $_POST["name"]);
                $service_data[$username][$new_key] = array(); // Initialize this new service.
                $service_data[$username][$new_key]["name"] = $service_name; // Add the specified name of this service.
                $service_data[$username][$new_key]["permissions"] = array(); // Initialize this service's permissions.
                $service_data[$username][$new_key]["permissions"]["access"] = array();
                $service_data[$username][$new_key]["permissions"]["actions"] = array();

                save_servicedata($service_data);
                echo "<p>A new service has been registered with the key '" . $new_key . "'</p>";
            }
            ?>
            <form method="POST">
                <label for="name">Name: </label><input type="text" id="name" name="name" max="100" autocomplete="off" pattern="[a-zA-Z0-9 '_\-]{1,100}" required><br>
                <input class="button" name="submit" id="submit" type="submit" value="Register">
            </form>

            <hr>
            <h3>Remove Service</h3>
            <?php
            if ($_POST["submit"] == "Remove") {
                $service_id = preg_replace("/[^a-z0-9]/", '', strtolower($_POST["id"]));
                if (in_array($service_id, array_keys($service_data[$username]))) { // Check to see if this ID exists in this user's registered services.

                    // Remove any datapoints associated with this service in the health database.
                    foreach (array_keys($health_data) as $user) { // Iterate through each user in the health database.
                        foreach (array_keys($health_data[$user]) as $category) { // Iterate through each category in this user's health data.
                            foreach (array_keys($health_data[$user][$category]) as $metric) { // Iterate through each metric in this category.
                                foreach (array_keys($health_data[$user][$category][$metric]) as $datapoint) { // Iterate through each metric in this category.
                                    if ($health_data[$user][$category][$metric][$datapoint]["service"] == $service_id) {
                                        $health_data = delete_datapoint($health_data, $user, $category, $metric, $datapoint);
                                    }
                                }
                            }
                        }
                    }

                    // Remove any foods associated with the service.
                    foreach (array_keys($food_data["entries"][$username]["foods"]) as $food) {
                        if ($food_data["entries"][$username]["foods"][$food]["service"] == $service_id) {
                            unset($food_data["entries"][$username]["foods"][$food]);
                        }
                    }
                    unset($service_data[$username][$service_id]); // Remove this service from the service database.


                    save_healthdata($health_data);
                    save_food($food_data);
                    save_servicedata($service_data);
                    echo "<p>The specified service has been removed.</p>";
                } else {
                    echo "<p>The specified service ID does not exist.</p>";
                    echo "<a class=\"button\" role=\"button\" href=\"manageservices.php\">Back</a>";
                    exit();
                }
            }
            ?>
            <form method="POST" action="manageservices.php">
                <label for="id">ID: </label><input type="text" id="id" name="id" max="32" autocomplete="off" pattern="[a-f0-9]{1,32}" value="<?php echo $_GET["selected"]; ?>" required><br>
                <input class="button" name="submit" id="submit" type="submit" value="Remove">
            </form>

            <hr>

            <h3>View Service</h3>
            <?php
            if (sizeof($service_data[$username]) > 0) {
                foreach (array_keys($service_data[$username]) as $service) {
                    echo "<div class=\"buffer\">";
                    echo "<h4>" . $service_data[$username][$service]["name"] . " <a href='updateservice.php?selected=$service' class='button'>Update</a></h4>";
                    echo "<p><a href='?selected=" . $service . "'>" . $service . "</a></p>";
                    echo "</div>";
                }
            } else {
                echo "<p>You have no services registered.</p>";
            }
            ?>
        </main>
    </body>
</html>
