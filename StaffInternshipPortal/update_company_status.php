<?php
session_start();
require '../db_connection.php';

if (!isset($_SESSION['staff_id']) || $_SESSION['user_type'] !== 'staff') {
    header("Location: ../login.php");
    exit();
}

if (isset($_GET['id']) && isset($_GET['status'])) {
    $id = (int)$_GET['id'];
    $new_status = mysqli_real_escape_string($conn, $_GET['status']);

    $sql = "UPDATE companies SET status = '$new_status' WHERE company_id = $id";

    if (mysqli_query($conn, $sql)) {
        echo "<script>
                
                window.location.href = 'companies.php?status=$new_status';
              </script>";
    } else {
        echo "<script>
                alert('Error updating status');
                window.location.href = 'companies.php';
              </script>";
    }
} else {
    header("Location: companies.php");
    exit();
}
?>