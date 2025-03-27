<?php

class EE24Api {
	private function getEndpoint() {
		return get_option( 'euroclimatecheck-endpoint' );
	}

	private function initializeCurl( $endpoint, $headers, $method, $data ) {
		$curl = curl_init();

		curl_setopt_array( $curl, array(
			CURLOPT_URL            => $endpoint,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_ENCODING       => '',
			CURLOPT_MAXREDIRS      => 10,
			CURLOPT_TIMEOUT        => 0,
			CURLOPT_FOLLOWLOCATION => true,
			CURLOPT_HTTP_VERSION   => CURL_HTTP_VERSION_1_1,
			CURLOPT_CUSTOMREQUEST  => $method,
			CURLOPT_POSTFIELDS     => json_encode( $data ),
			CURLOPT_HTTPHEADER     => array(
				'X-API-KEY: ' . $headers['X-API-KEY'],
				'X-DOMAIN: ' . $headers['X-DOMAIN'],
				'Content-Type: application/json'
			),
		) );

		return $curl;
	}

	private function handleCurlResponse( $statusCode, $response ) {
		if ( $statusCode != 200 && $statusCode != 201 ) {
			if ( ! $response ) {
				throw new Exception( 'Error' . $statusCode );
			}
			$json = json_decode( $response, true );
			if ( json_last_error() == JSON_ERROR_NONE ) {
				$message = $json['message'] ?? 'Error';
				throw new Exception( is_array( $message ) ? implode( ', ', $message ) : $message );
			} else {
				throw new Exception( 'Error' );
			}
		}

		return $response;
	}

	private function transformDataForApi($data) {
		// Transform base data
		$transformed = [
			'type' => $data['type'],
			'url' => $data['url'],
			'headline' => $data['headline'],
			'headlineNative' => $data['headlineNative'],
			'datePublished' => $data['datePublished'],
			'publisher' => get_bloginfo('name'),
			'publisherUrl' => parse_url(get_bloginfo('url'), PHP_URL_HOST),
			'image' => $data['image'],
			'keywords' => $data['keywords'],
			'inLanguage' => $data['inLanguage']['code'],
			'topic' => $data['topic'],
			'subtopics' => array_map(function($subtopic) use ($data) {
				return $data['topic'] . ' - ' . $subtopic;
			}, $data['subtopics'] ?? []),
			'contentLocation' => array_map(function($location) {
				return $location['code'];
			}, $data['contentLocation'] ?? []),
			'countryOfOrigin' => get_option('euroclimatecheck-country')
		];

		// Add Factcheck specific fields if type is Factcheck
		if ($data['type'] === 'Factcheck') {
			// Transformar las appearances
			$appearances = array_map(function($appearance) {
				return [
					'url' => $appearance['url'],
					'archivedAt' => $appearance['archivedAt'],
					'difussionFormat' => $appearance['difussionFormat'],
					'platform' => $appearance['platform'],
					'appearanceDate' => $appearance['appearanceDate'],
					'views' => intval($appearance['views']),
					'likes' => intval($appearance['likes']),
					'comments' => intval($appearance['comments']),
					'shares' => intval($appearance['shares']),
					'actionTaken' => filter_var($appearance['actionTakenByPlatform'], FILTER_VALIDATE_BOOLEAN),
					'appearanceBody' => $appearance['appearanceBody'],
					'claimant' => $appearance['claimant'],
					'claimantType' => $appearance['claimantType'],
					'claimantInfluence' => $appearance['claimantInfluence']
				];
			}, $data['claimAppearances'] ?? []);

			$transformed['claimReview'] = [
				'claimReviewed' => $data['claimReviewed'],
				'claimReviewedNative' => $data['claimReviewedNative'],
				'multiclaim' => filter_var($data['multiclaim'], FILTER_VALIDATE_BOOLEAN),
				'distortionType' => $data['distortionType'],
				'aiVerification' => $data['aiVerification'],
				'harm' => filter_var($data['harm'], FILTER_VALIDATE_BOOLEAN),
				'harmEscalation' => $data['harmEscalation'],
				'reviewRating' => $data['reviewRating'],
				'appearances' => $appearances,
				'associatedClaimReview' => [] // Añadir si se implementa en el futuro
			];

			// Transformar evidences
			$transformed['evidences'] = array_map(function($evidence) {
				return [
					'question' => $evidence['question'],
					'answer' => $evidence['answer'],
					'url' => $evidence['url'],
					'type' => $evidence['type']
				];
			}, $data['evidences'] ?? []);
		}

		return $transformed;
	}

	/**
	 * @throws Exception
	 */
	public function sendPostRequest( $data, $headers = [] ) {
		$transformedData = $this->transformDataForApi($data);

		$curl       = $this->initializeCurl( $this->getEndpoint(), $headers, 'POST', $transformedData );
		$response   = curl_exec( $curl );
		$statusCode = curl_getinfo( $curl, CURLINFO_HTTP_CODE );
		curl_close( $curl );

		return $this->handleCurlResponse( $statusCode, $response );
	}

	public function sendPatchRequest( $externalId, $data, $headers = [] ) {
		$transformedData = $this->transformDataForApi($data);

		$curl       = $this->initializeCurl( $this->getEndpoint() . '/' . $externalId, $headers, 'PATCH', $transformedData );
		$response   = curl_exec( $curl );
		$statusCode = curl_getinfo( $curl, CURLINFO_HTTP_CODE );
		curl_close( $curl );

		return $this->handleCurlResponse( $statusCode, $response );
	}
}
