    <?php
    session_start();
    include "pages/data_pages/db.php";
    require_once __DIR__ . "/data-pages/db.php";
    $toastMsg = $_SESSION['toastMsg'] ?? "";
    $toastType = $_SESSION['toastType'] ?? "";
    unset($_SESSION['toastMsg'], $_SESSION['toastType']);

    if (!isset($_SESSION['loggedin']) || $_SESSION['role'] !== 'admin') {
        header("Location: ../dashboard.php");
        exit();
    }

    // Add new field
    if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['add_field'])) {
    $field_name = trim($_POST['field_name']);
    $field_type = $_POST['field_type'];

    if (!isset($_SESSION['user_id'])) {
        die("Error: Admin not logged in.");
    }

    $admin_id = $_SESSION['user_id'];

    if (!empty($field_name) && !empty($field_type)) {

        // Check for duplicate
        $stmtCheck = $mysqli->prepare("SELECT id FROM form_fields WHERE field_name = ?");
        $stmtCheck->bind_param("s", $field_name);
        $stmtCheck->execute();
        $stmtCheck->store_result();
        if ($stmtCheck->num_rows > 0) {
            $_SESSION['toastMsg'] = "Field '$field_name' already exists!";
            $_SESSION['toastType'] = "error";
            header("Location: admin_dashboard.php");
            exit();
        }

        $max_order_result = $mysqli->query("SELECT MAX(field_order) AS max_order FROM form_fields");
        $max_order_row = $max_order_result->fetch_assoc();
        $field_order = $max_order_row ? $max_order_row['max_order'] + 1 : 1;

        $stmt = $mysqli->prepare("INSERT INTO form_fields (field_name, field_type, created_by, field_order) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssii", $field_name, $field_type, $admin_id, $field_order);
        $stmt->execute();

        $_SESSION['toastMsg'] = "Field '$field_name' added successfully!";
        $_SESSION['toastType'] = "success";
        header("Location: admin_dashboard.php");
        exit();
    }
}


    // Delete field
    if (isset($_GET['delete'])) {
    $field_id = intval($_GET['delete']);
    $stmt = $mysqli->prepare("DELETE FROM form_fields WHERE id=?");
    $stmt->bind_param("i", $field_id);
    $stmt->execute();

    $_SESSION['toastMsg'] = "Field deleted successfully!";
    $_SESSION['toastType'] = "success";
    header("Location: admin_dashboard.php");
    exit();
}

    // Update field
    if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['edit_field'])) {
    $field_id   = intval($_POST['field_id']);
    $field_name = trim($_POST['field_name']);
    $field_type = $_POST['field_type'];

    if ($field_id && $field_name && $field_type) {
        $stmtCheck = $mysqli->prepare("SELECT id FROM form_fields WHERE field_name = ? AND id != ?");
        $stmtCheck->bind_param("si", $field_name, $field_id);
        $stmtCheck->execute();
        $stmtCheck->store_result();
        if ($stmtCheck->num_rows > 0) {
            $_SESSION['toastMsg'] = "Field '$field_name' already exists!";
            $_SESSION['toastType'] = "error";
            header("Location: admin_dashboard.php");
            exit();
        }

        $stmt = $mysqli->prepare("UPDATE form_fields SET field_name=?, field_type=? WHERE id=?");
        $stmt->bind_param("ssi", $field_name, $field_type, $field_id);
        $stmt->execute();

        $_SESSION['toastMsg'] = "Field updated successfully!";
        $_SESSION['toastType'] = "success";
        header("Location: admin_dashboard.php");
        exit();
    }
}
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['order'])) {
    foreach ($_POST['order'] as $position => $id) {
        $stmt = $mysqli->prepare("UPDATE form_fields SET field_order=? WHERE id=?");
        $pos = $position + 1;
        $stmt->bind_param("ii", $pos, $id);
        $stmt->execute();
    }
    $_SESSION['toastMsg'] = "Field order saved successfully!";
    $_SESSION['toastType'] = "success";
    header("Location: admin_dashboard.php");
    exit();
}
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
    <meta charset="UTF-8">
    <title>Admin Dashboard</title>
    <link rel="shortcut icon" href="assets/website.png" type="image/x-icon">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://code.jquery.com/ui/1.13.2/themes/base/jquery-ui.css">
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://code.jquery.com/ui/1.13.2/jquery-ui.min.js"></script>
    <style>
    #fieldsTable { table-layout: fixed; width: 100%; }
    #fieldsTable td, #fieldsTable th { white-space: nowrap; }
    .ui-state-highlight { background-color: #f0f0f0; border: 2px dashed #999 !important; display: table-row; }
    </style>
    </head>
    <body class="bg-light">

    <div class="container mt-5">
        <div class="card shadow p-4 mb-4">
            <h2 class="mb-4">Welcome Admin, <?php echo htmlspecialchars($_SESSION['user']); ?> 👋</h2>
            <div class="mb-4">
        <form id="adminsubmissionRedirectForm" action="data-pages/admin_submissions.php" method="POST" style="display:none;">
            <input type="hidden" name="fromLogin" value="1">
        </form>

        <button class="btn btn-outline-primary" onclick="document.getElementById('adminsubmissionRedirectForm').submit(); return false;">
            📑 Go to Admin Submissions
        </button>
    </div>

            <h4>Add New Form Field</h4>
            <form method="post">
                <div class="mb-3">
                    <label class="form-label">Field Name</label>
                    <input type="text" class="form-control" name="field_name" >
                </div>
                <div class="mb-3">
                    <label class="form-label">Field Type</label>
                    <select class="form-control" name="field_type" >
                        <option value="text">Text</option>
                        <option value="email">Email</option>
                        <option value="number">Number</option>
                        <option value="password">Password</option>
                        <option value="date">Date</option>
                    </select>
                </div>
                <button class="btn btn-success" type="submit" name="add_field">Add Field</button>
            </form>
        </div>

        <div class="card shadow p-4">
            <h4>Existing Fields</h4>
            <form method="post" action="admin_dashboard.php" id="orderForm">
                <table class="table table-bordered mt-3" id="fieldsTable">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Field Name</th>
                            <th>Field Type</th>
                            <th>Added By</th>
                            <th>Created At</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="sortable-fields">
                    <?php
                    $index = 1;
                    $result = $mysqli->query("
                        SELECT ff.*, u.username AS added_by
                        FROM form_fields ff
                        LEFT JOIN cruddemo u ON ff.created_by = u.id
                        ORDER BY field_order ASC
                    ");
                    while($row = $result->fetch_assoc()) {
                        echo "<tr data-id='{$row['id']}'>";
                        echo "<td>".$index."</td>";
                        echo "<td>".htmlspecialchars($row['field_name'])."</td>";
                        echo "<td>".htmlspecialchars($row['field_type'])."</td>";
                        echo "<td>".htmlspecialchars($row['added_by'] ?? 'Unknown')."</td>";
                        echo "<td>".htmlspecialchars($row['created_at'])."</td>";
                        echo "<td>
                                <a href='?delete={$row['id']}' class='btn btn-sm btn-danger'>Delete</a>
                                <button type='button' class='btn btn-sm btn-primary' data-bs-toggle='modal' data-bs-target='#editModal{$row['id']}'>Edit</button>
                            </td>";
                        echo "<input type='hidden' name='order[]' value='{$row['id']}'>";
                        echo "</tr>";

                        // Edit modal
                        echo "
                        <div class='modal fade' id='editModal{$row['id']}' tabindex='-1'>
                        <div class='modal-dialog'>
                            <div class='modal-content'>
                            <form method='post'>
                                <div class='modal-header'>
                                <h5 class='modal-title'>Edit Field</h5>
                                <button type='button' class='btn-close' data-bs-dismiss='modal'></button>
                                </div>
                                <div class='modal-body'>
                                <input type='hidden' name='field_id' value='{$row['id']}'>
                                <div class='mb-3'>
                                    <label class='form-label'>Field Name</label>
                                    <input type='text' class='form-control' name='field_name' value='".htmlspecialchars($row['field_name'])."' required>
                                </div>
                                <div class='mb-3'>
                                    <label class='form-label'>Field Type</label>
                                    <select class='form-control' name='field_type' required>
                                    <option ".($row['field_type']=='text'?'selected':'')." value='text'>Text</option>
                                    <option ".($row['field_type']=='email'?'selected':'')." value='email'>Email</option>
                                    <option ".($row['field_type']=='number'?'selected':'')." value='number'>Number</option>
                                    <option ".($row['field_type']=='password'?'selected':'')." value='password'>Password</option>
                                    <option ".($row['field_type']=='date'?'selected':'')." value='date'>Date</option>
                                    </select>
                                </div>
                                </div>
                                <div class='modal-footer'>
                                <button type='button' class='btn btn-secondary' data-bs-dismiss='modal'>Close</button>
                                <button type='submit' name='edit_field' class='btn btn-primary'>Save Changes</button>
                                </div>
                            </form>
                            </div>
                        </div>
                        </div>";
                        $index++;
                    }
                    ?>
                    </tbody>
                </table>
                <div class="d-flex justify-content-between">
                    <button type="submit" class="btn btn-success mt-2">Save Order</button>
                <b class="text-warning">tip-(click and drag ↑  ↓ to move rows)</b>
                </div>
            </form>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    $(function() {
        $("#sortable-fields").sortable({
            axis: "y",
            containment: "parent",
            helper: function(e, tr) {
                var $originals = tr.children();
                var $helper = tr.clone();
                $helper.children().each(function(index){
                    $(this).width($originals.eq(index).width());
                });
                $helper.height(tr.height());
                return $helper;
            },
            placeholder: "ui-state-highlight",
            forcePlaceholderSize: true,
            tolerance: "pointer", 
            start: function(e, ui) {
                ui.placeholder.height(ui.item.height());
            },
            update: function(event, ui) {
                $("#sortable-fields tr").each(function(i, tr){
                    $(tr).find("input[name='order[]']").val($(tr).data("id"));
                });
            }
        });
    });

    </script>
<div id="toastContainer"></div>
<?php include "data-pages/toast.php"; ?>
    </body>
    </html>
