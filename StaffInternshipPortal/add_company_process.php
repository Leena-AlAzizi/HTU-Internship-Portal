<?php
session_start();
require '../db_connection.php';

if (!isset($_SESSION['staff_id']) || $_SESSION['user_type'] !== 'staff') {
    header("Location: ../login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_company'])) {
    
    $company_name = mysqli_real_escape_string($conn, $_POST['company_name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $phone_number = mysqli_real_escape_string($conn, $_POST['phone_number']);
    $location = mysqli_real_escape_string($conn, $_POST['location']);
    $password = $_POST['password']; 
    $company_description = mysqli_real_escape_string($conn, $_POST['company_description']);
    $status = 'active';

    $image_db_path = 'img/default_company.png'; 

    if (isset($_FILES['company_image']) && $_FILES['company_image']['error'] == 0) {
        $target_dir = "../uploads/company_images/";
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0777, true);
        }

        $file_extension = pathinfo($_FILES["company_image"]["name"], PATHINFO_EXTENSION);
        $new_filename = uniqid('comp_') . "." . $file_extension;
        $target_file = $target_dir . $new_filename;

        if (move_uploaded_file($_FILES["company_image"]["tmp_name"], $target_file)) {
            $image_db_path = "uploads/company_images/" . $new_filename;
        }
    }

    $stmt = $conn->prepare("INSERT INTO companies (company_name, email, phone_number, location, password, company_description, image, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssssss", $company_name, $email, $phone_number, $location, $password, $company_description, $image_db_path, $status);

    if ($stmt->execute()) {
        echo "<script>
                alert('Company registered successfully!');
                window.location.href = 'companies.php';
              </script>";
    } else {
        echo "<script>
                alert('Error: " . $stmt->error . "');
                window.location.href = 'companies.php';
              </script>";
    }

    $stmt->close();
    $conn->close();
} else {
    header("Location: companies.php");
    exit();
}