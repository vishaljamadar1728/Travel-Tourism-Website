<?php
session_start();
require_once __DIR__ . "/db.php";

// Redirect users who are not logged in
if (!isset($_SESSION["user_id"])) {
    header("Location: login.html");
    exit();
}

$user_id = (int) $_SESSION["user_id"];

// Fetch only the logged-in user's bookings
$sql = "SELECT id, customer_name, email, phone, destination,
               package_name, travel_date, travelers,
               special_requests, status, created_at
        FROM bookings
        WHERE user_id = ?
        ORDER BY created_at DESC";

$stmt = mysqli_prepare($conn, $sql);

if (!$stmt) {
    http_response_code(500);
    exit("Unable to load bookings. Please try again later.");
}

mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);

// Safely display database values
function safe($value)
{
    return htmlspecialchars(
        (string)($value ?? ""),
        ENT_QUOTES,
        "UTF-8"
    );
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>My Bookings - TravelGo</title>

    <link rel="stylesheet" href="style.css?v=20261012">

    <style>
        /* ================= TRAVELGO THEME ================= */

        body.my-bookings-page {
            background: #F8F1E8;
            color: #11100E;
            font-family: 'DM Sans', sans-serif;
        }

        /* Navbar */

        body.my-bookings-page .navbar {
            background: #E6C9AE;
            border-bottom: 1px solid rgba(17, 16, 14, 0.08);
            box-shadow: 0 2px 15px rgba(17, 16, 14, 0.08);
        }

        body.my-bookings-page .navbar nav {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 22px;
            flex-wrap: wrap;
        }

        body.my-bookings-page .navbar nav a {
            color: #11100E;
            font-size: 14px;
            font-weight: 600;
            transition: color 0.3s ease;
        }

        body.my-bookings-page .navbar nav a:hover,
        body.my-bookings-page .navbar nav a.active {
            color: #3F5125;
        }

        body.my-bookings-page .navbar .login-btn {
            background: #3F5125;
            color: #FFFFFF;
        }

        body.my-bookings-page .navbar .login-btn:hover {
            background: #2F3D1C;
        }

        /* Page heading */

        body.my-bookings-page .page-header {
            background: #E6C9AE;
            padding: 55px 20px 45px;
            text-align: center;
        }

        body.my-bookings-page .page-header h1 {
            color: #11100E;
            font-family: 'Space Grotesk', sans-serif;
            font-size: 38px;
            margin-bottom: 10px;
        }

        body.my-bookings-page .page-header p {
            color: #554D45;
            font-size: 16px;
        }

        /* Main section */

        #myBookings {
            max-width: 1150px;
            width: 100%;
            margin: 0 auto;
            padding: 45px 25px 70px;
            background: #F8F1E8;
            min-height: 350px;
        }

        #myBookings .booking-subtitle {
            text-align: center;
            color: #6B6259;
            margin-bottom: 30px;
            font-size: 16px;
        }

        #myBookings .booking-count {
            color: #3F5125;
            font-size: 15px;
            font-weight: 700;
            margin-bottom: 20px;
        }

        /* Individual booking cards */

        #myBookings .booking-card {
            background: #FFF9F2;
            border: 1px solid rgba(17, 16, 14, 0.10);
            border-radius: 5px;
            padding: 28px;
            margin-bottom: 25px;
            box-shadow: 0 8px 25px rgba(17, 16, 14, 0.08);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        #myBookings .booking-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 14px 30px rgba(17, 16, 14, 0.12);
        }

        #myBookings .booking-card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 15px;
            flex-wrap: wrap;
            padding-bottom: 18px;
            margin-bottom: 22px;
            border-bottom: 1px solid #E6C9AE;
        }

        #myBookings .booking-card-header h2 {
            font-family: 'Space Grotesk', sans-serif;
            font-size: 23px;
            color: #11100E;
            margin: 0;
            overflow-wrap: anywhere;
        }

        /* Booking status */

        #myBookings .booking-status {
            display: inline-block;
            background: #E6C9AE;
            color: #2F3D1C;
            border: 1px solid #B89B4A;
            padding: 7px 14px;
            border-radius: 4px;
            font-size: 13px;
            font-weight: 700;
            text-transform: capitalize;
            white-space: nowrap;
        }

        #myBookings .booking-status.status-confirmed {
            background: #E7EBD9;
            color: #2F3D1C;
            border-color: #AAB98A;
        }

        #myBookings .booking-status.status-pending {
            background: #F5E9C9;
            color: #6D5510;
            border-color: #B89B4A;
        }

        #myBookings .booking-status.status-cancelled {
            background: #F0DDD5;
            color: #793F2D;
            border-color: #D8A899;
        }

        /* Booking detail boxes */

        #myBookings .booking-details {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 16px;
        }

        #myBookings .booking-detail {
            min-width: 0;
            overflow-wrap: anywhere;
            padding: 15px;
            background: #F8F1E8;
            border: 1px solid rgba(17, 16, 14, 0.06);
            border-radius: 4px;
        }

        #myBookings .booking-detail strong {
            display: block;
            color: #6B6259;
            font-family: 'DM Sans', sans-serif;
            font-size: 13px;
            font-weight: 700;
            margin-bottom: 6px;
        }

        #myBookings .booking-detail span {
            color: #11100E;
            font-family: 'DM Sans', sans-serif;
            font-size: 15px;
            font-weight: 600;
            overflow-wrap: anywhere;
        }

     