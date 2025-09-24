<?php
// admin/positions_add.php
include 'includes/session.php';

if (isset($_POST['add'])) {
    $description = trim($_POST['description'] ?? '');
    $max_vote    = (int)($_POST['max_vote'] ?? 1);
    $electionId  = (int)($_SESSION['admin_election_id'] ?? 0);

    if ($electionId <= 0 || $description === '' || $max_vote <= 0) {
        $_SESSION['error'] = 'Invalid input or no election selected.';
        header('Location: positions.php'); exit;
    }

    // এই ইলেকশনের মধ্যে সর্বশেষ priority বের করে +1
    $stmt = $conn->prepare("SELECT priority FROM positions WHERE election_id=? ORDER BY priority DESC LIMIT 1");
    $stmt->bind_param("i", $electionId);
    $stmt->execute();
    $res = $stmt->get_result();
    $row = $res->fetch_assoc();
    $priority = $row ? ((int)$row['priority'] + 1) : 1;

    // নতুন position ইনসার্ট (এই ইলেকশনে)
    $ins = $conn->prepare("INSERT INTO positions (description, max_vote, priority, election_id) VALUES (?,?,?,?)");
    $ins->bind_param("siii", $description, $max_vote, $priority, $electionId);

    if ($ins->execute()) {
        $_SESSION['success'] = 'Position added successfully.';
    } else {
        $_SESSION['error'] = 'Failed to add position.';
    }
} else {
    $_SESSION['error'] = 'Fill up add form first.';
}

header('Location: positions.php');
?>