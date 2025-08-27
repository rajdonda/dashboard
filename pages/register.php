<?php
include "data-pages/db.php";
$toastMsg = "";
$toastType = "";

if ($_SERVER["REQUEST_METHOD"] === "POST" && ($_POST['action'] ?? '') === 'register') {
    $username   = trim($_POST["Username"] ?? "");
    $email      = trim($_POST["Email"] ?? "");
    $password   = $_POST["Password"] ?? "";
    $repassword = $_POST["re-Password"] ?? "";
    
    if (empty($username) || empty($email) || empty($password) || empty($repassword)) {
        $toastMsg = "Please fill in all required fields!";
        $toastType = "error";
    } 
    elseif ($password !== $repassword) {
        $toastMsg = "Passwords do not match!";
        $toastType = "error";
    }
    else {
        $hashed = password_hash($password, PASSWORD_DEFAULT);
        $role = 'user'; // default role
        $stmt = $mysqli->prepare("INSERT INTO cruddemo (username, email, pass, confirmpassword, role) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("sssss", $username, $email, $hashed, $hashed, $role);

        try {
            if ($stmt->execute()) {
                $toastMsg = "Account created successfully!";
                $toastType = "success";
            }
        } catch (mysqli_sql_exception $e) {
            if ($e->getCode() == 1062) {
                if (str_contains($e->getMessage(), "username")) {
                    $toastMsg = "This username is already taken!";
                } elseif (str_contains($e->getMessage(), "email")) {
                    $toastMsg = "This email is already registered!";
                } else {
                    $toastMsg = "Duplicate entry detected!";
                }
                $toastType = "error";
            } else {
                $toastMsg = "Database error: " . $e->getMessage();
                $toastType = "error";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">    
  <title>Register-Page</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="shortcut icon" href="../assets/website.png" type="image/x-icon">
</head>
<body class="bg-light">

<div class="d-flex justify-content-center align-items-center vh-100">
  <div class="card shadow p-4" style="width:400px">
    <h3 class="text-center mb-4">Register</h3>
    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
        <input type="hidden" name="action" value="register">
      <div class="mb-3">
        <label for="Username" class="form-label">Username</label>
        <input type="text" class="form-control" name="Username" >
      </div>
      <div class="mb-3">
        <label for="Email" class="form-label">Email</label>
        <input type="email" class="form-control" name="Email" >
      </div>
      <div class="mb-3">
        <label for="Password" class="form-label">Password</label>
        <input type="password" class="form-control" name="Password" >
      </div>
      <div class="mb-3">
        <label for="re-Password" class="form-label">Re-enter Password</label>
        <input type="password" class="form-control" name="re-Password"      >
      </div>
      <div class="d-flex justify-content-start">
        <button class="btn btn-success me-3" type="submit">Submit</button>
        <button class="btn btn-danger" type="reset">Reset</button>   
      </div>
    </form>
    <div class="mt-3">
        <a href="#" onclick="document.getElementById('loginRedirectForm').submit(); return false;">Already have an account? -> Login</a>
        <form id="loginRedirectForm" action="login.php" method="POST" style="display:none;">
    <input type="hidden" name="fromRegister" value="1">
</form>
      </div>
  </div>
</div>

<div id="toastContainer"></div>

<?php 
  include "data-pages/toast.php";
?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
