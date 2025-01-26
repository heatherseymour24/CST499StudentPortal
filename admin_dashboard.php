<?php
// Enable error reporting for debugging 
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Include database connection & notification files
require_once 'database.php';
require_once 'notifications.php';

// Start session to get user ID
session_start();

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php'); // Redirect to login page if not logged in
    exit;
}

// Initialize database object
$db = new Database();
$con = $db->getConnection();

// Check database connection
if (!$con) {
    die('Database connection failed.');
}

// Initialize the Notification class
$notification = new Notification($con);

// Fetch latest notifications 
$userNotifications = $notification->getNotifications($_SESSION['user_id']);

// Fetch courses for the logged-in user, including enrollment and waitlist
$userId = $_SESSION['user_id'];

$coursesQuery = "
    SELECT c.courseID, c.courseName, c.courseDescription, c.semester,
           CASE 
               WHEN e.enrollmentStatus = 'Enrolled' THEN 'Enrolled'
               WHEN w.userID IS NOT NULL THEN 'Waitlisted'
               ELSE 'Not Enrolled'
           END AS enrollmentStatus
    FROM tblCourses c
    LEFT JOIN tblEnrollments e ON c.courseID = e.courseID AND e.userID = :userId
    LEFT JOIN tblWaitlist w ON c.courseID = w.courseID AND w.userID = :userId
    WHERE e.userID = :userId OR w.userID = :userId
    GROUP BY c.courseID, c.courseName, c.courseDescription, c.semester, enrollmentStatus";

$coursesStmt = $con->prepare($coursesQuery);
$coursesStmt->bindParam(':userId', $userId, PDO::PARAM_INT);
$coursesStmt->execute();
$courses = $coursesStmt->fetchAll(PDO::FETCH_ASSOC);
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <?php include 'header.php'; ?>

    <div class="container mt-4">
	
	    <div class="container mt-4">
        <!-- Display success message -->
        <?php if (isset($_SESSION['successMessage'])): ?>
            <div class="alert alert-success">
                <?php 
                    echo htmlspecialchars($_SESSION['successMessage']);
                    unset($_SESSION['successMessage']); // Clear the message after displaying
                ?>
            </div>
        <?php endif; ?>
	
        <h2>Dashboard</h2>
		<?php 
			if (isset($_SESSION['successMessage'])) {
				echo '<div class="alert alert-success">' . htmlspecialchars($_SESSION['successMessage']) . '</div>';
				unset($_SESSION['successMessage']); // Clear the message after displaying
			}

			if (isset($_SESSION['errorMessage'])) {
				echo '<div class="alert alert-danger">' . htmlspecialchars($_SESSION['errorMessage']) . '</div>';
				unset($_SESSION['errorMessage']); // Clear the message after displaying
			} 
		?>

        <!-- Notifications Section -->
        <div class="mt-4">
            <h3>Recent Notifications</h3>
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Message</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($userNotifications as $userNotification): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($userNotification['message']); ?></td>
                        <td><?php echo htmlspecialchars($userNotification['createdAt']); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- My Courses Section -->
        <div class="mt-4">
            <h3>My Courses</h3>
			
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Course Name</th>
                        <th>Description</th>
                        <th>Semester</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($courses as $course): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($course['courseName']); ?></td>
                        <td><?php echo htmlspecialchars($course['courseDescription']); ?></td>
                        <td><?php echo htmlspecialchars($course['semester']); ?></td>
                        <td><?php echo htmlspecialchars($course['enrollmentStatus']); ?></td>
                        <td>
                            <?php if (in_array($course['enrollmentStatus'], ['Waitlisted', 'Enrolled'])): ?>
                                <!-- Delete button for waitlisted/enrolled courses -->
                                <form action="delete.php" method="POST" style="display: inline;">
                                    <input type="hidden" name="course_id" value="<?php echo htmlspecialchars($course['courseID']); ?>">
                                    <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                                </form>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <!-- View/Register for Courses Button -->
            <div class="mt-4">
                <a href="courses.php" class="btn btn-primary btn-lg">View/Register for Courses</a>
            </div>
        </div>
    </div>

    <?php include 'footer.php'; ?>
</body>
</html>
