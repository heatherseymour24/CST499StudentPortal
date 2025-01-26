<?php
require_once 'Database.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Collect form data
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT); // Hash the password
    $firstName = $_POST['firstName'];
    $lastName = $_POST['lastName'];
    $address = $_POST['address'];
    $phone = $_POST['phone'];

    // Create a new instance of the Database class
    $db = new Database();
    $con = $db->getConnection();

    // Insert the data into the database
    $sql = "INSERT INTO tblUser (email, password, firstName, lastName, address, phone) 
            VALUES (:email, :password, :firstName, :lastName, :address, :phone)";
    $stmt = $con->prepare($sql);
    $stmt->bindParam(':email', $email);
    $stmt->bindParam(':password', $password);
    $stmt->bindParam(':firstName', $firstName);
    $stmt->bindParam(':lastName', $lastName);
    $stmt->bindParam(':address', $address);
    $stmt->bindParam(':phone', $phone);

    if ($stmt->execute()) {
        echo "<div class='alert alert-success'>Registration successful!</div>";
    } else {
        echo "<div class='alert alert-danger'>Registration failed. Please try again.</div>";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Portal Registration</title>
    <!-- Bootstrap CSS -->
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <?php include 'header.php'; ?>
	<div class="container">
        <h2>Register for Student Portal</h2>
        <form action="registration.php" method="POST">
            <div class="form-group">
                <label for="email">Email:</label>
                <input type="email" class="form-control" id="email" name="email" required>
            </div>
            <div class="form-group">
                <label for="password">Password:</label>
                <input type="password" class="form-control" id="password" name="password" required>
            </div>
            <div class="form-group">
                <label for="firstName">First Name:</label>
                <input type="text" class="form-control" id="firstName" name="firstName" required>
            </div>
            <div class="form-group">
                <label for="lastName">Last Name:</label>
                <input type="text" class="form-control" id="lastName" name="lastName" required>
            </div>
            <div class="form-group">
                <label for="address">Address:</label>
                <input type="text" class="form-control" id="address" name="address" required>
            </div>
            <div class="form-group">
                <label for="phone">Phone:</label>
                <input type="text" class="form-control" id="phone" name="phone" required>
            </div>
            <button type="submit" class="btn btn-primary">Register</button>
        </form>
    </div>
	<?php include 'footer.php'; ?>
</body>
</html>


