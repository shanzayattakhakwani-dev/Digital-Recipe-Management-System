<?php
// db.php - Database connection file
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "digital_recipe_system";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    // die("Connection failed: " . $conn->connect_error);
    echo'The database connection is invalid please check that apche and MYSql must be start on a XXAMP control panel';
}
?>