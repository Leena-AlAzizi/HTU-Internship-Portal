<?php
session_start();
require '../db_connection.php'; 

if (!isset($_SESSION['company_id']) || $_SESSION['user_type'] !== 'company') {
    header("Location: ../login.php");
    exit();
}

$company_id = $_SESSION['company_id'];
$upload_dir = '../uploads/company_images/';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    $company_name = filter_var($_POST['company_name'], FILTER_SANITIZE_STRING);
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    $phone_number = filter_var($_POST['phone_number'], FILTER_SANITIZE_STRING);
    $location = filter_var($_POST['location'], FILTER_SANITIZE_STRING);
    $company_description = filter_var($_POST['company_description'], FILTER_SANITIZE_STRING);
    
    $params = [$company_name, $email, $phone_number, $location, $company_description];
    $types = "sssss";
    $update_image_clause = ""; 
    
    if (isset($_FILES['profile_pic']) && $_FILES['profile_pic']['error'] == 0) {
        $file_name = $_FILES['profile_pic']['name'];
        $file_tmp = $_FILES['profile_pic']['tmp_name'];
        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        $allowed_extensions = array("jpeg", "jpg", "png", "webp");

        if (in_array($file_ext, $allowed_extensions)) {
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }

            $new_file_name = uniqid('comp_', true) . '.' . $file_ext;
            $destination = $upload_dir . $new_file_name;
            $db_save_path = 'uploads/company_images/' . $new_file_name;
            
            if (move_uploaded_file($file_tmp, $destination)) {
                $old_image_sql = "SELECT image FROM companies WHERE company_id = ?";
                $stmt_old = $conn->prepare($old_image_sql);
                $stmt_old->bind_param("i", $company_id);
                $stmt_old->execute();
                $result_old = $stmt_old->get_result();
                $old_image = $result_old->fetch_assoc()['image'];
                $stmt_old->close();
                
                if (!empty($old_image) && file_exists("../" . $old_image) && $old_image != 'img/default_company.png') {
                    unlink("../" . $old_image);
                }

                $params[] = $db_save_path;
                $types .= "s";
                $update_image_clause = ", image = ?";
            } else {
                $_SESSION['error'] = "Failed to upload the image file.";
                header("Location: profile.php");
                exit();
            }
        } else {
            $_SESSION['error'] = "Invalid file type.";
            header("Location: profile.php");
            exit();
        }
    }

    $update_sql = "UPDATE companies SET company_name = ?, email = ?, phone_number = ?, location = ?, company_description = ? {$update_image_clause} WHERE company_id = ?";
    
    $params[] = $company_id;
    $types .= "i";
    
    if ($stmt = $conn->prepare($update_sql)) {
        $bind_params = array_merge(array($types), $params);
        $refs = array();
        foreach($bind_params as $key => $value) $refs[$key] = &$bind_params[$key];
        call_user_func_array(array($stmt, 'bind_param'), $refs);

        if ($stmt->execute()) {
            $_SESSION['success'] = "Profile updated successfully!";
        } else {
            $_SESSION['error'] = "Update failed: " . $stmt->error;
        }
        $stmt->close();
    }
}

header("Location: profile.php");
exit();
?>