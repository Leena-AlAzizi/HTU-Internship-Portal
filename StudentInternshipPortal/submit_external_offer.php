<?php
session_start();
require '../db_connection.php';

if (!isset($_SESSION['student_id']) || $_SESSION['user_type'] !== 'student') {
    header("Location: ../login.php");
    exit();
}

$student_id = $_SESSION['student_id'];

if (
    empty($_POST['position_title']) ||
    empty($_POST['company_name']) ||
    empty($_POST['location']) ||
    empty($_POST['duration']) ||
    empty($_POST['offer_description']) ||
    empty($_POST['start_date'])
) {
    die("Missing required fields.");
}

$position_title = $_POST['position_title'];
$company_name = $_POST['company_name'];
$location = $_POST['location'];
$duration = $_POST['duration'];
$offer_description = $_POST['offer_description'];
$start_date = $_POST['start_date'];

$document_name = null;

if (!empty($_FILES['document']['name'])) {

    $allowed_types = ['pdf', 'jpg', 'jpeg', 'png'];
    $file_ext = strtolower(pathinfo($_FILES['document']['name'], PATHINFO_EXTENSION));

    if (!in_array($file_ext, $allowed_types)) {
        die("Invalid file type. Allowed: PDF, JPG, PNG.");
    }

    $upload_dir = "../uploads/external_documents/";

    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }

    $document_name = uniqid("external_") . "." . $file_ext;
    $upload_path = $upload_dir . $document_name;

    if (!move_uploaded_file($_FILES['document']['tmp_name'], $upload_path)) {
        die("File upload failed.");
    }
    
    $document_db_path = "uploads/external_documents/" . $document_name;
} else {
    $document_db_path = null;
}

$sql = "INSERT INTO external_offers 
         (student_id, company_name, position_title, offer_description, start_date, status, document)
         VALUES (?, ?, ?, ?, ?, 'Pending', ?)";

$stmt = $conn->prepare($sql);
$stmt->bind_param("ssssss", $student_id, $company_name, $position_title, $offer_description, $start_date, $document_db_path);

if ($stmt->execute()) {
    header("Location: offers.php?success=1");
    exit();
} else {
    echo "Database error: " . $stmt->error;
}

$stmt->close();
$conn->close();
?>