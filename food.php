<?php
include "./config.php";

$food_database_filepath = $healthbox_config["database_location"] . "/food.json";

if (file_exists($food_database_filepath) == false) {
    $nutrients_raw_data = '{
            "metadata": {
                "metrics": {
                    "name": {"name": "Food Name", "type": "str", "required": true},
                    "brand": {"name": "Food Brand", "type": "str", "required": false},
                    "organic": {"name": "Organic", "type": "bool", "required": false},
                    "serving_size": {"name": "Serving Size", "type": "float", "required": true},
                    "serving_units": {"name": "Serving Unit", "type": "str", "required": true},
                    "nutrients": {
                        "calories": {"name": "Calories"},
                        "water": {"name": "Water"},
                        "sugar": {"name": "Sugar"},
                        "fiber": {"name": "Fiber"},
                        "protein": {"name": "Protein"},
                        "fat-saturated": {"name": "Saturated Fat"},
                        "fat-trans": {"name": "Trans Fat"},
                        "fat-monosaturated": {"name": "Monosaturated Fat"},
                        "fat-polysaturated": {"name": "Polysaturated Fat"},
                        "fat": {"name": "Unspecified Fat"},
                        "calcium": {"name": "Calcium"},
                        "carbohydrates": {"name": "Carbohydrates"},
                        "cholesterol": {"name": "Cholesterol"},
                        "iron": {"name": "Iron"},
                        "sodium": {"name": "Sodium"},
                        "vit-a": {"name": "Vitamin A"},
                        "vit-b6": {"name": "Vitamin B6"},
                        "bit-b12": {"name": "Vitamin B12"},
                        "vit-c": {"name": "Vitamin C"},
                        "vit-d": {"name": "Vitamin D"},
                        "vit-e": {"name": "Vitamin E"},
                        "vit-k": {"name": "Vitamin K"},
                        "zinc": {"name": "Zinc"},
                        "biotin": {"name": "Biotin"},
                        "caffeine": {"name": "Caffeine"},
                        "chloride": {"name": "Chloride"},
                        "copper": {"name": "Copper"},
                        "folate": {"name": "Folate"},
                        "iodine": {"name": "Iodine"},
                        "magnesium": {"name": "Magnesium"},
                        "manganese": {"name": "Manganese"},
                        "molybdenum": {"name": "Molybdenum"},
                        "niacin": {"name": "Niacin"},
                        "pantothenic-acid": {"name": "Pantothenic Acid"},
                        "phosphorus": {"name": "Phosphorus"},
                        "potassium": {"name": "Potassium"},
                        "riboflavin": {"name": "Riboflavin"},
                        "selenium": {"name": "Selenium"},
                        "thaimin": {"name": "Thiamin"}
                    }
                }
            },
            "foods": {
            }
        }
    }';

    $healthbox_food_database_file = fopen($food_database_filepath, "w") or die("Unable to create food database file."); // Create the file.
    fwrite($healthbox_food_database_file, json_encode($nutrients_raw_data, (JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT))); // Set the contents of the database file to the placeholder configuration.
    fclose($healthbox_food_database_file); // Close the database file.
}

if (file_exists($food_database_filepath) == true) {
    $food_database = json_decode(file_get_contents($food_database_filepath), true);
} else {
}


// TODO: Define function for registering foods in the database.
// TODO: Define a function for removing foods from the database.
// TODO: Define a function to update a food in the database.

?>
