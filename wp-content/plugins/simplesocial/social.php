<?php
/*
Plugin Name: SimpleSocial
Plugin URI: https://simplesocial.pro/
Description: Add icons for your social media profiles.
Version: 2022
Requires at least: 5.0
Author: Bryan Hadaway
Author URI: https://calmestghost.com/
License: GPL
License URI: https://www.gnu.org/licenses/gpl.html
Text Domain: simplesocial
*/

if ( !defined( 'ABSPATH' ) ) {
	http_response_code( 404 );
	die();
}

add_action( 'admin_notices', 'simplesocial_admin_notice' );
function simplesocial_admin_notice() {
	$user_id = get_current_user_id();
	if ( !get_user_meta( $user_id, 'simplesocial_notice_dismissed_3' ) && current_user_can( 'manage_options' ) ) {
		echo '<div class="notice notice-info"><p><a href="?simplesocial-dismiss" class="alignright">' . __( 'Dismiss', 'simplesocial' ) . '</a>' . __( '<big><strong>SimpleSocial</strong></big> — <a href="https://wordpress.org/support/plugin/simplesocial/reviews/#new-post" target="_blank">We need your help!</a> 😥 Please take 30 sec to let us know what you like and what sucks about SimpleSocial. Thank you. 💛', 'simplesocial' ) . '</p></div>';
	}
}

add_action( 'admin_init', 'simplesocial_notice_dismissed' );
function simplesocial_notice_dismissed() {
	$user_id = get_current_user_id();
	if ( isset( $_GET['simplesocial-dismiss'] ) ) {
		add_user_meta( $user_id, 'simplesocial_notice_dismissed_3', 'true', true );
	}
}

add_action( 'admin_menu', 'simplesocial_menu_link' );
function simplesocial_menu_link() {
	add_options_page( __( 'SimpleSocial Settings', 'simplesocial' ), __( 'SimpleSocial', 'simplesocial' ), 'manage_options', 'simplesocial', 'simplesocial_options_page' );
}

add_action( 'admin_init', 'simplesocial_admin_init' );
function simplesocial_admin_init() {
	add_settings_section( 'simplesocial-section', __( '', 'simplesocial' ), 'simplesocial_section_callback', 'simplesocial' );
	add_settings_field( 'simplesocial-field', __( '', 'simplesocial' ), 'simplesocial_field_callback', 'simplesocial', 'simplesocial-section' );
	register_setting( 'simplesocial-options', 'simplesocial_icon_size', 'sanitize_text_field' );
	register_setting( 'simplesocial-options', 'simplesocial_icon_color', 'sanitize_text_field' );
}

function simplesocial_section_callback() {
	echo __( '', 'simplesocial' );
}

function simplesocial_field_callback() {
	echo "<div class='postbox'><h2>Universal Icon Style</h2>";
	$size = get_option( 'simplesocial_icon_size' );
	echo "<h3>Icon Size</h3><input type='number' size='10' name='simplesocial_icon_size' value='$size' placeholder='Default is 32' />";
	$color = get_option( 'simplesocial_icon_color' );
	echo "<h3>Icon Color</h3><input type='text' size='6' name='simplesocial_icon_color' data-jscolor='{required:false}' value='$color' /></div>";
}

function simplesocial_options_page() {
	?>
	<div id="simplesocial" class="wrap">
		<?php
		wp_register_script( 'simplesocial-script', plugin_dir_url( __FILE__ ) . 'js/script.js' );
		wp_enqueue_script( 'simplesocial-script' );
		wp_register_style( 'simplesocial-style', plugin_dir_url( __FILE__ ) . 'css/style.css' );
		wp_enqueue_style( 'simplesocial-style' );
		?>
		<h1><?php _e( 'SimpleSocial Settings', 'simplesocial' ); ?></h1>
		<p><?php _e( 'Thank you for using SimpleSocial by <a href="https://calmestghost.com/" target="_blank">Bryan Hadaway</a>. Need help? <a href="mailto:bhadaway@gmail.com">Email me</a>.', 'simplesocial' ); ?></p>
		<p><?php _e( 'Create a custom menu under <em>Appearance > Menus</em>, assign it to the SimpleSocial location, and then use one of the following to add icons anywhere in your theme:', 'simplesocial' ); ?></p>
        <pre><code>[simplesocial]</code></pre>
		<pre><code>&lt;?php echo do_shortcode( '[simplesocial]' ); ?&gt;</code></pre>
		<details>
			<summary><?php _e( '1500+ Icons Available in Pro', 'simplesocial' ); ?></summary>
			<p><a href="https://simplesocial.pro/" target="_blank" class="button-primary"><?php _e( 'Upgrade to Pro', 'simplesocial' ); ?></a></p>
			<ul>
				<?php
				require_once( 'inc/names.php' );
				foreach ( $names as $name ) {
					echo '<li>' . $name . '</li>';
				}
				?>
			</ul>
			<p><a href="https://simplesocial.pro/" target="_blank" class="button-primary"><?php _e( 'Upgrade to Pro', 'simplesocial' ); ?></a></p>
		</details>
		<form action="options.php" method="post">
			<?php settings_fields( 'simplesocial-options' ); ?>
			<?php do_settings_sections( 'simplesocial' ); ?>
			<?php submit_button(); ?>
		</form>
	</div>
	<?php
}

