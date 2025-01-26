<?php
// Include database connection & notifications file
require_once 'database.php';
require_once 'notifications.php';

// Start session to identify the user
session_start();

// Initialize database connection
$db = new Database();
$con = $db->getConnection();

// Check if the connection was successful
if (!$con) {
    die("Database connection failed.");
}

// Initialize the Notification class
$notification = new Notification($con);

// Check if the request is valid
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['course_id'])) {
    try {

        $courseID = $_POST['course_id'];
        $userID = $_SESSION['user_id'];

        // Check if the user is enrolled
        $checkEnrollmentQuery = "SELECT enrollmentID FROM tblEnrollments WHERE courseID = :courseID AND userID = :userID";
        $checkEnrollmentStmt = $con->prepare($checkEnrollmentQuery);
        $checkEnrollmentStmt->bindParam(':courseID', $courseID, PDO::PARAM_INT);
        $checkEnrollmentStmt->bindParam(':userID', $userID, PDO::PARAM_INT);
        $checkEnrollmentStmt->execute();

        if ($checkEnrollmentStmt->rowCount() > 0) {
            // Delete enrollment record
            $deleteEnrollmentQuery = "DELETE FROM tblEnrollments WHERE courseID = :courseID AND userID = :userID";
            $deleteEnrollmentStmt = $con->prepare($deleteEnrollmentQuery);
            $deleteEnrollmentStmt->bindParam(':courseID', $courseID, PDO::PARAM_INT);
            $deleteEnrollmentStmt->bindParam(':userID', $userID, PDO::PARAM_INT);
            $deleteEnrollmentStmt->execute();

            $_SESSION['successMessage'] = 'You have successfully unenrolled from the course.';

			// Handle waitlist logic
            $notification->notifyNextWaitlistedUser($courseID);

        } else {
            // Delete waitlist record
            $deleteWaitlistQuery = "DELETE FROM tblWaitlist WHERE courseID = :courseID AND userID = :userID";
            $deleteWaitlistStmt = $con->prepare($deleteWaitlistQuery);
            $deleteWaitlistStmt->bindParam(':courseID', $courseID, PDO::PARAM_INT);
            $deleteWaitlistStmt->bindParam(':userID', $userID, PDO::PARAM_INT);
            $deleteWaitlistStmt->execute();

            $_SESSION['successMessage'] = 'You have been removed from the waitlist for the course.';
        }
    } catch (PDOException $e) {
        // Display error message
        echo "Error: " . $e->getMessage();
    }

	// Notify the next waitlisted user
	$notification->notifyNextWaitlistedUser($courseID);

    // Redirect back to admin dashboard
    header("Location: admin_dashboard.php");
    exit;
}

?>
