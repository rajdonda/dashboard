<?php
session_start();
include "db.php";

if (!isset($_SESSION['loggedin'])) {
    header("Location: ../login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

foreach ($_POST as $key => $value) {
    if (strpos($key, 'field_') === 0) {
        $field_id = intval(str_replace('field_', '', $key));
        $response_value = $value;

        $stmt = $mysqli->prepare("INSERT INTO form_responses (user_id, field_id, response_value) VALUES (?, ?, ?)");
        $stmt->bind_param("iis", $user_id, $field_id, $response_value);
        $stmt->execute();
    }
}

$_SESSION['toastMsg'] = "Form submitted successfully!";
$_SESSION['toastType'] = "success";
header("Location: dashboard.php");
exit();
