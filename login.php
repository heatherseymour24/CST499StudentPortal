<?php 

if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true) {
    header("Location: admin_dashboard.php");
    exit;
}
require_once 'database.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <?php include 'header.php'; ?>
    <div class="container mt-5">
        <h2>Login</h2>
        <form action="login.php" method="post">
            <div class="form-group">
                <label for="username">Username:</label>
                <input type="text" class="form-control" id="username" name="username" required>
            </div>
            <div class="form-group">
                <label for="password">Password:</label>
                <input type="password" class="form-control" id="password" name="password" required>
            </div>
            <button type="submit" class="btn btn-primary">Log In</button>
        </form>
    </div>

    <?php
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $username = htmlspecialchars($_POST['username']);
        $password = htmlspecialchars($_POST['password']);

        $db = new Database();
        $con = $db->getConnection();

        $sql = "SELECT * FROM tblUser WHERE email = :username";
        $stmt = $con->prepare($sql);
        $stmt->bindParam(':username', $username, PDO::PARAM_STR);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['firstName'] . " " . $user['lastName'];
            $_SESSION['logged_in'] = true;

            header("Location: admin_dashboard.php");
            exit;
        } else {
            echo "<div class='alert alert-danger mt-3'>Invalid username or password. Please try again.</div>";
        }
    }
    ?>
    <?php include 'footer.php'; ?>
</body>
</html>
