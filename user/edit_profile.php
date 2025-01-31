<?php
session_start();
include '../config/db.php';

// Redirect if user is not logged in
if (!isset($_SESSION["user_id"])) {
    header("Location: ../user/login.php");
    exit();
}

$user_id = $_SESSION["user_id"];

// Fetch user data
$stmt = $conn->prepare("SELECT name, email FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// Update user data
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST["name"];
    $email = $_POST["email"];
    $password = $_POST["password"];
    $confirm_password = $_POST["confirm_password"];
    
    // Check if password fields match
    if (!empty($password) && $password != $confirm_password) {
        $_SESSION["message"] = "Passwords do not match!";
        header("Location: edit_profile.php");
        exit();
    }

    // Update user information
    try {
        $update_query = "UPDATE users SET name = ?, email = ?";
        $params = [$name, $email];

        // If password is entered, hash it and update the password field
        if (!empty($password)) {
            $password_hash = password_hash($password, PASSWORD_BCRYPT);
            $update_query .= ", password = ?";
            $params[] = $password_hash;
        }

        $update_query .= " WHERE id = ?";
        $params[] = $user_id;

        $stmt = $conn->prepare($update_query);
        $stmt->execute($params);

        $_SESSION["message"] = "Profile updated successfully!";
        header("Location: edit_profile.php");
        exit();
    } catch (PDOException $e) {
        $_SESSION["message"] = "Error updating profile: " . $e->getMessage();
        header("Location: edit_profile.php");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Profile</title>
    <link rel="stylesheet" href="../public/css/styles.css">
</head>
<body>

<?php include '../includes/header.php'; ?>

<div class="container">
    <h2>Edit Your Profile</h2>

    <?php if (!empty($_SESSION["message"])): ?>
        <p class="message"><?php echo $_SESSION["message"]; unset($_SESSION["message"]); ?></p>
    <?php endif; ?>

    <form method="POST" action="edit_profile.php">
        <div class="form-group">
            <label for="name">Name:</label>
            <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($user['name']); ?>" required>
        </div>

        <div class="form-group">
            <label for="email">Email:</label>
            <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
        </div>

        <div class="form-group">
            <label for="password">New Password (Leave blank to keep current password):</label>
            <input type="password" id="password" name="password">
        </div>

        <div class="form-group">
            <label for="confirm_password">Confirm New Password:</label>
            <input type="password" id="confirm_password" name="confirm_password">
        </div>

        <button type="submit" class="btn-submit">Update Profile</button>
    </form>

    <a href="index.php" class="btn-back">Back to Dashboard</a>
</div>

<?php include '../includes/footer.php'; ?>

</body>
</html>
