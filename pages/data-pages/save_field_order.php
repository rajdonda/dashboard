<?php
session_start();
include "db.php";

if ( !isset($_SESSION['loggedin']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../dashboard.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['order'])) {
    foreach ($_POST['order'] as $position => $id) {
        $stmt = $mysqli->prepare("UPDATE form_fields SET field_order=? WHERE id=?");
        $pos = $position + 1;
        $stmt->bind_param("ii", $pos, $id);
        $stmt->execute();
    }
    header("Location: admin_dashboard.php");
    exit();
} 