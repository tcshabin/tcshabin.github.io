<?php

use PHPUnit\Framework\TestCase;

define('PHPUNIT_RUNNING', true);
require 'evaluation.php';
require 'history.php';

function handleErrorResponse($statusCode, $message, $status)
{
    $response = [
        'status' => $status,
        'status_code' => $statusCode,
        'message' => $message
    ];
    echo json_encode($response);
}

function authenticateRequest($status)
{
    if ($status == 'valid_token') {
        return true;
    }
    return false;
}


class EvaluationTest extends TestCase
{

    public function testHistoryWithValidData()
    {
        // Start output buffering
        ob_start();

        // Create a mock database connection
        $mockDatabase = $this->createMock(DatabaseWrapper::class);

        // Set up expectations for the mock database
        $mockDatabase->expects($this->any())
            ->method('queryParams')
            ->willReturnCallback(function () {

                return true;
            });

        $mockDatabase->expects($this->any())
            ->method('close')
            ->willReturnCallback(function () {

                return null;
            });

        $mockDatabase->expects($this->any())
            ->method('fetchAssoc')
            ->willReturnCallback(function () {
                static $counter = 0;

                if ($counter === 0) {
                    $counter++;
                    return ['evaluation_date' => date("Y-m-d"), 'response_one' => 'value1'];
                }

                return null;
            });

        $inputData = '{"userId": "123", "grade_level": "one"}';

        // Set a specific token for testing
        $_SERVER['HTTP_X_LIFF_TOKEN'] = 'valid_token';

        history($inputData, $mockDatabase);

        $output = ob_get_clean();

        // Decode the JSON output for assertion
        $outputResponse = json_decode($output, true);

        $expectedResponse = [
            'status' => 'success',
            'status_code' => 200,
            'message' => 'Data retrieved successfully',
        ];

        $this->assertEquals($expectedResponse['status'], $outputResponse['status']);
        $this->assertEquals($expectedResponse['status_code'], $outputResponse['status_code']);
        $this->assertEquals($expectedResponse['message'], $outputResponse['message']);
        // Unset the token after the teststatus_code
        unset($_SERVER['HTTP_X_LIFF_TOKEN']);
    }

    public function testHistoryWithAuthenticationFailure()
    {

        // Start output buffering
        ob_start();

        // Create a mock database connection
        $mockDatabase = $this->createMock(DatabaseWrapper::class);

        $inputData = '{"userId": "123", "grade_level": "one"}';

        // Set a specific token for testing
        $_SERVER['HTTP_X_LIFF_TOKEN'] = 'invalid_token';

        history($inputData, $mockDatabase);
        $output = ob_get_clean();

        // Decode the JSON output for assertion
        $resultArray = json_decode($output, true);

        // Assert that the response matches the expected authentication failure response
        $expectedResponse = [
            'status' => 'error',
            'status_code' => 400,
            'message' => 'Authentication Failed!',
        ];

        $this->assertEquals($expectedResponse, $resultArray);

        // Unset the token after the test
        unset($_SERVER['HTTP_X_LIFF_TOKEN']);
    }

    public function testHistoryWithNoToken()
    {
        // Start output buffering
        ob_start();

        // Create a mock database connection
        $mockDatabase = $this->createMock(DatabaseWrapper::class);

        $inputData = '{"userId": "123", "grade_level": "one"}';

        history($inputData, $mockDatabase);
        $output = ob_get_clean();

        // Decode the JSON output for assertion
        $resultArray = json_decode($output, true);

        // Assert that the response matches the expected authentication failure response
        $expectedResponse = [
            'status' => 'error',
            'status_code' => 403,
            'message' => 'Forbidden!',
        ];

        $this->assertEquals($expectedResponse, $resultArray);
    }

