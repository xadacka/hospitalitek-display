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
define('DB_NAME', 'rds-local');

/** MySQL database username */
define('DB_USER', 'wp-user');

/** MySQL database password */
define('DB_PASSWORD', '(uSer-wp$$).mb');

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
define('AUTH_KEY',         'N8WDD=X3*@ijX7)xZ20XOaXThVmjP8qO{prm`jb<GstGT67CEMnW2dspjL!>V1 h');
define('SECURE_AUTH_KEY',  'R~n8NP,iL[5i)b,7,nn$}#lZHQaPm&0:N=$ ^v(Qkg_C b?r$wOj{Nz{MA),JmK^');
define('LOGGED_IN_KEY',    'rwCUKFUm1YAB,tJxC6P[}zhU/p@=0Yg]:5>dyPc]wvbBDBkIvypC;=H!0u~xNr8~');
define('NONCE_KEY',        '1i~SDR$.[ QZfCO2(w=F0c+S2i+2a,*lTT+[-Oyh@#`x:f5!B1o~0M=d_KH1[.?$');
define('AUTH_SALT',        'ZYoIU#P&+sjjAM=ra18,G)JpMlZ/,6U>@t@`s[K<$9Y=>?}N=c;HX6_rG^Iv9a Y');
define('SECURE_AUTH_SALT', 'BGo-xGfIVs>pP?l2~8Y9hm]{Ud^zdgz:WIno{no&%CV2Xq^>F)QTFQlm-iJNN) ,');
define('LOGGED_IN_SALT',   'l-.!UD`weA>T-{[*|(rz]inV6L2voeD,[Dx~M)@sE>(-Zykttrx-yBAu[:f,WL_]');
define('NONCE_SALT',       'MsYK<%*@,}`ZsUp gI)IG+0}J!?lB1Wc*xE=QSS[3A$F21kJUr,By.ubI`6sQR:[');

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
