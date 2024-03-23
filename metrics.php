<?php

include "./config.php";
include "./food.php";

$metrics_database_filepath = $healthbox_config["database_location"] . "/metrics.json";

if (!function_exists("load_metrics")) { // Check to see if the 'load_metrics' function hasn't yet been created.
    function load_metrics() {
        global $metrics_database_filepath;

        if (file_exists($metrics_database_filepath) == false) {
            $metrics_raw_data = '{
                "physical": {
                    "name": "Physical",
                    "metrics": {
                        "steps": {"name": "Steps", "description": "How many steps are taken", "validation": ["int", "start_time", "end_time"], "requirements": [true, true, true], "keys": ["steps_count", "start_time", "end_time"]},
                        "active_calories": {"name": "Active Calories", "description": "Calories that have been burned above the resting baseline.", "validation": ["float", "start_time", "end_time"], "requirements": [true, true, true], "keys": ["calories", "start_time", "end_time"]},
                        "resting_calories": {"name": "Resting Calories", "description": "How much energy is burned while resting", "validation": ["float", "start_time", "end_time"], "requirements": [true, true, true], "keys": ["calories", "start_time", "end_time"]},
                        "distance_pedestrian": {"name": "On Foot Distance", "description": "Kilometers traveled through walking, running, or otherwise on foot", "validation": ["float", "start_time", "end_time"], "requirements": [true, true, true], "keys": ["distance", "start_time", "end_time"]},
                        "distance_wheelchair": {"name": "Wheelchair Distance", "description": "Kilometers traveled by manual wheel chair movement", "validation": ["float", "start_time", "end_time", "vehicleid"], "requirements": [true, true, true, false], "keys": ["distance", "start_time", "end_time", "vehicleid"]},
                        "distance_cycling_manual": {"name": "Cycling Distance", "description": "Kilometers traveled by manual bicycle pedaling", "validation": ["float", "start_time", "end_time", "vehicleid"], "requirements": [true, true, true, false], "keys": ["distance", "start_time", "end_time", "vehicleid"]},
                        "distance_cycling_assisted": {"name": "Cycling Distance Assisted", "description": "Kilometers traveled by bicycle with powered assistance", "validation": ["float", "start_time", "end_time", "short_string", "vehicleid"], "requirements": [true, true, true, false, false], "keys": ["distance", "start_time", "end_time", "assistance_level", "vehicleid"]},
                        "distance_vehicle": {"name": "Vehicle Distance", "description": "Kilometers traveled by motor vehicle", "validation": ["float", "start_time", "end_time", "vehicleid"], "requirements": [true, true, true, false], "keys": ["distance", "start_time", "end_time", "vehicleid"]},
                        "minutes_active": {"name": "Active Minutes", "description": "Minutes that are spent moving around, being active", "validation": ["start_time", "end_time"], "requirements": [true, true], "keys": ["start_time", "end_time"]},
                        "minutes_resting": {"name": "Resting Minutes", "description": "Minutes that are spent dormant, not being active", "validation": ["start_time", "end_time"], "requirements": [true, true], "keys": ["start_time", "end_time"]},
                        "minutes_standing": {"name": "Minutes Standing", "description": "Minutes spent standing", "validation": ["start_time", "end_time"], "requirements": [true, true], "keys": ["start_time", "end_time"]},
                        "minutes_sitting": {"name": "Minutes Sitting", "description": "Minutes spent sitting", "validation": ["start_time", "end_time"], "requirements": [true, true], "keys": ["start_time", "end_time"]},
                        "wheelchair_pushes": {"name": "Wheelchair Pushes", "description": "How many times the wheels on a wheelchair are pushed", "validation": ["int", "start_time", "end_time"], "requirements": [true, true, true], "keys": ["pushes", "start_time", "end_time"]},
                        "food": {"name": "Food", "description": "Consuming an amount of a given food", "validation": ["float", "datetime", "mealid", "foodid"], "requirements": [true, false, true, true], "keys": ["servings", "time", "mealid", "foodid"]},
                        "sport": {"name": "Sport", "description": "A physical sport played, usually competitively", "validation": ["short_string", "start_time", "end_time"], "requirements": [true, true, true], "keys": ["sport_name", "start_time", "end_time"]}
                    }
                },
                "mental": {
                    "name": "Mental",
                    "metrics": {
                        "phq9": {"name": "PHQ-9 Score (Depression Test)", "description": "PHQ-9 scores can be an indicator of depression, and its severity", "validation": ["int", "datetime"], "requirements": [true, true], "keys": ["score", "time"]},
                        "ybocs": {"name": "Y-BOCS Score (OCD Test)", "description": "Y-BOCS scores can be an indicator of obessive-compulsive disorder, and its severity.", "validation": ["int", "datetime"], "requirements": [true, true], "keys": ["score", "time"]},
                        "gad7": {"name": "GAD-7 Score (Anxiety Test)", "description": "GAD-7 scores can be an indicator of anxiety, and its severity", "validation": ["int", "datetime"], "requirements": [true, true], "keys": ["score", "time"]},
                        "mdq": {"name": "MDQ Score (Bipolar Test)", "description": "MDQ scores can be an indicator of bipolar disorder, and its severity", "validation": ["int", "datetime"], "requirements": [true, true], "keys": ["score", "time"]},
                        "asrs": {"name": "ASRS Score (ADHD Test)", "description": "ASRS scores can be an indicator of ADHD, and its severity", "validation": ["int", "datetime"], "requirements": [true, true], "keys": ["score", "time"]},
                        "mindful_minutes": {"name": "Mindful Minutes", "description": "Minutes spent being mindful of thoughts, emotions, and feelings", "validation": ["short_string", "start_time", "end_time"], "requirements": [true, true, true], "keys": ["type_of_mindfulness", "start_time", "end_time"]},
                        "mood": {"name": "Mood", "description": "The current mood at a point in time", "validation": ["short_string", "datetime"], "requirements": [true, true], "keys": ["mood", "time"]},
                        "sexual_activity": {"name": "Sexual Activity", "description": "Sexual activity with a partner", "validation": ["boolean", "datetime"], "requirements": [false, true], "keys": ["safe", "time"]}
                    }
                },
                "measurements": {
                    "name": "Measurements",
                    "metrics": {
                        "weight": {"name": "Weight", "description": "Total body weight in kilograms", "validation": ["float", "datetime"], "requirements": [true, true], "keys": ["measurement", "time"]},
                        "height": {"name": "Height", "description": "Total height when standing straight upright in centimeters", "validation": ["float", "datetime"], "requirements": [true, true], "keys": ["measurement", "time"]},
                        "sex": {"name": "Biological Sex", "description": "Genetic sex as identified at birth", "validation": ["sex"], "requirements": [true], "keys": ["sex"]},
                        "gender": {"name": "Gender", "description": "Gender as defined by which gender one identifies with", "validation": ["short_string"], "requirements": [true], "keys": ["gender"]},
                        "sexuality": {"name": "Sexuality", "description": "Sexual orientation", "validation": ["sexuality"], "requirements": [true], "keys": ["sexuality"]},
                        "body_temperature": {"name": "Body Temperature", "description": "Measure of the temperature of the body in celcius", "validation": ["measurement", "datetime"], "requirements": [true, true], "keys": ["temperature", "time"]},
                        "electrodermal_activity": {"name": "Electrodermal Activity", "description": "Electrodermal activity serves as an indicator of how much sweat is on the skin.", "validation": ["float", "datetime"], "requirements": [true, true], "keys": ["measurement", "time"]},
                        "circumference_bust": {"name": "Bust Circumference", "description": "The measurement of the circumference of the chest at the widest point in centimeters", "validation": ["float", "datetime"], "requirements": [true, true], "keys": ["measurement", "time"]},
                        "circumference_waist": {"name": "Waist Circumference", "description": "The measurement of the circumference of the waist at the narrowest point in centimeters", "validation": ["float", "datetime"], "requirements": [true, true], "keys": ["measurement", "time"]},
                        "circumference_hips": {"name": "Hip Circumference", "description": "The measurement of the circumference of the hips at the widest point in centimeters", "validation": ["float", "datetime"], "requirements": [true, true], "keys": ["measurement", "time"]},
                        "breathing_rate": {"name": "Breathing Rate", "description": "Rate of breathing measured in breaths per second", "validation": ["float", "datetime"], "requirements": [true, true], "keys": ["measurement", "time"]},
                        "lung_capacity": {"name": "Lung Capacity", "description": "How much air the lungs are capable of holding in liters", "validation": ["float", "datetime"], "requirements": [true, true], "keys": ["measurement", "time"]},
                        "oxygen_saturation": {"name": "Oxygen Saturation", "description": "Percentage of oxygen in present in the blood", "validation": ["percentage", "datetime"], "requirements": [true, true], "keys": ["measurement", "time"]},
                        "heartrate": {"name": "Heart Rate", "description": "How many times per minute the heart beats", "validation": ["int", "datetime"], "requirements": [true, true], "keys": ["measurement", "time"]},
                        "heartrate_resting": {"name": "Resting Heart Rate", "description": "Heart rate, measured while sitting, and inactive", "validation": ["int", "datetime"], "requirements": [true, true], "keys": ["measurement", "time"]},
                        "heartrate_walking": {"name": "Walking Heart Rate", "description": "Heart rate, measured at a steady walking pace", "validation": ["int", "datetime"], "requirements": [true, true], "keys": ["measurement", "time"]},
                        "heartrate_running": {"name": "Running Heart Rate", "description": "Heart race, measured at a steady run", "validation": ["int", "datetime"], "requirements": [true, true], "keys": ["measurement", "time"]},
                        "heartrate_variability": {"name": "Heart Rate Variability", "description": "Variation in the time interval between heart beats in milliseconds", "validation": ["int", "datetime"], "requirements": [true, true], "keys": ["measurement", "time"]},
                        "vo2_max": {"name": "VO2 Max", "description": "The maximum amount of oxygen burned while exercising, measured in mL/(kg*min)", "validation": ["float", "datetime"], "requirements": [true, true], "keys": ["measurement", "time"]},
                        "ailments": {"name": "Ailments", "description": "A record of injuries and illnesses, both mental and physical", "validation": ["long_string", "start_time", "end_time"], "requirements": [true, true, true], "keys": ["ailment", "start_time", "end_time"]},
                        "blood_pressure": {"name": "Blood Pressure", "description": "The pressure at which blood pushes against the walls of the arteries", "validation": ["int", "int", "datetime"], "requirements": [false, false, true], "keys": ["systolic", "diastolic", "time"]},
                        "blood_sugar": {"name": "Blood Sugar", "description": "The amount of glucose in the blood", "validation": ["int", "datetime"], "requirements": [true, true], "keys": ["measurement", "time"]},
                        "blood_alcohol_content": {"name": "Blood Alcohol Content", "description": "The amount of alcohol in the blood", "validation": ["float", "datetime"], "requirements": [true, true], "keys": ["measurement", "time"]},
                        "sound_exposure": {"name": "Sound Exposure", "description": "Periods of time exposed to sounds of a certain volume", "validation": ["int", "start_time", "end_time"], "requirements": [true, true, true], "keys": ["decibles", "start_time", "end_time"]},
                        "sleep": {"name": "Sleep", "description": "Record of periods of sleep in its various stages", "validation": ["short_string", "start_time", "end_time"], "requirements": [false, true, true], "keys": ["sleep_stage", "start_time", "end_time"]},
                        "times_fallen": {"name": "Times Fallen", "description": "Times unintentionally fallen, with or without injury", "validation": ["datetime"], "requirements": [true], "keys": ["time"]},
                        "atypical_pulse": {"name": "Atypical Pulse", "description": "A record of occasions on which heart rate was atypically fast or slow.", "validation": ["float", "datetime"], "requirements": [true, true], "keys": ["measurement", "time"]},
                        "audiogram": {"name": "Audiogram", "description": "A test used to determine how loud a sound has to be to be heard.", "validation": ["int", "side", "datetime"], "requirements": [true, true, true], "keys": ["decibles", "side", "time"]}
                    }
                }
            }';

            $encoded_metrics_data = json_encode(json_decode($metrics_raw_data, true), (JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT));
            if ($encoded_metrics_data == "null") {
                echo "<p>Failed to initialize metrics data. It is possible the JSON string is malformed.</p>";
                echo $metrics_raw_data;
                return false;
            }
            file_put_contents($metrics_database_filepath, $encoded_metrics_data); // Set the contents of the database file to the placeholder configuration.
        }

        if (file_exists($metrics_database_filepath) == true) {
            $metrics_data = json_decode(file_get_contents($metrics_database_filepath), true);
            return $metrics_data;
        } else {
            echo "<p>Failed to create metrics database file.</p>";
            return false;
        }
    }
}


