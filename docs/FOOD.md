# Food

Here you can learn more about the food database system for HealthBox.

## Introduction

The food database and health database (while related), are completely separate. The food database contains nutrition and serving information for various foods. This data can be referenced from the health database by food ID. For example, the physical\>food metric in the health database keeps track of food consumed by the user. This metric has a "foodid" key, which refers to a food in the food database.


## Data

Each food in the food database is associated with various pieces of information.

### General

Information here is found in the `metadata` section of the database, and applies globally to the HealthBox instance.

- **displayed_nutrients** are the nutrients that are displayed to all users.
    - This does not determine which nutrients are active, only which are shown to users of the instance.
    - HealthBox clients can always display whatever nutrients they want to the user, regardless of what is configured on the server.
- **values** determines what pieces of information are tracked for each food added to the database (in addition to nutrients).
    - This can be used to track custom information about each food (whether the food is vegan, the precense of allergens, etc.)
    - Each entry in this section must have the following attributes:
        - **name**, which is simply a human-friendly name of the entry.
        - **type**, which is the type of value that this entry will hold.
            - The following types are currently supported:
                - "str" for a string of plain text.
                - "bool" for a true or false value.
        - **required**, which is a boolean value that determines if this entry is required to be included when a new food is created.
    - Values defined here are stored alongside other information within each food. As such, the following strings can not be used as keys for custom values, since they conflict with hard-coded values:
        - "serving"
        - "service"
        - "nutrients"
- **nutrients** contains a complete collection of all nutrients that this HealthBox instance will recognize.
    - You can feel free to add custom nutrients to this section, but be aware that removing existing nutrients may cause unexpected behavior.

### Values

These are the values supported by HealthBox by default. The exact values recognized by your particular instance of HealthBox can be found in the food database under the `metadata>values` section, or by viewing the `/healthboxviewnutrients.php` page in your browser while signed in to HealthBox.
- **name** (str): A human-friendly name for the food.
- **brand** (str): The brand of the food.
- **organic** (bool): Whether or not the food is classified as organic.

### Nutrients

These are the nutrients supported by HealthBox by default. The exact nutrients supported by your particular instance of HealthBox can be found in the food database under the `metadata>nutrients` section, or by viewing the `/healthbox/viewnutrients.php` page in your browser while signed in to HealthBox.
- calories: Calories (kilocalories)
- fat-total: Total Fat (grams)
- fat-saturated: Saturated Fat (grams)
- fat-trans: Trans Fat (grams)
- fat-monosaturated: Monosaturated Fat (grams)
- fat-polysaturated: Polysaturated Fat (grams)
- cholesterol: Cholesterol (milligrams)
- sodium: Sodium (milligrams)
- carbohydrates: Carbohydrates (grams)
- fiber: Fiber (grams)
- sugar: Sugar (grams)
- protein: Protein (grams)
- vit-d: Vitamin D (micrograms)
- calcium: Calcium (milligrams)
- iron: Iron (milligrams)
- potassium: Potassium (milligrams)
- water: Water (fluid ounce)
- vit-a: Vitamin A (micrograms)
- vit-b1: Vitamin B1 (milligrams)
- vit-b2: Vitamin B2 (milligrams)
- vit-b3: Vitamin B3 (milligrams)
- vit-b5: Vitamin B5 (milligrams)
- vit-b6: Vitamin B6 (milligrams)
- vit-b7: Vitamin B7 (micrograms)
- vit-b9: Vitamin B9 (micrograms)
- bit-b12: Vitamin B12 (micrograms)
- vit-c: Vitamin C (milligrams)
- vit-e: Vitamin E (milligrams)
- vit-k: Vitamin K (micrograms)
- zinc: Zinc (milligrams)
- caffeine: Caffeine (milligrams)
- chloride: Chloride (milligrams)
- copper: Copper (milligrams)
- iodine: Iodine (micrograms)
- magnesium: Magnesium (milligrams)
- manganese: Manganese (milligrams)
- molybdenum: Molybdenum (micrograms)
- pantothenic-acid: Pantothenic Acid)
- phosphorus: Phosphorus (milligrams)
- selenium: Selenium (micrograms)
