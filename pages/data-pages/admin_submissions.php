<?php
session_start();
include "db.php";

if (!isset($_SESSION['loggedin']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../../dashboard.php");
    exit();
}

// Handle deletion
if (isset($_GET['delete'])) {
    $submission_id = intval($_GET['delete']);
    $stmt = $mysqli->prepare("DELETE FROM user_submissions WHERE id=?");
    $stmt->bind_param("i", $submission_id);
    $stmt->execute();

    $_SESSION['toastMsg'] = "Submission deleted successfully!";
    $_SESSION['toastType'] = "success";
    header("Location: ".$_SERVER['PHP_SELF']);
    exit();
}

// Fetch all unique submission times per user
$submissions_result = $mysqli->query("
    SELECT DISTINCT user_id, created_at
    FROM user_submissions
    ORDER BY created_at DESC
");

$toastMsg = $_SESSION['toastMsg'] ?? "";
$toastType = $_SESSION['toastType'] ?? "";
unset($_SESSION['toastMsg'], $_SESSION['toastType']);
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

    <?php while($submission = $submissions_result->fetch_assoc()): ?>
        <?php
            $user_id = $submission['user_id'];
            $created_at = $submission['created_at'];

            // Fetch all fields for this submission
            $fields_result = $mysqli->query("
                SELECT f.field_name, s.value, s.id
                FROM user_submissions s
                JOIN form_fields f ON s.field_id = f.id
                WHERE s.user_id = $user_id AND s.created_at = '$created_at'
            ");

            // Fetch username
            $user_res = $mysqli->query("SELECT username FROM cruddemo WHERE id = $user_id");
            $user_row = $user_res->fetch_assoc();
            $username = $user_row['username'] ?? 'Unknown';
        ?>
        <div class="card shadow p-3 mb-4">
            <h5>User: <?= htmlspecialchars($username) ?> | Submitted At: <?= $created_at ?></h5>
            <table class="table table-bordered mt-2">
                <thead>
                    <tr>
                        <th>Field</th>
                        <th>Response</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($field = $fields_result->fetch_assoc()): ?>
                        <tr>
                            <td><?= htmlspecialchars($field['field_name']); ?></td>
                            <td><?= htmlspecialchars($field['value']); ?></td>
                            <td>
                                <a href="?delete=<?= $field['id'] ?>" class="btn btn-sm btn-danger">Delete</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    <?php endwhile; ?>

    <a href="../admin_dashboard.php" class="btn btn-primary mt-3">Back to Admin Dashboard</a>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<?php include "toast.php"; ?>
</body>
</html>
