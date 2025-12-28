//This is a script that we wrote incase the regular way of adding the admin does not work you can use the file url to create an admin if anything
<?php
require_once 'config.php';

$conn = getDBConnection();

$check = $conn->query("SELECT id FROM Users WHERE email = 'admin@project2.com'");

if ($check->num_rows > 0) {
    echo "The admin user already exists<br>";
} else {

    $firstname = 'Admin';
    $lastname = 'User';
    $email = 'admin@project2.com';
    $password = 'password123';
    $role = 'Admin';
    

    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    
    $stmt = $conn->prepare("INSERT INTO Users (firstname, lastname, email, password, role, created_at) VALUES (?, ?, ?, ?, ?, NOW())");
    $stmt->bind_param("sssss", $firstname, $lastname, $email, $hashed_password, $role);
    
    if ($stmt->execute()) {
        echo "The admin user is created<br><br>";
        echo "<strong>Login Details:</strong><br>";
        echo "Email: admin@project2.com<br>";
        echo "Password: password123<br><br>";
        echo "<a href='login.php'>Go to Login Page</a><br><br>";
    } else {
        echo "Error: creating the admin user: " . $stmt->error;
    }
    
    $stmt->close();
}

$conn->close();
?>