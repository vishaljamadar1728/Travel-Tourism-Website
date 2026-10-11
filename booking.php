```php
<?php

session_start();

error_reporting(E_ALL);
ini_set('display_errors', 1);

// User must be logged in
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

// Allowed packages and destinations
$allowedPackages = [
    "Agra Weekend Escape",
    "Royal Rajasthan",
    "Kerala Experience",
    "Manali Adventure"
];

$allowedDestinations = [
    "Agra",
    "Jaipur",
    "Kerala",
    "Goa",
    "Manali"
];

// Validate booking details
if (
    $name === "" ||
    !filter_var($email, FILTER_VALIDATE_EMAIL) ||
    $phone === "" ||
    !in_array($destination, $allowedDestinations, true) ||
    !in_array($package, $allowedPackages, true) ||
    $travel_date === "" ||
    $people === false ||
    $people < 1 ||
    $people > 20
) {
    die("Invalid booking details. Please go back and check your form.");
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

$user_id = (int) $_SESSION["user_id"];

// Save booking
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
    echo "<p>Destination: " . htmlspecialchars($destination, ENT_QUOTES, 'UTF-8') . "</p>";
    echo "<p>Package: " . htmlspecialchars($package, ENT_QUOTES, 'UTF-8') . "</p>";
    echo "<p>Travel Date: " . htmlspecialchars($travel_date, ENT_QUOTES, 'UTF-8') . "</p>";
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
```