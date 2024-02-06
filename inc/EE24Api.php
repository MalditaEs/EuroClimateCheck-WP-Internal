<?php

class EE24Api
{

    private function getEndpoint(){
        return get_option('ee24-endpoint');
    }

    /**
     * @throws Exception
     */
    public function sendPostRequest($data, $headers = [])
    {
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => $this->getEndpoint(),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => json_encode($data),
            CURLOPT_HTTPHEADER => array(
                'X-API-KEY: ' . $headers['X-API-KEY'],
                'X-DOMAIN: ' . $headers['X-DOMAIN'],
                'Content-Type: application/json'
            ),
        ));

        $response = curl_exec($curl);
        $statusCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);

        curl_close($curl);

        if ($statusCode != 200 && $statusCode != 201) {
            if (!$response) {
                throw new Exception('Error');
            }

            $json = json_decode($response, true);

            if (json_last_error() == JSON_ERROR_NONE) {
                $message = $json['message'] ?? 'Error';
                throw new Exception($message);
            } else {
                throw new Exception('Error');
            }
        }

        return $response;
    }

    public function sendPatchRequest($externalId, $data, $headers = [])
    {
      $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => $this->getEndpoint() . $externalId,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'PATCH',
            CURLOPT_POSTFIELDS => json_encode($data),
            CURLOPT_HTTPHEADER => array(
                'X-API-KEY: ' . $headers['X-API-KEY'],
                'X-DOMAIN: ' . $headers['X-DOMAIN'],
                'Content-Type: application/json'
            ),
        ));

        $response = curl_exec($curl);
        $statusCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);

        curl_close($curl);

        if ($statusCode != 200 && $statusCode != 201) {
            if (!$response) {
                throw new Exception('Error');
            }

            $json = json_decode($response, true);

            if (json_last_error() == JSON_ERROR_NONE) {
                $message = $json['message'] ?? 'Error';
                throw new Exception($message);
            } else {
                throw new Exception('Error');
            }
        }

        return $response;
    }

    public function sendDeleteRequest($externalId, $headers = [])
    {
        $url = $this->getEndpoint() . '/' . $externalId;
        $ch = curl_init($url);

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');

        $defaultHeaders = [];
        $allHeaders = array_merge($defaultHeaders, $headers);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $allHeaders);

        $response = curl_exec($ch);
        curl_close($ch);

        return $response;
    }
}