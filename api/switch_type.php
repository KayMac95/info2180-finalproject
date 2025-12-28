<?php
require_once '../config.php';
requireLogin();
header('Content-Type: application/json; charset=utf-8');

$contact_id = $_POST['contact_id'] ?? 0;
if ($contact_id === 0) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid contact ID']);
    exit();
}

$conn = getDBConnection();

$stmt = $conn->prepare("SELECT type FROM Contacts WHERE id = ?");
$stmt->bind_param("i", $contact_id);
$stmt->execute();
$result = $stmt->get_result();
$contact = $result->fetch_assoc();
$stmt->close();

if ($contact === false || $contact === null) {
    echo json_encode(['status' => 'error', 'message' => 'Contact not found']);
    exit();
}
$newType = ($contact['type'] === 'Sales Lead') ? 'Support' : 'Sales Lead';

$stmt = $conn->prepare("UPDATE Contacts SET type = ? WHERE id = ?");
$stmt->bind_param("si", $newType, $contact_id);
$stmt->execute();
$stmt->close();
$conn->close();

echo json_encode(['status' => 'success', 'type' => $newType]);
?>