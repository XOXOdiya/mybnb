<!DOCTYPE HTML>
<html>
<head>
    <title>Register New Customer</title>
</head>
<body>

<?php
// Function to clean input but not validate type and content
function cleanInput($data) {
    return htmlspecialchars(stripslashes(trim($data)));
}

// Check if the form was submitted
if (isset($_POST['submit']) && !empty($_POST['submit']) && ($_POST['submit'] == 'Register')) {
    include "config.php"; // Load in any variables
    $DBC = mysqli_connect("127.0.0.1", DBUSER, DBPASSWORD, DBDATABASE);

    if (mysqli_connect_errno()) {
        echo "Error: Unable to connect to MySQL. " . mysqli_connect_error();
        exit; // Stop processing the page further
    }

    $error = 0; // Clear our error flag
    $msg = 'Error: ';

    // Validate firstname
    if (isset($_POST['firstname']) && !empty($_POST['firstname']) && is_string($_POST['firstname'])) {
        $fn = cleanInput($_POST['firstname']);
        $firstname = (strlen($fn) > 50) ? substr($fn, 0, 50) : $fn;
    } else {
        $error++;
        $msg .= 'Invalid firstname. ';
        $firstname = '';
    }

    // Validate lastname
    if (isset($_POST['lastname']) && !empty($_POST['lastname']) && is_string($_POST['lastname'])) {
        $ln = cleanInput($_POST['lastname']);
        $lastname = (strlen($ln) > 50) ? substr($ln, 0, 50) : $ln;
    } else {
        $error++;
        $msg .= 'Invalid lastname. ';
        $lastname = '';
    }

    // Validate email
    if (isset($_POST['email']) && filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
        $email = cleanInput($_POST['email']);
    } else {
        $error++;
        $msg .= 'Invalid email. ';
        $email = '';
    }

    // Validate password
    if (isset($_POST['password']) && strlen($_POST['password']) >= 8 && strlen($_POST['password']) <= 32) {
        $password = password_hash(cleanInput($_POST['password']), PASSWORD_DEFAULT);
    } else {
        $error++;
        $msg .= 'Invalid password. ';
        $password = '';
    }

    // Save the customer data if the error flag is still clear
    if ($error == 0) {
        $query = "INSERT INTO customers (firstname, lastname, email,password) VALUES (?, ?, ?, ?)";
        $stmt = mysqli_prepare($DBC, $query);
        mysqli_stmt_bind_param($stmt, 'ssss', $firstname, $lastname, $email, $password);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        echo "<h2>Customer saved successfully!</h2>";
    } else {
        echo "<h2>$msg</h2>" . PHP_EOL;
    }

    mysqli_close($DBC); // Close the connection once done
}
?>

<h1>New Customer Registration</h1>
<h2>
    <a href='listcustomers.php'>[Return to the Customer listing]</a>
    <a href='/bnb/'>[Return to the main page]</a>
</h2>

<form method="POST" action="registercustomer.php">
    <p>
        <label for="firstname">First Name: </label>
        <input type="text" id="firstname" name="firstname" minlength="5" maxlength="50" required>
    </p>
    <p>
        <label for="lastname">Last Name: </label>
        <input type="text" id="lastname" name="lastname" minlength="5" maxlength="50" required>
    </p>
    <p>
        <label for="email">Email: </label>
        <input type="email" id="email" name="email" maxlength="100" size="50" required>
    </p>
    <p>
        <label for="password">Password: </label>
        <input type="password" id="password" name="password" minlength="8" maxlength="32" required>
    </p>
    <input type="submit" name="submit" value="Register">
</form>
</body>
</html>