```php
<?php
session_start();
require_once "db.php";

// Check whether the user is logged in
if (!isset($_SESSION["user_id"])) {
    header("Location: login.html");
    exit();
}

$user_id = $_SESSION["user_id"];

// Fetch only the logged-in user's bookings
$sql = "SELECT id, customer_name, email, phone, destination,
               package_name, travel_date, travelers,
               special_requests, status, created_at
        FROM bookings
        WHERE user_id = ?
        ORDER BY created_at DESC";

$stmt = mysqli_prepare($conn, $sql);

if (!$stmt) {
    die("Unable to load bookings. Please try again later.");
}

mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);

function safe($value) {
    return htmlspecialchars((string)($value ?? ""), ENT_QUOTES, "UTF-8");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>My Bookings - TravelGo</title>

    <link rel="stylesheet" href="style.css">

    <style>
        .my-bookings-section {
            max-width: 1150px;
            margin: 50px auto;
            padding: 25px;
        }

        .my-bookings-section h1 {
            margin-bottom: 10px;
        }

        .booking-subtitle {
            margin-bottom: 30px;
            color: #666;
        }

        .booking-card {
            background: #fff;
            border: 1px solid #e5e7eb;
            border-radius: 14px;
            padding: 25px;
            margin-bottom: 22px;
            box-shadow: 0 5px 18px rgba(0, 0, 0, 0.05);
        }

        .booking-card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 15px;
            flex-wrap: wrap;
            margin-bottom: 20px;
        }

        .booking-card-header h2 {
            font-size: 22px;
            margin: 0;
        }

        .booking-status {
            background: #fff3cd;
            color: #856404;
            padding: 7px 14px;
            border-radius: 20px;
            font-size: 13px;
            font-weight: 600;
        }

        .booking-details {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 18px;
        }

        .booking-detail {
            overflow-wrap: anywhere;
        }

        .booking-detail strong {
            display: block;
            color: #666;
            font-size: 13px;
            margin-bottom: 5px;
        }

        .booking-detail span {
            color: #222;
            font-size: 15px;
        }

        .no-bookings {
            text-align: center;
            padding: 45px 20px;
            border: 1px dashed #ccc;
            border-radius: 14px;
        }

        .no-bookings p {
            margin: 12px 0 20px;
        }

        .booking-link {
            display: inline-block;
            padding: 12px 22px;
            background: #0d6efd;
            color: white;
            text-decoration: none;
            border-radius: 8px;
        }

        .booking-link:hover {
            opacity: 0.9;
        }

        @media (max-width: 600px) {
            .my-bookings-section {
                margin: 25px auto;
                padding: 15px;
            }

            .booking-details {
                grid-template-columns: 1fr;
            }

            .booking-card {
                padding: 18px;
            }
        }
    </style>
</head>

<body>

<nav>
    <a href="index.html">Home</a>
    <a href="destinations.html">Destinations</a>
    <a href="packages.html">Packages</a>
    <a href="my-bookings.php">My Bookings</a>
</nav>

<main class="my-bookings-section">

    <h1>My Bookings</h1>
    <p class="booking-subtitle">
        View your travel packages and booking details.
    </p>

    <?php if (mysqli_num_rows($result) > 0): ?>

        <?php while ($booking = mysqli_fetch_assoc($result)): ?>

            <section class="booking-card">

                <div class="booking-card-header">
                    <h2><?= safe($booking["package_name"]) ?></h2>

                    <span class="booking-status">
                        <?= safe($booking["status"]) ?>
                    </span>
                </div>

                <div class="booking-details">

                    <div class="booking-detail">
                        <strong>Booking ID</strong>
                        <span>#<?= safe($booking["id"]) ?></span>
                    </div>

                    <div class="booking-detail">
                        <strong>Destination</strong>
                        <span><?= safe($booking["destination"]) ?></span>
                    </div>

                    <div class="booking-detail">
                        <strong>Customer Name</strong>
                        <span><?= safe($booking["customer_name"]) ?></span>
                    </div>

                    <div class="booking-detail">
                        <strong>Email</strong>
                        <span><?= safe($booking["email"]) ?></span>
                    </div>

                    <div class="booking-detail">
                        <strong>Phone</strong>
                        <span><?= safe($booking["phone"]) ?></span>
                    </div>

                    <div class="booking-detail">
                        <strong>Travel Date</strong>
                        <span><?= safe($booking["travel_date"]) ?></span>
                    </div>

                    <div class="booking-detail">
                        <strong>Number of Travelers</strong>
                        <span><?= safe($booking["travelers"]) ?></span>
                    </div>

                    <div class="booking-detail">
                        <strong>Booking Date</strong>
                        <span><?= safe($booking["created_at"]) ?></span>
                    </div>

                    <?php if (!empty($booking["special_requests"])): ?>
                        <div class="booking-detail">
                            <strong>Special Requests</strong>
                            <span><?= safe($booking["special_requests"]) ?></span>
                        </div>
                    <?php endif; ?>

                </div>

            </section>

        <?php endwhile; ?>

    <?php else: ?>

        <div class="no-bookings">
            <h2>No bookings yet</h2>
            <p>You haven't booked any travel packages yet.</p>
            <a href="packages.html" class="booking-link">
                Explore Packages
            </a>
        </div>

    <?php endif; ?>

</main>

</body>
</html>

<?php
mysqli_stmt_close($stmt);
?>
```