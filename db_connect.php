<?php

class DatabaseWrapper
{
    private $conn;

    public function __construct($conn)
    {
        $this->conn = $conn;
    }

    public function queryParams($conn, $query, $params)
    {
        $result = pg_query_params($conn, $query, $params);
        return $result;
    }

    public function num_rows($result)
    {
        return pg_num_rows($result);
    }

    public function close($conn)
    {
        pg_close($conn);
    }

    public function fetchAssoc($result)
    {
        // Use this method to fetch the result from the database
        return pg_fetch_assoc($result);
    }
}


// Function to get a database connection
function getDBConnection($conn = null)
{
    if ($conn) {
        return $conn;
    }

    // Actual database connection parameters
    $host = "localhost";
    $port = "5432";
    $dbname = "hokushin_sbs_test_db";
    $user = "hokushin_sbs";
    $password = "hokushin_sbs";
    $conn = pg_connect("host=$host port=$port dbname=$dbname user=$user password=$password");

    if (!$conn) {
        die("Connection failed: " . pg_last_error());
    }
    return $conn;
}
