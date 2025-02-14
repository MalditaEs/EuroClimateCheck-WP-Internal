<?php

include_once 'EE24Api.php';

/**
 * Add the Claim Review Metabox to various post types
 *
 * @return void
 */
function euroclimatecheck_add_custom_box() {
	$screens    = array();
	$post_types = get_option( 'cr-post-types' ) ? get_option( 'cr-post-types' ) : array( 'post', 'page' );


	foreach ( $post_types as $key => $value ) {
		if ( $value ) {
			$screentoshow = str_replace( 'cr-showon', '', $key );
			$screens[]    = $screentoshow;
		}
	}


	foreach ( $screens as $screen ) {
		add_meta_box(
			'euroclimatecheck_metabox',           // Unique ID
			__( 'Claim Review Schema', 'claimreview' ),  // Box title
			'euroclimatecheck_custom_box_html',  // Content callback, must be of type callable
			$screen,                   // Post type
			'normal',
			'high'
		);

		add_meta_box(
			'ee24_repository_metabox',           // Unique ID
			__( 'EuroClimateCheck Repository', 'ee24' ),  // Box title
			'ee24_custom_box_html',  // Content callback, must be of type callable
			$screen,                   // Post type
			'normal',
			'high'
		);
	}
}

add_action( 'add_meta_boxes', 'euroclimatecheck_add_custom_box' );


/**
 * Function to add the claim review meta data
 *
 * @param object $post The post object for the pag we're currently on
 *
 * @return void
 */
function euroclimatecheck_custom_box_html( $post ) {

	$claims = get_post_meta( $post->ID, '_fullfact_all_claims', true );
	$x      = 1;
	echo '<div class="allclaims-box">';
	wp_nonce_field( basename( __FILE__ ), 'euroclimatecheck_nonce' );
	if ( $claims ) {
		foreach ( $claims as $claim ) {

			$claimbox = euroclimatecheck_build_claim_box( $x, $claim );

			if ( $claimbox ) {
				echo $claimbox;
				$x ++;
			}
		}
	}

	echo euroclimatecheck_build_claim_box( $x );

	echo '</div>';
	$x ++;

	echo '<p class="cr-add-wrapper"><button type="button" class="cr-add-claim-field button button-primary" data-target="' . $x . '">' . __( 'Add a New Claim', 'claimreview' ) . '</button></p>';
}

/**
 * Function to add the claim review meta data
 *
 * @param object $post The post object for the pag we're currently on
 *
 * @return void
 */
function ee24_custom_box_html( $post ) {

	$ee24Metadata = get_post_meta( $post->ID, '_ee24_repository', true );
	wp_nonce_field( basename( __FILE__ ), 'ee24_nonce' );
	echo ee24_build_claim_box( $ee24Metadata ?: [] );
}

