<?php
function handleSuccessResponse($message)
{
    $response = [
        'status' => 'success',
        'status_code' => 200,
        'message' => $message
    ];
    echo json_encode($response);
}

if (!function_exists('handleErrorResponse')) {
    function handleErrorResponse($statusCode, $message, $status)
    {
        http_response_code($statusCode);
        $response = [
            'status' => $status,
            'status_code' => $statusCode,
            'message' => $message
        ];
        echo json_encode($response);
    }
}    