    public function testHistoryWithEmptyInput()
    {
        $inputData = '';

        // Set a specific token for testing
        $_SERVER['HTTP_X_LIFF_TOKEN'] = 'valid_token';

        ob_start();
        history($inputData, null);
        $output = ob_get_clean();

        // Decode the JSON output for assertion
        $resultArray = json_decode($output, true);

        // Assert that the response matches the expected authentication failure response
        $expectedResponse = [
            'status' => 'error',
            'status_code' => 400,
            'message' => 'Unable to decode JSON data.',
        ];

        $this->assertEquals($expectedResponse, $resultArray);

        // Unset the token after the test
        unset($_SERVER['HTTP_X_LIFF_TOKEN']);
    }



    public function testHistoryWithEmptyResult()
    {
        // Start output buffering
        ob_start();

        // Create a mock database connection
        $mockDatabase = $this->createMock(DatabaseWrapper::class);

        // Set up expectations for the mock database
        $mockDatabase->expects($this->any())
            ->method('queryParams')
            ->willReturnCallback(function () {
                return false;
            });

        $mockDatabase->expects($this->any())
            ->method('close')
            ->willReturnCallback(function () {
                return null;
            });


        $inputData = '{"userId": "123", "grade_level": "one"}';

        // Set a specific token for testing
        $_SERVER['HTTP_X_LIFF_TOKEN'] = 'valid_token';

        history($inputData, $mockDatabase);

        $output = ob_get_clean();

        // Decode the JSON output for assertion
        $outputResponse = json_decode($output, true);

        $expectedResponse = [
            'status' => 'error',
            'status_code' => 500,
            'message' => 'Error: Query execution failed.',
        ];

        $this->assertEquals($expectedResponse, $outputResponse);
        // Unset the token after the test
        unset($_SERVER['HTTP_X_LIFF_TOKEN']);
    }

    public function testInsertEvaluationWithValidData()
    {

        ob_clean();

        // Start output buffering
        ob_start();
        // Create a mock database connection
        $mockDatabase = $this->createMock(DatabaseWrapper::class);
        // Set up expectations for the mock database
        $mockDatabase->expects($this->any())
            ->method('queryParams')
            ->willReturnCallback(function () {
                return true;
            });

        $mockDatabase->expects($this->any())
            ->method('num_rows')
            ->willReturnCallback(function () {
                return 1;
            });

        $mockDatabase->expects($this->any())
            ->method('close')
            ->willReturnCallback(function () {
                return null;
            });

        // Set a specific token for testing
        $_SERVER['HTTP_X_LIFF_TOKEN'] = 'valid_token';

        $inputData = '{"userId": "123", "evaluationResults": [90, 85, 75], "grade_level": "one", "evaluation_date": "20211201"}';

        insertEvaluation($inputData, $mockDatabase);

        $output = ob_get_clean();

        // Decode the JSON output for assertion
        $outputResponse = json_decode($output, true);

        $expectedResponse = [
            'status' => 'success',
            'status_code' => 200,
            'message' => 'Data inserted successfully',
        ];

        $this->assertEquals($expectedResponse, $outputResponse);
        // Unset the token after the test
        unset($_SERVER['HTTP_X_LIFF_TOKEN']);
    }

    public function testInsertEvaluationWithAuthenticationFailure()
    {

        $mockDatabase = $this->createMock(DatabaseWrapper::class);

        $inputData = '{"userId": "123", "evaluationResults": [90, 85, 75], "grade_level": "one", "evaluation_date": "20211201"}';
        // Capture the output of the function
        ob_start();

        // Set a specific token for testing
        $_SERVER['HTTP_X_LIFF_TOKEN'] = 'invalid_token';

        insertEvaluation($inputData, $mockDatabase);
        $output = ob_get_clean();

        // Decode the JSON output for assertion
        $resultArray = json_decode($output, true);

        // Assert that the response matches the expected authentication failure response
        $expectedResponse = [
            'status' => 'error',
            'status_code' => 400,
            'message' => 'Authentication Failed!',
        ];

        $this->assertEquals($expectedResponse, $resultArray);

        // Unset the token after the test
        unset($_SERVER['HTTP_X_LIFF_TOKEN']);
    }

