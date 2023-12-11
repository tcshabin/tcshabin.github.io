<?php
require_once 'log_function.php';

if (!function_exists('authenticateRequest')) {
    function authenticateRequest($accessToken)
    {
        if ($accessToken == "" || $accessToken == null) {
            echo "empty token";
        }
        // Make a request to LINE's token verification endpoint
        $verificationUrl = 'https://api.line.me/oauth2/v2.1/verify?access_token=' . $accessToken;
        //writeToLog($verificationUrl);

        $options = [
            'http' => [
                'method' => 'GET',
            ],
        ];

        $context = stream_context_create($options);

        // Handle errors gracefully
        $result = @file_get_contents($verificationUrl, false, $context);

        if ($result !== false) {
            // writeToLog("result !== false");
            // Check if the response is successful (HTTP status code 200)
            $statusCode = $http_response_header[0];
            if (strpos($statusCode, '200') !== false) {
                //  writeToLog("statusCode==200");
                // Verification successful
                return true;
            } else {
                // Error occurred (e.g., non-200 status code)
                // writeToLog("non-200 status code");
                echo "Access token verification failed. Status code: $statusCode";
            }
        } else {
            // Handle file_get_contents error
            // writeToLog("Handle file_get_contents error");
            echo "Error making request to LINE's token verification endpoint";
        }

        return false;
    }
}
