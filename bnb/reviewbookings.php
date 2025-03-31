<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Room Review</title>
</head>
<body>
    <?php
    // Include the config file with your database connection settings
    include "config.php"; // Ensure this file contains your DB connection details

    // Establish a connection to the database
    $DBC = mysqli_connect("127.0.0.1", DBUSER, DBPASSWORD, DBDATABASE);

    // Check if the connection was successful
    if (mysqli_connect_error()) {
        echo "Error: Unable to connect to MySQL. " . mysqli_connect_error();
        exit; // Stop further processing if database connection fails
    }

    // Function to clean input data (removes unwanted characters)
    function cleanInput($data)
    {
        return htmlspecialchars(stripslashes(trim($data)));
    }

    // Check if 'id' exists and is valid (numeric)
    if (isset($_GET['id']) && is_numeric($_GET['id'])) {
        $id = $_GET['id']; // Get the booking ID from the GET parameter
    } else {
        echo "<h2>Invalid booking ID</h2>";
        exit; // Stop further execution if the 'id' is invalid
    }

    // Handle the form submission for updating the room review
    if (isset($_POST['submit']) && $_POST['submit'] == 'Update') {
        $roomReview = cleanInput($_POST['room_review']); // Clean and get the review from the form
        $id = cleanInput($_POST['id']); // Get the booking ID from the form (hidden field)

        // Prepare the update SQL statement to change the room review
        $upd = "UPDATE `booking` SET room_review=? WHERE bookingID=?";
        $stmt = mysqli_prepare($DBC, $upd); // Prepare the SQL query
        mysqli_stmt_bind_param($stmt, 'si', $roomReview, $id); // Bind parameters (s = string, i = integer)
        mysqli_stmt_execute($stmt); // Execute the query
        mysqli_stmt_close($stmt); // Close the prepared statement

        // Display a message confirming the update
        echo "<h5>Room review updated successfully!</h5>";
    }

    // Fetch the current room review for the given booking ID from the database
    $query = "SELECT room_review FROM `booking` WHERE bookingID=" . $id;
    $result = mysqli_query($DBC, $query);

    // Check if the query returned a valid result
    if (mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result); // Fetch the row with room review
        // Now you can safely access the 'Room_review' key
        $roomReview = isset($row['room_review']) ? $row['room_review'] : 'No review available'; // Fallback if empty
    } else {
        echo "<h5>No booking found with this ID!</h5>";
        exit; // Stop further execution if the booking ID doesn't exist
    }

    mysqli_free_result($result); // Free the result set
    mysqli_close($DBC); // Close the database connection
    ?>

    <h1>Edit Room Review</h1>
    <h2>
        <a href="listbookings.php">[Return to the Booking List]</a>
        <a href="index.php">[Return to Main Page]</a>
    </h2>

    <div>
        <form method="POST">
            <!-- Hidden field to pass the booking ID -->
            <div>
                <input type="hidden" name="id" value="<?php echo $id; ?>">
            </div>

            <!-- Room Review input field -->
            <div>
                <label for="room_review">Room Review:</label>
                <textarea id="roomreviews" name="roomreviews" rows="10" cols="30"></textarea><?php echo htmlspecialchars($roomreview); ?>
            </div>

            <br> <br>

            <!-- Submit button -->
            <div>
                <input type="submit" name="submit" value="Update">
            </div>
        </form>
    </div>
</body>
</html>
