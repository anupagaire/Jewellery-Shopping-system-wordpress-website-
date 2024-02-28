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
define( 'DB_NAME', 'jewshop' );

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
define( 'AUTH_KEY',         '-(NhzQx7x*1033]S`w1?B4QL/EUzcZ9A/*F%ZZ!- nUH4b+@@J3Uc{A49;:!k6K>' );
define( 'SECURE_AUTH_KEY',  '~)]Ut>m@zy(c6mU8rs&Ec#_[mG4E}x8YB2NzG%lK@T*C$fB!&ZL> lQa/-@/h{1k' );
define( 'LOGGED_IN_KEY',    'bIMQ@OUF5)Vt-icRxD[9XqaJ:Up*|dD,T!>0?zB)xx*:=^@,KMWQGI8%xx0{&U,3' );
define( 'NONCE_KEY',        '#W-4O;].AszPfJYL:`jk{^^7%J~Vf|BsDX!Oa}1E(y_M%f=:@TLg}Z4^<=S14~&R' );
define( 'AUTH_SALT',        '(uK$+5!43;E%2wuHk_}>(cZPg:dR>goo<7Dc*?)9+bb$Nzxxi:_XM.7 GHa0|&!D' );
define( 'SECURE_AUTH_SALT', '!o^y]w!5YfHk/=LJo(&8-+C]K(Six{rqyT_KVBw5_O 0H/Gos{1);>w~%j8T>]ge' );
define( 'LOGGED_IN_SALT',   'gDGQS_;Rrpo<82kiK[HpvwS0#ZsK,6b-;nKwGFmbQB9BG,pYb;Ws&M4X3Q@D>7=y' );
define( 'NONCE_SALT',       'xYk7BdcMpp;AY6~Pn_6K5<rf)2 /fO]GRt#,a+IYly!md^Z9<6_IF8bj6jg]pKMW' );

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
define( 'WP_DEBUG', false );

/* Add any custom values between this line and the "stop editing" line. */



/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';
