# Development

This document explains the basics of developing services that integrate with HealthBox.


## Introduction

Practically all interaction with HealthBox from external services is done through GET requests. HealthBox contains various endpoints for completing tasks like registering new datapoints, editing foods, and fetching health data.

### Endpoints

Here is a list of the endpoints currently implemented by HealthBox. Each endpoint is described in-depth in its corresponding section later in this document.

- **/healthbox/submit.php**: Used to submit datapoints to health metrics.
- **/healthbox/fetch.php**: Used to fetch health information for a particular metric.
- **/healthbox/delete.php**: Used to delete a specific datapoint for a particular metric.
- **/healthbox/updatefood.php**: Used to add or edit existing foods.
- **/healthbox/deletefood.php**: Used to delete foods.

### Metrics

All metrics (and their corresponding keys) are listed on the `/healthbox/viewmetrics.php` page.


## Responses

When submitting requests to endpoints, all responses take the form of a JSON string.

### Errors

When an error is encountered, HealthBox will generally return a JSON dictionary with the following format:
```
{
    'error': {
        'id': [Error ID],
        'reason': [Specific error],
        'value': [Relevant value ID],
        'description': [Human-readable error description]
    }
}
```
The `id` field is included with all error responses, but the `reason` and `value` fields may or may not be present depending on the specific error. The `reason` field can be considered a more specific error given the main error ID. The `value` field is only present if the error is related to a specific value in the submission. Below is a comprehensive list of error IDs, as well as the associated reasons (if any). Keep in mind that some of these errors are exclusive to certain endpoints, while others are more universal.

- `invalid_service`: The service identifier provided is invalid.
    - `disallowed_characters`: The service identifier contains disallowed characters. Only a-f and 0-9 are permitted.
    - `too_long`: The service identifier is longer than expected.
    - `too_short`: The service identifier is shorter than expected.
    - `not_found`: The service identifier does not exist in the database.
- `permission_denied`: The given service ID is not permitted to access the given metric.
- `invalid_category`: The given category ID does not exist in the database.
- `invalid_metric`: The given metric ID does not exist in the database.
- `invalid_id`: The given identifier is invalid.
    - `disallowed_characters`: The service identifier contains disallowed characters.
    - `too_long`: The service identifier is longer than expected.
    - `too_short`: The service identifier is shorter than expected.
    - `not_found`: The service identifier does not exist in the database.
- `missing_required_data`: Required keys are missing from the submission.
- `invalid_value`: Required keys are missing from the submission.
    - `disallowed_characters`: The service identifier contains disallowed characters. Only a-f and 0-9 are permitted.
    - `too_long`: The service identifier is longer than expected.
    - `too_short`: The service identifier is shorter than expected.
    - `not_found`: The service identifier does not exist in the database.
- `duplicate`: The submitted information is an exact duplicate of an existing datapoint.

### Success

When a request is completed successfully, the response depends on the type of request. For data fetch requests, the requested data will be returned as a JSON string. For data submission requests that don't fetch any data, a JSON string with the following format will be returned:
```
{
    'success': {
        'description': [Human-readable response]
    }
}
```


## Submitting Datapoints

To submit datapoints, use the **/healthbox/submit.php** endpoint. This endpoint takes the following inputs:
- `service`: The identifier of a service with write permissions to the metric of the datapoint being submitted.
- `category`: The category of the metric being submitted.
- `metric`: The metric of the datapoint being submitted.
- `key-X`: Each key for this metric, where X is replaced by the key name.

### Example

This is an example for submitting a datapoint for steps taken on March 27th between noon and 5PM:
`http://localhost/healthbox/submit.php?service=abcde123456789&category=physical&metric=steps&key-steps_count=2544&key-start_time=1711557900&key-end_time=1711575900`

