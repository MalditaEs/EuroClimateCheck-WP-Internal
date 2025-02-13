<?php

class EE24Api {
	private function getEndpoint() {
		return get_option( 'ee24-endpoint' );
	}

	private function filterEmptyElementsInData( $data ) {
		if ( is_array( $data ) || is_object( $data ) ) {
			foreach ( $data as $key => $value ) {
				if ( $key === 'contentLocation' ) {
					continue;
				}
				if ( is_object( $value ) || is_array( $value ) ) {
					$value = $this->filterEmptyElementsInData( $value );
				}
				if ( empty( $value ) ) {
					unset( $data[ $key ] );
				} else {
					$data[ $key ] = $value;
				}
			}
		}

		return $data;
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
			'inLanguage' => $data['language']['code'] ?? null,
			'topic' => $data['topic'],
			'subtopics' => array_map(function($subtopic) use ($data) {
				return $data['topic'] . ' - ' . $subtopic;
			}, $data['subtopics'] ?? []),
			'contentLocation' => array_map(function($location) {
				return $location['code'];
			}, $data['contentLocation'] ?? []),
			'countryOfOrigin' => get_option('ee24-country')
		];

		// Add Factcheck specific fields if type is Factcheck
		if ($data['type'] === 'Factcheck') {
			$transformed = array_merge($transformed, [
				'claimText' => $data['claimText'],
				'claimTextNative' => $data['claimTextNative'],
				'rating' => $data['rating'],
				'multiclaim' => $data['multiclaim'],
				'distortion' => $data['distortion'],
				'aiVerification' => $data['aiVerification'],
				'harm' => $data['harm'],
				'harmEscalation' => $data['harmEscalation'],
				'evidences' => $data['evidences'],
				'claimAppearances' => $data['claimAppearances']
			]);
		}

		return $transformed;
	}

	/**
	 * @throws Exception
	 */
	public function sendPostRequest( $data, $headers = [] ) {
		$transformedData = $this->transformDataForApi($data);
		$transformedData = $this->filterEmptyElementsInData($transformedData);

		$curl       = $this->initializeCurl( $this->getEndpoint(), $headers, 'POST', $transformedData );
		$response   = curl_exec( $curl );
		$statusCode = curl_getinfo( $curl, CURLINFO_HTTP_CODE );
		curl_close( $curl );

		return $this->handleCurlResponse( $statusCode, $response );
	}

	public function sendPatchRequest( $externalId, $data, $headers = [] ) {
		$transformedData = $this->transformDataForApi($data);
		$transformedData = $this->filterEmptyElementsInData($transformedData);

		$curl       = $this->initializeCurl( $this->getEndpoint() . '/' . $externalId, $headers, 'PATCH', $transformedData );
		$response   = curl_exec( $curl );
		$statusCode = curl_getinfo( $curl, CURLINFO_HTTP_CODE );
		curl_close( $curl );

		return $this->handleCurlResponse( $statusCode, $response );
	}
}
