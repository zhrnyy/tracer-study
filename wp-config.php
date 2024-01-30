<?php
/**
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the installation.
 * You don't have to use the web site, you can copy this file to "wp-config.php"
 * and fill in the values.
 *
 * This file contains the following configurations:
 *
 * * Database settings
 * * Secret keys
 * * Database table prefix
 * * ABSPATH
 *
 * @link https://wordpress.org/documentation/article/editing-wp-config-php/
 *
 * @package WordPress
 */

// ** Database settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define( 'DB_NAME', 'tracer_study_db' );

/** Database username */
define( 'DB_USER', 'root' );

/** Database password */
define( 'DB_PASSWORD', '' );

/** Database hostname */
define( 'DB_HOST', 'localhost' );

/** Database charset to use in creating database tables. */
define( 'DB_CHARSET', 'utf8mb4' );

/** The database collate type. Don't change this if in doubt. */
define( 'DB_COLLATE', '' );

/**#@+
 * Authentication unique keys and salts.
 *
 * Change these to different unique phrases! You can generate these using
 * the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}.
 *
 * You can change these at any point in time to invalidate all existing cookies.
 * This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define( 'AUTH_KEY',         'TlNW<c#tH^KLT7s!_-`h/u/o~=I%a2{]D5t_AQTBU7lG~0%[E<+Hiu=}X?pC`dM;' );
define( 'SECURE_AUTH_KEY',  'f{KQ;3^2xO!~m#Xu*Srh47?<x$B3[bHV~-#5$#c]wp)O0w.<iGTxK<j?~scaRF|z' );
define( 'LOGGED_IN_KEY',    '7~s.~b%NwM6i}4mOh;tJ(oM%ZXYbuD$.T;YbFetZ]?+Js<eB{&aT^h.?g*rr ~-5' );
define( 'NONCE_KEY',        '<wsat_xy`b.ub,6>N>~8wz%Z#M&qHIbl8LqE%~4bD7R|!iJP~J{-7SQeL$`%N_~J' );
define( 'AUTH_SALT',        'h:te4U_Z!K[]:.{9r&H!isupNS(~qAaNGU=T&aT#_^tx?C/<iiYO3G{?:} F)&8s' );
define( 'SECURE_AUTH_SALT', 'EJMUu<+sVE*_~c8@~st8j e},%`JVn1FRylY>{<l&qyc/n>m>NNy2x&+I-Xn7]yS' );
define( 'LOGGED_IN_SALT',   'lC]+u)<b`zRFTq2E~Z[an*n@?Ks3s@LjS)H}zd2*^{w:<re=Ch4Kpksl!E{(7H&g' );
define( 'NONCE_SALT',       'cphD-QMG:18;582Ks_;OkCAOS@=5encg$hzW*Ce6@j1C1.AJZ?*.zZZDc,6U PaE' );

/**#@-*/

/**
 * WordPress database table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix = 'wp_';

/**
 * For developers: WordPress debugging mode.
 *
 * Change this to true to enable the display of notices during development.
 * It is strongly recommended that plugin and theme developers use WP_DEBUG
 * in their development environments.
 *
 * For information on other constants that can be used for debugging,
 * visit the documentation.
 *
 * @link https://wordpress.org/documentation/article/debugging-in-wordpress/
 */
define( 'WP_DEBUG', isset($_GET['debug']) );

/* Add any custom values between this line and the "stop editing" line. */



/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';