if (!function_exists("validate_metrics")) { // Check to see if the 'validate_metrics' function hasn't yet been created.
    function validate_metrics($metrics_data, $output = 0) {
        // Validation options:
        $validation_options = array("int", "float", "start_time", "end_time", "datetime", "short_string", "long_string", "boolean", "sex", "sexuality", "temperature", "percentage", "side", "vehicleid", "foodid", "mealid");
        # int: A positive whole number
        # float: A positive decimal number
        # start_time: A Unix timestamp before end_time (integer)
        # end_time: A Unix timestamp after start_time (integer)
        # datetime: A Unix timestamp (integer)
        # short_string: A string under 20 characters (Allowed characters: a-zA-Z0-9 '_-())
        # long_string: A string under 150 characters (Allowed characters: a-zA-Z0-9 '_-())
        # boolean: A 'true' or 'false' value
        # sex: A 1 character string: M, F, or I
        # sexuality: A 1 character string: S, G, B, or A
        # temperature: A positive or negative float, above -273
        # percentage: A decimal number ranged 0 to 1, inclusively
        # side: A 1 character string: L or R
        # vehicleid: A vehicle ID that exists in the vehicle database.
        # foodid: A food ID that exists in the food database.
        # mealid: A string that combines a date (YYYY-MM-DD) and meal number, where 0 is a snack (1 for breakfast, 2 for lunch, 3 for dinner) separated by a comma. For example, dinner on May 5th would be "2024-05-21,3".


        $valid = true; // Assume the metric data is valid until an invalid field is found.
        foreach (array_keys($metrics_data) as $category) {
            foreach (array_keys($metrics_data[$category]["metrics"]) as $metric) {
                if ($output >= 2) {
                    echo "<p>Checking <b>" . $category . "/" . $metric . "</b>.</p>";
                }

                // Check to make sure the keys, requirements, and validation all exist.
                if (isset($metrics_data[$category]["metrics"][$metric]["validation"]) == false) {
                    if ($output >= 1) {
                        echo "<p><b>" . $category . "/" . $metric . "</b> is missing validation information.</p>";
                        $valid = false;
                    }
                } else if (isset($metrics_data[$category]["metrics"][$metric]["requirements"]) == false) {
                    if ($output >= 1) {
                        echo "<p><b>" . $category . "/" . $metric . "</b> is missing requirements information.</p>";
                        $valid = false;
                    }
                } else if (isset($metrics_data[$category]["metrics"][$metric]["keys"]) == false) {
                    if ($output >= 1) {
                        echo "<p><b>" . $category . "/" . $metric . "</b> is missing key information.</p>";
                        $valid = false;
                    }
                } else {
                    // Check to see if the keys, requirements, and validation are all the same length.
                    $validation_length = sizeof($metrics_data[$category]["metrics"][$metric]["validation"]);
                    $requirements_length = sizeof($metrics_data[$category]["metrics"][$metric]["requirements"]);
                    $keys_length = sizeof($metrics_data[$category]["metrics"][$metric]["keys"]);
                    if ($validation_length !== $requirements_length or $requirements_length !== $keys_length or $keys_length !== $validation_length) {
                        if ($output >= 1) {
                            echo "<p><b>" . $category . "/" . $metric . "</b> has mis-matched validation, requirements, and keys.</p>";
                            $valid = false;
                        }
                    }

                    foreach ($metrics_data[$category]["metrics"][$metric]["validation"] as $validation) {
                        if (in_array($validation, $validation_options) == false) {
                            echo "<p><b>" . $category . "/" . $metric . "</b> has an invalid validation key (" . $validation . ").</p>";
                        }
                    }
                }
            }
        }

        return $valid;
    }
}
?>
