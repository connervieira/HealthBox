# Installation

This document explains how to install, setup, and use HealthBox.


## Commercial Support

While HealthBox can be installed independently by those with technical experience, V0LT offers one-on-one technical support for the installation process for users who are interested in more direct guidance. If you're an individual or business user interested in one-on-one support for the HealthBox installation, setup, usage, and/or development process, you can contact V0LT to schedule a call or meeting at <https://v0lttech.com/contact.php>. V0LT charges a flat rate of $25/hour. The majority of installs will take less than 2 hours to complete from scratch.


## Dependencies

There are a few dependencies that need to be installed for HealthBox to function.

### Web Server

1. Install Apache, or another web-server host.
    - Example: `sudo apt-get install apache2`
2. Install and enable PHP for your web-server.
    - Example: `sudo apt-get install php; sudo a2enmod php*`
3. Restart your web-server host.
    - Example: `sudo apache2ctl restart`

### DropAuth

HealthBox depends on DropAuth for authentication. Downloads for DropAuth can be found at <https://v0lttech.com/dropauth.php>. By default, HealthBox expects DropAuth to be installed in a directory next to itself. For example, if HealthBox is installed at `/var/www/html/healthbox/`, DropAuth should be installed at `/var/www/html/dropauth/`. This path can be manually changed by modifying the `config.json` file in the HealthBox directory, which will be created the first time HealthBox is loaded.


## Installation

After the dependencies are installed, copy the HealthBox directory from the source you received it from, to the root of your web-server directory.

For example: `cp ~/Downloads/HealthBox /var/www/html/healthbox`


## Set Up

### Support Directory

HealthBox stores support files in directory outside of the default web-server location for sake of security. By default, the `/var/www/protected/healthbox` directory is used for this purpose. This path can be manually changed by modifying the `config.json` file in the HealthBox directory, which will be created the first time HealthBox is loaded.

Make sure the main HealthBox directory and the support directory are both writable to PHP.

For example: `sudo mkdir -p /var/www/protected/healthbox; sudo chown www-data /var/www/protected/healthbox/; sudo chmod 777 /var/www/html/healthbox/


### Connecting

After the basic set-up process is complete, you should be able to view the HealthBox interface in a web browser.

1. Open a web browser of your choice.
2. Enter the URL for your HealthBox installation.
    - Example: `http://192.168.0.76/healthbox/`
3. If everything has been set-up properly, you should be redirected to DropAuth.
4. After logging in to DropAuth, you can return to the HealthBox URL if not redirected automatically.


### Configuring

Once you've verified that HealthBox is working as expected, you should configure it. To access the configuration interface, click the "Configure" button on the main HealthBox page.

- **Authentication** contains settings for controlling how users authenticate with this HealthBox instance.
    - **Provider** determines the authentication provider (DropAuth instance) that HealthBox will use.
        - **Core Provider** points to the primary authentication script that will be loaded on every page.
        - **Sign-In Page** determines where users who are not authenticated will be redirected to sign in.
        - **Sign-Out Page** determines where users will be redirected when the want to log out.
        - **Sign-Up Page** determines where users will be redirected if they want to create an account.
    - **Access** determines how users are permitted to access this HealthBox instance.
        - **Admin** is a comma-separated list of users who are allowed to configure this instance. These users override the access mode (whitelist/blacklist).
        - **Whitelist** is a comma-separated list of users who are exclusively allowed access when HealthBox is in whitelist access mode.
        - **Blacklist** is a comma-separated list of users who are prohibited from accessing HealthBox when in blacklist mode.
            - If HealthBox is in blacklist mode, and this list is left blank, then all users with accounts on the connected DropAuth instance will be able to access HealthBox.
        - **Mode** determines the access mode that HealthBox is operating in.
            - In whitelist mode, only users specified in the whitelist can access this HealthBox instance.
                - In the vast majority of cases, private users should leave HealthBox in this mode.
            - In blacklist mode, everyone except users specified in the blacklist can access this HealthBox instance.
