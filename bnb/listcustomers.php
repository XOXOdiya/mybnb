<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);
include "config.php";


// Include your database configuration
$DBC = mysqli_connect("127.0.0.1", DBUSER, DBPASSWORD, DBDATABASE);
 
    
    if (mysqli_connect_errno()) {
        echo "Error: Unable to connect to MySQL." . mysqli_connect_error();
        exit; //stop processing the page further.
    }


// Initialize variables


// If no AJAX request, fetch customers from the database for initial page load
if (!isset($_GET['sq'])) {
    // Prepare the SQL statement
    $query = "SELECT customerID, firstname, lastname FROM customers";
    $result = mysqli_query($DBC, $query);  // Use mysqli_query for normal query execution
    $rowcount = mysqli_num_rows($result);  // Get the number of rows

    if ($rowcount > 0) {
        while ($row = mysqli_fetch_assoc($result)) {
            $customers[] = $row; // Add each customer to the array
        }
    }

    mysqli_free_result($result); // Free result memory
    mysqli_close($DBC); // Close the DB connection
} 

// Handle AJAX request to search customers by last name
if (isset($_GET['sq'])) {
    $searchstr = cleanInput($_GET['sq']); // Clean the input
    $DBC = mysqli_connect("127.0.0.1", DBUSER, DBPASSWORD, DBDATABASE);

    // Check database connection
    if (mysqli_connect_errno()) {
        echo json_encode(["error" => "Database connection failed: " . mysqli_connect_error()]);
        exit;
    }

    // Prepare the SQL statement with a LIKE clause for partial matching
    $query = "SELECT customerID, firstname, lastname FROM customers ";
    $stmt = mysqli_prepare($DBC, $query);
    $likeSearchStr = "%" . $searchstr . "%"; // Use LIKE for partial matches
    mysqli_stmt_bind_param($stmt, 's', $likeSearchStr);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    $customers = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $customers[] = $row; // Add each customer to the array
    }

    mysqli_stmt_close($stmt);
    mysqli_close($DBC);

    // Return the JSON-encoded array of customers or error if no result
    if (empty($customers)) {
        echo json_encode(["message" => "No customers found."]);
    } else {
        echo json_encode($customers);
    }

    exit; // Stop further execution for AJAX
}
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
<h2><a href="registercustomer.php">[Make a Booking]</a> | <a href="index.php">[Return to Main Page]</a></h2>

<table border="1">
    <thead>
        <tr>
            <th>Customer (firstname, lastname)</th>
            <th>Action</th>
        </tr>
    </thead>
    <tbody>
        <?php
        if ($rowcount > 0) {
            foreach ($customers as $row) {
                $id = $row['customerID'];
                echo '<tr>';
                echo '<td>' . $row['firstname'] . ' ' . $row['lastname'] . '</td>';
                echo '<td><a href="viewcustomer.php?id=' . $id . '">[view]</a>';
                echo '<a href="editcustomer.php?id=' . $id . '">[edit]</a>';
                echo '<a href="deletecustomer.php?id=' . $id . '">[delete]</a>';
                echo '</td></tr>' . PHP_EOL;
            }
        } else {
            echo "<tr><td colspan='3'><h2>No bookings found!</h2></td></tr>";
        }
        ?>
    </tbody>
</table>

</body>
</html>
