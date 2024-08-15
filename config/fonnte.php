<?php

/**
 * Sends a message to the Fonnte API.
 *
 * This function uses cURL to send a POST request to the Fonnte API endpoint with the provided data.
 * The function initializes a cURL session, sets the necessary cURL options, including the API endpoint,
 * request method, POST fields, and authorization token. It then executes the cURL request and closes
 * the session. The response from the API is returned as a string.
 *
 * @param array $data The data to be sent in the POST request.
 * array(
 *  'data' => '[
 *      {
 *          "target" : "08XXXXXXXXXX", 
 *          "message":"msg", 
 *          "delay":"1"
 *      }, 
 *      {
 *          ...
 *      }
 *  ]') 
 * 
 * @return string The response from the Fonnte API.
 */
function sendMessage($data)
{
    $curl = curl_init();

    $token = 'Yg5KeT@iH!nAUqmRoz1B';

    curl_setopt_array($curl, [
        CURLOPT_URL => 'https://api.fonnte.com/send',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_POSTFIELDS => $data,
        CURLOPT_HTTPHEADER => ["Authorization: $token"],
    ]);

    $response = curl_exec($curl);

    curl_close($curl);

    return $response;
}
