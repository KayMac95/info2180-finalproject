<?php
require_once '../config.php';
requireLogin();

header('Content-Type: application/json; charset=utf-8');

if (isset($_POST['contact_id'])) {
    $contact_id = $_POST['contact_id'];
} else {
    $contact_id = 0;
}

$comment = trim($_POST['comment'] ?? '');


if ($contact_id === 0 || $comment === '')  {
    echo json_encode(['status' => 'error', 'message' => 'Invalid input']);
    exit();
}

$conn = getDBConnection();

try {
    $stmt = $conn->prepare("INSERT INTO Notes (contact_id, comment, created_by) VALUES (?, ?, ?)");
    $stmt->bind_param("isi", $contact_id, $comment, $_SESSION['user_id']);
    $stmt->execute();
    $note_id = $stmt->insert_id;
    $stmt->close();
    $stmt = $conn->prepare("SELECT u.firstname, u.lastname, n.created_at 
                            FROM Notes n 
                            JOIN Users u ON n.created_by = u.id 
                            WHERE n.id = ?");
    $stmt->bind_param("i", $note_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $note = $result->fetch_assoc();
    $stmt->close();
    $conn->close();
    $html = '<div class="note">
        <p>' . htmlspecialchars($comment) . '</p>
        <small>By ' . htmlspecialchars($note['firstname'] . ' ' . $note['lastname']) . 
        ' on ' . date('M d, Y g:i A', strtotime($note['created_at'])) . '</small>
    </div>';

    echo json_encode(['status' => 'success', 'html' => $html]);
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => 'Database error']);
}
?>