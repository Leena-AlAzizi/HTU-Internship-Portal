<?php
session_start();
require '../db_connection.php'; 

if (!isset($_SESSION['company_id']) || $_SESSION['user_type'] !== 'company') {
    header("Location: ../login.php"); 
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['student_id']) && isset($_POST['offer_id'])) {
    
    $student_id = filter_var($_POST['student_id'], FILTER_SANITIZE_STRING);
    $offer_id = filter_var($_POST['offer_id'], FILTER_SANITIZE_NUMBER_INT);
    $application_status = 'Pending';
    $student_new_status = 'Interviewing'; 

    if (empty($offer_id) || $offer_id == 'Select') {
        header("Location: students.php?error=invalid_offer"); 
        exit();
    }

    $insert_app_sql = "INSERT INTO applications (student_id, offer_id, status) VALUES (?, ?, ?)";

    if ($stmt_app = $conn->prepare($insert_app_sql)) {
        $stmt_app->bind_param("sis", $student_id, $offer_id, $application_status);
        $stmt_app->execute();
        $stmt_app->close();
    } else {
        die("ERROR: " . $conn->error);
    }

    $update_student_sql = "UPDATE students SET status = ? WHERE student_id = ?";

    if ($stmt_student = $conn->prepare($update_student_sql)) {
        $stmt_student->bind_param("ss", $student_new_status, $student_id);
        $stmt_student->execute();
        $stmt_student->close();
    } else {
        die("ERROR: " . $conn->error);
    }

    header("Location: students.php?msg=offer_sent");
    exit();

} else {
    header("Location: students.php?error=missing_data");
    exit();
}