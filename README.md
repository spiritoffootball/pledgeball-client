# Pledgeball Client

Enables access to the Pledgeball API.

## Description

*Pledgeball Client* is a WordPress plugin that enables access to the Pledgeball API.

## Installation

There are two ways to install from GitHub:

### ZIP Download

If you have downloaded this plugin as a ZIP file from the GitHub repository, do the following to install the plugin:

1. Unzip the .zip file and, if needed, rename the enclosing folder so that the plugin's files are located directly inside `/wp-content/plugins/pledgeball-client-side`
2. Activate the plugin.
3. You're done.

### `git clone`

If you have cloned the code from GitHub, it is assumed that you know what you're doing.

## Setup

To use this plugin to access the Pledgeball API, there needs to be:

* A WordPress User on the API host site with the appropriate capabilities.
* An Application Password added to the WordPress User.

When these have been set up, add the following to `wp-config.php` or similar on the client site:

```php
/**
 * Pledgeball URL and credentials.
 *
 * @see https://github.com/pledgeball/pledgeball-client-side
 */
define( 'PLEDGEBALL_URL', 'https://pledgeball.com' );
define( 'PLEDGEBALL_USER', 'sofusername' );
define( 'PLEDGEBALL_PWD', 'nB0X 76eZ czKK QLt9 TVPI ZgEu' );

// Add this if your API host is on locahost - bypasses SSL checks.
define( 'PLEDGEBALL_HOST', 'localhost' );
```

The plugin is now ready to use.

Note: at some point, these credentials can be stored via an admin settings page.
