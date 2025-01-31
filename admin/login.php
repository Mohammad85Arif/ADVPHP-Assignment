<?php
// Start the session
session_start();

// Include database connection
include('../config/db.php');

// Define variables and initialize with empty values
$email = $password = "";
$email_err = $password_err = $login_err = "";

// Process the form data when form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Check if email is empty
    if (empty(trim($_POST["email"]))) {
        $email_err = "Please enter email.";
    } else {
        $email = trim($_POST["email"]);
    }

    // Check if password is empty
    if (empty(trim($_POST["password"]))) {
        $password_err = "Please enter your password.";
    } else {
        $password = trim($_POST["password"]);
    }

    // If no errors, proceed with checking credentials
    if (empty($email_err) && empty($password_err)) {

        // Prepare a SQL query to get user data
        $sql = "SELECT id, email, password FROM users WHERE email = :email AND role = 'admin'";

        if ($stmt = $conn->prepare($sql)) {
            // Bind email parameter
            $stmt->bindParam(":email", $email, PDO::PARAM_STR);

            // Execute the query
            if ($stmt->execute()) {
                // Check if the email exists
                if ($stmt->rowCount() == 1) {
                    if ($row = $stmt->fetch()) {
                        $id = $row['id'];
                        $hashed_password = $row['password'];

                        // Verify the password using password_verify
                        if (password_verify($password, $hashed_password)) {
                            // Password is correct, start a new session
                            session_start();

                            // Store session variables
                            $_SESSION["loggedin"] = true;
                            $_SESSION["id"] = $id;
                            $_SESSION["email"] = $email;

                            // Redirect to admin dashboard
                            header("location: dashboard.php");
                        } else {
                            // Display an error message if password is incorrect
                            $login_err = "The password you entered is incorrect.";
                        }
                    }
                } else {
                    // Display an error message if email doesn't exist
                    $login_err = "No account found with that email address.";
                }
            } else {
                echo "Something went wrong. Please try again later.";
            }
            // Close the statement
            unset($stmt);
        }
    }

    // Close the database connection
    unset($conn);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login</title>
    <link rel="stylesheet" href="../public/css/styles.css">
</head>
<body>
    <div class="login-container">
        <h2>Admin Login</h2>
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
            <div class="form-group">
                <label for="email">Email:</label>
                <input type="text" id="email" name="email" value="<?php echo $email; ?>" class="form-control" required>
                <span class="error"><?php echo $email_err; ?></span>
            </div>    
            <div class="form-group">
                <label for="password">Password:</label>
                <input type="password" id="password" name="password" class="form-control" required>
                <span class="error"><?php echo $password_err; ?></span>
            </div>
            <div class="form-group">
                <button type="submit" class="btn">Login</button>
            </div>
            <span class="error"><?php echo $login_err; ?></span>
        </form>
        <p>Don't have an account? <a href="register.php">Register here</a>.</p>
    </div>
</body>
</html>
