<?php

// Include database connection file
require_once 'database.php';

// Initialize database object
$db = new Database();
$con = $db->getConnection();

$userID = 1; 

// Fetch user profile data
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $sql = "SELECT firstName, lastName, email, phone, address FROM tblUser WHERE id = :userID";
    $stmt = $con->prepare($sql);
    $stmt->bindParam(':userID', $userID, PDO::PARAM_INT);
    $stmt->execute();
    $profile = $stmt->fetch(PDO::FETCH_ASSOC);
}

// Update profile data
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $firstName = $_POST['firstName'];
    $lastName = $_POST['lastName'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $address = $_POST['address'];

    $sql = "UPDATE tblUser 
            SET firstName = :firstName, lastName = :lastName, email = :email, phone = :phone, address = :address 
            WHERE id = :userID";
    $stmt = $con->prepare($sql);
    $stmt->bindParam(':firstName', $firstName);
    $stmt->bindParam(':lastName', $lastName);
    $stmt->bindParam(':email', $email);
    $stmt->bindParam(':phone', $phone);
    $stmt->bindParam(':address', $address);
    $stmt->bindParam(':userID', $userID);

    if ($stmt->execute()) {
        $message = "Profile updated successfully!";
    } else {
        $message = "Failed to update profile.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Profile</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <?php include 'header.php'; ?>

    <div class="container mt-4">
        <h2>Your Profile</h2>
        <?php if (isset($message)): ?>
        <div class="alert alert-info"><?php echo $message; ?></div>
        <?php endif; ?>
        <form action="profile.php" method="POST">
            <div class="form-group">
                <label for="firstName">First Name:</label>
                <input type="text" class="form-control" id="firstName" name="firstName" value="<?php echo htmlspecialchars($profile['firstName']); ?>" required>
            </div>
            <div class="form-group">
                <label for="lastName">Last Name:</label>
                <input type="text" class="form-control" id="lastName" name="lastName" value="<?php echo htmlspecialchars($profile['lastName']); ?>" required>
            </div>
            <div class="form-group">
                <label for="email">Email:</label>
                <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($profile['email']); ?>" required>
            </div>
            <div class="form-group">
                <label for="phone">Phone:</label>
                <input type="text" class="form-control" id="phone" name="phone" value="<?php echo htmlspecialchars($profile['phone']); ?>" required>
            </div>
            <div class="form-group">
                <label for="address">Address:</label>
                <input type="text" class="form-control" id="address" name="address" value="<?php echo htmlspecialchars($profile['address']); ?>" required>
            </div>
            <button type="submit" class="btn btn-primary">Update Profile</button>
        </form>
    </div>

    <?php include 'footer.php'; ?>
</body>
</html>
