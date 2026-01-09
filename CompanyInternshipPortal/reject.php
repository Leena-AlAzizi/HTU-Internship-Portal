<?php
session_start();
require '../db_connection.php'; 

if (!isset($_SESSION['company_id']) || $_SESSION['user_type'] !== 'company') {
    header("Location: ../login.php"); 
    exit();
}

if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: students.php"); 
    exit();
}

$application_id = filter_var($_GET['id'], FILTER_SANITIZE_NUMBER_INT);

$update_application_sql = "UPDATE applications SET status = 'Rejected' WHERE application_id = ?";

if ($stmt = $conn->prepare($update_application_sql)) {
    $stmt->bind_param("i", $application_id);
    $stmt->execute();
    $stmt->close();
} else {
    die("ERROR in application update query: " . $conn->error);
}

$get_student_id_sql = "SELECT student_id FROM applications WHERE application_id = ?";
$student_id = null;

if ($stmt = $conn->prepare($get_student_id_sql)) {
    $stmt->bind_param("i", $application_id);
    $stmt->execute();
    $stmt->bind_result($student_id);
    $stmt->fetch();
    $stmt->close();
} else {
    die("ERROR in fetching student_id query: " . $conn->error);
}

if ($student_id !== null) {
    $check_remaining_applications_sql = "SELECT COUNT(*) FROM applications WHERE student_id = ? AND status IN ('Pending', 'Interview')";
    $remaining_applications = 0;

    if ($stmt_check = $conn->prepare($check_remaining_applications_sql)) {
        $stmt_check->bind_param("s", $student_id);
        $stmt_check->execute();
        $stmt_check->bind_result($remaining_applications);
        $stmt_check->fetch();
        $stmt_check->close();
    } else {
        die("ERROR in checking remaining applications query: " . $conn->error);
    }
    
    if ($remaining_applications == 0) {
        $new_student_status = 'Not Found';
        $update_student_sql = "UPDATE students SET status = ? WHERE student_id = ?";

        if ($stmt = $conn->prepare($update_student_sql)) {
            $stmt->bind_param("ss", $new_student_status, $student_id);
            $stmt->execute();
            $stmt->close();
        } else {
            die("ERROR in student update query: " . $conn->error);
        }
    } 
}

header("Location: students.php?msg=rejected");
exit();
?>