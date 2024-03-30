<?php
include "./config.php";

$food_database_filepath = $healthbox_config["database_location"] . "/food.json";

if (!function_exists("load_food")) {
    function load_food() {
        global $food_database_filepath;
        if (file_exists($food_database_filepath) == false) {
            $nutrients_raw_data = '{
                    "metadata": {
                        "displayed_nutrients": ["calories", "fat-total", "fat-saturated", "fat-trans", "cholesterol", "sodium", "carbohydrates", "fiber", "sugar", "protein", "vit-d", "calcium", "iron", "potassium"],
                        "values": {
                            "name": {"name": "Food Name", "type": "str", "required": true},
                            "brand": {"name": "Food Brand", "type": "str", "required": false},
                            "organic": {"name": "Organic", "type": "bool", "required": false}
                        },
                        "nutrients": {
                            "calories": {"name": "Calories", "unit": "kcal"},
                            "fat-total": {"name": "Total Fat", "unit": "g"},
                            "fat-saturated": {"name": "Saturated Fat", "unit": "g"},
                            "fat-trans": {"name": "Trans Fat", "unit": "g"},
                            "fat-monosaturated": {"name": "Monosaturated Fat", "unit": "g"},
                            "fat-polysaturated": {"name": "Polysaturated Fat", "unit": "g"},
                            "cholesterol": {"name": "Cholesterol", "unit": "mg"},
                            "sodium": {"name": "Sodium", "unit": "mg"},
                            "carbohydrates": {"name": "Carbohydrates", "unit": "g"},
                            "fiber": {"name": "Fiber", "unit": "g"},
                            "sugar": {"name": "Sugar", "unit": "g"},
                            "protein": {"name": "Protein", "unit": "g"},
                            "vit-d": {"name": "Vitamin D", "unit": "µg"},
                            "calcium": {"name": "Calcium", "unit": "mg"},
                            "iron": {"name": "Iron", "unit": "mg"},
                            "potassium": {"name": "Potassium", "unit": "mg"},
                            "water": {"name": "Water", "unit": "fl oz"},
                            "vit-a": {"name": "Vitamin A", "unit": "µg"},
                            "vit-b1": {"name": "Vitamin B1", "unit": "mg"},
                            "vit-b2": {"name": "Vitamin B2", "unit": "mg"},
                            "vit-b3": {"name": "Vitamin B3", "unit": "mg"},
                            "vit-b5": {"name": "Vitamin B5", "unit": "mg"},
                            "vit-b6": {"name": "Vitamin B6", "unit": "mg"},
                            "vit-b7": {"name": "Vitamin B7", "unit": "µg"},
                            "vit-b9": {"name": "Vitamin B9", "unit": "µg"},
                            "bit-b12": {"name": "Vitamin B12", "unit": "µg"},
                            "vit-c": {"name": "Vitamin C", "unit": "mg"},
                            "vit-e": {"name": "Vitamin E", "unit": "mg"},
                            "vit-k": {"name": "Vitamin K", "unit": "µg"},
                            "zinc": {"name": "Zinc", "unit": "mg"},
                            "caffeine": {"name": "Caffeine", "unit": "mg"},
                            "chloride": {"name": "Chloride", "unit": "mg"},
                            "copper": {"name": "Copper", "unit": "mg"},
                            "iodine": {"name": "Iodine", "unit": "µg"},
                            "magnesium": {"name": "Magnesium", "unit": "mg"},
                            "manganese": {"name": "Manganese", "unit": "mg"},
                            "molybdenum": {"name": "Molybdenum", "unit": "µg"},
                            "pantothenic-acid": {"name": "Pantothenic Acid"},
                            "phosphorus": {"name": "Phosphorus", "unit": "mg"},
                            "selenium": {"name": "Selenium", "unit": "µg"}
                        }
                    },
                    "entries": {
                    }
                }';

            $encoded_food_data = json_encode(json_decode($nutrients_raw_data, true), (JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT));

            file_put_contents($food_database_filepath, $encoded_food_data); // Set the contents of the database file to the placeholder configuration.
        }

        if (file_exists($food_database_filepath) == true) {
            $food_database = json_decode(file_get_contents($food_database_filepath), true);
            return $food_database;
        } else {
            echo "<p>Failed to intialized food database.";
        }
    }
}


if (!function_exists("save_food")) {
    function save_food($data) {
        global $food_database_filepath;

        $encoded_food_data = json_encode($data, (JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT));
        if (!file_put_contents($food_database_filepath, $encoded_food_data)) { // Set the contents of the database file to the supplied data.
            echo "<p>Failed to save service database</p>";
        }
    }
}

?>
