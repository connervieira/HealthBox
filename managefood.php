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


$selected_food = preg_replace("/[^a-zA-Z0-9 _\-]/", '', $_GET["selected"]); // Sanitize the selected food input from the URL.
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

            <?php
            if (strlen($selected_food) > 0) { // Check to see if there is currently a food selected.
                echo "<p>'$selected_food' is currently selected.</p>";
                echo "<a class=\"button\" role=\"button\" href=\"managefood.php\">Reset</a>";
            }
            ?>



            <hr>
            <h3 id="add">Add Food</h3>
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


                $service_id = preg_replace("/[^a-f0-9]/", '', strtolower($_POST["service"]));
                $food_id = preg_replace("/[^a-zA-Z0-9 _\-]/", '', $_POST["food"]);
                $food_name = $_POST["name"];
                $serving_size = floatval($_POST["serving_size"]);
                $serving_unit = $_POST["serving_unit"];




                $get_data = "?service=" . $service_id . "&id=" . $food_id . "&servingsize=" . $serving_size . "&servingunit=" . $serving_unit;
                foreach (array_keys($food_data["metadata"]["values"]) as $value) { // Iterate through each custom value in the database.
                    if (strlen($_POST["value>" . $value]) > 0) { // Check to see if this value is set.
                        $get_data = $get_data . "&" . $value . "=" . $_POST["value>" . $value];
                    }
                }
                foreach (array_keys($food_data["metadata"]["nutrients"]) as $nutrient) { // Iterate through each nutrient in the database.
                    $nutrient_input = $_POST[$nutrient];
                    if ($nutrient_input != "") { // Check to see if this nutrient has been filled out.
                        $get_data = $get_data . "&" . $nutrient . "=" . $nutrient_input;
                    }
                }

                echo "<p>Request URL: <a href=\"./updatefood.php" . $get_data . "\">" . "./updatefood.php" . $get_data . "</a></p>";

            }
            ?>
            <form method="POST" action="managefood.php#add">
                <label for="service">Service: </label>
                <select id="service" name="service">
                    <?php
                    foreach (array_keys($service_data[$username]) as $key) {
                        echo "<option value=\"" . $key. "\" ";
                        if ($food_data["entries"][$username]["foods"][$selected_food]["service"] == $key) { echo "selected"; }
                        echo ">" . $service_data[$username][$key]["name"] . " (" . substr($key, 0, 6) . ")</option>";
                    }
                    ?>
                </select><br><br>
                <label for="food">Food ID: </label><input type="text" id="food" name="food" max="100" autocomplete="off" pattern="[a-zA-Z0-9 _\-]{1,100}" value="<?php echo $selected_food; ?>" required><br>
                <?php
                foreach (array_keys($food_data["metadata"]["values"]) as $value) {
                    echo '<label for="value>' . $value . '">' . $food_data["metadata"]["values"][$value]["name"] . ': </label>';
                    if ($food_data["metadata"]["values"][$value]["type"] == "str") {
                        echo '<input type="text" id="value>' . $value . '" name="value>' . $value . '" max="100" autocomplete="off" pattern="[a-zA-Z0-9 _\-\'()$%#!=+]{1,100}" value="' . $food_data["entries"][$username]["foods"][$selected_food][$value] . '"';
                        if ($food_data["metadata"]["values"][$value]["required"] == true) {
                            echo " required";
                        }
                        echo '><br>';
                    } else if ($food_data["metadata"]["values"][$value]["type"] == "bool") {
                        echo '<select id="value>' . $value . '" name="value>' . $value . '">';
                        if ($food_data["metadata"]["values"][$value]["required"] == false) {
                            echo '    <option value="">Undefined</option>';
                        }
                        echo '    <option value="true"'; if ($food_data["entries"][$username]["foods"][$selected_food][$value] == true) { echo " selected"; } echo '>True</option>';
                        echo '    <option value="false"'; if (isset($food_data["entries"][$username]["foods"][$selected_food][$value]) and $food_data["entries"][$username]["foods"][$selected_food][$value] == false) { echo " selected"; } echo '>False</option>';
                        echo '</select><br>';
                    } else {
                        echo "<p>The type for this value is invalid</p>";
                    }
                }
                ?>
                <label for="serving_size">Serving Size: </label><input type="number" id="serving_size" name="serving_size" autocomplete="off" min="0" max="10000" value="<?php echo $food_data["entries"][$username]["foods"][$selected_food]["serving"]["size"]; ?>" required><br>
                <label for="serving_unit">Serving Unit: </label><input type="text" id="serving_unit" name="serving_unit" maxlength="20" pattern="[a-z ]{1,20}" value="<?php echo $food_data["entries"][$username]["foods"][$selected_food]["serving"]["unit"]; ?>" required><br><br>
                <?php
                foreach ($food_data["metadata"]["displayed_nutrients"] as $nutrient) {
                    echo "<label for='" . $nutrient . "'>" . $food_data["metadata"]["nutrients"][$nutrient]["name"] . "</label>: <input id='" . $nutrient . "' name='" . $nutrient . "' min='0' value='" . $food_data["entries"][$username]["foods"][$selected_food]["nutrients"][$nutrient] . "' style='width:80px;' type='number' step=\"0.01\"> " . $food_data["metadata"]["nutrients"][$nutrient]["unit"] . "<br>";
                } 
                ?>
                <input class="button" name="submit" id="submit" type="submit" value="Add">
            </form>





            <hr>
            <h3 id="remove">Remove Food</h3>
            <?php
            if ($_POST["submit"] == "Remove") {
                $food_id = preg_replace("/[^a-zA-Z0-9 _\-]/", '', $_POST["food"]);
                $service_id = preg_replace("/[^a-f0-9]/", '', strtolower($_POST["service"]));
                if (in_array($username, array_keys($food_data["entries"]))) { // Check to see if this user exists in the food database.
                    if (in_array($food_id, array_keys($food_data["entries"][$username]["foods"]))) { // Check to see if this ID exists in this users foods.
                        $get_data = "?service=" . $service_id . "&food=" . $food_id;
                        echo "<p>Request URL: <a href='./deletefood.php" . $get_data . "'>" . "./updatefood.php" . $get_data . "</a></p>";
                    } else {
                        echo "<p>The specified food ID does not exist.</p>";
                        echo "<a class=\"button\" role=\"button\" href=\"managefood.php\">Back</a>";
                        exit();
                    }
                } else {
                    echo "<p>You do not have any entries in the food database.</p>";
                    echo "<a class=\"button\" role=\"button\" href=\"managefood.php\">Back</a>";
                    exit();
                }
            }
            ?>
            <form method="POST" action="managefood.php#remove">
                <select id="service" name="service" required>
                    <?php
                    foreach (array_keys($service_data[$username]) as $key) {
                        echo "<option value=\"" . $key. "\" ";
                        if ($food_data["entries"][$username]["foods"][$selected_food]["service"] == $key) { echo "selected"; }
                        echo ">" . $service_data[$username][$key]["name"] . " (" . substr($key, 0, 6) . ")</option>";
                    }
                    ?>
                </select><br>
                <label for="food">Food ID: </label><input type="text" id="food" name="food" max="100" pattern="[a-zA-Z0-9 _\-]{1,100}" autocomplete="off" value="<?php echo $selected_food; ?>" required><br>
                <input class="button" name="submit" id="submit" type="submit" value="Remove">
            </form>




            <hr>
            <h3 id="view">View Food</h3>
            <?php
            if ($_POST["submit"] == "View") {
                $food_id = preg_replace("/[^a-zA-Z0-9 _\-]/", '', $_POST["food"]);
                if (in_array($food_id, array_keys($food_data["entries"][$username]["foods"]))) { // Check to see if this ID exists in this users foods.
                    print_r($food_data["entries"][$username]["foods"][$food_id]);
                } else {
                    echo "<p>The specified food ID does not exist.</p>";
                    echo "<a class=\"button\" role=\"button\" href=\"managefood.php\">Back</a>";
                    exit();
                }
            }
            ?>
            <form method="POST" action="managefood.php#view">
                <label for="food">Food ID: </label><input type="text" id="food" name="food" max="100" pattern="[a-zA-Z0-9 _\-]{1,100}" value="<?php echo $selected_food; ?>" required><br>
                <input class="button" name="submit" id="submit" type="submit" value="View">
            </form>




            <hr>
            <h3 id="list">List Foods</h3>
            <?php
            if (in_array($username, array_keys($food_data["entries"])) and sizeof($food_data["entries"][$username]["foods"]) > 0) {
                $food_ids = array_keys($food_data["entries"][$username]["foods"]);
                asort($food_ids);
                foreach ($food_ids as $food) {
                    echo "<div class=\"buffer\">";
                    echo "<h4>" . $food_data["entries"][$username]["foods"][$food]["brand"] . " " . $food_data["entries"][$username]["foods"][$food]["name"] . "</h4><a style=\"\" href='?selected=" . $food . "'>(" . $food . ")</a></h4>";
                    echo "</div>";
                }
            } else {
                echo "<p>You have no foods registered.</p>";
            }
            ?>
        </main>
    </body>
</html>
