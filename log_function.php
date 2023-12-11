<?php

function writeToLog($message)
{
    // Log message with timestamp
    $logMessage = date("Y-m-d H:i:s") . " - " . $message . PHP_EOL;

    // Open or create the log file in append mode
    $file = fopen(__DIR__ . '/log.txt', 'a');

    // Check if the file was opened successfully
    if ($file) {
        // Write the log message to the file
        fwrite($file, $logMessage);

        // Close the file
        fclose($file);
    } else {
        // Handle the case where the file could not be opened
        echo "Error opening the log file.";
    }
}
?>
