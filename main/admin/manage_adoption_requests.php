<?php
// main/admin/manage_adoption_requests.php
$pageTitle = "Manage Adoption Requests";
require_once('auth_check.php'); // Ensures admin is logged in

$status_filter = $_GET['status_filter'] ?? 'Pending'; // Default to show Pending requests
$allowed_statuses = ['Pending', 'Approved', 'Rejected', 'Cancelled', 'All'];
if (!in_array($status_filter, $allowed_statuses)) {
    $status_filter = 'Pending'; // Fallback to default if invalid filter
}

$message = '';
$message_type = ''; // 'success' or 'error'

// Handle processing actions (Approve/Reject)
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action'])) {
    // CSRF token validation needed here
    $request_id_to_process = filter_input(INPUT_POST, 'request_id', FILTER_VALIDATE_INT);
    $admin_user_id = $_SESSION['user_id']; // Assuming admin's user_id is in session
    $admin_notes_from_form = trim($_POST['admin_notes'] ?? '');

    if ($request_id_to_process) {
        $new_request_status = '';
        if ($_POST['action'] === 'approve_request') {
            $new_request_status = 'Approved';
        } elseif ($_POST['action'] === 'reject_request') {
            $new_request_status = 'Rejected';
        }

        if (!empty($new_request_status)) {
            $processed = processAdoptionRequest($request_id_to_process, $new_request_status, $admin_user_id, $admin_notes_from_form);
            if ($processed) {
                $message = "Request ID {$request_id_to_process} has been " . strtolower($new_request_status) . ".";
                $message_type = 'success';
            } else {
                $message = "Failed to process request ID {$request_id_to_process}.";
                $message_type = 'error';
            }
        }
    } else {
        $message = "Invalid request ID for processing.";
        $message_type = 'error';
    }
}


// Fetch requests based on filter
$requests = getAdoptionRequestsAdmin($status_filter === 'All' ? null : $status_filter);

require_once('partials/header.php');
?>

<h1><?php echo $pageTitle; ?></h1>

<?php if ($message): ?>
    <div class="message <?php echo ($message_type === 'success' ? 'success-message' : 'error-message'); ?>">
        <?php echo htmlspecialchars($message); ?>
    </div>
<?php endif; ?>

<div class="admin-table-section">
    <div style="margin-bottom: 1rem; padding-bottom:1rem; border-bottom:1px solid var(--divider-border);">
        <form action="manage_adoption_requests.php" method="GET" style="display:flex; gap:1rem; align-items:center;">
            <label for="status_filter">Filter by Status:</label>
            <select name="status_filter" id="status_filter" onchange="this.form.submit()" class="form-group" style="padding:0.5rem; min-width:150px; margin-bottom:0;">
                <option value="Pending" <?php echo ($status_filter === 'Pending') ? 'selected' : ''; ?>>Pending</option>
                <option value="Approved" <?php echo ($status_filter === 'Approved') ? 'selected' : ''; ?>>Approved</option>
                <option value="Rejected" <?php echo ($status_filter === 'Rejected') ? 'selected' : ''; ?>>Rejected</option>
                <option value="Cancelled" <?php echo ($status_filter === 'Cancelled') ? 'selected' : ''; ?>>Cancelled</option>
                <option value="All" <?php echo ($status_filter === 'All') ? 'selected' : ''; ?>>Show All</option>
            </select>
        </form>
    </div>

    <?php if (empty($requests)): ?>
        <p>No adoption requests found<?php echo ($status_filter !== 'All' ? ' with status: ' . htmlspecialchars($status_filter) : ''); ?>.</p>
    <?php else: ?>
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Req. ID</th>
                    <th>Pet Name</th>
                    <th>Adopter Name</th>
                    <th>Request Date</th>
                    <th>Status</th>
                    <th>Adopter Msg.</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($requests as $request): ?>
                    <tr>
                        <td><?php echo $request['request_id']; ?></td>
                        <td>
                            <a href="edit_pet.php?id=<?php echo $request['pet_id']; ?>" title="View Pet">
                                <?php echo htmlspecialchars($request['pet_name']); ?>
                            </a>
                        </td>
                        <td>
                            <a href="edit_user.php?id=<?php echo $request['adopter_user_id']; ?>" title="View User">
                                <?php echo htmlspecialchars($request['adopter_name']); ?>
                            </a><br>
                            <small><?php echo htmlspecialchars($request['adopter_email']); ?></small>
                        </td>
                        <td><?php echo date('M d, Y H:i', strtotime($request['request_date'])); ?></td>
                        <td>
                            <span class="health-status <?php echo strtolower(htmlspecialchars($request['status'])); ?>">
                                <?php echo htmlspecialchars($request['status']); ?>
                            </span>
                        </td>
                        <td style="max-width:200px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;" title="<?php echo htmlspecialchars($request['adopter_message'] ?? ''); ?>">
                            <?php echo htmlspecialchars(substr($request['adopter_message'] ?? 'N/A', 0, 50)) . (strlen($request['adopter_message'] ?? '') > 50 ? '...' : ''); ?>
                        </td>
                        <td class="actions">
                            <?php if ($request['status'] === 'Pending'): ?>
                                <form action="manage_adoption_requests.php?status_filter=<?php echo htmlspecialchars($status_filter); ?>" method="POST" style="display: inline-block; margin-bottom: 5px;">
                                    <input type="hidden" name="request_id" value="<?php echo $request['request_id']; ?>">
                                    <input type="hidden" name="admin_notes" value=""> <button type="submit" name="action" value="approve_request" class="action-btn view" onclick="return confirm('Are you sure you want to APPROVE this adoption request? This will mark the pet as adopted.');">Approve</button>
                                </form>
                                <form action="manage_adoption_requests.php?status_filter=<?php echo htmlspecialchars($status_filter); ?>" method="POST" style="display: inline-block;">
                                    <input type="hidden" name="request_id" value="<?php echo $request['request_id']; ?>">
                                     <input type="hidden" name="admin_notes" value=""> <button type="submit" name="action" value="reject_request" class="action-btn delete" onclick="return confirm('Are you sure you want to REJECT this adoption request?');">Reject</button>
                                </form>
                            <?php else: ?>
                                <small>Processed by <?php echo htmlspecialchars($request['processed_by_admin_name'] ?? 'N/A'); ?></small>
                            <?php endif; ?>
                            </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<?php
require_once('partials/footer.php');
?>
