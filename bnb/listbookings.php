<?php
// Enable error reporting to show any issues
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database connection
include "config.php";  // Make sure config.php contains correct DB credentials.

$DBC = mysqli_connect("127.0.0.1", DBUSER, DBPASSWORD, DBDATABASE);

if (mysqli_connect_errno()) {
    echo "Error: Unable to connect to MySQL." . mysqli_connect_error();
    exit; // Stop processing the page further.
}

// SQL query to fetch bookings
$query = "SELECT
        booking.bookingID,
        room.roomname,
        booking.checkin_date,
        booking.checkout_date,
        customers.firstname,
        customers.lastname
    FROM
        booking
    JOIN
        room ON booking.RoomID = room.roomID
    JOIN
        customers ON booking.CustomerID = customers.customerID
    ORDER BY
        booking.bookingID;";

$result = mysqli_query($DBC, $query);

if (!$result) {
    echo "Error: " . mysqli_error($DBC);  // Show query errors
    exit;
}

$rowcount = mysqli_num_rows($result);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Browse Bookings</title>
</head>
<body>

<h1>Current Bookings</h1>
<h2><a href="makebookings.php">[Make a Booking]</a> | <a href="index.php">[Return to Main Page]</a></h2>

<table border="1">
    <thead>
        <tr>
            <th>Booking (Room, Check-in Date, Check-out Date)</th>
            <th>Customer</th>
            <th>Action</th>
        </tr>
    </thead>
    <tbody>
        <?php
        if ($rowcount > 0) {
            while ($row = mysqli_fetch_assoc($result)) {
                $id = $row['bookingID'];
                echo '<tr><td>' . $row['roomname'] . ', ' . $row['checkin_date'] . ', ' . $row['checkout_date'] . '</td>';
                echo '<td>' . $row['firstname'] . ' ' . $row['lastname'] . '</td>';
                echo '<td><a href="previewbookings.php?id=' . $id . '">[view]</a>';
                echo '<a href="editbookings.php?id=' . $id . '">[edit]</a>';
                echo '<a href="deletebookings.php?id=' . $id . '">[delete]</a></td>';
                echo '</tr>' . PHP_EOL;
            }
        } else {
            echo "<tr><td colspan='3'><h2>No bookings found!</h2></td></tr>";
        }
        mysqli_free_result($result);
        mysqli_close($DBC);
        ?>
    </tbody>
</table>

</body>
</html>
