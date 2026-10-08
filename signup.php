```php
<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

include "db.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $name = $_POST["name"] ?? "";
    $email = $_POST["email"] ?? "";
    $password = $_POST["password"] ?? "";
    $confirm_password = $_POST["confirm_password"] ?? "";

    // Check required fields
    if (empty($name) || empty($email) || empty($password) || empty($confirm_password)) {
        die("Please fill all fields.");
    }

    // Check password confirmation
    if ($password !== $confirm_password) {
        die("Passwords do not match!");
    }

    // Check if email already exists
    $check = $conn->prepare("SELECT id FROM users WHERE email = ?");

    if (!$check) {
        die("Database error: " . $conn->error);
    }

    $check->bind_param("s", $email);
    $check->execute();
    $check->store_result();

    if ($check->num_rows > 0) {

        $check->close();
        $conn->close();

        echo "<h2>Email already registered!</h2>";
        echo '<p><a href="signup.html">Go Back</a></p>';

        exit();
    }

    $check->close();

    // Hash password
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // Insert user into database
    $stmt = $conn->prepare(
        "INSERT INTO users (name, email, password) VALUES (?, ?, ?)"
    );

    if (!$stmt) {
        die("Database error: " . $conn->error);
    }

    $stmt->bind_param("sss", $name, $email, $hashed_password);

    if ($stmt->execute()) {

        echo "<!DOCTYPE html>";
        echo "<html>";
        echo "<head>";
        echo "<title>Registration Successful</title>";
        echo "</head>";
        echo "<body style='font-family: Arial; text-align: center; padding-top: 100px;'>";

        echo "<h1>Registration Successful!</h1>";
        echo "<p>Welcome to TravelGo, " . htmlspecialchars($name) . ".</p>";
        echo "<br>";
        echo "<a href='login.html'>Go to Login</a>";

        echo "</body>";
        echo "</html>";

    } else {

        echo "<h2>Registration Failed!</h2>";
        echo "<p>Error: " . $stmt->error . "</p>";
    }

    $stmt->close();
    $conn->close();

} else {

    echo "<h2>Please submit the signup form.</h2>";
}

?>
```
