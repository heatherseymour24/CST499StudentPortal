<?php
if (session_status() == PHP_SESSION_NONE) {
    ini_set('session.use_only_cookies', 1);
    session_start();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1"/>
    <title>Student Portal</title>
    <!-- Bootstrap CSS and jQuery for styling and interactivity -->
    <link href="http://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.0/jquery.min.js"></script>
    <script src="http://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/js/bootstrap.min.js"></script>
</head>
<body>
    <div class="container text-center">
        <h1>Student Portal</h1>
    </div>

    <!-- Navbar setup -->
    <nav class="navbar navbar-inverse">
        <div class="container-fluid">
            <div class="navbar-header">
                <button type="button" class="navbar-toggle" data-toggle="collapse" data-target="#myNavbar">
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>                        
                </button>
                <a class="navbar-brand" href="#">Student Portal</a>
            </div>
            <div class="collapse navbar-collapse" id="myNavbar">
                <ul class="nav navbar-nav">
                    <li class="active">
                        <!-- Dynamically change the "Home" link -->
                        <?php
                        if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true) {
                            echo '<a href="admin_dashboard.php"><span class="glyphicon glyphicon-home"></span> Home</a>';
                        } else {
                            echo '<a href="index.php"><span class="glyphicon glyphicon-home"></span> Home</a>';
                        }
                        ?>
                    </li>
                </ul>
                <ul class="nav navbar-nav navbar-right">
                    <?php
                    // Check if the user is logged in
                    if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true) {
                        echo '<li><a href="logout.php"><span class="glyphicon glyphicon-off"></span> Logout</a></li>';
                        echo '<li><a href="profile.php"><span class="glyphicon glyphicon-briefcase"></span> Profile</a></li>';
                    } else {
                        echo '<li><a href="login.php"><span class="glyphicon glyphicon-user"></span> Login</a></li>';
                        echo '<li><a href="registration.php"><span class="glyphicon glyphicon-pencil"></span> Registration</a></li>';
                    }
                    ?>
                </ul>
            </div>
        </div>
    </nav>
</body>
</html>
