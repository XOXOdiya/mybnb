<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Booking Details</title>
</head>
<body>
<?php
// Enable error reporting for debugging purposes
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Include database configuration file
include "config.php";

// Connect to MySQL database
$DBC = mysqli_connect("127.0.0.1", DBUSER, DBPASSWORD, DBDATABASE);

// Check for connection errors
if (mysqli_connect_errno()) {
    echo "Error: Unable to connect to MySQL." . mysqli_connect_error();
    exit; // Stop processing the page further if connection fails
}

// Check if 'id' exists in the URL and is valid
if ($_SERVER["REQUEST_METHOD"] == "GET") {
    if (!isset($_GET['id']) || empty($_GET['id']) || !is_numeric($_GET['id'])) {
        echo "<h2>Invalid or missing booking ID.</h2>";
        exit; // Stop processing if booking id is invalid or missing
    }
    
    $id = $_GET['id'];
}

// Query to retrieve booking details based on booking ID using a prepared statement
$query = 'SELECT booking.bookingID, room.roomname,
                 booking.checkin_date, booking.checkout_date,
                 customers.firstname, customers.lastname,
                 booking.contact_number, booking.booking_extra
          FROM booking
          INNER JOIN room ON booking.RoomID = room.roomID
          INNER JOIN customers ON booking.CustomerID = customers.CustomerID
          WHERE booking.bookingID = ?';

// Prepare the statement
$stmt = mysqli_prepare($DBC, $query);

// Bind the 'id' parameter (integer)
mysqli_stmt_bind_param($stmt, 'i', $id);

// Execute the statement
mysqli_stmt_execute($stmt);

// Get the result
$result = mysqli_stmt_get_result($stmt);

// Count the number of rows returned
$rowcount = mysqli_num_rows($result);
?>

<!-- HTML section to display booking details -->
<h1>Booking Details View</h1>
<h2>
    <a href="listbookings.php">[Return to the booking listing]</a>
    <a href="index.php">[Return to the main page]</a>
</h2>

<?php
// Check if any rows were returned from the query
if ($rowcount > 0) {
    echo "<fieldset><legend>Booking Detail #$id</legend><dl>";
    $row = mysqli_fetch_assoc($result);

    // Display each booking detail
    echo "<dt>Room name: </dt><dd>" . htmlspecialchars($row['roomname']) . "</dd>" . PHP_EOL;
    echo "<dt>Check-in Date: </dt><dd>" . htmlspecialchars($row['checkin_date']) . "</dd>" . PHP_EOL;
    echo "<dt>Check-out Date: </dt><dd>" . htmlspecialchars($row['checkout_date']) . "</dd>" . PHP_EOL;
    echo "<dt>Customer Name: </dt><dd>" . htmlspecialchars($row['firstname']) . " " . htmlspecialchars($row['lastname']) . "</dd>" . PHP_EOL;
    echo "<dt>Contact Number: </dt><dd>" . htmlspecialchars($row['contact_number']) . "</dd>" . PHP_EOL;
    echo "<dt>Booking Extra: </dt><dd>" . htmlspecialchars($row['booking_extra']) . "</dd>" . PHP_EOL;
   
   
    echo '</dl></fieldset>' . PHP_EOL;
} else {
    echo "<h5>No booking found! Possibly deleted!</h5>";
}

// Free result set and close database connection
mysqli_free_result($result);
mysqli_close($DBC);
?>

</body>
</html>
