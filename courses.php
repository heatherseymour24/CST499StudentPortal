<?php
// Include database connection file
require_once 'database.php';

// Start session to identify the user
session_start();

// Initialize database object
$db = new Database();
$con = $db->getConnection();

// Fetch all courses with instructor details, semester, and enrollment info
$coursesQuery = "
    SELECT c.courseID, c.courseName, c.courseDescription, c.semester, 
           IFNULL(COUNT(e.enrollmentID), 0) AS currentEnrollment, c.maxEnrollment, 
           CONCAT(i.firstName, ' ', i.lastName) AS instructorName
    FROM tblCourses c
    LEFT JOIN tblInstructors i ON c.instructorID = i.instructorID
	LEFT JOIN 
        tblEnrollments e ON c.courseID = e.courseID AND e.enrollmentStatus = 'Enrolled'
    GROUP BY 
        c.courseID, c.courseName, c.courseDescription, c.semester, c.maxEnrollment, i.firstName, i.lastName
";

$coursesStmt = $con->prepare($coursesQuery);
$coursesStmt->execute();
$courses = $coursesStmt->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $courseID = $_POST['course_id'];
    $userID = $_SESSION['user_id'];
    
    if (isset($_POST['enroll'])) {
        // Enroll the user in the course
        $enrollQuery = "INSERT INTO tblEnrollments (userID, courseID, enrollmentStatus) 
                        VALUES (:userID, :courseID, 'Enrolled')";
        $stmt = $con->prepare($enrollQuery);
        $stmt->bindParam(':userID', $userID, PDO::PARAM_INT);
        $stmt->bindParam(':courseID', $courseID, PDO::PARAM_INT);
        $stmt->execute();

        $_SESSION['message'] = "You have successfully enrolled in the course.";
    } elseif (isset($_POST['waitlist'])) {
        // Add the user to the waitlist
        $waitlistQuery = "INSERT INTO tblWaitlist (userID, courseID) 
                          VALUES (:userID, :courseID)";
        $stmt = $con->prepare($waitlistQuery);
        $stmt->bindParam(':userID', $userID, PDO::PARAM_INT);
        $stmt->bindParam(':courseID', $courseID, PDO::PARAM_INT);
        $stmt->execute();

        $_SESSION['message'] = "You have been added to the waitlist.";
    }

    // Redirect to avoid resubmission
    header("Location: courses.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View & Register for Courses</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <?php include 'header.php'; ?>

    <div class="container mt-4">
        <h2>View & Register for Courses</h2>
        
        <!-- Display success message -->
        <?php if (isset($_SESSION['message'])): ?>
            <div class="alert alert-success">
                <?php 
                echo htmlspecialchars($_SESSION['message']); 
                unset($_SESSION['message']); // Clear message after displaying
                ?>
            </div>
        <?php endif; ?>

        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Course Name</th>
                    <th>Description</th>
                    <th>Instructor</th>
                    <th>Semester</th>
                    <th>Students Enrolled</th>
                    <th>Max Enrollment</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($courses as $course): ?>
                <tr>
                    <td><?php echo htmlspecialchars($course['courseName']); ?></td>
                    <td><?php echo htmlspecialchars($course['courseDescription']); ?></td>
                    <td><?php echo htmlspecialchars($course['instructorName'] ?: 'N/A'); ?></td>
                    <td><?php echo htmlspecialchars($course['semester']); ?></td>
                    <td><?php echo htmlspecialchars($course['currentEnrollment']); ?></td>
                    <td><?php echo htmlspecialchars($course['maxEnrollment']); ?></td>
                    <td>
                        <form action="courses.php" method="POST" style="display: inline;">
                            <input type="hidden" name="course_id" value="<?php echo htmlspecialchars($course['courseID']); ?>">
                            <?php if ($course['currentEnrollment'] < $course['maxEnrollment']): ?>
                                <button type="submit" name="enroll" class="btn btn-primary btn-sm">Enroll</button>
                            <?php else: ?>
                                <button type="submit" name="waitlist" class="btn btn-warning btn-sm">Waitlist</button>
                            <?php endif; ?>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <?php include 'footer.php'; ?>
</body>
</html>
