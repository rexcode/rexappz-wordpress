<?php
/**
 * The base configurations of the WordPress.
 *
 * This file has the following configurations: MySQL settings, Table Prefix,
 * Secret Keys, and ABSPATH. You can find more information by visiting
 * {@link http://codex.wordpress.org/Editing_wp-config.php Editing wp-config.php}
 * Codex page. You can get the MySQL settings from your web host.
 *
 * This file is used by the wp-config.php creation script during the
 * installation. You don't have to use the web site, you can just copy this file
 * to "wp-config.php" and fill in the values.
 *
 * @package WordPress
 */

// ** MySQL settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define('DB_NAME', 'u753928040_bataq');

/** MySQL database username */
define('DB_USER', 'u753928040_begag');

/** MySQL database password */
define('DB_PASSWORD', 'VyqabeZaqa');

/** MySQL hostname */
define('DB_HOST', 'mysql');

/** Database Charset to use in creating database tables. */
define('DB_CHARSET', 'utf8');

/** The Database Collate type. Don't change this if in doubt. */
define('DB_COLLATE', '');

/**#@+
 * Authentication Unique Keys and Salts.
 *
 * Change these to different unique phrases!
 * You can generate these using the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}
 * You can change these at any point in time to invalidate all existing cookies. This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define('AUTH_KEY',         'ryB6fiiz28VxzkgfpNewsD8SOANQHEydHdXWGC8y1rr2GNqAOLF8QDRYkyApLTZQ');
define('SECURE_AUTH_KEY',  'cEVr7cZ9GxzBrj5EzcyQpmsnew3S7fyBnr4k6sswVNtFydQin71q7JTez2XDxQAK');
define('LOGGED_IN_KEY',    'DBO7HXGwZlK7MRwTVgwqxBMXvyhkzPN996QQq99NBxGnOP5FkdvdQJKcDVe7UAxW');
define('NONCE_KEY',        'tjM7WZf119GuBus95ervDz6FbHahelEii2VMtHZ9CY2cbJ5u6DWXfMFJdmM9t02j');
define('AUTH_SALT',        'K6XchG19PxDw0r81ahDXMq7QpAXNFagjXSsNZXNqdcPskITLzFIuMyjY24pS0cXC');
define('SECURE_AUTH_SALT', 'c9yzfKDn5FjxZN5v4DzkVkBDDutxTzyGrt1I3ipLmhlIotB2J0xh71FuNPpayueK');
define('LOGGED_IN_SALT',   'Uw8T7NFk0gmQB7HqzfEUJPYLS4vPlMtKu1oBgxJaLdLxXYgH9sLs2et6KFAi85WF');
define('NONCE_SALT',       'BSqLSqRAR7kxZAorHFpTIf0Co86KwOKBTXt6nmJCjwB4RycTPF3zeZYerGlE9hhc');

/**
 * Other customizations.
 */
define('FS_METHOD','direct');define('FS_CHMOD_DIR',0755);define('FS_CHMOD_FILE',0644);
define('WP_TEMP_DIR',dirname(__FILE__).'/wp-content/uploads');

/**
 * Turn off automatic updates since these are managed upstream.
 */
define('AUTOMATIC_UPDATER_DISABLED', true);


/**#@-*/

/**
 * WordPress Database Table prefix.
 *
 * You can have multiple installations in one database if you give each a unique
 * prefix. Only numbers, letters, and underscores please!
 */
$table_prefix  = 'zsyd_';

/**
 * For developers: WordPress debugging mode.
 *
 * Change this to true to enable the display of notices during development.
 * It is strongly recommended that plugin and theme developers use WP_DEBUG
 * in their development environments.
 */
define('WP_DEBUG', false);

/* That's all, stop editing! Happy blogging. */

/** Absolute path to the WordPress directory. */
if ( !defined('ABSPATH') )
	define('ABSPATH', dirname(__FILE__) . '/');

/** Sets up WordPress vars and included files. */
require_once(ABSPATH . 'wp-settings.php');