function ee24_build_claim_box( $data ) {


    ?>
    <script type="application/json" id="euroclimatecheck-data">
    <?php
		echo json_encode([
			"data" => $data,
			"apikey" => get_option('ee24-apikey'),
			"domain" => get_option('ee24-domain'),
			"language" => get_option('ee24-language')
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


	$apikey = get_option( 'ee24-apikey' );
	$domain = get_option( 'ee24-domain' );

	return "<div id='ee24-data' data-endpoint='" . get_option( 'ee24-endpoint' ) . "' data-apikey='$apikey' data-domain='$domain'></div>";
}

/**
 * Function to build the claim review box
 *
 * @param integer $x The number of the claim we're adding
 * @param mixed $data The data to be added.
 *
 * @return string         The claim review box
 */
function euroclimatecheck_build_claim_box( $x = 1, $data = [] ) {
	$claimbox                = '';
	$claimreviewedpresent    = '';
	$claimdatecurrent        = '';
	$claimauthorcurrent      = '';
	$claimappearancecurrent  = array();
	$claimanchorcurrent      = '';
	$claimlocationcurrent    = '';
	$claimjobtitlecurrent    = '';
	$claimimagecurrent       = '';
	$claimnumericcurrent     = '';
	$claimratingimagecurrent = '';

	$max = get_option( 'cr-organisation-max-number-rating' );
	$min = get_option( 'cr-organisation-min-number-rating' );

	if ( is_numeric( $x ) ) {
		$arraykey = $x - 1;
	} else {
		$arraykey = $x;
	}

	$claimreviewedcurrent    = array_key_exists( 'claimreviewed', $data ) ? $data['claimreviewed'] : '';
	$claimdatecurrent        = array_key_exists( 'date', $data ) ? $data['date'] : '';
	$claimauthorcurrent      = array_key_exists( 'author', $data ) ? $data['author'] : '';
	$claimasssessmentcurrent = array_key_exists( 'assessment', $data ) ? $data['assessment'] : '';
	$claimanchorcurrent      = array_key_exists( 'anchor', $data ) ? $data['anchor'] : '';
	$claimlocationcurrent    = array_key_exists( 'location', $data ) ? $data['location'] : '';
	$claimjobtitlecurrent    = array_key_exists( 'job-title', $data ) ? $data['job-title'] : '';
	$claimimagecurrent       = array_key_exists( 'image', $data ) ? $data['image'] : '';
	$claimnumericcurrent     = array_key_exists( 'numeric-rating', $data ) ? $data['numeric-rating'] : '';
	$claimratingimagecurrent = array_key_exists( 'rating-image', $data ) ? $data['rating-image'] : '';

	if ( $data ) {
		$claimappearancecurrent = array_key_exists( 'url', $data['appearance'] ) ? $data['appearance']['url'] : array();
		$claimoriginalcurrent   = array_key_exists( 'original', $data['appearance'] ) ? $data['appearance']['original'] : '';
	} else {
		$claimappearancecurrent = array();
		$claimoriginalcurrent   = '';
	}

	if ( $data && '' == $claimreviewedcurrent ) {
		return false;
	}

	$claimbox .= '<div class="claimbox" id="claimbox' . $x . '" data-box="' . $x . '">';

	$claimbox .= '<h3>' . sprintf( __( 'Claim Review #%s', 'claimreview' ), $x ) . '</h3>';

	$claimbox .= '<div class="crfull"><label for="claim-reviewed-' . $x . '"><strong>' . __( 'Claim Reviewed', 'claimreview' ) . '</strong></label>
	<br />
	<textarea name="claim[' . $arraykey . '][claimreviewed]" id="claim-reviewed-' . $x . '" placeholder="" cols="90" rows="5" />' . $claimreviewedcurrent . '</textarea><br/>
	<span class="description">' . __( 'What the person or entity claimed to be true. Required by Google, Facebook &amp; Bing.', 'claimreview' ) . '</span></div>';

	$claimbox .= '<div class="crhalf"><label for="claim-date-' . $x . '"><strong>' . __( 'Claim Date', 'claimreview' ) . '</strong></label>
	<br />
	<input class="widefat crdatepicker" type="text" name="claim[' . $arraykey . '][date]" id="claim-date-' . $x . '" value="' . $claimdatecurrent . '" /><br/>
	<span class="description">' . __( 'When the person or entity made the claim.', 'claimreview' ) . '</span></div>';

	$claimbox .= '<div class="crfull"><label for="claim-appearance-' . $x . '"><strong>' . __( 'Claim Appearance(s)', 'claimreview' ) . '</strong></label>
	<br /><span class="description">' . __( 'Url(s) for a document where this claim appears.', 'claimreview' ) . '
	<table class="claim-appearance">
	<tbody>';

	$firstrow = true;

	foreach ( $claimappearancecurrent as $url ) {

		if ( ! wp_http_validate_url( $url ) ) {
			continue;
		}

		if ( $firstrow ) {
			$claimbox .= '<tr><td style="width:75%;"><input class="widefat" type="text" name="claim[' . $arraykey . '][appearance][url][]" id="claim-reviewed-' . $x . '" value="' . $url . '" placeholder="" /></td><td style="width:25%;""><input type="checkbox" name="claim[' . $arraykey . '][appearance][original]" id="claim-reviewed-' . $x . '" value="1" ' . checked( $claimoriginalcurrent, '1', false ) . '/>' . __( 'Original Appearance', 'claimreview' ) . '</td></tr>';
			$firstrow = false;
		} else {
			$claimbox .= '<tr><td style="width:75%;"><input class="widefat" type="text" name="claim[' . $arraykey . '][appearance][url][]" value="' . $url . '" placeholder="" /></td><td style="width:25%;"><button type="button" class="button button-secondary cr-remove-row">Remove</button></td></tr>';
		}
	}

	if ( $firstrow ) {
		$claimbox .= '<tr><td style="width:75%;"><input class="widefat" type="text" name="claim[' . $arraykey . '][appearance][url][]" id="claim-reviewed-' . $x . '" value="" placeholder="" /></td><td style="width:25%;""><input type="checkbox" name="claim[' . $arraykey . '][appearance][original]' . $x . '" id="claim-reviewed-' . $x . '" value="1" ' . checked( $claimoriginalcurrent, '1', false ) . '/>' . __( 'Original Appearance', 'claimreview' ) . '</td></tr>';
	} else {
		$claimbox .= '<tr><td style="width:75%;"><input class="widefat" type="text" name="claim[' . $arraykey . '][appearance][url][]" value="" placeholder="" /></td><td style="width:25%;"><button type="button" class="button button-secondary cr-remove-row">Remove</button></td></tr>';
	}

	$claimbox .= '</tbody>
	</table>
	<a href="#" class="add-claim-appearance" data-arraykey="' . $arraykey . '">+' . __( 'Add another claim appearance', 'claimreview' ) . '</a></span></div>';

	$claimbox .= '<div class="crfull"><label for="claim-author-' . $x . '"><strong>' . __( 'Claim Author Name', 'claimreview' ) . '</strong></label>
	<br />
	<input class="widefat" type="text" name="claim[' . $arraykey . '][author]" id="claim-author-' . $x . '" value="' . $claimauthorcurrent . '" /><br/>
	<span class="description">' . __( 'Name of the person or entity who made the claim. Just their name, not their job or title. For viral social media posts without a clear source, use your discretion to show that the claim is viral e.g. Viral social media post. Take care not to imply that a particular social media company made the claim.', 'claimreview' ) . '</span></div>';

	$claimbox .= '<div class="crfull"><label for="claim-assesment-' . $x . '"><strong>' . __( 'Claim Assessment', 'claimreview' ) . '</strong></label>
	<br />
	<textarea name="claim[' . $arraykey . '][assessment]" id="claim-assesment-' . $x . '"  cols="90" rows="5" />' . $claimasssessmentcurrent . '</textarea>
	<br/><span class="description">' . __( 'Your written assessment of the claim. Required by Google, Facebook &amp; Bing.', 'claimreview' ) . '</span></div>';

	$claimbox .= '<p><button type="button" class="claim-more-fields button button-secondary">' . __( 'More Fields', 'claimreview' ) . '</button></p>';

	$claimbox .= '<div class="claim-more-fields-box">';

	$claimbox .= '<div class="crfull"><label for="claim-review-anchor-' . $x . '"><strong>' . __( 'Claim Review Anchor', 'claimreview' ) . '</strong></label>
	<br />
	<input class="widefat" type="text" name="claim[' . $arraykey . '][anchor]" id="claim-review-anchor-' . $x . '" value="' . $claimanchorcurrent . '" /><br/>
	<span class="description">' . __( 'If provided, this will be added to the end of the URL of the page. This will be sanitized to be a URL slug.', 'claimreview' ) . '</span></div>';

	$claimbox .= '<div class="crfull"><label for="claim-location-' . $x . '"><strong>' . __( 'Claim Location', 'claimreview' ) . '</strong></label>
	<br />
	<input class="widefat" type="text" name="claim[' . $arraykey . '][location]" id="claim-location-' . $x . '" value="' . $claimlocationcurrent . '" /><br/>
	<span class="description">' . __( 'Where the claim was made e.g. "At a press conference".', 'claimreview' ) . '</span></div>';

	$claimbox .= '<div class="crhalf"><label for="claim-author-job-title-' . $x . '"><strong>' . __( 'Claim Author Job Title', 'claimreview' ) . '</strong></label>
	<br />
	<input class="widefat" type="text" name="claim[' . $arraykey . '][job-title]" id="claim-author-job-title-' . $x . '" value="' . $claimjobtitlecurrent . '" /><br/>
	<span class="description">' . __( 'Position of the person or entity making the claim.', 'claimreview' ) . '</span></div>';

	$claimbox .= '<div class="crhalf"><label for="claim-author-image-' . $x . '"><strong>' . __( 'Claim Author Image', 'claimreview' ) . '</strong></label>
	<br />
	<input class="widefat" type="text" name="claim[' . $arraykey . '][image]" id="claim-author-image-' . $x . '" value="' . $claimimagecurrent . '" /><br/>
	<span class="description">' . __( 'Image URL of the person or entity making the claim.', 'claimreview' ) . '</span></div>';

	if ( - 1 != $max && - 1 != $min ) {

		$claimbox .= '<div class="crhalf"><label for="claim-numeric-rating-' . $x . '"><strong>' . __( 'Numeric Rating', 'claimreview' ) . '</strong></label>
		<br />
		<input class="widefat" type="number" step="1" name="claim[' . $arraykey . '][numeric-rating]" id="claim-numeric-rating-' . $x . '" value="' . $claimnumericcurrent . '" max="' . $max . '" min="' . $min . '" /><br/>
		<span class="description">' . sprintf( __( 'A number rating for the claim. Between %s and %s.', 'claimreview' ), $min, $max ) . '</span></div>';

	}

	$claimbox .= '<div class="crfull"><label for="claim-rating-image-' . $x . '"><strong>' . __( 'Claim Rating Image', 'claimreview' ) . '</strong></label>
	<br />
	<input class="widefat" type="text" name="claim[' . $arraykey . '][rating-image]" id="claim-rating-image-' . $x . '" value="' . $claimratingimagecurrent . '" /><br/>
	<span class="description">' . __( 'Image URL for the given rating.', 'claimreview' ) . '</span></div>';

	if ( $x != 1 ) {
		$claimbox .= '<div class="crfull cr-text-right"><button type="button" class="button button-secondary cr-remove-claim" data-remove-target="' . $x . '">' . __( 'Remove Claim', 'claimreview' ) . '</button></div>';
	}

	$claimbox .= '</div>';

	$claimbox .= '</div>';

	return $claimbox;
}


/**
 * Helper function to get an arrow to put anywhere.
 *
 * @return string
 */
function claimbox_get_arrow() {
	return '<svg class="claim-review-arrow" width="24px" height="24px" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" role="img" aria-hidden="true" focusable="false"><g><path fill="none" d="M0,0h24v24H0V0z"></path></g><g><path d="M7.41,8.59L12,13.17l4.59-4.58L18,10l-6,6l-6-6L7.41,8.59z"></path></g></svg>';
}


/**
 * Save the metabox claim data
 *
 * @param integer $post_id The post ID we're looking at
 * @param object $post The post object we're using
 *
 * @return mixed              Usually the post ID
 */
function claimbox_save_data( $post_id, $post ) {
	/* Get the post type object. */
	$post_type = get_post_type_object( $post->post_type );

	/* Check if the current user has permission to edit the post. */
	if ( ! current_user_can( $post_type->cap->edit_post, $post_id ) ) {
		return $post_id;
	}


	$post_types = get_option( 'cr-post-types' );

	$post_type_string = 'cr-showon' . $post_type->name;

	$isinarray = false;


	foreach ( $post_types as $key => $value ) {
		if ( $key == $post_type_string ) {
			$isinarray = true;
			break;
		}
	}

	if ( ! $isinarray ) {
		return $post_id;
	}

	if ( array_key_exists( 'claim', $_POST ) ) {
		$newclaim = $_POST['claim'];

		if ( is_array( $newclaim ) ) {
			$newclaim       = array_values( $newclaim );
			$sanitizedclaim = array();


			for ( $x = 0; $x < sizeof( $newclaim ); $x ++ ) {

				if ( array_key_exists( 'claimreviewed', $newclaim[ $x ] ) ) {
					$sanitizedclaim[ $x ]['claimreviewed'] = sanitize_text_field( $newclaim[ $x ]['claimreviewed'] );
				}

				//wp_die( print_r( $sanitizedclaim ) . print_r( $newclaim[0] ) );

				if ( array_key_exists( 'date', $newclaim[ $x ] ) ) {
					$sanitizedclaim[ $x ]['date'] = sanitize_text_field( $newclaim[ $x ]['date'] );
				}

				if ( array_key_exists( 'appearance', $newclaim[ $x ] ) ) {

					if ( array_key_exists( 'url', $newclaim[ $x ]['appearance'] ) ) {
						for ( $y = 0; $y < sizeof( $newclaim[ $x ]['appearance']['url'] ); $y ++ ) {
							$sanitizedclaim[ $x ]['appearance']['url'][ $y ] = esc_url( $newclaim[ $x ]['appearance']['url'][ $y ] );
						}
					}

					if ( array_key_exists( 'original', $newclaim[ $x ]['appearance'] ) ) {
						$sanitizedclaim[ $x ]['appearance']['original'] = sanitize_text_field( $newclaim[ $x ]['appearance']['original'] );
					}
				}

				if ( array_key_exists( 'author', $newclaim[ $x ] ) ) {
					$sanitizedclaim[ $x ]['author'] = sanitize_text_field( $newclaim[ $x ]['author'] );
				}

				if ( array_key_exists( 'author', $newclaim[ $x ] ) ) {
					$sanitizedclaim[ $x ]['assessment'] = sanitize_text_field( $newclaim[ $x ]['assessment'] );
				}

				if ( array_key_exists( 'anchor', $newclaim[ $x ] ) ) {
					$sanitizedclaim[ $x ]['anchor'] = sanitize_title( $newclaim[ $x ]['anchor'] );
				}

				if ( array_key_exists( 'location', $newclaim[ $x ] ) ) {
					$sanitizedclaim[ $x ]['location'] = sanitize_text_field( $newclaim[ $x ]['location'] );
				}

				if ( array_key_exists( 'job-title', $newclaim[ $x ] ) ) {
					$sanitizedclaim[ $x ]['job-title'] = sanitize_text_field( $newclaim[ $x ]['job-title'] );
				}

				if ( array_key_exists( 'image', $newclaim[ $x ] ) ) {
					$sanitizedclaim[ $x ]['image'] = esc_url( $newclaim[ $x ]['image'] );
				}

				if ( array_key_exists( 'numeric-rating', $newclaim[ $x ] ) ) {
					$sanitizedclaim[ $x ]['numeric-rating'] = sanitize_text_field( $newclaim[ $x ]['numeric-rating'] );
				}

				if ( array_key_exists( 'rating-image', $newclaim[ $x ] ) ) {
					$sanitizedclaim[ $x ]['rating-image'] = esc_url( $newclaim[ $x ]['rating-image'] );
				}
			}
		}

		update_post_meta( $post_id, '_fullfact_all_claims', $sanitizedclaim );

	}
}

function ee24_save_data( $post_id, $post ) {
	updateEE24Metadata( $post_id );
}

function ee24_publish_data( $post_id, $post ) {

	if ( $post->post_status != 'publish' ) {
		return;
	}

	$data = updateEE24Metadata( $post_id );

	if ( $data === null ) {
		return;
	}

	$publishedData = get_post_meta( $post_id, '_ee24_repository_published', true ) ?: [];

	$api = new EE24Api();

	$headers = [
		'X-API-KEY' => get_option( 'ee24-apikey' ),
		'X-DOMAIN'  => get_option( 'ee24-domain' ),
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

	update_post_meta( $post_id, '_ee24_repository', $data );
	update_post_meta( $post_id, '_ee24_repository_published', $publishedData );
}

add_action( 'save_post', 'claimbox_save_data', 10, 2 );

add_action( 'save_post', 'ee24_save_data', 11, 2 );
add_action( 'wp_after_insert_post', 'ee24_publish_data', 12, 2 );

add_action( 'admin_notices', 'ee24_admin_notice' );

function updateEE24Metadata($post_id) {
	// Check if our form data exists
	if (array_key_exists('ee24-form-data', $_POST)) {
		// Decode the JSON data
		$formData = json_decode(stripslashes($_POST['ee24-form-data']), true);

		if ($formData === null) {
			// JSON decode failed
			return null;
		}

		$data = get_post_meta($post_id, '_ee24_repository', true) ?: [];

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

		update_post_meta($post_id, '_ee24_repository', $data);
		return $data;
	}

	return null;
}


function ee24_admin_notice() {
	// Get the API response stored earlier
	$error   = get_transient( 'ee24_error' );
	$success = get_transient( 'ee24_success' );

	if ( $error === false && $success === false ) {
		return;
	}

	if ( $error !== false ) {
		echo '<div class="notice notice-error is-dismissible">';
		echo 'Error exporting to the EuroClimateCheck Repository: ' . esc_html( $error );
		echo '</div>';
	}

	if ( $success !== false ) {
		echo '<div class="notice notice-success is-dismissible">';
		echo 'The EuroClimateCheck Repository has been updated: ' . esc_html( $success );
		echo '</div>';
	}

	$post = get_post();
	if ( $post && ! use_block_editor_for_post( $post ) ) {
		if ( $error ) {
			delete_transient( 'ee24_error' );
		} else if ( $success ) {
			delete_transient( 'ee24_success' );
		}
	}
}
