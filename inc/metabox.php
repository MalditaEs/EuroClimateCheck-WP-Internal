<?php

include_once 'EE24Api.php';

/**
 * Add the Claim Review Metabox to various post types
 *
 * @return void
 */
function euroclimatecheck_add_custom_box() {
	$screens    = array('post');
	foreach ( $screens as $screen ) {
		add_meta_box(
			'euroclimatecheck_repository_metabox',           // Unique ID
			__( 'EuroClimateCheck Repository', 'ee24' ),  // Box title
			'euroclimatecheck_custom_box_html',  // Content callback, must be of type callable
			$screen,                   // Post type
			'normal',
			'high'
		);
	}
}

add_action( 'add_meta_boxes', 'euroclimatecheck_add_custom_box' );

/**
 * Function to add the EuroClimateCheck meta data
 *
 * @param object $post The post object for the pag we're currently on
 *
 * @return void
 */
function euroclimatecheck_custom_box_html( $post ) {

	$ee24Metadata = get_post_meta( $post->ID, '_euroclimatecheck_repository', true );
	wp_nonce_field( basename( __FILE__ ), 'ee24_nonce' );
	echo ee24_build_claim_box( $ee24Metadata ?: [] );
}

function ee24_build_claim_box( $data ) {
    ?>
    <script type="application/json" id="euroclimatecheck-data">
    <?php
		echo json_encode([
			"data" => $data,
			"apikey" => get_option('euroclimatecheck-apikey'),
			"domain" => get_option('euroclimatecheck-domain'),
			"language" => get_option('euroclimatecheck-language'),
            "endpoint" => get_option( 'euroclimatecheck-endpoint' )
		]);
		?>
</script>
    <?php


	echo '<div id="euroclimatecheck-plugin"></div>';

	?>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            if (window.mountEuroClimateCheck) {
                window.mountEuroClimateCheck('#euroclimatecheck-plugin')
            }
        });
    </script>
	<?php


	$apikey = get_option( 'euroclimatecheck-apikey' );
	$domain = get_option( 'euroclimatecheck-domain' );

	return "<div id='euroclimatecheck-data' data-endpoint='" . get_option( 'euroclimatecheck-endpoint' ) . "' data-apikey='$apikey' data-domain='$domain'></div>";
}

/**
 * Helper function to get an arrow to put anywhere.
 *
 * @return string
 */
function ecc_claimbox_get_arrow() {
	return '<svg class="claim-review-arrow" width="24px" height="24px" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" role="img" aria-hidden="true" focusable="false"><g><path fill="none" d="M0,0h24v24H0V0z"></path></g><g><path d="M7.41,8.59L12,13.17l4.59-4.58L18,10l-6,6l-6-6L7.41,8.59z"></path></g></svg>';
}

function ee24_save_data( $post_id, $post ) {
	updateEE24Metadata( $post_id );
}