Here is the same example broken down into its components for sake of readability:
```
http://localhost/healthbox/submit.php?
    service=abcde123456789
    &category=physical
    &metric=steps
    &key-steps_count=2544
    &key-start_time=1711557900
    &key-end_time=1711575900`
```

Since the steps metric has 3 keys (`steps\_count`, `start\_time`, and `end\_time`), you'll notice there are 3 `key-X` values in the submission (where X is replaced with the key ID). These exact values will change depending on the metric being submitted. If certain key is not marked as required in the metric database, then it does not need to be included in the submission.


## Fetching Datapoints

To view datapoints for a particular metric, use the **/healthbox/fetch.php** endpoint. This endpoint takes the following inputs:
- `service`: The identifier of a service with read permissions to the metric being viewed.
- `category`: The category of the metric to be viewed.
- `metric`: The metric of the datapoint to be viewed.
- `start_time` and `end_time`: Optional values that specify a time range of datapoints to retreive.
    - If one of these values is specified, the other must also be included.
    - Each of these values is a Unix timestamp (the number of seconds since midnight on January 1st, 1970).
    - The end time must be after the start time.
    - If these values are ommitted, all datapoints for the metric will be returned.

### Example

This is an example for fetching all datapoints in the steps metric.
`http://localhost/healthbox/fetch.php?service=abcde123456789&&category=physical&metric=steps`

Here is the same example broken down into its components for sake of readability:
```
http://localhost/healthbox/fetch.php?
    service=abcde123456789
    &category=physical
    &metric=steps
```


## Deleting Datapoints

To delete existing datapoints, use the **/healthbox/delete.php** endpoint. This endpoint takes the following inputs:
- `service`: The identifier of a service with write permissions to the metric of the datapoint being deleted.
- `category`: The category of the metric to be erased.
- `metric`: The metric of the datapoint to be erased.
- `datapoint`: The key of the datapoint to be erased.

### Example

This is an example for deleting a datapoint in the steps metric that was submitted on March 27th, 2024.
`http://localhost/healthbox/delete.php?service=abcde123456789&&category=physical&metric=steps&datapoint=1711575998`

Here is the same example broken down into its components for sake of readability:
```
http://localhost/healthbox/delete.php?
    service=abcde123456789
    &category=physical
    &metric=steps
    &datapoint=1711575998
```


## Adding/Editing Foods

To add or edit foods in the food database, use the **/healthbox/updatefood.php** endpoint. This endpoint takes the following inputs:
- `service`: The identifier of a service with permission add (foods-add) or edit (foods-edit) foods, depending on the nature of the request.
- `id`: A unique identifier for the new food, or the identifier of the existing food to edit.
- `name`: A human-readable name for the food.
- `servingsize`: The number of units of this food per serving.
- `servingunit`: The unit of measurement for servings of this food.
- `X`: The amount of a particular nutrient of this food, where X is replaced with a nutrient ID. (See [/docs/FOOD.md](/docs/FOOD.md) for more information)

### Example

This is an example of adding an entry in the food database for a banana. This same example will edit an existing food if one already exists with the "banana" ID.
`http://localhost/healthbox/updatefood.php?service=6afb238acdb8888d42945cc3&id=banana&name=Banana&servingsize=1&servingunit=medium%20banana&calories=100&carbohydrates=27&fiber=3&sugar=14&protein=1`

Here is the same example broken down into its components for sake of readability:
```
http://localhost/healthbox/updatefood.php?
    service=6afb238acdb8888d42945cc3&
    &id=banana
    &name=Banana
    &servingsize=1
    &servingunit=fruit
    &calories=100
    &carbohydrates=27
    &fiber=3
    &sugar=14
    &protein=1
```


## Removing Foods

To delete foods from the food database, use the **/healthbox/deletefood.php** endpoint. Note that deleting a food from the database will also delete any health datapoints that reference it. This endpoint takes the following inputs:
- `service`: The identifier of a service with permission delete foods (foods-delete).
- `food`: The unique identifier of the food to be deleted.

