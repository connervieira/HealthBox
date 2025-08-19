<?php
include "./config.php";

include $healthbox_config["auth"]["provider"]["core"];
if ($_SESSION['authid'] !== "dropauth") { // Check to see if the user is not signed in.
    header("Location: ./landing.php");
    exit();
}

if (in_array($username, $healthbox_config["auth"]["access"]["admin"]) == false) {
    if ($healthbox_config["auth"]["access"]["mode"] == "whitelist") {
        if (in_array($username, $healthbox_config["auth"]["access"]["whitelist"]) == false) { // Check to make sure this user is in the whitelist.
            echo "<p>You are not permitted to access this utility.</p>";
            exit();
        }
    } else if ($healthbox_config["auth"]["access"]["mode"] == "blacklist") {
        if (in_array($username, $healthbox_config["auth"]["access"]["blacklist"]) == true) { // Check to make sure this user is not in the blacklist.
            echo "<p>You are not permitted to access this utility.</p>";
            exit();
        }
    } else {
        echo "<p>The configured access mode is invalid.</p>";
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>HealthBox - Dashboard</title>
        <link rel="stylesheet" href="./assets/styles/main.css">
        <link rel="stylesheet" href="./assets/fonts/lato/latofonts.css">
    </head>
    <body>
        <main>
            <div class="navbar" role="navigation">
                <a class="button" role="button" href="<?php echo $healthbox_config["auth"]["provider"]["signout"]; ?>">Logout</a>
                <a class="button" role="button" href="./management.php">Management</a>
                <?php
                if (in_array($username, $healthbox_config["auth"]["access"]["admin"]) == true) { // Check to see if this user is an administrator.
                    echo '<a class="button" role="button" href="./configure.php">Configure</a>';
                }
                ?>
            </div>
            <h1><span style="color:#ff55aa">Health</span><span style="padding:3px;border-radius:10px;background:#ff55aa;">Box</span></h1>

            <h2>Dashboard</h2>
            <hr>
            <a class="button" role="button" href="./manageservices.php">Manage&nbsp;Services</a>
            <a class="button" role="button" href="./managedata.php">Manage&nbsp;Data</a>
            <a class="button" role="button" href="./managefood.php">Manage&nbsp;Food</a>
        </main>
    </body>
</html>
