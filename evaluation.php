<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Origin, Content-Type, Accept, Authorization');
require_once 'db_connect.php';
require 'liff_authentication.php';
require 'responseHandler.php';

function insertEvaluation($input_data, $dbWrapper = null)
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

    if ($json_data === null || empty($json_data)) {
        handleErrorResponse(400, 'Unable to decode JSON data.', 'error');
        return;
    }


    $userId = $json_data['userId'];
    $evaluationResults = $json_data['evaluationResults'];
    $gradeLevel = $json_data['grade_level'];
    $evaluationDate = $json_data['evaluation_date'];

    if (empty($userId)) {
        handleErrorResponse(400, 'User id cannot be empty.', 'error');
        return;
    }

    if (empty($evaluationResults)) {
        handleErrorResponse(400, 'Evaluation result cannot be empty.', 'error');
        return;
    }

    if (empty($gradeLevel) || !in_array($gradeLevel, ["one", "two", "three"])) {
        handleErrorResponse(400, 'Invalid or empty grade.', 'error');
        return;
    }

    if (empty($evaluationDate)) {
        handleErrorResponse(400, 'Evaluation date cannot be empty.', 'error');
        return;
    }

    $dateObject = DateTime::createFromFormat('Ymd', $evaluationDate);
    if (!$dateObject || $dateObject->format('Ymd') !== $evaluationDate) {
        handleErrorResponse(400, 'Invalid format for evaluation date.', 'error');
        return;
    }

    $formattedDate = $dateObject->format('Y-m-d');
    $table = "evaluation_result_grade_" . $gradeLevel;
    $currentDate = $formattedDate;



    $conn = getDBConnection($dbWrapper);

    if (is_null($dbWrapper)) $dbWrapper = new DatabaseWrapper($conn);

    if (!$conn) {
        handleErrorResponse(500, 'Error: Unable to connect to the database.', 'error');
        return;
    }

    $entryCheck = "SELECT * FROM $table WHERE user_id='$userId' AND evaluation_date='$currentDate'";
    $entryCheckResult = $dbWrapper->queryParams($conn, $entryCheck, array());

    if ($entryCheckResult === false) {
        handleErrorResponse(500, 'Error: Query execution failed.', 'error');
        $dbWrapper->close($conn);
        return;
    }

    $numRows = $dbWrapper->num_rows($entryCheckResult);
    if ($numRows > 0) {
        $query = "UPDATE $table SET response_one='{$evaluationResults[0]}', response_two='{$evaluationResults[1]}',
                    response_three='{$evaluationResults[2]}' WHERE user_id='$userId' AND evaluation_date='$currentDate'";
    } else {
        $query = "INSERT INTO $table (user_id, response_one, response_two, response_three, status, evaluation_date) VALUES 
                   ('$userId', '{$evaluationResults[0]}', '{$evaluationResults[1]}', '{$evaluationResults[2]}', 1, '$currentDate')";
    }

    $result = $dbWrapper->queryParams($conn, $query, array());

    if ($result) {
        handleSuccessResponse('Data inserted successfully');
    } else {
        handleErrorResponse(500, 'Error: ' . pg_last_error($conn), 'error');
    }

    $dbWrapper->close($conn);
}

if (!defined('PHPUNIT_RUNNING')) {
    insertEvaluation(file_get_contents("php://input")); 
    exit; // Exit to prevent further output
}
