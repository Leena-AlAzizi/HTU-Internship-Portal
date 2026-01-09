<?php
session_start();
require '../db_connection.php'; 

if (!isset($_SESSION['student_id'])) {
    echo "Unauthorized access.";
    exit();
}

$student_id = $_SESSION['student_id'];

if (isset($_FILES['cv_file']) && $_FILES['cv_file']['error'] === 0) {
    $targetDir = "../uploads/cv/"; 
    if (!is_dir($targetDir)) {
        mkdir($targetDir, 0777, true);
    }

    $cvFileName = uniqid() . "_" . basename($_FILES["cv_file"]["name"]);
    $cvFilePath = $targetDir . $cvFileName;
    $dbPath = "uploads/cv/" . $cvFileName; 

    if (move_uploaded_file($_FILES["cv_file"]["tmp_name"], $cvFilePath)) {
        $stmt = $conn->prepare("UPDATE students SET cv_file = ? WHERE student_id = ?");
        $stmt->bind_param("ss", $dbPath, $student_id);
        
        if ($stmt->execute()) {
            echo "success:../" . $dbPath; 
        } else {
            echo "Database update error: " . $stmt->error;
        }
        $stmt->close();
    } else {
        echo "Failed to move uploaded file.";
    }
} else {
    echo "No file uploaded or upload error.";
}