function ee24_publish_data( $post_id, $post ) {

	$data = updateEE24Metadata( $post_id );

	if ( $data === null ) {
		return;
	}

	if ( $post->post_status !== 'publish' ) {
		return;
	}

	// Validate required fields before sending to repository
	$validationErrors = validateEE24RequiredFields($data);

	if (!empty($validationErrors)) {
		// Store validation errors in transient
		set_transient("ee24_validation_errors", $validationErrors, 60);
		// Set error message
		set_transient("ee24_error", "Cannot submit to repository: Required fields are missing", 60);
		return;
	}

	$publishedData = get_post_meta( $post_id, '_euroclimatecheck_repository_published', true ) ?: [];

	$api = new EE24Api();

	$headers = [
		'X-API-KEY' => get_option( 'euroclimatecheck-apikey' ),
		'X-DOMAIN'  => get_option( 'euroclimatecheck-domain' ),
	];

	// Serialize dates
	$serializedData = $data;
	if ( $serializedData['datePublished'] ) {
		$serializedData['datePublished'] = $serializedData['datePublished']->format( 'Y-m-d\TH:i:s.u\Z' );
	} else {
		$date                            = new DateTime( $post->post_modified );
		$serializedData['datePublished'] = $date ? $date->format( 'Y-m-d\TH:i:s.u\Z' ) : null;
	}

	if ( $publishedData['externalId'] ?? null ) {
		// Already exists in the Repository
		try {
			$response                    = $api->sendPatchRequest( $publishedData['externalId'], $serializedData, $headers );
			$publishedData['externalId'] = json_decode( $response )->externalId;
			set_transient( "ee24_success", "Repository ID: " . $publishedData['externalId'], 25 );
		} catch ( \Throwable $e ) {
			set_transient( "ee24_error", $e->getMessage(), 25 );
		}
	} else {
		// Doesn't exist in the Repository
		try {
			$response                    = $api->sendPostRequest( $serializedData, $headers );
			$publishedData['externalId'] = json_decode( $response )->externalId;
			set_transient( "ee24_success", "Repository ID: " . $publishedData['externalId'], 25 );
		} catch ( \Throwable $e ) {
			set_transient( "ee24_error", $e->getMessage(), 25 );
		}
	}

	update_post_meta( $post_id, '_euroclimatecheck_repository', $data );
	update_post_meta( $post_id, '_euroclimatecheck_repository_published', $publishedData );
}

add_action( 'save_post', 'ee24_save_data', 11, 2 );
add_action( 'wp_after_insert_post', 'ee24_publish_data', 12, 2 );

add_action( 'admin_notices', 'ee24_admin_notice' );

function updateEE24Metadata($post_id) {
	// Check if our form data exists
	if (array_key_exists('euroclimatecheck-form-data', $_POST)) {
		// Decode the JSON data
		$formData = json_decode(stripslashes($_POST['euroclimatecheck-form-data']), true);

		if ($formData === null) {
			// JSON decode failed
			return null;
		}

		$data = get_post_meta($post_id, '_euroclimatecheck_repository', true) ?: [];

		// Update the data with form values
		$data = array_merge($data, [
			'type' => $formData['type'],
			'url' => get_permalink($post_id),
			'headlineNative' => get_the_title($post_id),
			'headline' => $formData['headline'],
			'datePublished' => get_post_datetime($post_id),
			'image' => get_the_post_thumbnail_url(get_the_ID(), 'full'),
			'keywords' => $formData['keywords'],
			'inLanguage' => $formData['inLanguage'],
			'topic' => $formData['topic'],
			'subtopics' => $formData['subtopics'],
			'contentLocation' => $formData['contentLocation'],
			'claimReviewedNative' => $formData['claimReviewedNative'],
			'claimReviewed' => $formData['claimReviewed'],
			'reviewRating' => $formData['reviewRating'],
			'multiclaim' => $formData['multiclaim'],
			'distortionType' => $formData['distortionType'],
			'aiVerification' => $formData['aiVerification'],
			'harm' => $formData['harm'],
			'harmEscalation' => $formData['harmEscalation'],
			'evidences' => $formData['evidences'],
			'claimAppearances' => array_map(function($appearance) {
				return array_merge($appearance, [
					'difussionFormat' => $appearance['difussionFormat'],
					'actionTaken' => $appearance['actionTaken']
				]);
			}, $formData['claimAppearances'] ?? []),
			'associatedClaimReview' => $formData['associatedClaimReview'] ?? []
		]);

		update_post_meta($post_id, '_euroclimatecheck_repository', $data);
		return $data;
	}

	return null;
}


