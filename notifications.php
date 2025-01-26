<?php
// Include database connection file
require_once 'database.php';

class Notification {
    private $db;

    public function __construct($dbConnection) {
        $this->db = $dbConnection;
    }

    // Fetch user's notifications 
    public function getNotifications($userId) {
        try {
            $query = "SELECT * FROM tblNotifications WHERE userID = :userID ORDER BY createdAt DESC";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':userID', $userId, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            echo "Error fetching notifications: " . $e->getMessage();
            return [];
        }
    }

    // Notify the next waitlisted user
    public function notifyNextWaitlistedUser($courseID) {
        try {
            // Fetch the course name
            $courseNameQuery = "SELECT courseName FROM tblCourses WHERE courseID = :courseID";
            $courseNameStmt = $this->db->prepare($courseNameQuery);
            $courseNameStmt->bindParam(':courseID', $courseID, PDO::PARAM_INT);
            $courseNameStmt->execute();
            $courseName = $courseNameStmt->fetch(PDO::FETCH_ASSOC)['courseName'];

            // Get the next user on the waitlist
            $waitlistQuery = "
                SELECT userID 
                FROM tblWaitlist 
                WHERE courseID = :courseID 
                ORDER BY position ASC 
                LIMIT 1";
            $waitlistStmt = $this->db->prepare($waitlistQuery);
            $waitlistStmt->bindParam(':courseID', $courseID, PDO::PARAM_INT);
            $waitlistStmt->execute();

            if ($waitlistStmt->rowCount() > 0) {
                $nextWaitlistedUser = $waitlistStmt->fetch(PDO::FETCH_ASSOC)['userID'];

                // Remove the user from the waitlist
                $removeWaitlistQuery = "DELETE FROM tblWaitlist WHERE courseID = :courseID AND userID = :userID";
                $removeWaitlistStmt = $this->db->prepare($removeWaitlistQuery);
                $removeWaitlistStmt->bindParam(':courseID', $courseID, PDO::PARAM_INT);
                $removeWaitlistStmt->bindParam(':userID', $nextWaitlistedUser, PDO::PARAM_INT);
                $removeWaitlistStmt->execute();

                // Enroll the user in the course
                $enrollQuery = "INSERT INTO tblEnrollments (userID, courseID, enrollmentStatus) VALUES (:userID, :courseID, 'Enrolled')";
                $enrollStmt = $this->db->prepare($enrollQuery);
                $enrollStmt->bindParam(':userID', $nextWaitlistedUser, PDO::PARAM_INT);
                $enrollStmt->bindParam(':courseID', $courseID, PDO::PARAM_INT);
                $enrollStmt->execute();

                // Add a notification for the user
                $notificationQuery = "
                    INSERT INTO tblNotifications (userID, message, createdAt) 
                    VALUES (:userID, :message, NOW())";
                $notificationStmt = $this->db->prepare($notificationQuery);
                $notificationMessage = "A spot has opened up and you have been enrolled in: " . htmlspecialchars($courseName) . ".";
                $notificationStmt->bindParam(':userID', $nextWaitlistedUser, PDO::PARAM_INT);
                $notificationStmt->bindParam(':message', $notificationMessage, PDO::PARAM_STR);
                $notificationStmt->execute();
            }
        } catch (PDOException $e) {
            echo "Error notifying waitlisted user: " . $e->getMessage();
        }
    }
}
?>
