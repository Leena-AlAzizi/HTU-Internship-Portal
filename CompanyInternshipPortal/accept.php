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

$update_current_application_sql = "UPDATE applications SET status = 'Accepted' WHERE application_id = ?";

if ($stmt = $conn->prepare($update_current_application_sql)) {
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
    $new_student_status = 'Found Opportunity';
    $update_student_sql = "UPDATE students SET status = ? WHERE student_id = ?";

    if ($stmt = $conn->prepare($update_student_sql)) {
        $stmt->bind_param("ss", $new_student_status, $student_id);
        $stmt->execute();
        $stmt->close();
    } else {
        die("ERROR in student update query: " . $conn->error);
    }

    $reject_other_apps_sql = "UPDATE applications SET status = 'Rejected' WHERE student_id = ? AND application_id != ?";

    if ($stmt_reject = $conn->prepare($reject_other_apps_sql)) {
        $stmt_reject->bind_param("si", $student_id, $application_id);
        $stmt_reject->execute();
        $stmt_reject->close();
    } else {
        die("ERROR in rejecting other applications query: " . $conn->error);
    }
}

header("Location: students.php?msg=accepted"); 
exit();
?>