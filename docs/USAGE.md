# Usage

This document explains the basics of using HealthBox.


## Managing Services

Services are applications or devices that interact with HealthBox by either fetching existing data, or submitting their own data. HealthBox allows you to create with services with specific permissions. To manage services, click the "Manage Services" button on the main HealthBox page.

### Register Services

Before a service can interact with HealthBox, it needs to be registered. To register a service, simply enter a recognizable name under the "Register Service", and press "Register". The service will be created with a unique identifier. This identifier will appear immediately after registering the service, but can also be found under the "View Service" section of the same page. This identifier is what the service will use to interact with HealthBox.

Note that newly registered services will not have any permissions, meaning they can not read or write information.

### Remove Services

Removing a service will remove all datapoints submitted by the service. Keep in mind that you can revoke a service's access to HealthBox without deleting it completely. If you really do want to delete a service, you can do so by entering its ID in the "Remove Service" field, then clicking "Remove". You can click an ID under the "View Service" section to autofill this field.

### Manage Permissions

HealthBox allows you to set permissions regarding what information each service is allowed to read and write to. To update the permissions of a particular service, location it under the "View Service" section, and click "Update". Alternatively, you can navigate directly to `/healthbox/updateservice.php` and select the service you want to update from the list.

Once a service ID has been selected (or manually entered), you can select the metric you want to set permissions for, set the action ("grant" to allow, and "revoke" for disallow), then the permission itself ("read" or "write"). For sake of security, you should try to grant only the minimum required access to each service.

Services can also be granted permission to use certain "actions". These actions allow services to control aspects of HealthBox outside of submitting and reading health information. Below is a list of what each action allows a service to do:
- **foods-add** allows a service to add a new food to the food database (but not overwrite existing foods).
- **foods-edit** allows a service to overwrite existing foods in the food database (but not create new ones).
- **foods-delete** allows a service to delete existing foods in the food database.
- **foods-fetch-all** allows a service to read the entire food database, and all information it includes.
- **foods-fetch-list** allows a service to fetch a list of all food IDs in the database, as well as the food name, serving size, and serving unit. It does not allow the service to see nutrition information for each food.
- **foods-fetch-nutrients** allows a service to get all information associated with a specific food (including nutrition information), given its food ID. It does not allow a service to list all food IDs.


## Managing Food

HealthBox maintains a food database that allows the user to register foods with serving sizes and nutrition information. This information is used by the `physical>food` metric to track nutrition. Every food referenced by datapoints in the `physical>food` metric must exist in the food database. If you don't intend to use this metric, you don't need to use the food database, and this section can be skipped. To manage foods, click the "Manage Food" button on the main HealthBox page.

### Adding/Editing Foods

To add or edit a food, you'll first need to specify a service ID. This service needs to have the **foods-add** action to add a food, or **foods-edit** to edit an existing food. If you don't have a service with these permissions, you should create one now. Most users will create a "Web Interface" service for this purpose.

After selecting a service ID, you need to enter a food ID. This is a string that uniquely identifies this food. If you want to edit an existing food, you can autofill this value by selecting a food under the "List Foods" section at the bottom of the page. Setting the food ID to an ID that already exists in the database will overwrite it.

The "food name" is a friendly name that is only used for display purposes. It does not need to be unique, but it generally should be to avoid confusion.

The "serving size" is a numerical value that determines the base serving size of this food. The "serving unit" is the unit associated with the the serving size. For example, the serving size for strawberries might be "12", with the unit being "berries". A soda might have a size of "8" with a unit of "fl oz". These values can be found at the top of the nutrition label on food products.

The nutrition information section is shown at the bottom of the form allows you to enter the nutrition facts per serving. All values here are optional, and leaving any field blank will omit it from the database.

After all required information has been filled out, pressing the "Add" button at the bottom of the form will generate a request URL. You can click this request URL to redirect to it, or copy it into a separate tab. Keep in mind that it is not explicitly required to use the form to generate a request URL. As long as a service has the appropriate permissions, this request can be submitted from anywhere. This allows you to submit requests manually from the web interface, as well as through a separate service.

### Removing Foods

To remove a food, simply enter its ID in the "Remove Food" section, and select a service with the **foods-delete** action permission. Note that deleting a food from the database will also recursively remove all references to it in the physical/food metric.

### Viewing Foods

To view the nutrition information associated with a food from the web interface, simply enter the food ID in the "View Food" section, then press "View".


## Managing Data

HealthBox is primarily designed to read and write health information from external services like mobile applications and fitness trackers. However, you can also manually interact with the database through the web interface. To manage data, click the "Manage Data" button on the main HealthBox page.

### Adding Data

To register a new datapoint, select a service under the "Register Data" section that has permission to write to the desired metric. Then select the category of the metric you want to write to. Press "Continue" to move to the next page step. Next, select the metric you want to submit, and press "Continue". You should now see fields for the raw metric data. Fields that are required are displayed in bold font. Datapoint keys that involve a timestamp will be split into two fields for the date and time, and will be converted into a single timestamp automatically. After the information has been filled out, press the "Submit" button to generate a submission URL. To submit the data to HealthBox, either click the submission URL to redirect to it, or manually copy it into a separate tab. It should be noted that you are not required to use the web interface to submit data to HealthBox. Similar submission URLs are used by services to submit data completely independently.

### View Data

To view the datapoints for a particular metric, first locate it in the "View Data" section at the bottom of the page, then click "View Datapoints". All datapoints registered for this metric will be displayed in a list.

### Delete Data

To delete a datapoint from the web interface, first navigate to the metric by clicking "View Datapoints" under the metric in the "View Data" section of the "Manage Data" page. Then locate the datapoint you want to delete in the list, and click "Delete". Verify that the selected datapoint is the point you intend to delete, then press "Confirm".
