<?php

/**
 * Enqueue the admin styles for the plugin
 *
 * @return void
 */
function euroclimatecheck_admin_style() {

	$plugin_data = get_plugin_data( __FILE__ );

	wp_register_style( 'euroclimatecheck_admin_css', EUROCLIMATECHECK_PLUGIN_URL . '/EuroClimateCheck/dist/euroclimatecheck.css', false, $plugin_data['Version'] );
	wp_enqueue_style( 'euroclimatecheck_admin_css' );

	// Register the standalone Vue component (includes all dependencies)
	wp_register_script(
		'euroclimatecheck_vue_component',
		EUROCLIMATECHECK_PLUGIN_URL . '/EuroClimateCheck/dist/euroclimatecheck.js',
		array(),
		'1.0.0',
		true
	);

	// Enqueue the standalone script
	wp_enqueue_script('euroclimatecheck_vue_component');

	wp_register_style( 'euroclimatecheck_fontawesome', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css' );
	wp_enqueue_style( 'euroclimatecheck_fontawesome' );
}

add_action( 'admin_enqueue_scripts', 'euroclimatecheck_admin_style' );
