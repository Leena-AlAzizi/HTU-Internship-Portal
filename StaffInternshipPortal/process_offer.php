<?php
session_start();
require '../db_connection.php';

if (!isset($_SESSION['staff_id']) || $_SESSION['user_type'] !== 'staff') {
    header("Location: ../login.php");
    exit();
}

if (isset($_GET['id']) && isset($_GET['action'])) {
    $offer_id = (int)$_GET['id'];
    $action = $_GET['action'];
    
    if ($action === 'approve') {
        $new_status = 'Open';
    } elseif ($action === 'reject') {
        $new_status = 'Expired';
    } elseif ($action === 'expire') {
        $new_status = 'Expired'; 
    } else {
        header("Location: offers.php");
        exit();
    }

    $stmt = $conn->prepare("UPDATE offers SET status = ? WHERE offer_id = ?");
    $stmt->bind_param("si", $new_status, $offer_id);

    if ($stmt->execute()) {
        $tab = ($new_status == 'Open') ? 'pills-home' : 'pills-profile';
        header("Location: offers.php?tab=$tab");
        exit();
    } else {
        echo "<script>alert('Error updating status.'); window.location.href='offers.php';</script>";
    }
    $stmt->close();
} else {
    header("Location: offers.php");
    exit();
}
?>