# Security

## Disclaimer

While HealthBox strives to be reliable and secure, you should not depend on it as your only line of defense. This document contains important security information and guidelines to ensure a safe and secure experience.


## Support

Due to HealthBox's update cycle, only the latest version receives security patches. However, in severe cases, patches for older version might be released on a case by case basis. Regardless, for the safest experience, you should always use the latest version of HealthBox.


## Reporting

In the event that you find a security issue with HealthBox, the safest way to report it is to contact V0LT directly. You can find relevant contact information, including PGP keys, at <https://v0lttech.com/contact.php>.


## Considerations

Here are some security considerations you should account for before using HealthBox.

1. For the ultimate security, you should only run HealthBox on your local network, isolated from the internet.
    - In this case, only users on your local network will have the ability to connect to HealthBox, which dramatically decreases your attack surface.
    - If your use case requires accessing HealthBox from external networks, consider using a self-hosted VPN to tunnel back to your home network, rather than directly exposing HealthBox to the internet.
2. If your use case requires allowing multiple users to access your HealthBox instance (for example, friends/family members), ensure that you have HealthBox configured to operate in whitelist mode.
    - Additionally, ensure that only users you trust completely are configured as HealthBox administrators.
3. Ensure that the HealthBox database storage directory is not accessible over the internet.
    - By default, HealthBox stores databases in the `/var/www/protected/healthbox/` directory, which is generally not accessible over your web-server (Apache).
    - If you store databases inside the `/var/www/html/` directory, then it may be possible for users to directly view databases via your web-server (Apache).
4. When granting permissions to services, only grant the minimum required permissions.
    - The damage caused by a malicious service can be mitigated by limiting the amount of data it has access to.
