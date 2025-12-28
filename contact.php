<?php
require_once 'config.php';
requireLogin();

$contact_id = $_GET['id'] ?? 0;
$conn = getDBConnection();


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['assign_to_me'])) {
    $stmt = $conn->prepare(
        "UPDATE Contacts 
         SET assigned_to = ?, updated_at = NOW() 
         WHERE id = ?"
    );
    $stmt->bind_param("ii", $_SESSION['user_id'], $contact_id);
    $stmt->execute();
    $stmt->close();

    header("Location: contact.php?id=$contact_id");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['switch_type'])) {


    $stmt = $conn->prepare("SELECT type FROM Contacts WHERE id = ?");
    $stmt->bind_param("i", $contact_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $current = $result->fetch_assoc();
    $stmt->close();


   if ($type === 'Sales Lead') {
    $newType = 'Support';
} else {
    $newType = 'Sales Lead';
}


    $stmt = $conn->prepare(
        "UPDATE Contacts 
         SET type = ?, updated_at = NOW() 
         WHERE id = ?"
    );
    $stmt->bind_param("si", $newType, $contact_id);
    $stmt->execute();
    $stmt->close();

    header("Location: contact.php?id=$contact_id");
    exit();
}




if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_note'])) {


    $comment = trim($_POST['comment'] ?? '');

    if (!empty($comment)) {


        $stmt = $conn->prepare(
            "INSERT INTO Notes (contact_id, comment, created_by) 
             VALUES (?, ?, ?)"
        );
        $stmt->bind_param("isi", $contact_id, $comment, $_SESSION['user_id']);
        $stmt->execute();
        $stmt->close();

       
        $stmt = $conn->prepare(
            "UPDATE Contacts 
             SET updated_at = NOW() 
             WHERE id = ?"
        );
        $stmt->bind_param("i", $contact_id);
        $stmt->execute();
        $stmt->close();

       
        header("Location: contact.php?id=$contact_id");
        exit();
    }
}


$stmt = $conn->prepare("SELECT c.*, u1.firstname as assigned_firstname, u1.lastname as assigned_lastname, 
                        u2.firstname as created_firstname, u2.lastname as created_lastname 
                        FROM Contacts c 
                        LEFT JOIN Users u1 ON c.assigned_to = u1.id 
                        LEFT JOIN Users u2 ON c.created_by = u2.id 
                        WHERE c.id = ?");
$stmt->bind_param("i", $contact_id);
$stmt->execute();
$result = $stmt->get_result();
$contact = $result->fetch_assoc();
$stmt->close();

if ($contact === null || $contact === false) {
    header('Location: dashboard.php');
    exit();
}


$stmt = $conn->prepare("SELECT n.*, u.firstname, u.lastname 
                        FROM Notes n 
                        JOIN Users u ON n.created_by = u.id 
                        WHERE n.contact_id = ? 
                        ORDER BY n.created_at DESC");
$stmt->bind_param("i", $contact_id);
$stmt->execute();
$notes = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($contact['firstname'] . ' ' . $contact['lastname']); ?> - Dolphin CRM</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <?php include 'header.php'; ?>
    
    <div class="contain">
        <a href="dashboard.php" class="backbutton">Back to Contacts</a>
        
        <div class="contact-detail">
            <div class="contact-header">
                <div>
                    <h1><?php echo htmlspecialchars($contact['title'] . ' ' . $contact['firstname'] . ' ' . $contact['lastname']); ?></h1>
                  
                </div>
                <div class="contact-dates">
                    <small>Created: <?php echo date('M d, Y', strtotime($contact['created_at'])); ?></small><br>
                    <small>Updated: <?php echo date('M d, Y', strtotime($contact['updated_at'])); ?></small>
                </div>
                <button id="assignBtn" class="btn btn2">Assign to me</button>

<p id="assignedTo">
    <?= $contact['assigned_firstname'] 
        ? $contact['assigned_firstname'].' '.$contact['assigned_lastname']
        : 'Unassigned'; ?>
</p>

<button id="switchTypeBtn" class="btn btn1">Switch Type</button>
<span id="contactType" ><?= $contact['type'] ?></span>

            </div>
            
            <div class="contact-info">
                <div class="info-section">
                    <div class="info-item">
                        <strong>Email</strong>
                        <p><?php echo htmlspecialchars($contact['email']); ?></p>
                    </div>
                    <div class="info-item">
                        <strong>Telephone</strong>
                        <p><?php echo htmlspecialchars($contact['telephone']); ?></p>
                    </div>
                    <div class="info-item">
                        <strong>Company</strong>
                        <p><?php echo htmlspecialchars($contact['company']); ?></p>
                    </div>
                </div>
                <div class="info-section">
                    <div class="info-item">
                        <strong>Assigned To</strong>
                        <p>
                            <?php 
                            if ($contact['assigned_firstname']) {
                                echo htmlspecialchars($contact['assigned_firstname'] . ' ' . $contact['assigned_lastname']);
                            } else {
                                echo 'Unassigned';
                            }
                            ?>
                        </p>
                    </div>
                    <div class="info-item">
                        <strong>Created By</strong>
                        <p><?php echo htmlspecialchars($contact['created_firstname'] . ' ' . $contact['created_lastname']); ?></p>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="notes-section">
            <h2>Notes</h2>
            
    <form id="noteForm">
    <textarea id="noteText" required></textarea>
    <button type="submit" class="btn btn1">Add Note</button>
</form>

<div id="notes-list">
    <?php while ($note = $notes->fetch_assoc()): ?>
    <div class="note">
        <p><?php echo nl2br(htmlspecialchars($note['comment'])); ?></p>
        <small>
            By <?php echo htmlspecialchars($note['firstname'].' '.$note['lastname']); ?> 
            on <?php echo date('M d, Y g:i A', strtotime($note['created_at'])); ?>
        </small>
    </div>
    <?php endwhile; ?>
    <?php if ($notes->num_rows === 0): ?>
        <p class="no-notes">No notes yet. Add one above!</p>
    <?php endif; ?>
</div>


            
    <script>

document.getElementById('assignBtn').addEventListener('click', function() {
    fetch('api/assign_contact.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'contact_id=<?= $contact_id ?>'
    })
    .then(res => res.json())
    .then(data => {
        if(data.status === 'success'){
            document.getElementById('assignedTo').innerText = data.assigned_to;
        }
    });
});


document.getElementById('switchTypeBtn').addEventListener('click', function() {
    fetch('api/switch_type.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'contact_id=<?= $contact_id ?>'
    })
    .then(res => res.json())
    .then(data => {
        if(data.status === 'success'){
            document.getElementById('contactType').innerText = data.type;
        }
    });
});

document.getElementById('noteForm').addEventListener('submit', function(e){
    e.preventDefault();
    const comment = document.getElementById('noteText').value;
    if(comment === "") {
        return;
    }

    fetch('api/add_note.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'contact_id=<?= $contact_id ?>&comment=' + encodeURIComponent(comment)
    })
    .then(res => res.json())
    .then(data => {
        if(data.status === 'success'){
            document.getElementById('notes-list').insertAdjacentHTML('afterbegin', data.html);
            document.getElementById('noteText').value = '';
        }
    });
});
</script>


</body>
</html>
<?php
$stmt->close();
$conn->close();
?>