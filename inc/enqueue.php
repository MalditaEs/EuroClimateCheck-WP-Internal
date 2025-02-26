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

	// First register Vue from CDN
	wp_register_script(
		'vue',
		'https://unpkg.com/vue@3/dist/vue.global.js',
		array(),
		'3.5.13',
		true
	);

	// Register the built Vue component
	wp_register_script(
		'euroclimatecheck_vue_component',
		EUROCLIMATECHECK_PLUGIN_URL . '/EuroClimateCheck/dist/euroclimatecheck.umd.cjs', // Update path to built file
		array('vue'),
		'1.0.0',
		true
	);

	// Enqueue both scripts
	wp_enqueue_script('vue');
	wp_enqueue_script('euroclimatecheck_vue_component');

	wp_register_style( 'euroclimatecheck_fontawesome', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css' );
	wp_enqueue_style( 'euroclimatecheck_fontawesome' );
}

add_action( 'admin_enqueue_scripts', 'euroclimatecheck_admin_style' );