    public function testInsertEvaluationWithNoToken()
    {

        $mockDatabase = $this->createMock(DatabaseWrapper::class);

        $inputData = '{"userId": "123", "evaluationResults": [90, 85, 75], "grade_level": "one", "evaluation_date": "20211201"}';
        // Capture the output of the function
        ob_start();

        insertEvaluation($inputData, $mockDatabase);
        $output = ob_get_clean();

        // Decode the JSON output for assertion
        $resultArray = json_decode($output, true);

        // Assert that the response matches the expected authentication failure response
        $expectedResponse = [
            'status' => 'error',
            'status_code' => 403,
            'message' => 'Forbidden!',
        ];

        $this->assertEquals($expectedResponse, $resultArray);
    }

    public function testInsertEvaluationWithEmptyInput()
    {
        $inputData = '';

        // Set a specific token for testing
        $_SERVER['HTTP_X_LIFF_TOKEN'] = 'valid_token';

        ob_start();
        insertEvaluation($inputData, null);
        $output = ob_get_clean();

        // Decode the JSON output for assertion
        $resultArray = json_decode($output, true);

        // Assert that the response matches the expected authentication failure response
        $expectedResponse = [
            'status' => 'error',
            'status_code' => 400,
            'message' => 'Unable to decode JSON data.',
        ];

        $this->assertEquals($expectedResponse, $resultArray);

        // Unset the token after the test
        unset($_SERVER['HTTP_X_LIFF_TOKEN']);
    }

    public function testInsertEvaluationWithInValidUserId()
    {

        $inputData = '{"userId": null, "evaluationResults": [90, 85, 75], "grade_level": "one", "evaluation_date": "20211201"}';

        // Set a specific token for testing
        $_SERVER['HTTP_X_LIFF_TOKEN'] = 'valid_token';
        // Capture the output of the function
        ob_start();
        insertEvaluation($inputData, null);
        $output = ob_get_clean();

        // Decode the JSON output for assertion
        $outputResponse = json_decode($output, true);

        $expectedResponse = [
            'status' => 'error',
            'status_code' => 400,
            'message' => 'User id cannot be empty.',
        ];

        $this->assertEquals($expectedResponse, $outputResponse);

        unset($_SERVER['HTTP_X_LIFF_TOKEN']);
    }

    public function testInsertEvaluationWithInValidEvaluationResult()
    {

        $inputData = '{"userId": "123", "evaluationResults": null, "grade_level": "one", "evaluation_date": "20211201"}';

        // Set a specific token for testing
        $_SERVER['HTTP_X_LIFF_TOKEN'] = 'valid_token';
        // Capture the output of the function
        ob_start();
        insertEvaluation($inputData, null);
        $output = ob_get_clean();

        // Decode the JSON output for assertion
        $outputResponse = json_decode($output, true);


        $expectedResponse = [
            'status' => 'error',
            'status_code' => 400,
            'message' => 'Evaluation result cannot be empty.',
        ];

        $this->assertEquals($expectedResponse, $outputResponse);

        unset($_SERVER['HTTP_X_LIFF_TOKEN']);
    }

    public function testInsertEvaluationWithEmptyGradeLevel()
    {

        $inputData = '{"userId": "123", "evaluationResults": [90, 85, 75], "grade_level": null, "evaluation_date": "20211201"}';

        // Set a specific token for testing
        $_SERVER['HTTP_X_LIFF_TOKEN'] = 'valid_token';
        // Capture the output of the function
        ob_start();
        insertEvaluation($inputData, null);
        $output = ob_get_clean();

        // Decode the JSON output for assertion
        $outputResponse = json_decode($output, true);

        $expectedResponse = [
            'status' => 'error',
            'status_code' => 400,
            'message' => 'Invalid or empty grade.',
        ];

        $this->assertEquals($expectedResponse, $outputResponse);

        unset($_SERVER['HTTP_X_LIFF_TOKEN']);
    }