register_nav_menus( array( 'simplesocial' => esc_html__( 'SimpleSocial', 'simplesocial' ) ) );

add_shortcode( 'simplesocial', 'simplesocial_shortcode' );
function simplesocial_shortcode() {
	wp_enqueue_script( 'jquery' );
	add_action( 'wp_footer', 'simplesocial_footer_scripts', 100 );
	ob_start();
	$menu_name = 'simplesocial';
	if ( ( $locations = get_nav_menu_locations() ) && isset( $locations[ $menu_name ] ) ) {
		$menu 		= wp_get_nav_menu_object( $locations[ $menu_name ] );
		$menu_items = wp_get_nav_menu_items( $menu->term_id );
		echo '<div class="simplesocial">';
		foreach ( ( array ) $menu_items as $key => $menu_item ) {
			$title = $menu_item->title;
			$url   = $menu_item->url;
			if ( strpos( strtolower( $title ), 'facebook' ) !== false || strpos( $url, 'facebook' ) !== false ) {
				$social = 'facebook';
			} elseif ( strpos( strtolower( $title ), 'twitter' ) !== false || strpos( $url, 'twitter' ) !== false ) {
				$social = 'twitter';
			} elseif ( strpos( strtolower( $title ), 'linkedin' ) !== false || strpos( $url, 'linkedin' ) !== false ) {
				$social = 'linkedin';
			} elseif ( strpos( strtolower( $title ), 'instagram' ) !== false || strpos( $url, 'instagram' ) !== false ) {
				$social = 'instagram';
			} elseif ( strpos( strtolower( $title ), 'pinterest' ) !== false || strpos( $url, 'pinterest' ) !== false ) {
				$social = 'pinterest';
			} elseif ( strpos( strtolower( $title ), 'youtube' ) !== false || strpos( $url, 'youtube' ) !== false ) {
				$social = 'youtube';
			} else {
				$social = '';
			}
			echo '<a href="' . $url . '" title="' . $title . '" rel="me" target="_blank"><img src="' . plugin_dir_url( __FILE__ ) . 'img/' . $social . '.svg" alt="' . $title . '" class="svg" /></a>';
		}
		echo '</div>';
		$size  = get_option( 'simplesocial_icon_size' );
		$color = get_option( 'simplesocial_icon_color' );
		echo '<style>.simplesocial{text-align:center}.simplesocial a, .simplesocial a:hover, .simplesocial a:focus{color:' . ( $color ? $color : 'gray' ) . ' !important;text-decoration:none !important;box-shadow:none !important}.simplesocial .svg{display:inline-block !important;max-width:' . ( $size ? $size : '32' ) . 'px !important;border:none !important;margin:10px !important;opacity:1 !important;transition:all 0.5s ease !important}.simplesocial .svg:hover, .simplesocial a:focus .svg{opacity:0.8 !important}</style>';
	} else {
		_e( '<p>You need to create a custom menu and assign it to SimpleSocial under <em>Appearance > Menus</em> before icons will appear here.</p>', 'simplesocial' );
	}
	$output = ob_get_clean();
	return $output;
}

function simplesocial_footer_scripts() {
	?>
	<script>
	jQuery(document).ready(function ($) {
		$("img.svg").each(function () {
			var $img = $(this);
			var imgURL = $img.attr("src");
			var attributes = $img.prop("attributes");
			$.get(imgURL, function (data) {
				var $svg = $(data).find("svg");
				$svg = $svg.removeAttr("xmlns:a");
				$.each(attributes, function () {
					$svg.attr(this.name, this.value);
				});
			$img.replaceWith($svg);
			}, "xml");
		});
	});
	</script>
	<?php
}

add_filter( 'widget_text', 'do_shortcode' );

register_activation_hook( __FILE__, 'simplesocial_deactivate_pro' );
function simplesocial_deactivate_pro() {
	deactivate_plugins( 'simplesocialpro/social.php' );
}