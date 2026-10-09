<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

include "db.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $name = trim($_POST["name"] ?? "");
    $email = trim($_POST["email"] ?? "");
    $phone = trim($_POST["phone"] ?? "");
    $password = $_POST["password"] ?? "";
    $confirm_password = $_POST["confirm_password"] ?? "";

    // Check required fields
    if (
        empty($name) ||
        empty($email) ||
        empty($phone) ||
        empty($password) ||
        empty($confirm_password)
    ) {
        die("Please fill all fields.");
    }

    // Check email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        die("Please enter a valid email address.");
    }

    // Check password confirmation
    if ($password !== $confirm_password) {
        die("Passwords do not match!");
    }

    // Check whether email already exists
    $check = $conn->prepare(
        "SELECT id FROM users WHERE email = ?"
    );

    if (!$check) {
        die("Database error: " . $conn->error);
    }

    $check->bind_param("s", $email);
    $check->execute();
    $check->store_result();

    if ($check->num_rows > 0) {

        $check->close();
        $conn->close();

        die(
            "Email already registered! " .
            '<a href="signup.html">Try another email</a>'
        );
    }

    $check->close();

    // Securely hash password
    $hashed_password = password_hash(
        $password,
        PASSWORD_DEFAULT
    );

    // Save name, email, phone and password
    $stmt = $conn->prepare(
        "INSERT INTO users (name, email, phone, password)
         VALUES (?, ?, ?, ?)"
    );

    if (!$stmt) {
        die("Database error: " . $conn->error);
    }

    $stmt->bind_param(
        "ssss",
        $name,
        $email,
        $phone,
        $hashed_password
    );

    if ($stmt->execute()) {

        echo "<h1>Registration Successful!</h1>";
        echo "<p>Welcome to TravelGo, "
            . htmlspecialchars($name, ENT_QUOTES, 'UTF-8')
            . ".</p>";

        echo '<a href="login.html">Go to Login</a>';

    } else {

        echo "Registration failed. Please try again.";
    }

    $stmt->close();
    $conn->close();

} else {

    echo "Please submit the signup form.";
}

?>