<?php
include "./config.php";

include $healthbox_config["auth"]["provider"]["core"];
if ($_SESSION['authid'] == "dropauth") { // Check to see if the user is signed in.
    header("Location: ./index.php");
    exit();
}

?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>HealthBox - Landing</title>
        <link rel="stylesheet" href="./assets/styles/main.css">
        <link rel="stylesheet" href="./assets/fonts/lato/latofonts.css">
    </head>
    <body>
        <main>
            <div class="navbar" role="navigation">
                <a class="button" role="button" href="<?php echo $healthbox_config["auth"]["provider"]["signup"]; ?>">Sign Up</a>
                <a class="button" role="button" href="<?php echo $healthbox_config["auth"]["provider"]["signin"]; ?>?redirect=<?php echo $_SERVER["REQUEST_URI"] ?>">Sign In</a>
            </div>
            <h1><span style="color:#ff55aa">Health</span><span style="padding:3px;border-radius:10px;background:#ff55aa;">Box</span></h1>
            <p>Welcome to HealthBox! You've been directed to this page since you are not currently signed in. To begin using HealthBox, either log in to an existing account, or create a new one.</p>
            <hr>

            <h2>Introduction</h2>
            <p>HealthBox is a privacy and security focused health data management platform designed to centralize information from various applications, devices, and services. HealthBox allows you link compatible products to automatically collect health information, and securely share it with services you authorize.</p>
            <p>Whether you just want to stay organized so you can keep tabs on your health, or you want to create an extensive ecosystem of health data, HealthBox is the most private and independent way to do so.</p>

            <br><h2>The Basics</h2>
            <p>HealthBox works primarily on a system of health metrics. Each metric allows you to submit data about a particular aspect of your health. Common health metrics include calories burned, steps taken, emotional state, weight measurements, and much more. To view all metrics supported by this instance of HealthBox, see the <a href="./viewmetrics.php">View Metrics</a> page.</p>
            <p>Health datapoints are typically submitted to HealthBox using a linked service (application, device, etc), although submissions can also be made manually through the HealthBox web interface. In any case, each submission is associated with a service identifier, which is created by the user, and authorized to read/write to specific health metrics. This gives the user fine control over what information each service has access to, which greatly improves privacy and security. Service identifiers are created and managed through the HealthBox web interface. This service identifier can then be used with a compatible external service to allow it to submit/fetch information from HealthBox.</p>

            <br><h2>Security and Privacy</h2>
            <p>Security and privacy is at the core of HealthBox's design. HealthBox is completely open source, meaning any experienced developer can inspect and audit the code that powers it to independently verify security claims. In fact, users with sufficient technical experience are encouraged to self-host HealthBox on their own hardware for the ultimate peace of mind and control over their information. To learn more about the HealthBox code-base, and how you can host it yourself, see the <a href="https://v0lttech.com/healthbox.php">HealthBox webpage</a> on the V0LT website.</p>
            <p>While HealthBox itself is designed to ensure your privacy is respected, that doesn't mean you should blindly trust all HealthBox instances. Since anyone can host their own instance, it's possible for bad actors to create instances with malicious behavior. As such (just like with any other online service) you should make sure you trust the entity hosting your HealthBox instance before you trust them with your information.</p>

        </main>
    </body>
</html>
