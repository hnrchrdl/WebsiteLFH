<?php
/**
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the
 * installation. You don't have to use the web site, you can
 * copy this file to "wp-config.php" and fill in the values.
 *
 * This file contains the following configurations:
 *
 * * MySQL settings
 * * Secret keys
 * * Database table prefix
 * * ABSPATH
 *
 * @link https://codex.wordpress.org/Editing_wp-config.php
 *
 * @package WordPress
 */

// ** MySQL settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define('DB_NAME', 'landfrauenharburgde');

/** MySQL database username */
define('DB_USER', 'root');

/** MySQL database password */
define('DB_PASSWORD', 'root');

/** MySQL hostname */
define('DB_HOST', 'localhost');

/** Database Charset to use in creating database tables. */
define('DB_CHARSET', 'utf8mb4');

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
define('AUTH_KEY',         'IG($S{95&h*0,Yi%OWtgL<wmmPN*n-zN#f $`j$=KVhf`t+(m;}lLoKIdhr:qE7q');
define('SECURE_AUTH_KEY',  'mNJ /sDtx&-)iH8]LZ?).R6TlPLO!y6f4cr9v@Ow4RLrd=XEnRgw}OR0M8b9Tjm]');
define('LOGGED_IN_KEY',    '7KXzQgHgg|v$!=P-73L#H|pK&z;oZ(|O/n@@m:9&5+;mn1fw0 +a$ >%I$zY0CkM');
define('NONCE_KEY',        'Ga]_NM<cp/&S,mJkdjMf4c6bA:OD )dSOsGn)=Vg+-e:*^_eK0f?^r e(LUXBEzh');
define('AUTH_SALT',        '}pXk9?moCeM-a1D%MGdzr9YaARTST.K$4bwF9LwjjxjgvY:zKGul({WR:B?qnb%*');
define('SECURE_AUTH_SALT', 'Q Hfou-<LZ|Zs(^nQ,E*ZWB(1|lS}^?:(:Z=Zf1pY(#8N<FSr*!.A[`.>n3o%zUN');
define('LOGGED_IN_SALT',   ')LX+~eMcAh-Ta6@pU^F(mS1&d{@wA3`b+J633jz-62&h+.*!AYy:iPmwuVG_j:=-');
define('NONCE_SALT',       'B`XGh*w/RijuXbc_et+##KWGtH(~bf>@#m8@)t;Z_NX4$|0-wQu)F0f6V0kZ8] ;');

/**#@-*/

/**
 * WordPress Database Table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix  = 'wp_';

/**
 * For developers: WordPress debugging mode.
 *
 * Change this to true to enable the display of notices during development.
 * It is strongly recommended that plugin and theme developers use WP_DEBUG
 * in their development environments.
 *
 * For information on other constants that can be used for debugging,
 * visit the Codex.
 *
 * @link https://codex.wordpress.org/Debugging_in_WordPress
 */
define('WP_DEBUG', false);

/* That's all, stop editing! Happy blogging. */

/** Absolute path to the WordPress directory. */
if ( !defined('ABSPATH') )
	define('ABSPATH', dirname(__FILE__) . '/');

/** Sets up WordPress vars and included files. */
require_once(ABSPATH . 'wp-settings.php');