### Example

This is an example of deleting the "banana" entry from the food database.
`http://localhost/healthbox/deletefood.php?service=6afb238acdb8888d42945cc3&food=banana`

Here is the same example broken down into its components for sake of readability:
```
http://localhost/healthbox/deletefood.php?
    service=6afb238acdb8888d42945cc3
    &food=banana
```


## Fetching Foods List

To fetch the list of foods registered in the database (including serving sizes), use the **/healthbox/fetchfoodlist.php** endpoint. Note that this endpoint doesn't return nutrition information. Access to nutrition information is not required to submit datapoints to the physical\>food, since the user only needs to specify how many servings they consumed. This endpoint takes the following inputs:
- `service`: The identifier of a service with permission fetch the list of foods (foods-fetch-list).

### Example

This is an example of fetching a particular user's food list.
`http://localhost/healthbox/fetchfoodlist.php?service=6afb238acdb8888d42945cc3`

Here is a potential response for this example. Note that the actual response will be returned as a single line without formatting or indentation.
```
{
    "apple": {
        "name":"Apple",
        "serving": {
            "size": 1,
            "unit":"fruit"
        }
    },
    "orange": {
        "name": "Orange",
        "serving": {
            "size": 1,
            "unit": "fruit"
        }
    },
    "banana": {
        "name": "Banana",
        "serving": {
            "size": 1,
            "unit": "medium banana"
        }
    }
}
```


## Fetching Food Nutrients

To fetch all information for a specific food (including nutrition information), use the **/healthbox/fetchfoodnutrients.php** endpoint. This endpoint takes the following inputs:
- `service`: The identifier of a service with permission to fetch food nutrients (foods-fetch-nutrients)
- `food`: The identifier of the food to fetch.

### Example

This is an example of fetching nutrition information for a food with the ID 'banana'.
`http://localhost/healthbox/fetchfoodlist.php?service=6afb238acdb8888d42945cc3&food=banana`

Here is the same example broken down into its components for sake of readability:
```
http://localhost/healthbox/fetchfoodnutrients.php?
    service=6afb238acdb8888d42945cc3
    &food=banana
```

Here is a potential response for this example. Note that the actual response will be returned as a single line without formatting or indentation.
```
{
    "name": "Banana",
    "serving": {
        "size": 1,
        "unit": "medium banana"
    },
    "nutrients":{
        "calories": 100,
        "carbohydrates": 27,
        "fiber": 3,
        "sugar": 14,
        "protein": 1
    }
}
```


## Fetching All Foods

To fetch all foods, including their associated nutrition information, use the **/healthbox/foodsfetchall.php** endpoint. This endpoint takes the following inputs:
- `service`: The identifier of a service with permission fetch all foods (foods-fetch-all).

### Example

This is an example of fetching a particular user's food information.
`http://localhost/healthbox/fetchfoodall.php?service=6afb238acdb8888d42945cc3`

Here is a potential response for this example. Note that the actual response will be returned as a single line without formatting or indentation.
```
{
    "apple": {
        "name": "Apple",
        "serving": {
            "size": 1,
            "unit": "fruit"
        },
        "nutrients": {
            "calories": 95,
            "carbohydrates": 23,
            "fiber": 4,
            "sugar": 12,
            "protein": 0
        }
    },
    "orange": {
        "name": "Orange",
        "serving": {
            "size": 1,
            "unit": "fruit"
        },
        "nutrients": {
            "calories": 140,
            "carbohydrates": 30,
            "fiber": 2,
            "sugar": 17,
            "protein": 1
        }
    },
    "banana": {
        "name": "Banana",
        "serving": {
            "size": 1,
            "unit": "medium banana"
        },
        "nutrients": {
            "calories": 100,
            "carbohydrates": 27,
            "fiber": 3,
            "sugar": 14,
            "protein": 1
        }
    }
}
```
