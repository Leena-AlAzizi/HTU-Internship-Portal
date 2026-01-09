<?php
session_start();
require '../db_connection.php';

header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['student_id']) || $_SESSION['user_type'] !== 'student') {
    echo json_encode(['status' => 'error', 'message' => 'not_authenticated']);
    exit;
}

$student_id = $_SESSION['student_id'];
$offer_id = $_POST['offer_id'] ?? '';
$status = $_POST['status'] ?? '';

if (! $offer_id || ! in_array($status, ['Rejected', 'Interview'])) {
    echo json_encode(['status' => 'error', 'message' => 'invalid_input']);
    exit;
}

try {
    $stmt = $conn->prepare("UPDATE applications SET status = ? WHERE student_id = ? AND offer_id = ?");
    if (! $stmt) {
        throw new Exception('prepare_failed_app');
    }
    $stmt->bind_param("sss", $status, $student_id, $offer_id);
    if (! $stmt->execute()) {
        throw new Exception('execute_failed_app');
    }
    $stmt->close();

    if ($status === 'Interview') {
        $stmt2 = $conn->prepare("UPDATE students SET status = 'Interviewing' WHERE student_id = ?");
        if (! $stmt2) {
            throw new Exception('prepare_failed_student');
        }
        $stmt2->bind_param("s", $student_id);
        if (! $stmt2->execute()) {
            throw new Exception('execute_failed_student');
        }
        $stmt2->close();
    }

    echo json_encode(['status' => 'success']);
    exit;

} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    exit;
}