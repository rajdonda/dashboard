<?php
session_start();
include "pages/data-pages/db.php";
$toastMsg = $_SESSION['toastMsg'] ?? "";
$toastType = $_SESSION['toastType'] ?? "";
unset($_SESSION['toastMsg'], $_SESSION['toastType']);

if (!isset($_SESSION['loggedin']) || $_SESSION['role'] !== 'user') {
    header("Location: pages/login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['submit_form'])) {
    foreach ($_POST as $key => $value) {
        if (str_starts_with($key, 'field_')) {
            $field_id = intval(str_replace('field_', '', $key));
            
            $stmt = $mysqli->prepare("INSERT INTO user_submissions (user_id, field_id, value, created_at) VALUES (?, ?, ?, NOW())");
            if (!$stmt) die("Prepare failed: " . $mysqli->error);
            
            $stmt->bind_param("iis", $user_id, $field_id, $value);
            if (!$stmt->execute()) die("Execute failed: " . $stmt->error);
        }
    }

    $_SESSION['toastMsg'] = "Form submitted successfully!";
    $_SESSION['toastType'] = "success";

    header("Location: dashboard.php");
    exit();
}

// Fetch form fields
$fields_result = $mysqli->query("SELECT * FROM form_fields ORDER BY field_order ASC");

// Fetch all submission IDs for this user
$submission_ids_result = $mysqli->query("
    SELECT DISTINCT created_at 
    FROM user_submissions 
    WHERE user_id = $user_id 
    ORDER BY created_at DESC
");
$submission_times = [];
while ($row = $submission_ids_result->fetch_assoc()) {
    $submission_times[] = $row['created_at'];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Dashboard</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<div class="container-fluid mt-3 px-5">
  <h1>Welcome To Dashboard, <?= htmlspecialchars($_SESSION['user']); ?> 🎉</h1> 

  <!-- Form Card -->
  <div class="card shadow p-4 mt-4">
    <h3 class="mb-3">Fill Your Form</h3>
    <form method="post" action="">
      <?php while($row = $fields_result->fetch_assoc()): ?>
        <div class="mb-3">
          <label class="form-label"><?= htmlspecialchars($row['field_name']); ?></label>
          <input type="<?= htmlspecialchars($row['field_type']); ?>" class="form-control" name="field_<?= $row['id']; ?>" required>
        </div>
      <?php endwhile; ?>
      <button class="btn btn-success" type="submit" name="submit_form">Submit</button>
    </form>
  </div>

  <!-- Submissions -->
  <h3 class="mt-5">Your Previous Submissions</h3>

  <?php $index = 1; ?>
  <?php foreach ($submission_times as $time): ?>
      <?php
        // Fetch all fields of this submission
        $fields_submission = $mysqli->query("
            SELECT f.field_name, s.value 
            FROM user_submissions s
            JOIN form_fields f ON s.field_id = f.id
            WHERE s.user_id = $user_id AND s.created_at = '$time'
        ");
      ?>
      <div class="card shadow p-3 mb-4">
        <h5>Submission #<?= $index ?> (<?= $time ?>)</h5>
        <table class="table table-bordered mt-2">
          <thead>
            <tr>
              <th>Field Name</th>
              <th>Value</th>
            </tr>
          </thead>
          <tbody>
            <?php while($field = $fields_submission->fetch_assoc()): ?>
              <tr>
                <td><?= htmlspecialchars($field['field_name']); ?></td>
                <td><?= htmlspecialchars($field['value']); ?></td>
              </tr>
            <?php endwhile; ?>
          </tbody>
        </table>
      </div>
      <?php $index++; ?>
  <?php endforeach; ?>

</div>
 <a href="pages/data-pages/logout.php" class="btn btn-danger">Logout</a>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<div id="toastContainer"></div>
<?php include "pages/data-pages/toast.php"; ?>
</body>
</html>