function ee24_admin_notice() {
	// Get the API response stored earlier
	$error   = get_transient( 'ee24_error' );
	$success = get_transient( 'ee24_success' );
	$validationErrors = get_transient( 'ee24_validation_errors' );

	if ( $error === false && $success === false && $validationErrors === false ) {
		return;
	}

	if ( $error !== false ) {
		echo '<div class="notice notice-error is-dismissible">';
		echo '<p>' . wp_kses_post( 'Error exporting to the EuroClimateCheck Repository: ' . $error ) . '</p>';

		// Display validation errors if they exist
		if ($validationErrors !== false && is_array($validationErrors) && !empty($validationErrors)) {
			echo '<ul style="list-style-type: disc; margin-left: 20px;">';
			foreach ($validationErrors as $errorMsg) {
				echo '<li>' . wp_kses_post($errorMsg) . '</li>';
			}
			echo '</ul>';
		}

		echo '</div>';
	}

	if ( $success !== false ) {
		echo '<div class="notice notice-success is-dismissible">';
		echo '<p>' . wp_kses_post( 'The EuroClimateCheck Repository has been updated: ' . $success ) . '</p>';
		echo '</div>';
	}

	$post = get_post();
	if ( $post && ! use_block_editor_for_post( $post ) ) {
		if ( $error ) {
			delete_transient( 'ee24_error' );
			if ($validationErrors) {
				delete_transient( 'ee24_validation_errors' );
			}
		} else if ( $success ) {
			delete_transient( 'ee24_success' );
		}
	}
}

/**
 * Validate required fields for EuroClimateCheck repository
 *
 * @param array $data The data to validate
 * @return array Array of validation errors
 */
function validateEE24RequiredFields($data) {
	$validationErrors = [];

	// Skip validation if type is None
	if ($data['type'] === 'None' || empty($data['type'])) {
		return $validationErrors;
	}

	// Common required fields for both Factcheck and Prebunk
	if (empty($data['headline'])) $validationErrors[] = 'Headline in English';
	if (empty($data['headlineNative'])) $validationErrors[] = 'Headline in native language';
	if (empty($data['inLanguage'])) $validationErrors[] = 'Language of the article';
	if (empty($data['contentLocation']) || !is_array($data['contentLocation']) || count($data['contentLocation']) === 0) $validationErrors[] = 'Mentioned countries';
	if (empty($data['topic'])) $validationErrors[] = 'Topic';
	if (empty($data['subtopics']) || !is_array($data['subtopics']) || count($data['subtopics']) === 0) $validationErrors[] = 'Subtopics';
	if (empty($data['keywords']) || !is_array($data['keywords']) || count($data['keywords']) === 0) $validationErrors[] = 'Keywords';

	// Fields specific to Factcheck type
	if ($data['type'] === 'Factcheck') {
		if (empty($data['claimReviewed'])) $validationErrors[] = 'Claim text in English';
		if (empty($data['claimReviewedNative'])) $validationErrors[] = 'Claim text in native language';
		if (empty($data['reviewRating'])) $validationErrors[] = 'Rating';
		if (empty($data['distortionType']) || !is_array($data['distortionType']) || count($data['distortionType']) === 0) $validationErrors[] = 'Distortion types';

		// Validate claim appearances if they exist
		if (!empty($data['claimAppearances']) && is_array($data['claimAppearances']) && count($data['claimAppearances']) > 0) {
			foreach ($data['claimAppearances'] as $index => $appearance) {
				if (empty($appearance['platform'])) $validationErrors[] = "Claim appearance #" . ($index + 1) . ": Platform";
				if (empty($appearance['difussionFormat'])) $validationErrors[] = "Claim appearance #" . ($index + 1) . ": Diffusion format";
				if (empty($appearance['appearanceDate'])) $validationErrors[] = "Claim appearance #" . ($index + 1) . ": Appearance date";
				if (empty($appearance['url'])) $validationErrors[] = "Claim appearance #" . ($index + 1) . ": URL";
			}
		}
	}

	return $validationErrors;
}

// Register REST API endpoints for Gutenberg
add_action('rest_api_init', 'ee24_register_rest_routes');

/**
 * Register REST API routes for EuroClimateCheck
 */
