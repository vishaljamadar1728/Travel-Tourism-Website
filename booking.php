<?php

session_start();

error_reporting(E_ALL);
ini_set('display_errors', 1);

// User must be logged in before booking
if (!isset($_SESSION["user_id"])) {
    header("Location: login.html");
    exit();
}

include "db.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: booking.html");
    exit();
}

// Get form data
$name = trim($_POST["name"] ?? "");
$email = trim($_POST["email"] ?? "");
$phone = trim($_POST["phone"] ?? "");
$destination = trim($_POST["destination"] ?? "");
$package = trim($_POST["package"] ?? "");
$travel_date = $_POST["travel_date"] ?? "";
$people = filter_var(
    $_POST["people"] ?? "",
    FILTER_VALIDATE_INT
);

// Validate required fields
if (
    $name === "" ||
    !filter_var($email, FILTER_VALIDATE_EMAIL) ||
    $phone === "" ||
    $destination === "" ||
    $package === "" ||
    $travel_date === "" ||
    $people === false ||
    $people < 1 ||
    $people > 20
) {
    die("Please enter valid booking details.");
}

// Validate travel date
$date = DateTime::createFromFormat("!Y-m-d", $travel_date);

if (
    !$date ||
    $date->format("Y-m-d") !== $travel_date ||
    $travel_date < date("Y-m-d")
) {
    die("Please select a valid travel date.");
}

// Use the logged-in user's ID
$user_id = (int) $_SESSION["user_id"];

// Insert booking into MySQL
$stmt = $conn->prepare(
    "INSERT INTO bookings
    (user_id, customer_name, email, phone, destination,
     package_name, travel_date, travelers)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?)"
);

if (!$stmt) {
    die("Database error: " . $conn->error);
}

$stmt->bind_param(
    "issssssi",
    $user_id,
    $name,
    $email,
    $phone,
    $destination,
    $package,
    $travel_date,
    $people
);

if ($stmt->execute()) {

    echo "<!DOCTYPE html>";
    echo "<html lang='en'>";
    echo "<head>";
    echo "<meta charset='UTF-8'>";
    echo "<meta name='viewport' content='width=device-width, initial-scale=1.0'>";
    echo "<title>Booking Successful - TravelGo</title>";
    echo "</head>";

    echo "<body style='font-family:Arial;text-align:center;padding:60px 20px;'>";

    echo "<h1>Booking Successful!</h1>";
    echo "<p>Your booking request has been saved.</p>";
    echo "<p>Destination: " . htmlspecialchars($destination) . "</p>";
    echo "<p>Package: " . htmlspecialchars($package) . "</p>";
    echo "<p>Travel Date: " . htmlspecialchars($travel_date) . "</p>";
    echo "<p>Number of People: " . (int) $people . "</p>";
    echo "<p>Status: Pending</p>";
    echo "<a href='packages.html'>Explore Packages</a>";
    echo " | ";
    echo "<a href='index.html'>Home</a>";

    echo "</body></html>";

} else {
    echo "Booking failed. Please try again.";
}

$stmt->close();
$conn->close();

?>