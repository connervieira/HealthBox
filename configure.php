<?php
include "./config.php";

$force_login_redirect = true;
include $healthbox_config["auth"]["provider"]["core"];

if (in_array($username, $healthbox_config["auth"]["access"]["admin"]) == false) {
    echo "<p>You are not permitted to access this page.</p>";
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>HealthBox - Configure</title>
        <link rel="stylesheet" href="./assets/styles/main.css">
        <link rel="stylesheet" href="./assets/fonts/lato/latofonts.css">
    </head>
    <body>
        <main>
            <div class="navbar" role="navigation">
                <a class="button" role="button" href="index.php">Back</a>
            </div>
            <h1>HealthBox</h1>
            <h2>Configure</h2>
            <hr>

            <?php

            if ($_POST["submit"] == "Submit") {
                $valid = true; // Assume the submitted configuration is valid until an invalid value is found.

                $healthbox_config["auth"]["provider"]["core"] = $_POST["auth>provider>core"];
                if (!file_exists($healthbox_config["auth"]["provider"]["core"])) {
                    $valid = false;
                    echo "<p class='error'>The specified core authentication provider does not appear to exist.</p>";
                }
                $healthbox_config["auth"]["provider"]["signin"] = $_POST["auth>provider>signin"];
                if (!file_exists($healthbox_config["auth"]["provider"]["signin"])) {
                    $valid = false;
                    echo "<p class='error'>The specified sign-in page does not appear to exist.</p>";
                }
                $healthbox_config["auth"]["provider"]["signout"] = $_POST["auth>provider>signout"];
                if (!file_exists($healthbox_config["auth"]["provider"]["signout"])) {
                    $valid = false;
                    echo "<p class='error'>The specified sign-outpage does not appear to exist.</p>";
                }
                $healthbox_config["auth"]["provider"]["signup"] = $_POST["auth>provider>signup"];
                if (!file_exists($healthbox_config["auth"]["provider"]["signup"])) {
                    $valid = false;
                    echo "<p class='error'>The specified sign-up page does not appear to exist.</p>";
                }

                $healthbox_config["auth"]["access"]["admin"] = array();
                if (strlen($_POST["auth>access>admin"]) > 0) {
                    foreach (explode(",", $_POST["auth>access>admin"]) as $user) {
                        if (strlen($user) > 0) {
                            $user = trim($user); // Trim any trailing or leading whitespace from this entry.
                            if ($user != preg_replace("/[^a-zA-Z0-9]/", '', $user)) { // Verify that this entry only contains permitted characters.
                                $valid = false;
                                echo "<p class='error'>The <b>" . htmlspecialchars($user) . "</b> username in the admin list contains disallowed characters.</p>";
                            } else {
                                array_push($healthbox_config["auth"]["access"]["admin"], $user);
                            }
                        }
                    }
                }
                if (!in_array($username, $healthbox_config["auth"]["access"]["admin"])) { // Check to see if the current user is no longer in the admin list.
                    echo "<p class='error'>The user you are currently signed in as (" . htmlspecialchars($username) . ") was not presnted in the updated list of administrators. If you want to remove this user from the administrators, please do so from another account.</p>";
                    $valid = false;
                }

                $healthbox_config["auth"]["access"]["whitelist"] = array();
                if (strlen($_POST["auth>access>whitelist"]) > 0) {
                    foreach (explode(",", $_POST["auth>access>whitelist"]) as $user) {
                        if (strlen($user) > 0) {
                            $user = trim($user); // Trim any trailing or leading whitespace from this entry.
                            if ($user != preg_replace("/[^a-zA-Z0-9]/", '', $user)) { // Verify that this entry only contains permitted characters.
                                $valid = false;
                                echo "<p class='error'>The <b>" . htmlspecialchars($user) . "</b> username in the whitelist contains disallowed characters.</p>";
                            } else {
                                array_push($healthbox_config["auth"]["access"]["whitelist"], $user);
                            }
                        }
                    }
                }

                $healthbox_config["auth"]["access"]["blacklist"] = array();
                if (strlen($_POST["auth>access>blacklist"]) > 0) {
                    foreach (explode(",", $_POST["auth>access>blacklist"]) as $user) {
                        if (strlen($user) > 0) {
                            $user = trim($user); // Trim any trailing or leading whitespace from this entry.
                            if ($user != preg_replace("/[^a-zA-Z0-9]/", '', $user)) { // Verify that this entry only contains permitted characters.
                                $valid = false;
                                echo "<p class='error'>The <b>" . htmlspecialchars($user) . "</b> username in the blacklist contains disallowed characters.</p>";
                            } else {
                                array_push($healthbox_config["auth"]["access"]["blacklist"], $user);
                            }
                        }
                    }
                }

                if ($_POST["auth>access>mode"] == "whitelist" or $_POST["auth>access>mode"] == "blacklist") { // Check to see if the access mode is set to a valid value.
                    $healthbox_config["auth"]["access"]["mode"] = $_POST["auth>access>mode"]; // Update the access mode in the configuration.
                } else {
                    $valid = false;
                    echo "<p class='error'>The supplied access mode (auth>access>mode) value was invalid.</p>";
                }

                if ($valid == true) {
                    save_config($healthbox_config);
                    echo "<p>The configuration was successfully updated.</p>";
                } else {
                    echo "<p class='error'>The configuration was not updated.</p>";
                }
            }




            $formatted_admins = "";
            foreach ($healthbox_config["auth"]["access"]["admin"] as $user) {
                $formatted_admins = $formatted_admins . $user . ",";
            }
            $formatted_admins = substr($formatted_admins, 0, strlen($formatted_admins)-1);

            $formatted_whitelist = "";
            foreach ($healthbox_config["auth"]["access"]["whitelist"] as $user) {
                $formatted_whitelist = $formatted_whitelist . $user . ",";
            }
            $formatted_whitelist = substr($formatted_whitelist, 0, strlen($formatted_whitelist)-1);

            $formatted_blacklist = "";
            foreach ($photoguardian_config["auth"]["access"]["blacklist"] as $user) {
                $formatted_blacklist = $formatted_blacklist . $user . ",";
            }
            $formatted_blacklist = substr($formatted_blacklist, 0, strlen($formatted_blacklist)-1);
            ?>
            <form method="POST">
                <h3>Authentication</h3>
                <h4>Provider</h4>
                <label for="auth>provider>core">Core Provider</label>: <input type="text" name="auth>provider>core" id="auth>provider>core" value="<?php echo $healthbox_config["auth"]["provider"]["core"]; ?>" readonly><br>
                <label for="auth>provider>signin">Sign-In Page</label>: <input type="text" name="auth>provider>signin" id="auth>provider>signin" value="<?php echo $healthbox_config["auth"]["provider"]["signin"]; ?>"><br>
                <label for="auth>provider>signout">Sign-Out Page</label>: <input type="text" name="auth>provider>signout" id="auth>provider>signout" value="<?php echo $healthbox_config["auth"]["provider"]["signout"]; ?>"><br>
                <label for="auth>provider>signup">Sign-Up Page</label>: <input type="text" name="auth>provider>signup" id="auth>provider>signup" value="<?php echo $healthbox_config["auth"]["provider"]["signup"]; ?>"><br>
                <br><h4>Access</h4>
                <label for="auth>access>admin">Admin</label>: <input type="text" name="auth>access>admin" id="auth>access>admin" value="<?php echo $formatted_admins; ?>"><br>
                <label for="auth>access>whitelist">Whitelist</label>: <input type="text" name="auth>access>whitelist" id="auth>access>whitelist" value="<?php echo $formatted_whitelist; ?>"><br>
                <label for="auth>access>blacklist">Blacklist</label>: <input type="text" name="auth>access>blacklist" id="auth>access>blacklist" value="<?php echo $formatted_blacklist; ?>"><br>
                <label for="auth>access>mode">Mode</label>: 
                <select name="auth>access>mode" id="auth>access>mode">
                    <option value="whitelist" <?php if ($healthbox_config["auth"]["access"]["mode"] == "whitelist") { echo "selected"; } ?>>Whitelist</option>
                    <option value="blacklist" <?php if ($healthbox_config["auth"]["access"]["mode"] == "blacklist") { echo "selected"; } ?>>Blacklist</option>
                </select><br>

                <br><input type="submit" id="submit" name="submit" value="Submit">
            </form>
        </main>
    </body>
</html>
