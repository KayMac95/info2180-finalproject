<nav class="navbar">
    <div class="nav-contain">
        <div class="nav-brand">
            <h2>Dolphin CRM</h2>
        </div>
        
        <div class="nav-user">
            <span><?php echo htmlspecialchars($_SESSION['firstname'] . ' ' . $_SESSION['lastname']); ?></span>
        </div>
        <div class="side-bar">
    <a href="dashboard.php">Home</a>
    <a href="new_contact.php">New Contact</a>
    <?php if (isAdmin()): ?>
        <a href="users.php">Users</a>
    <?php endif; ?>
    <a href="logout.php">Logout</a>
</div>

    </div>
</nav>