    public function testInsertEvaluationWithEmptyEvaluationDate()
    {

        $inputData = '{"userId": "123", "evaluationResults": [90, 85, 75], "grade_level": "one", "evaluation_date": ""}';

        // Set a specific token for testing
        $_SERVER['HTTP_X_LIFF_TOKEN'] = 'valid_token';
        // Capture the output of the function
        ob_start();
        insertEvaluation($inputData, null);
        $output = ob_get_clean();

        // Decode the JSON output for assertion
        $outputResponse = json_decode($output, true);


        $expectedResponse = [
            'status' => 'error',
            'status_code' => 400,
            'message' => 'Evaluation date cannot be empty.',
        ];

        $this->assertEquals($expectedResponse, $outputResponse);

        unset($_SERVER['HTTP_X_LIFF_TOKEN']);
    }

    public function testInsertEvaluationWithInvalidEvaluationDate()
    {

        $inputData = '{"userId": "123", "evaluationResults": [90, 85, 75], "grade_level": "one", "evaluation_date": "20211301"}';

        // Set a specific token for testing
        $_SERVER['HTTP_X_LIFF_TOKEN'] = 'valid_token';
        // Capture the output of the function
        ob_start();
        insertEvaluation($inputData, null);
        $output = ob_get_clean();

        // Decode the JSON output for assertion
        $outputResponse = json_decode($output, true);


        $expectedResponse = [
            'status' => 'error',
            'status_code' => 400,
            'message' => 'Invalid format for evaluation date.',
        ];

        $this->assertEquals($expectedResponse, $outputResponse);

        unset($_SERVER['HTTP_X_LIFF_TOKEN']);
    }

    public function testInsertEvaluationWithFalseEntryCheck()
    {
        ob_clean();
        // Start output buffering
        ob_start();
        // Create a mock database connection
        $mockDatabase = $this->createMock(DatabaseWrapper::class);
        // Set up expectations for the mock database
        $mockDatabase->expects($this->any())
            ->method('queryParams')
            ->willReturnCallback(function () {
                return false;
            });

        $mockDatabase->expects($this->any())
            ->method('close')
            ->willReturnCallback(function () {
                return null;
            });

        $_SERVER['HTTP_X_LIFF_TOKEN'] = 'valid_token';

        $inputData = '{"userId": "123", "evaluationResults": [90, 85, 75], "grade_level": "one", "evaluation_date": "20211201"}';

        insertEvaluation($inputData, $mockDatabase);

        $output = ob_get_clean();

        // Decode the JSON output for assertion
        $outputResponse = json_decode($output, true);

        $expectedResponse = [
            'status' => 'error',
            'status_code' => 500,
            'message' => 'Error: Query execution failed.',
        ];

        $this->assertEquals($expectedResponse, $outputResponse);
        // Unset the token after the test
        unset($_SERVER['HTTP_X_LIFF_TOKEN']);
    }

    public function testInsertEvaluationWithNumberRowsZero()
    {
        ob_clean();
        // Start output buffering
        ob_start();
        // Create a mock database connection
        $mockDatabase = $this->createMock(DatabaseWrapper::class);
        // Set up expectations for the mock database
        $mockDatabase->expects($this->any())
            ->method('queryParams')
            ->willReturnCallback(function () {
                return true;
            });

        $mockDatabase->expects($this->any())
            ->method('num_rows')
            ->willReturnCallback(function () {
                return 0;
            });

        $mockDatabase->expects($this->any())
            ->method('close')
            ->willReturnCallback(function () {
                return null;
            });

        $_SERVER['HTTP_X_LIFF_TOKEN'] = 'valid_token';

        $inputData = '{"userId": "123", "evaluationResults": [90, 85, 75], "grade_level": "one", "evaluation_date": "20211201"}';

        insertEvaluation($inputData, $mockDatabase);

        $output = ob_get_clean();

        // Decode the JSON output for assertion
        $outputResponse = json_decode($output, true);

        $expectedResponse = [
            'status' => 'success',
            'status_code' => 200,
            'message' => 'Data inserted successfully',
        ];

        $this->assertEquals($expectedResponse, $outputResponse);
        // Unset the token after the test
        unset($_SERVER['HTTP_X_LIFF_TOKEN']);
    }
}
