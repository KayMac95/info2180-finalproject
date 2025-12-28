<?php
require_once 'config.php';
requireAdmin();

$conn = getDBConnection();
$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $firstname = trim($_POST['firstname'] ?? '');
    $lastname  = trim($_POST['lastname'] ?? '');
    $email     = trim($_POST['email'] ?? '');
    $password  = $_POST['password'] ?? '';
    $role      = $_POST['role'] ?? 'Member';

    
    if (!$firstname || !$lastname || !$email || !$password) {
        $error = 'All fields need input.';
    }

    elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Invalid email address input.';
    }

    elseif (!preg_match('/^(?=.*[A-Z])(?=.*[a-zA-Z])(?=.*\d).{8,}$/', $password)) {
        $error = 'Password must be at least 8 characters and must contain at least one capital letter and a number.';
    }

   
    elseif (!in_array($role, ['Admin', 'Member'])) {
        $error = 'Not a valid user role.';
    }

    else {

        $check_stmt = $conn->prepare(
            "SELECT id FROM Users WHERE email = ?"
        );
        $check_stmt->bind_param("s", $email);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();

        if ($check_result->num_rows > 0) {
            $error = 'this Email already exists. Try again';
        } else {

            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

       
            $stmt = $conn->prepare(
                "INSERT INTO Users (firstname, lastname, email, password, role)
                 VALUES (?, ?, ?, ?, ?)"
            );
            $stmt->bind_param(
                "sssss",
                $firstname,
                $lastname,
                $email,
                $hashed_password,
                $role
            );

            if ($stmt->execute()) {
                $success = 'User is suscessfully addes thank you';
            } else {
                $error = 'Error in adding user. Please try again.';
            }

            $stmt->close();
        }

        $check_stmt->close();
    }
}
 
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add User - Dolphin CRM</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <?php include 'header.php'; ?>
    
    <div class="contain">
        <div class="form-contain">
            <h1>Add New User</h1>
            
            <?php if ($success): ?>
                <div class="success-message"><?php echo htmlspecialchars($success); ?></div>
            <?php endif; ?>
            
            <?php if ($error): ?>
                <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            
            <form method="POST" action="new_user.php">
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
                    <label>Password *</label>
                    <input type="password" name="password" required minlength="6">
                </div>
                
                <div class="form-group">
                    <label>Role *</label>
                    <select name="role" required>
                        <option value="Member">Member</option>
                        <option value="Admin">Admin</option>
                    </select>
                </div>
                
                <div class="form-buttons">
                    <button type="submit" class="btn btn1">Add User</button>
                    <a href="users.php" class="btn btn2">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
<?php $conn->close(); ?>