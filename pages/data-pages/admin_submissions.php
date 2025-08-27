<?php
session_start();
include "db.php";

if (!isset($_SESSION['loggedin']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../../dashboard.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Admin - Submissions</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container mt-5">
  <h2>All User Submissions</h2>
  <table class="table table-bordered table-striped mt-3">
    <thead>
      <tr>
        <th>User</th>
        <th>Field</th>
        <th>Response</th>
        <th>Submitted At</th>
      </tr>
    </thead>
    <tbody>
      <?php
      $query = "SELECT s.*, u.username, f.field_name 
          FROM user_submissions s
          JOIN cruddemo u ON s.user_id = u.id
          JOIN form_fields f ON s.field_id = f.id
          ORDER BY s.created_at DESC";
      $result = $mysqli->query($query);

      while ($row = $result->fetch_assoc()) {
          echo "<tr>";
                echo "<td>".htmlspecialchars($row['username'])."</td>";
                echo "<td>".htmlspecialchars($row['field_name'])."</td>";
                echo "<td>".htmlspecialchars($row['value'])."</td>";
                echo "<td>".htmlspecialchars($row['created_at'])."</td>";
          echo "</tr>";
      }
      ?>
    </tbody>
  </table>
  <a href="../admin_dashboard.php" class="btn btn-primary mt-3">Back to Admin Dashboard</a>
</div>

</body>
</html>
