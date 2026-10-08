<?php

session_start();

include "db.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $email = $_POST["email"] ?? "";
    $password = $_POST["password"] ?? "";

    if (empty($email) || empty($password)) {
        die("Please enter email and password.");
    }

    // Find user by email
    $stmt = $conn->prepare("SELECT id, name, password FROM users WHERE email = ?");

    if (!$stmt) {
        die("Database error: " . $conn->error);
    }

    $stmt->bind_param("s", $email);
    $stmt->execute();

    $result = $stmt->get_result();

    if ($result->num_rows == 1) {

        $user = $result->fetch_assoc();

        // Verify password
        if (password_verify($password, $user["password"])) {

            $_SESSION["user_id"] = $user["id"];
            $_SESSION["user_name"] = $user["name"];
            $_SESSION["user_email"] = $email;

            header("Location: index.html");
            exit();

        } else {

            echo "<h2>Invalid password!</h2>";
            echo '<p><a href="login.html">Try Again</a></p>';
        }

    } else {

        echo "<h2>Email not registered!</h2>";
        echo '<p><a href="signup.html">Create Account</a></p>';
    }

    $stmt->close();
    $conn->close();
}

?>