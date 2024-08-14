# Migration

Here you can learn how to migrate between HealthBox instances.

## Introduction

HealthBox supports the ability to import and export account information (food and health data). This functionality is primarily designed to allow users to back-up their information so that it can be restored in the event of server data loss. However, it can also be used to migrate between HealthBox instances (with some caveats).

## Caveats

### Services

The HealthBox export tool exports both health and food data associated with your account, but it does not allow you to restore service information. When importing HealthBox information, you'll have to create a service with the appropriate permissions to restore data. If the service identifiers associated with the exported data do not exist under you account during the import process, then this new service will be used in their place.

In practice, this means that restoring a back-up to the same server it was created from will likely preserve the service identifiers (assuming the haven't been deleted). However, restoring a back-up to a different server will overwrite the service identifiers.

### Integrity

While HealthBox validates the information in imported back-ups, it does so primarily for security, rather than data integrity. Manipulating the back-up file before it is imported is likely to cause unexpected behavior.

### Overwriting

When importing from a back-up, HealthBox attempts to merge existing data with the imported data. However, in the event that a particular food or datapoint is already present in the existing data, the imported data will overwrite it. As such, you should back-up any existing data associated with your account before you import a new file.


## Migration

This section contains separate instructions for migrating between two externally hosted instances, and between an externally hosted and self hosted instance. While the process is extremely similar between the two methods, self-hosted users have considerably more control over their instance, and may be able to preserve service data.


### External to External

**On the initial instance**:
1. Sign into your account.
2. Click the "Management" button at the top of the main dashboard.
3. Click the "Export Data" button.
4. Download the exported JSON file.
5. Optionally, open the JSON file to verify that all information was backed-up as expected.

**On the new instance**:
1. Sign into your account.
2. Click the "Manage Services" button.
3. Register a new service with a recognizable name, such as "DataImport".
    - The name you choose can be anything you want, but you'll need to remember it for the following steps.
4. Under the "View Service" section, click "Update" next to your new service.
5. Under the "Control Actions" section, grant the following action permissions:
    - data-writeall
    - foods-add
    - foods-edit
6. Press the "Back" button at the top of the page to return to the service management page.
7. Press the "Back" button at the top of the page once again to return to the main dashboard.
8. Press the "Management" button at the top of the page.
9. Press the "Import Data" button.
10. After reviewing the warning, press "Confirm".
11. Under the "Service" drop-down, select the new service.
12. Upload the back-up JSON file exported from the initial HealthBox instance.
13. Press "Submit".
14. Assuming the uploaded JSON file is valid, you should see a message reading "Import success".
    - If the import process fails, here are common reasons for errors:
        - If you tampered with the exported JSON file between downloading it and restoring it on the new instance, then it's possible the data is malformed. HealthBox will only accept imported data that meets the same validation requirements as normal submissions.
        - If the initial instance makes use of custom health metrics or food nutrients, then the new instance needs to support the same values.
15. Optionally, confirm that the information was imported properly by reviewing information on the "Manage Data" and "Manage Food" pages from the main dashboard.


### External to Self-Hosted

**On the initial instance**:
1. Sign into your account.
2. Click the "Management" button at the top of the main dashboard.
3. Click the "Export Data" button.
4. Download the exported JSON file.
5. Optionally, open the JSON file to verify that all information was backed-up as expected.
6. Return to the main dashboard by pressing the "Back" button.
7. Click the "Manage Services" button.
8. Under the "View Service" section, take note of each service name and identifier.

**On your new self-hosted instance**:
1. Sign into your account.
2. Click the "Manage Services" button.
3. Register a new service for each of the services you had on the initial HealthBox instance.
    - You can use the same service names for conveinence, or change them to something new.
4. Log into your self-hosted server's console using whichever method you prefer (SSH, FTP, physical access, etc.)
5. Navigate to the HealthBox data directory.
    - By default, this directory is generally set to `/var/www/protected/healthbox`
6. Inside the data directory, open the `service.json` file.
7. Locate your account username. You should see each of the services you've created in the previous steps.
8. Using the service identifiers noted from the initial instance, you can directly modify the service IDs in the `service.json` file to match.
    - Make sure that the following are true:
        - You're modifying the service IDs under your username, not someone else (assuming there are other accounts on your instance).
        - The service IDs you're copying in are not duplicates of ones already present anywhere else in the database.
            - Having two different services with the same identifier (even across users) will break HealthBox.
        - You maintain the JSON syntax. (Don't remove any brackets, quotes, commas, or colons)
        - The service IDs from the initial instance match the ones you're pasting in exactly.
            - A single character difference in the ID will cause HealthBox to recognize them as entirely difference services.
9. Back in the HealthBox web interface, return to the service management page if you aren't still there.
10. Refresh the page.
11. Confirm that the service IDs have been updated to match the ones noted from the initial instance.
12. Under the "Register Service" section, create a new service with a recognizable name, such as "DataImport".
    - The name you choose can be anything you want, but you'll need to remember it for the following steps.
13. Under the "View Service" section, click "Update" next to your new service.
14. Under the "Control Actions" section, grant the following action permissions:
    - data-writeall
    - foods-add
    - foods-edit
15. Press the "Back" button at the top of the page to return to the service management page.
16. Press the "Back" button at the top of the page once again to return to the main dashboard.
17. Press the "Management" button at the top of the page.
18. Press the "Import Data" button.
19. After reviewing the warning, press "Confirm".
20. Under the "Service" drop-down, select the new service.
21. Upload the back-up JSON file exported from the initial HealthBox instance.
22. Press "Submit".
23. Assuming the uploaded JSON file is valid, you should see a message reading "Import success".
    - If the import process fails, here are common reasons for errors:
        - If you tampered with the exported JSON file between downloading it and restoring it on the new instance, then it's possible the data is malformed. HealthBox will only accept imported data that meets the same validation requirements as normal submissions.
        - If the initial instance makes use of custom health metrics or food nutrients, then the new instance needs to support the same values.
24. Return to the console logged into the server hosting HealthBox (SSH, FTP, physical access, etc.)
25. Navigate to the HealthBox data directory.
    - This directory is set to `/var/www/protected/healthbox/` by default.
26. Inspect the contents of both the `food.json` and `data.json` files.
    - Ensure that all data from the initial instance was imported properly.
    - In particular, you should make sure that the service identifier associated with each entry is the same as the services initially used to submit them, **not** the service used to import them on the new instance.
        - In other words, you should not find the service used to import the data anywhere in either the `food.json` or `data.json` files.
27. Return to the HealthBox web-interface, and navigate back to the main dashboard.
28. Click the "Manage Services" button.
29. If desired, you can now removed the service used to import the data by clicking it's identifier under the "View Service" section, then pressing the "Remove" button under the "Remove Service" section.
    - Assuming you copied over the initial service identifiers properly in the earlier steps, shouldn't result in any data loss. However, if any of the entries in the food or health databases are still associated with the data import service at the time of deletion, then those entries will be removed as well.
