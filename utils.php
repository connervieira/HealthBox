<?php

function lock_file($file_path) {
    $locked_timestamp = round(microtime(true) * 1000);
    if (file_put_contents($file_path . ".lock", strval($locked_timestamp))) {
        return true;
    } else {
        echo "{\"error\": {\"id\": \"system\", \"reason\": \"file_lock_failed\", \"description\": \"The " . basename($file_path) . " file could not be locked for writing.\"}}";
        exit();
        return false;
    }
}
function unlock_file($file_path) {
    if (unlink($file_path . ".lock")) { // Remove the lock file for this file.
        return true;
    } else {
        echo "{\"error\": {\"id\": \"system\", \"reason\": \"file_unlock_failed\", \"description\": \"The " . basename($file_path) . " file could not be unlocked after writing.\"}}";
        exit();
        return false;
    }
}

function is_file_unlocked($file_path) {
    if (file_exists($file_path . ".lock")) {
        $locked_timestamp = intval(file_get_contents(file_exists($file_path . ".lock"))); // Get the timestamp of when this file was locked (in milliseconds).
        if (round(microtime(true) * 1000) - $locked_timestamp > 5000) { // Check to see if it has been more than 5 seconds since the file was locked.
            // This case should generally never happen. In this case, it is likely that a script somewhere has terminated before the file could be properly unlocked.
            return true;
        } else {
            return false;
        }
    } else {
        return true;
    }
}

?>
