# Terminology

These are some terms that are use frequently in relation to HealthBox.

- A **service** is a program or device that interacts with HealthBox. Services are defined by their service key, which acts like an API key. Services can be individually granted permissions for improved security. Services can be created or removed from the web interface.
- A **category** is a general category for health data. By default, the 3 categories are "Physical", "Mental", and "Measurements". Think of the category as the top level classifier for health data.
- A **metric** is a specific health metric, inside a category. Some examples of metrics include "steps", "mood", and "distance walked". Each metric is classified under one (and only one) category.
- A **datapoint** is a instance of data associated with a metric. For example, the "steps" metric might contain a datapoint indicating that you took 1263 steps between 3pm and 4pm on March 27th, 2024. When a service submits data to HealthBox it is registered as a datapoint.
