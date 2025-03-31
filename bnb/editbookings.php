<?php
// Enable error reporting to show any issues
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database connection
include "config.php"; // Ensure config.php includes your database credentials and settings
$DBC = mysqli_connect("127.0.0.1", DBUSER, DBPASSWORD, DBDATABASE);

if (mysqli_connect_errno()) {
    echo "Error: Unable to connect to MySQL." . mysqli_connect_error();
    exit;
}

function cleanInput($data)
{
    global $DBC;
    return mysqli_real_escape_string($DBC, htmlspecialchars(stripslashes(trim($data))));
}

// Check if ID exists and is numeric
if ($_SERVER["REQUEST_METHOD"] == "GET") {
    $id = $_GET['id'];
    if (empty($id) or !is_numeric($id)) {
        echo "<h2>Invalid booking ID</h2>";
        exit;
    }

    // Fetch booking details for the given ID
    $query = "SELECT booking.bookingID, room.roomname, booking.checkin_date, booking.checkout_date, booking.contact_number, booking.booking_extra
              FROM booking
              LEFT JOIN room ON booking.roomID = room.roomID
              WHERE booking.bookingID = ?";
    $stmt = mysqli_prepare($DBC, $query);
    mysqli_stmt_bind_param($stmt, 'i', $id); // Correct binding for an integer ID
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    // Check if booking exists
    if (mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
    } else {
        echo "<h2>Booking not found</h2>";
        exit;
    }

    mysqli_stmt_close($stmt);
}

// On form submission, update booking details
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Map departure_date to checkin_date and arrival_date to checkout_date
    $departure_date = cleanInput($_POST['departure_date']); // Correct form field name
    $arrival_date = cleanInput($_POST['arrival_date']); // Correct form field name
    $contactNumber = cleanInput($_POST['contact_Number']); // Correct form field name
    $bookingExtra = cleanInput($_POST['bookingExtra']); // Correct form field name
    
    $id = cleanInput($_POST['id']);

    // Update query with checkin_date and checkout_date
    $query = "UPDATE booking SET checkin_date=?, checkout_date=?, contact_number=?, booking_extra=? WHERE bookingID=?";
    $stmt = mysqli_prepare($DBC, $query);

    if ($stmt === false) {
        die("Error preparing statement: " . mysqli_error($DBC));
    }

    // Bind parameters for the query
    // 'sssssi' matches 4 strings (check-in date, check-out date, contact number, booking extra) and 1 integer (booking ID)
    mysqli_stmt_bind_param($stmt, $departure_date, $arrival_date, $contactNumber, $bookingExtra, $id);

    // Execute the query and check if it was successful
    if (mysqli_stmt_execute($stmt)) {
        echo "<h5>Booking updated successfully.</h5>";
    } else {
        echo "Error updating booking: " . mysqli_error($DBC);
    }

    mysqli_stmt_close($stmt);
}

mysqli_close($DBC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Booking</title>
    <link rel="stylesheet" href="//code.jquery.com/ui/1.13.2/themes/base/jquery-ui.css">
    <script src="https://code.jquery.com/jquery-3.6.0.js"></script>
    <script src="https://code.jquery.com/ui/1.13.2/jquery-ui.js"></script>
    <script>
        $(document).ready(function() {
            $.datepicker.setDefaults({
                dateFormat: 'yy-mm-dd'
            });
            $(function() {
                $("#departure_date").datepicker();
                $("#arrival_date").datepicker();
            });
        });
    </script>
</head>
<body>

<h1>Update Booking</h1>
<h2>
    <a href="listbookings.php">[Return to the booking list]</a>
    <a href="index.php">[Return to main page]</a>
</h2>

<div>
     <form action="editbookings.php" method="POST">
        <p>
            <label for="roomname">Room:</label>
            <input type="text" id="roomname" name="roomname" value="<?php echo htmlspecialchars($row['roomname']); ?>" readonly>
        </p>
        <p>
            <label for="departure_date">Check-in Date:</label>
            <input type="text" id="departure_date" name="departure_date" required value="<?php echo htmlspecialchars($row['checkin_date']); ?>">
        </p>
        <p>
            <label for="arrival_date">Check-out Date:</label>
            <input type="text" id="arrival_date" name="arrival_date" required value="<?php echo htmlspecialchars($row['checkout_date']); ?>">
        </p>
        <p>
            <label for="contact_Number">Contact Number:</label>
            <input type="text" id="contact_Number" name="contact_Number" value="<?php echo htmlspecialchars($row['contact_number']); ?>">
        </p>
        <p>
            <label for="bookingExtra">Booking Extra:</label>
            <input type="text" id="bookingExtra" name="bookingExtra" value="<?php echo htmlspecialchars($row['booking_extra']); ?>">
        </p>
      
        <input type="hidden" name="id" value="<?php echo $id; ?>">
        <input type="submit" name="submit" value="Update">
    </form>
</div>

</body>
</html>


