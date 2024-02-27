<?php

/**
 * Enqueue the admin styles for the plugin
 *
 * @return void
 */
function claim_review_admin_style() {

	$plugin_data = get_plugin_data( __FILE__ );
	wp_register_style( 'claim_review_admin_css', EE24_PLUGIN_URL . '/css/admin-styles.css', false, $plugin_data['Version'] );
	wp_enqueue_style( 'claim_review_admin_css' );

    wp_enqueue_script('media-upload');
    wp_enqueue_script('thickbox');
    wp_enqueue_style('thickbox');

    wp_register_script( 'claim_review_tomselect', 'https://cdnjs.cloudflare.com/ajax/libs/tom-select/2.3.1/js/tom-select.complete.js' );
	wp_register_style( 'claim_review_tomselect_css', 'https://cdnjs.cloudflare.com/ajax/libs/tom-select/2.3.1/css/tom-select.min.css' );
	wp_enqueue_style( 'claim_review_tomselect_css' );

	wp_register_style( 'claim_review_fontawesome', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css' );
	wp_enqueue_style( 'claim_review_fontawesome' );


	wp_register_script( 'claim_review_admin_js', EE24_PLUGIN_URL . '/js/claim-review-admin.js', array( 'jquery', 'jquery-ui-core', 'jquery-ui-datepicker', 'claim_review_tomselect', 'media-upload', 'thickbox' ), $plugin_data['Version'] );
	wp_register_style( 'claim_review_jquery_css', EE24_PLUGIN_URL . '/css/jquery-styles.css', false, $plugin_data['Version'] );
	wp_enqueue_style( 'claim_review_jquery_css' );

	$metabox = array(
		'metabox' => claim_review_build_claim_box( '%%JS%%' ),
	);

	wp_localize_script( 'claim_review_admin_js', 'metabox', $metabox );

	wp_enqueue_script( 'claim_review_admin_js' );
	wp_enqueue_script( 'claim_review_tomselect' );
}
add_action( 'admin_enqueue_scripts', 'claim_review_admin_style' );
