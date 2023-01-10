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
 * @link https://wordpress.org/support/article/editing-wp-config-php/
 *
 * @package WordPress
 */

// ** Database settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define( 'DB_NAME', 'linkedpie' );

/** Database username */
define( 'DB_USER', 'root' );

/** Database password */
define( 'DB_PASSWORD', '' );

/** Database hostname */
define( 'DB_HOST', '127.0.0.1' );

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
define( 'AUTH_KEY',         'Kbjj|wU,rN6Y2D{y!&*8oQ%0i)Zs`K%7*CjhGz2+[3shAp-{.lDG7{sW`I6[?rFC' );
define( 'SECURE_AUTH_KEY',  'l$26S_d9R&CnPB;oH_Hfp{!]IcYkEF:Gx`~9G^*4FJwONP9g4h~lr.|hW!Q<_FjC' );
define( 'LOGGED_IN_KEY',    'rFFI1ZK8%g&fAc6hRz|6hxjI).NA, C,ZN@ `?6V#A<fvpxK.V:V/ckJ_ra?EPx:' );
define( 'NONCE_KEY',        '@X,-w#;@|CT#hCFDE$FlK8:$sc3U*B59^I6a}B,%$ftIPiY3X]Ry]6(7PZFZgSxu' );
define( 'AUTH_SALT',        '/ywIkf+E5R^$69/*$,2U!tE$^7<]@BdY}V)wsa8as/=KYpdf5p^{P!,Z5<,v1HeJ' );
define( 'SECURE_AUTH_SALT', 'qQ$Rq rAa.N%e7bE)G:M7Q$[rk+`jC1E63rD;y^3#@gStr2hj:RKIcRNr~vVTjqL' );
define( 'LOGGED_IN_SALT',   'nP=IV~.Y/*aAbU $!2:RUCQ91nFx*;hw|!tkF~kD-Q>1B]bT$ fPA3^JwDC,l%Tb' );
define( 'NONCE_SALT',       '5;_Mj~oQoQjrr|L<MI|5chMWnBB:^c~Er2ApwTeCQ2q.qn|I##nF^T{l=Q=f!3W|' );

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
 * @link https://wordpress.org/support/article/debugging-in-wordpress/
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
