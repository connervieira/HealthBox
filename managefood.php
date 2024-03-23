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

include "./fooddata.php";
include "./servicedata.php";

$food_data = load_food();
$service_data = load_servicedata();

?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>HealthBox - Manage Food</title>
        <link rel="stylesheet" href="./assets/styles/main.css">
        <link rel="stylesheet" href="./assets/fonts/lato/latofonts.css">
    </head>
    <body>
        <main>
            <div class="navbar" role="navigation">
                <a class="button" role="button" href="index.php">Back</a>
            </div>
            <h1>HealthBox</h1>
            <h2>Manage Food</h2>

            <hr>

            <h3>Add Food</h3>
            <?php
            if ($_POST["submit"] == "Add") { // Check to see if the form has been submitted.
                if (in_array($username, array_keys($food_data)) == false) { // Check to see if this user hasn't yet been added to the food database.
                    $food_data[$username] = array();
                }
                if (sizeof($food_data[$username]) >= 10000) { // Check to see if this user already has an excessive amount of foods registered.
                    echo "<p>You have exceeded the maximum number of foods that can be added.</p>";
                    echo "<a class=\"button\" role=\"button\" href=\"managefood.php\">Back</a>";
                    exit();
                }


                $service_id = $_POST["service"];
                $food_id = $_POST["id"];
                $food_name = $_POST["name"];
                $serving_size = floatval($_POST["serving_size"]);
                $serving_unit = $_POST["serving_unit"];



                $get_data = "?service=" . $service_id . "&id=" . $food_id . "&name=" . $food_name . "&servingsize=" . $serving_size . "&servingunit=" . $serving_unit;
                foreach (array_keys($food_data["metadata"]["values"]["nutrients"]) as $nutrient) { // Iterate through each nutrient in the database.
                    $nutrient_input = $_POST[$nutrient];
                    if ($nutrient_input != "") { // Check to see if this nutrient has been filled out.
                        $get_data = $get_data . "&" . $nutrient . "=" . $nutrient_input;
                    }
                }

                echo "<p>Request URL: <a href='./updatefood.php" . $get_data . "'>" . "./updatefood.php" . $get_data . "</a></p>";

            }
            ?>
            <form method="POST">
                <label for="service">Service: </label>
                <select id="service" name="service">
                    <?php
                    foreach (array_keys($service_data[$username]) as $key) {
                        echo "<option value=\"" . $key. "\" ";
                        if ($_GET["service"] == $key) { echo "selected"; }
                        echo ">" . $service_data[$username][$key]["name"] . " (" . substr($key, 0, 6) . ")</option>";
                    }
                    ?>
                </select><br><br>
                <label for="id">ID: </label><input type="text" id="id" name="id" max="100" pattern="[a-zA-Z0-9 _\-]{1,100}" required><br>
                <label for="name">Name: </label><input type="text" id="name" name="name" max="100" pattern="[a-zA-Z0-9 _\-]{1,100}" required><br>
                <label for="serving_size">Serving Size: </label><input type="number" id="serving_size" name="serving_size" min="0" max="10000" required><br>
                <label for="serving_unit">Serving Unit: </label><input type="text" id="serving_unit" name="serving_unit" maxlength="20" pattern="[a-z ]{1,20}" required><br><br>
                <?php
                foreach ($food_data["metadata"]["values"]["displayed_nutrients"] as $nutrient) {
                    echo "<label for='" . $nutrient . "'>" . $food_data["metadata"]["values"]["nutrients"][$nutrient]["name"] . "</label>: <input id='" . $nutrient . "' name='" . $nutrient . "' min='0' style='width:80px;' type='number'> " . $food_data["metadata"]["values"]["nutrients"][$nutrient]["unit"] . "<br>";
                } 
                ?>
                <input class="button" name="submit" id="submit" type="submit" value="Add">
            </form>

            <hr>
            <h3>Remove Service</h3>
            <?php
            if ($_POST["submit"] == "Remove") {
                $id = preg_replace("/[^a-z0-9]/", '', strtolower($_POST["id"]));
                if (in_array($id, array_keys($service_data[$username]))) { // Check to see if this ID exists in this user's registered services.
                    unset($service_data[$username][$id]); // Remove this service.
                    echo "<p>The specified service has been removed.</p>";
                    save_servicedata($service_data);
                } else {
                    echo "<p>The specified service ID does not exist.</p>";
                    echo "<a class=\"button\" role=\"button\" href=\"manageservices.php\">Back</a>";
                    exit();
                }
            }
            ?>
            <form method="POST" action="manageservices.php">
                <label for="id">ID: </label><input type="text" id="id" name="id" max="32" pattern="[a-f0-9]{1,32}" value="<?php echo $_GET["selected"]; ?>" required><br>
                <input class="button" name="submit" id="submit" type="submit" value="Remove">
            </form>

            <hr>

            <h3>View Service</h3>
            <?php
            foreach (array_keys($service_data[$username]) as $service) {
                echo "<div class=\"buffer\">";
                echo "<h4>" . $service_data[$username][$service]["name"] . "</h4>";
                echo "<p><a href='?selected=" . $service . "'>" . $service . "</a></p>";
                echo "</div>";
            }
            ?>
        </main>
    </body>
</html>
