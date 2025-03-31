<?php
// Database connection settings
$servername = "127.0.0.1:3306";
$username = "root";
$password = "";
$dbname = "bnb";

// Get the from date and to date from the query string
if (!isset($_GET['fromDate']) || !isset($_GET['toDate'])) {
    echo "Please provide both check-in and check-out dates.";
    exit;
}

$fromDate = $_GET['fromDate'];
$toDate = $_GET['toDate'];

// Ensure the dates are in the correct format (optional but good practice)
if (!preg_match("/\d{4}-\d{2}-\d{2}/", $fromDate) || !preg_match("/\d{4}-\d{2}-\d{2}/", $toDate)) {
    echo "Invalid date format. Please use yyyy-mm-dd.";
    exit;
}

// Create a new database connection
$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Prepare the SQL query to search for available rooms
$sql = "SELECT * FROM room WHERE roomID NOT IN (
            SELECT roomID
            FROM booking
            WHERE checkin_date <= ? AND checkout_date >= ?
        )";

// Prepare and bind the parameters for the SQL statement
$stmt = $conn->prepare($sql);
$stmt->bind_param("ss", $toDate, $fromDate); // Swap parameters for correct SQL logic
$stmt->execute();

// Get the result of the query
$result = $stmt->get_result();

// Debugging: log the query execution
if ($result === false) {
    echo "Error executing the query: " . $stmt->error;
    exit;
}

// Build HTML for displaying results
if ($result->num_rows > 0) {
    echo "<table border='1'>
            <tr>
                <th>Room ID</th>
                <th>Room Name</th>
                <th>Room Type</th>
                <th>Beds</th>
            </tr>";
    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $row['roomID'] . "</td>";
        echo "<td>" . htmlspecialchars($row['roomname']) . "</td>";
        echo "<td>" . htmlspecialchars($row['roomtype']) . "</td>";
        echo "<td>" . $row['beds'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "No available rooms found.";
}

// Close the prepared statement and the database connection
$stmt->close();
$conn->close();
?>
