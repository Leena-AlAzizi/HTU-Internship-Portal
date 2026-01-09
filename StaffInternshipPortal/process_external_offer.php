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
        $new_status = 'Accepted';
        $target_tab = 'pills-home';
    } elseif ($action === 'reject') {
        $new_status = 'Rejected';
        $target_tab = 'pills-profile';
    } else {
        header("Location: external_offer.php");
        exit();
    }

    $stmt = $conn->prepare("UPDATE external_offers SET status = ? WHERE external_offer_id = ?");
    $stmt->bind_param("si", $new_status, $offer_id);

    if ($stmt->execute()) {
        header("Location: external_offer.php?tab=$target_tab");
        exit();
    } else {
        echo "<script>alert('Error updating status.'); window.location.href='external_offer.php';</script>";
    }
    $stmt->close();
} else {
    header("Location: external_offer.php");
    exit();
}