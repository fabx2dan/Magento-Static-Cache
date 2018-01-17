# Magento Static Content Cache

Magento 1.x cache extension optimized for static content.

This extension enables the user to cache static pages with more efficiency than standard cache or FPCs. Given specific URIs, after the first call it will store the results using GET and POST params to generate a unique key.

**Please note**: this extension must not be used to cache dynamic content. Its use is specific for static content, such as external widgets, descriptive CMS pages and so on.

## Settings ##

In System -> Configuration you can set the URIs that you wish to cache (without params) and a standard cache lifetime.
For instance:
- */customroute/customcontroller/customaction*
- */static-page.html*

![alt text](https://image.ibb.co/bTeD96/Untitled.png)

In Cache Management there will be a dedicated item to flush and disable the cache.

![alt text](https://image.ibb.co/jsBzhR/Untitled1.png)

## Compatibility ##

Tested with Magento 1.9.x version. Compatible with Magento 1.6, 1.7, 1.8 and 1.9.
