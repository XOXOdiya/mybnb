<?php
// Include configuration to load DB connection variables
include "config.php"; // load in any variables

// Establish the database connection
$DBC = mysqli_connect("127.0.0.1", DBUSER, DBPASSWORD, DBDATABASE) or die("Connection failed: " . mysqli_connect_error());

// Get the search query parameter from the URL (sq)
$sq = isset($_GET['sq']) ? trim($_GET['sq']) : '';

// Initialize the variable for storing the search result
$searchresult = '';

// Validate the search string (check if it's not empty, less than 31 characters, and sanitize input)
if (!empty($sq) && strlen($sq) < 31) {
    // Convert the string to lowercase for case-insensitive search
    $sq = strtolower($sq);

    // Prepare a parameterized query to prevent SQL injection
    $query = "SELECT CustomerID, firstname, lastname FROM customer WHERE lastname LIKE CONCAT(?, '%') ORDER BY lastname";

    // Prepare the statement
    if ($stmt = mysqli_prepare($DBC, $query)) {
        // Bind the parameter (use "s" for string)
        mysqli_stmt_bind_param($stmt, "s", $sq);
        
        // Execute the statement
        mysqli_stmt_execute($stmt);
        
        // Store the result
        mysqli_stmt_store_result($stmt);
        
        // Check if any customers were found
        $rowcount = mysqli_stmt_num_rows($stmt);
        
        if ($rowcount > 0) {
            // Fetch the results into an associative array
            mysqli_stmt_bind_result($stmt, $CustomerID, $firstname, $lastname);
            $rows = [];  // Empty array to store the results
            
            // Loop through each row and append it to the $rows array
            while (mysqli_stmt_fetch($stmt)) {
                $rows[] = [
                    'customerID' => $customerID,
                    'firstname' => $firstname,
                    'lastname' => $lastname
                ];
            }
            
            // Encode the result into JSON format
            $searchresult = json_encode($rows);
            
            // Set the content type header for JSON response
            header('Content-Type: application/json; charset=utf-8');
        } else {
            // No customers found
            $searchresult = json_encode(["message" => "No Customers found!"]);
        }
        
        // Close the prepared statement
        mysqli_stmt_close($stmt);
    } else {
        // Error preparing the statement
        $searchresult = json_encode(["error" => "Error in the query preparation"]);
    }
} else {
    // Invalid search query or empty input
    $searchresult = json_encode(["error" => "Invalid search query"]);
}

// Close the database connection
mysqli_close($DBC);

// Output the result (JSON)
echo $searchresult;
?>
