# Development

This document explains the basics of developing services that integrate with HealthBox.


## Introduction

Practically all interaction with HealthBox from external services is done through GET requests. HealthBox contains various endpoints for completing tasks like registering new datapoints, editing foods, and fetching health data.

### Endpoints

Here is a list of the endpoints currently implemented by HealthBox.

- **/healthbox/submit.php**: Used to submit datapoints to health metrics.
- **/healthbox/fetch.php**: Used to fetch health information for a particular metric.
- **/healthbox/updatefood.php**: Used to add or edit existing foods.
