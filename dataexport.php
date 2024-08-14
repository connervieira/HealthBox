<?php
include "./metrics.php";
include "./servicedata.php";
include "./healthdata.php";
include "./fooddata.php";

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

$food_data = load_food();
$health_data = load_healthdata();


$export_data = array();
if (in_array($username, array_keys($food_data["entries"]))) { $export_data["food"] = $food_data["entries"][$username]["foods"];
} else { $export_data["food"] = array(); }

if (in_array($username, array_keys($health_data))) { $export_data["data"] = $health_data[$username];
} else { $export_data["data"] = array(); }


$output_file_path = "/dev/shm/HealthBoxExport_" . $username . "_" . date("Ymd_His") . ".json";

$file = fopen($output_file_path, "w");
fwrite($file, json_encode($export_data));
fclose($file);

header('Content-Type: application/octet-stream');
header('Content-Disposition: attachment; filename=' . basename($output_file_path));
header('Expires: 0');
header('Cache-Control: must-revalidate');
header('Pragma: public');
header('Content-Length: ' . filesize($output_file_path));
readfile($output_file_path);
exit;

?>
