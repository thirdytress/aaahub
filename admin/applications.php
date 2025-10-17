<?php
session_start();
require_once "../classes/database.php";

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'admin') {
    header("Location: ../index.php");
    exit();
}

$db = new Database();

// Approve / Reject logic
if (isset($_GET['approve'])) {
    $app_id = $_GET['approve'];

    // Update application status
    $updated = $db->updateApplicationStatus($app_id, 'Approved');

    // Get application details
    $app = $db->getApplicationById($app_id);
    if ($app && isset($app['tenant_id'], $app['apartment_id'])) {
        $tenant_id = $app['tenant_id'];
        $apartment_id = $app['apartment_id'];

        // Check if lease already exists
        if (!$db->leaseExists($tenant_id, $apartment_id)) {
            $db->createLease(
                $tenant_id, 
                $apartment_id, 
                date('Y-m-d'), 
                date('Y-m-d', strtotime('+1 year'))
            );
        }
    }
}

if (isset($_GET['reject'])) {
    $db->updateApplicationStatus($_GET['reject'], 'Rejected');
}


$applications = $db->getAllApplications();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Applications | Admin</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container mt-5">
  <h3 class="text-primary mb-4">Tenant Applications</h3>

  <table class="table table-bordered table-striped bg-white">
    <thead class="table-primary">
      <tr>
        <th>#</th>
        <th>Tenant</th>
        <th>Apartment</th>
        <th>Status</th>
        <th>Date Applied</th>
        <th>Action</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($applications as $a): ?>
        <tr>
          <td><?= $a['application_id'] ?></td>
          <td><?= htmlspecialchars($a['firstname'] . ' ' . $a['lastname']) ?></td>
          <td><?= htmlspecialchars($a['apartment_name']) ?></td>
          <td><?= htmlspecialchars($a['status']) ?></td>
          <td><?= htmlspecialchars($a['date_applied']) ?></td>
          <td>
            <?php if ($a['status'] === 'Pending'): ?>
              <a href="?approve=<?= $a['application_id'] ?>" class="btn btn-success btn-sm">Approve</a>
              <a href="?reject=<?= $a['application_id'] ?>" class="btn btn-danger btn-sm">Reject</a>
            <?php else: ?>
              <span class="text-muted">No Action</span>
            <?php endif; ?>
          </td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>
</body>
</html>
