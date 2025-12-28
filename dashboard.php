<?php
require_once 'config.php';
requireLogin();

$conn = getDBConnection();


$filter = $_GET['filter'] ?? 'all';
$search = $_GET['search'] ?? '';


$query = "SELECT c.*, u.firstname as assigned_firstname, u.lastname as assigned_lastname 
          FROM Contacts c 
          LEFT JOIN Users u ON c.assigned_to = u.id 
          WHERE 1=1";

$params = [];
$types = '';

if ($filter === 'Sales Lead' || $filter === 'Support') {
    $query .= " AND c.type = ?";
    $params[] = $filter;
    $types .= 's';
}

if ($filter === 'assigned') {
    $query .= " AND c.assigned_to = ?";
    $params[] = $_SESSION['user_id'];
    $types .= 'i';
}

if ($search) {
    $query .= " AND (c.firstname LIKE ? OR c.lastname LIKE ? OR c.email LIKE ?)";
    $searchParam = "%$search%";
    $params[] = $searchParam;
    $params[] = $searchParam;
    $params[] = $searchParam;
    $types .= 'sss';
}

$query .= " ORDER BY c.created_at DESC";

$stmt = $conn->prepare($query);
if ($params) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$contacts = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Dolphin CRM</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <?php include 'header.php'; ?>
    
    <div class="contain">
        <div class="dashboard-header">
            <h1>Dashboard</h1>
          
            <div class="dashboard-controls">
                <form method="GET" class="filter-form">
                      <a href="new_contact.php" class="btn btn1">
        + Add Contact
    </a>
                </form>
            </div>

        </div>
        <div class="filter-bar">
    <span class="filter-label">Filter By:</span>

    <a href="dashboard.php?filter=all"
       class="<?php echo $filter === 'all' ? 'active' : ''; ?>">
        All
    </a>

    <a href="dashboard.php?filter=Sales Lead"
       class="<?php echo $filter === 'Sales Lead' ? 'active' : ''; ?>">
        Sales Leads
    </a>

    <a href="dashboard.php?filter=Support"
       class="<?php echo $filter === 'Support' ? 'active' : ''; ?>">
        Support
    </a>

    <a href="dashboard.php?filter=assigned"
       class="<?php echo $filter === 'assigned' ? 'active' : ''; ?>">
        Assigned to me
    </a>
    
</div>

        <div class="table-contain">
            <table class="contacts-table">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Company</th>
                        <th>Type</th>
                        <th>Assigned To</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($contact = $contacts->fetch_assoc()): ?>
                        <tr>
                            <td>
                                <strong><?php echo htmlspecialchars($contact['title'] . ' ' . $contact['firstname'] . ' ' . $contact['lastname']); ?></strong>
                            </td>
                            <td><?php echo htmlspecialchars($contact['email']); ?></td>
                            <td><?php echo htmlspecialchars($contact['company']); ?></td>
                            <td>
                                <span class="<?php echo $contact['type'] === 'Sales Lead' ? 'success' : 'info'; ?>">
                                    <?php echo htmlspecialchars($contact['type']); ?>
                                </span>
                            </td>
                            <td>
                                <?php 
                                if ($contact['assigned_firstname']) {
                                    echo htmlspecialchars($contact['assigned_firstname'] . ' ' . $contact['assigned_lastname']);
                                } else {
                                    echo 'Unassigned';
                                }
                                ?>
                            </td>
                            <td>
                                <a href="contact.php?id=<?php echo $contact['id']; ?>" class="btn btn-sm btn1">View</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                    <?php if ($contacts->num_rows === 0): ?>
                        <tr>
                            <td colspan="6" class="text-center">No contacts found. Try again</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>
<?php
$stmt->close();
$conn->close();
?>