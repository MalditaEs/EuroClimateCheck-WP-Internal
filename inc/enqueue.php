<?php

/**
 * Enqueue the admin styles for the plugin
 *
 * @return void
 */
function euroclimatecheck_admin_style() {

	$plugin_data = get_plugin_data( __FILE__ );

//	if ( get_option( 'euroclimatecheck-compat' ) ) {
//		wp_register_style( 'euroclimatecheck_admin_css', EUROCLIMATECHECK_PLUGIN_URL . '/css/styles.css', false, $plugin_data['Version'] );
//		wp_enqueue_style( 'euroclimatecheck_admin_css' );
//	} else {
//	}

	wp_register_style( 'euroclimatecheck_admin_css', EUROCLIMATECHECK_PLUGIN_URL . '/EuroClimateCheck/dist/euroclimatecheck.css', false, $plugin_data['Version'] );
	wp_enqueue_style( 'euroclimatecheck_admin_css' );

	wp_enqueue_script( 'media-upload' );
	wp_enqueue_script( 'thickbox' );
	wp_enqueue_style( 'thickbox' );

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


	wp_register_script( 'euroclimatecheck_admin_js', EUROCLIMATECHECK_PLUGIN_URL . '/js/claim-review-admin.js', array(
		'jquery',
		'jquery-ui-core',
		'jquery-ui-datepicker',
		'euroclimatecheck_tomselect',
		'media-upload',
		'thickbox'
	), $plugin_data['Version'] );

	$metabox = array(
		'metabox' => euroclimatecheck_build_claim_box( '%%JS%%' ),
	);

	wp_localize_script( 'euroclimatecheck_admin_js', 'metabox', $metabox );

	wp_enqueue_script( 'euroclimatecheck_admin_js' );
	wp_enqueue_script( 'euroclimatecheck_tomselect' );
}

add_action( 'admin_enqueue_scripts', 'euroclimatecheck_admin_style' );
