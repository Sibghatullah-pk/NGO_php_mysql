<?php
$host = 'localhost';
$user = 'root';
$pass = '';
$dbname = 'project';

$db = new mysqli($host, $user, $pass, $dbname);

if ($db->connect_error) {
    echo "Connection failed: " . $db->connect_error;
} else {
    echo "Database connected successfully!<br>";
    $result = $db->query("SHOW TABLES");
    if ($result) {
        echo "Tables in database:<br>";
        while ($row = $result->fetch_array()) {
            echo $row[0] . "<br>";
        }
    } else {
        echo "Error querying tables: " . $db->error;
    }
}

$db->close();
?>