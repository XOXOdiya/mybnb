<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
</head>

<body>
<?php
include "checksession.php";

// Simple logout
if (isset($_POST['logout'])) {
    logout();
}

if (isset($_POST['login']) and !empty($_POST['login']) and ($_POST['login'] == 'Login')) {
    include "config.php"; // Load database configuration

    $DBC = mysqli_connect("127.0.0.1", DBUSER, DBPASSWORD, DBDATABASE) or die();

    // Validate incoming data
    $error = 0;
    $msg = 'Error: ';
    

    if (isset($_POST['username']) and !empty($_POST['username']) and is_string($_POST['username'])) {
        $username = htmlspecialchars(stripslashes(trim($_POST['username'])));
    } else {
        $error++;
        $msg .= 'Invalid username ';
        $username = '';
    }

    $password = trim($_POST['password']);

    if ($error == 0) {
        // Using prepared statement to avoid SQL injection
        $query = "SELECT customerID, firstname, lastname, password FROM customers WHERE email = ?";

        if ($stmt = $DBC->prepare($query)) {
            $stmt->bind_param('s', $username);
            $stmt->execute();
            $stmt->store_result();

            if ($stmt->num_rows == 1) {
                $stmt->bind_result($customerID, $firstname, $lastname, $hashed_password);
                $stmt->fetch();

                // Verify password using password_verify() if passwords are hashed
                if (password_verify($password, $hashed_password)) {
                    // Login successful, set session
                    login($customerID, $username, $firstname, $lastname);
                } else {
                    echo "<h6>Login failed. Invalid username or password.</h6>";
                }
            } else {
                echo "<h6>Login failed. User not found.</h6>";
            }

            $stmt->close();
        } else {
            echo "<h6>Database error: Unable to prepare statement.</h6>";
        }
    } else {
        echo "<h6>$msg</h6>";
    }

    mysqli_close($DBC);
}
?>

    <h1>Login</h1>
    <h2>
        <a href="registercustomer.php">[Create new customer]</a>
        <a href="index.php">[Return to main page]</a>
    </h2>

    <form method="POST" action="index.php"> <!-- Ensure action points to itself -->
        <p>
            <label for="username">Username:</label>
            <input type="text" id="username" name="username" maxlength="100" autocomplete="off">
        </p>

        <p>
            <label for="password">Password:</label>
            <input type="password" id="password" name="password" maxlength="32" autocomplete="off">
        </p>

        <input type="submit" name="login" value="Login">
        <input type="submit" name="logout" value="Logout">
    </form>

</body>

</html>