function ee24_register_rest_routes() {
    register_rest_route('ee24/v1', '/validate', array(
        'methods' => 'POST',
        'callback' => 'ee24_validate_data_rest',
        'permission_callback' => function() {
            return current_user_can('edit_posts');
        }
    ));
}

/**
 * REST API callback for validating EuroClimateCheck data
 *
 * @param WP_REST_Request $request The request object
 * @return WP_REST_Response The response object
 */
function ee24_validate_data_rest($request) {
    $data = $request->get_json_params();
    $post_id = isset($data['post_id']) ? intval($data['post_id']) : 0;

    if (!$post_id) {
        return new WP_REST_Response(array(
            'success' => false,
            'message' => 'Invalid post ID'
        ), 400);
    }

    // Get the form data
    $formData = isset($data['formData']) ? $data['formData'] : null;

    if (!$formData) {
        return new WP_REST_Response(array(
            'success' => false,
            'message' => 'No form data provided'
        ), 400);
    }

    // Prepare data for validation
    $validationData = array(
        'type' => $formData['type'],
        'url' => get_permalink($post_id),
        'headlineNative' => $formData['headlineNative'] ?: get_the_title($post_id),
        'headline' => $formData['headline'],
        'datePublished' => get_post_datetime($post_id),
        'image' => get_the_post_thumbnail_url($post_id, 'full'),
        'keywords' => $formData['keywords'],
        'inLanguage' => $formData['inLanguage'],
        'topic' => $formData['topic'],
        'subtopics' => $formData['subtopics'],
        'contentLocation' => $formData['contentLocation'],
        'claimReviewedNative' => $formData['claimReviewedNative'],
        'claimReviewed' => $formData['claimReviewed'],
        'reviewRating' => $formData['reviewRating'],
        'multiclaim' => $formData['multiclaim'],
        'distortionType' => $formData['distortionType'],
        'aiVerification' => $formData['aiVerification'],
        'harm' => $formData['harm'],
        'harmEscalation' => $formData['harmEscalation'],
        'evidences' => $formData['evidences'],
        'claimAppearances' => $formData['claimAppearances'],
        'associatedClaimReview' => $formData['associatedClaimReview'] ?? []
    );

    // Validate the data
    $validationErrors = validateEE24RequiredFields($validationData);

    if (!empty($validationErrors)) {
        // Store validation errors in transient for admin notices
        set_transient("ee24_validation_errors_{$post_id}", $validationErrors, 300);

        return new WP_REST_Response(array(
            'success' => false,
            'message' => 'Validation failed',
            'errors' => $validationErrors
        ), 200);
    }

    return new WP_REST_Response(array(
        'success' => true,
        'message' => 'Validation successful'
    ), 200);
}

// Add filter for Gutenberg pre-publishing validation
add_filter('rest_pre_insert_post', 'ee24_rest_pre_insert_post', 10, 2);

/**
 * Filter for pre-publishing validation in Gutenberg
 *
 * @param stdClass $prepared_post The prepared post object
 * @param WP_REST_Request $request The request object
 * @return stdClass The prepared post object
 */
function ee24_rest_pre_insert_post($prepared_post, $request) {
    // Only check on publish status change
    if ($prepared_post->post_status === 'publish') {
        $post_id = isset($prepared_post->ID) ? $prepared_post->ID : 0;

        if ($post_id) {
            // Check if we have validation errors stored
            $validationErrors = get_transient("ee24_validation_errors_{$post_id}");

            if ($validationErrors && !empty($validationErrors)) {
                // Store the errors for admin notice
                set_transient("ee24_error", "Cannot submit to repository: Required fields are missing", 60);
                set_transient("ee24_validation_errors", $validationErrors, 60);

                // Clear the post-specific transient
                delete_transient("ee24_validation_errors_{$post_id}");
            }
        }
    }

    return $prepared_post;
}
