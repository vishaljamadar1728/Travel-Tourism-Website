```php
<?php

$servername = "localhost";
$username = "root";
$password = "";
$database = "travel_tourism";

// Create database connection
$conn = new mysqli(
    $servername,
    $username,
    $password,
    $database
);

// Check database connection
if ($conn->connect_error) {
    die("Database connection failed. Please try again later.");
}

// Set character encoding
$conn->set_charset("utf8mb4");

?>
```