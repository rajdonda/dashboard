<?php
session_start();
include "data-pages/db.php";

password_hash("Admin123!", PASSWORD_DEFAULT);
$toastMsg = "";
$toastType = "";
error_reporting(E_ALL);
ini_set('display_errors', 1);
if ($_SERVER["REQUEST_METHOD"] === "POST" && ($_POST['action'] ?? '') === 'login') {
    $email    = trim($_POST["Email"] ?? "");
    $password = $_POST["Password"] ?? "";

    if (empty($email) || empty($password)) {
        $toastMsg = "Please fill in all required fields!";
        $toastType = "error";
    } else {
        $stmt = $mysqli->prepare("SELECT id, username, pass, role FROM cruddemo WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows === 1) {
            $stmt->bind_result($id, $dbUsername, $hashedPassword, $dbRole);
            $stmt->fetch();

            if (password_verify($password, $hashedPassword)) {
                $_SESSION['loggedin'] = true;
                $_SESSION['user_id'] = $id;
                $_SESSION['user'] = $dbUsername;
                $_SESSION['role'] = $dbRole;
                $_SESSION['toastMsg'] = "Login successful!";
                $_SESSION['toastType'] = "success";
                error_reporting(E_ALL);
                          ini_set('display_errors', 1);
                if ($dbRole === "admin") {
                    header("Location: admin_dashboard.php");
                    exit();
                } else {
                    header("Location: ../dashboard.php");
                    exit();
                }
            } else {
                $toastMsg = "Invalid password!";
                $toastType = "error";
            }
        } else {
            $toastMsg = "No account found with this email!";
            $toastType = "error";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">    
  <title>Login-Page</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="shortcut icon" href="../assets/website.png" type="image/x-icon">
  
</head>
<body class="bg-light">

<div class="d-flex justify-content-center align-items-center vh-100">
  <div class="card shadow p-4" style="width:400px">
    <h3 class="text-center mb-4">Login</h3>
    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
        <input type="hidden" name="action" value="login">
      <div class="mb-3">
        <label for="Email" class="form-label">Email</label>
        <input type="email" class="form-control" name="Email" >
      </div>
      <div class="mb-3">
        <label for="Password" class="form-label">Password</label>
        <input type="password" class="form-control" name="Password" >
      </div>
      <div class="d-flex justify-content-start">
        <button class="btn btn-success me-3" type="submit">Submit</button>
        <button class="btn btn-danger" type="reset">Reset</button>   
      </div>
    </form>
   <div class="mt-3">
      <a href="#" onclick="document.getElementById('registerRedirectForm').submit(); return false;">
          Don't have an account? -> Register
      </a>
      <form id="registerRedirectForm" action="register.php" method="POST" style="display:none;">
          <input type="hidden" name="fromLogin" value="1">
      </form>
    </div>
  </div>
</div>

<div id="toastContainer"></div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<?php 
  include "data-pages/toast.php";
?>
</body>
</html>