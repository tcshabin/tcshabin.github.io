<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Origin, Content-Type, Accept, Authorization');
require_once 'db_connect.php';
require 'liff_authentication.php';

function history($input_data, $dbWrapper = null)
{

    if (!isset($_SERVER['HTTP_X_LIFF_TOKEN'])) {
        handleErrorResponse(403, 'Forbidden!', 'error');
        return;
    }

    $authorizationHeader = strval($_SERVER['HTTP_X_LIFF_TOKEN']);

    if (!authenticateRequest($authorizationHeader)) {
        handleErrorResponse(400, 'Authentication Failed!', 'error');
        return;
    }

    $json_data = json_decode($input_data, true);


    if ($json_data === null) {
        handleErrorResponse(400, 'Unable to decode JSON data.', 'error');
        return;
    }

    $userId = $json_data['userId'];
    $gradeLevel = $json_data['grade_level'];

    $conn = getDBConnection($dbWrapper);

    if (is_null($dbWrapper)) $dbWrapper = new DatabaseWrapper($conn);

    if (!$conn) {
        handleErrorResponse(500, 'Error: Unable to connect to the database.', 'error');
        return;
    }

    $table = "evaluation_result_sbs_" . $gradeLevel;

    $currentDayOfWeek = date('w');
    $daysAgo = $currentDayOfWeek;
    $startDate = date('Y-m-d', strtotime("-$daysAgo days"));
    $endDate = date('Y-m-d', strtotime("$startDate +6 days"));

    $query = "SELECT * FROM $table WHERE user_id = '$userId' 
        AND evaluation_date >= '$startDate' AND evaluation_date <= '$endDate' ORDER BY evaluation_date DESC";

    $result = $dbWrapper->queryParams($conn, $query, array());

    if ($result === false) {
        handleErrorResponse(500, 'Error: Query execution failed.', 'error');
        $dbWrapper->close($conn);
        return;
    }

    $response = array();
    $data = array();

    while ($row = $dbWrapper->fetchAssoc($result)) {
        $data[$row['evaluation_date']] = $row;
    }

    $response['status'] = 'success';
    $response['status_code'] = 200;
    $response['message'] = 'Data retrieved successfully';
    $response['data'] = array();

    for ($i = 6; $i >= 0; $i--) {
        $date = date('Y-m-d', strtotime("$endDate -$i days"));
        $response['data'][] = isset($data[$date]) ?
            $data[$date] :
            [
                'evaluation_date' => $date,
                'response_one' => '',
                'response_two' => '',
                'response_three' => '',
            ];
    }

    echo json_encode($response);
    $dbWrapper->close($conn);
}

if (!defined('PHPUNIT_RUNNING')) {
    history(file_get_contents("php://input"));
    exit; // Exit to prevent further output
}
