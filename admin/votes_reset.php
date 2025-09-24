<?php
// admin/votes_reset.php
include 'includes/session.php';

if (!isset($_SESSION['admin_election_id'])) {
    $_SESSION['error'] = 'No election selected.';
    header('location: votes.php');
    exit;
}

$electionId = (int)$_SESSION['admin_election_id'];

// শুধু এই ইলেকশনের ভোট ডিলিট হবে
$stmt = $conn->prepare("DELETE FROM votes WHERE election_id = ?");
$stmt->bind_param("i", $electionId);

if ($stmt->execute()) {
    $_SESSION['success'] = "Votes reset successfully for this election.";
} else {
    $_SESSION['error'] = "Something went wrong while resetting votes.";
}

header('location: votes.php');
exit;
?>