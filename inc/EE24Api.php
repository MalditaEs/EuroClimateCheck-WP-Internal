<?php

class EE24Api
{
    private function getEndpoint()
    {
        return get_option('ee24-endpoint');
    }

    private function filterEmptyElementsInData($data)
    {
        return array_filter($data, function ($value) {
            if (is_array($value)) {
                return !empty(array_filter($value, function ($v) {
                    return !empty($v);
                }));
            }
            return !empty($value);
        });
    }

    private function initializeCurl($endpoint, $headers, $method, $data)
    {
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => $endpoint,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_POSTFIELDS => json_encode($data),
            CURLOPT_HTTPHEADER => array(
                'X-API-KEY: ' . $headers['X-API-KEY'],
                'X-DOMAIN: ' . $headers['X-DOMAIN'],
                'Content-Type: application/json'
            ),
        ));

        return $curl;
    }

    private function handleCurlResponse($statusCode, $response)
    {
        if ($statusCode != 200 && $statusCode != 201) {
            if (!$response) {
                throw new Exception('Error' . $statusCode);
            }
            $json = json_decode($response, true);
            if (json_last_error() == JSON_ERROR_NONE) {
                $message = $json['message'] ?? 'Error';
                throw new Exception(is_array($message) ? implode(', ', $message) : $message);
            } else {
                throw new Exception('Error');
            }
        }

        return $response;
    }

    /**
     * @throws Exception
     */
    public function sendPostRequest($data, $headers = [])
    {
        $data['countryOfOrigin'] = get_option('ee24-country');
        $data = $this->filterEmptyElementsInData($data);

        $curl = $this->initializeCurl($this->getEndpoint(), $headers, 'POST', $data);
        $response = curl_exec($curl);
        $statusCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);

        return $this->handleCurlResponse($statusCode, $response);
    }

    public function sendPatchRequest($externalId, $data, $headers = [])
    {
        $data['countryOfOrigin'] = get_option('ee24-country');
        $data = $this->filterEmptyElementsInData($data);

        $curl = $this->initializeCurl($this->getEndpoint() . $externalId, $headers, 'PATCH', $data);
        $response = curl_exec($curl);
        $statusCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);

        return $this->handleCurlResponse($statusCode, $response);
    }
}