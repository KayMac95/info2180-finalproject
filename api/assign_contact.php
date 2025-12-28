<?php
require_once '../config.php';
requireLogin();


header('Content-Type: application/json; charset=utf-8');

if (isset($_SESSION['user_id'])) {
    $SYSTEM_USER_ID = $_SESSION['user_id'];
} else {
    $SYSTEM_USER_ID = 1;
}

if (isset($_POST['contact_id'])) {
    $contact_id = (int) $_POST['contact_id'];
} else {
    $contact_id = 0;
}


if ($contact_id === 0) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid contact ID']);
    exit();
}

$conn = getDBConnection();

try {
    $stmt = $conn->prepare("UPDATE Contacts SET assigned_to = ? WHERE id = ?");
    $stmt->bind_param("ii", $SYSTEM_USER_ID, $contact_id);
    $stmt->execute();
    $stmt->close();
    $conn->close();

    echo json_encode(['status' => 'success', 'assigned_to' => 'Admin user']);
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => 'Database error']);
}
?>