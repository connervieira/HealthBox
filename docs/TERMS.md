# Terminology

These are some terms that are use frequently in relation to HealthBox.

- A **service** is a program or device that interacts with HealthBox. For example, a pedometer that submits your steps to HealthBox would be considered a service, as would a diet app on your phone that reads your height and weight from your HealthBox information. To allow a service to interact with your HealthBox account, you need to generate a service key in the web interface, then add this key in the device or application configuration. Services can be individually granted permissions for improved security. Additionally, services can be created or removed from the web interface.
- A **category** is a general category for health data. By default, the 3 categories are "Physical", "Mental", and "Measurements". Think of the category as the top level classifier for health data.
- A **metric** is a specific health metric, inside a category. Some examples of metrics include "steps", "mood", and "distance walked". Each metric is classified under one (and only one) category.
- A **datapoint** is a instance of data associated with a metric. For example, the "steps" metric might contain a datapoint indicating that you took 1263 steps between 3pm and 4pm on March 27th, 2024. When a service submits data to HealthBox it is registered as a datapoint.
