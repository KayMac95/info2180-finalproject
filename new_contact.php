<?php
require_once 'config.php';
requireLogin();

$conn = getDBConnection();
$success = '';
$error = '';

$users_query = "SELECT id, firstname, lastname FROM Users ORDER BY firstname";
$users = $conn->query($users_query);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'] ?? '';
    $firstname = $_POST['firstname'] ?? '';
    $lastname = $_POST['lastname'] ?? '';
    $email = $_POST['email'] ?? '';
    $telephone = $_POST['telephone'] ?? '';
    $company = $_POST['company'] ?? '';
    $type = $_POST['type'] ?? '';
    $assigned_to = $_POST['assigned_to'] ?? null;
    
    if ($firstname && $lastname && $email && $company && $type) {
        $stmt = $conn->prepare("INSERT INTO Contacts (title, firstname, lastname, email, telephone, company, type, assigned_to, created_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sssssssis", $title, $firstname, $lastname, $email, $telephone, $company, $type, $assigned_to, $_SESSION['user_id']);

        
        if ($stmt->execute()) {
            $success = 'Contact is now added ';
        } else {
            $error = 'Error: Cant add contact: ' . $stmt->error;
        }
        $stmt->close();
    } else {
        $error = 'Please fill in all the fields';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>New Contact - Dolphin CRM</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <?php include 'header.php'; ?>
    
    <div class="contain">
        <div class="form-contain">
            <h1>Add New Contact</h1>
            
            <?php if ($success): ?>
                <div class="success-message"><?php echo htmlspecialchars($success); ?></div>
            <?php endif; ?>
            
            <?php if ($error): ?>
                <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            
            <form method="POST" action="new_contact.php">
                <div class="form-row">
                    <div class="form-group">
                        <label>Title *</label>
                        <select name="title" required>
                            <option value="Mr">Mr</option>
                            <option value="Ms">Ms</option>
                            <option value="Mrs">Mrs</option>
                            <option value="Dr">Dr</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label>Type *</label>
                        <select name="type" required>
                            <option value="Sales Lead">Sales Lead</option>
                            <option value="Support">Support</option>
                        </select>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label>First Name *</label>
                        <input type="text" name="firstname" required>
                    </div>
                    
                    <div class="form-group">
                        <label>Last Name *</label>
                        <input type="text" name="lastname" required>
                    </div>
                </div>
                
                <div class="form-group">
                    <label>Email *</label>
                    <input type="email" name="email" required>
                </div>
                
                <div class="form-group">
                    <label>Telephone</label>
                    <input type="tel" name="telephone">
                </div>
                
                <div class="form-group">
                    <label>Company *</label>
                    <input type="text" name="company" required>
                </div>
                
                <div class="form-group">
                    <label>Assign To</label>
                    <select name="assigned_to">
                        <option value="">Select User</option>
                        <?php 
                        $users->data_seek(0);
                        while ($user = $users->fetch_assoc()): 
                        ?>
                            <option value="<?php echo $user['id']; ?>">
                                <?php echo htmlspecialchars($user['firstname'] . ' ' . $user['lastname']); ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                
                <div class="form-buttons">
                    <button type="submit" class="btn btn1">Add Contact</button>
                    <a href="dashboard.php" class="btn btn2">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
<?php $conn->close(); ?>