<?php
include 'includes/session.php'; // Ensure this loads voter data, including $voter['id']

$election_id = $_GET['id'] ?? 0;
$voter_id = $voter['id'] ?? 0;

if ($election_id > 0 && $voter_id > 0) {
    // SECURITY CHECK: Verify the voter is actually registered for this election
    $stmt = $conn->prepare("SELECT 1 FROM voter_elections WHERE voter_id = ? AND election_id = ?");
    $stmt->bind_param('ii', $voter_id, $election_id);
    $stmt->execute();
    
    if ($stmt->get_result()->num_rows > 0) {
        // SUCCESS: Set the session and redirect to the voting page
        $_SESSION['election_id'] = $election_id;
        header('location: home.php');
        exit;
    }
}

// FAILURE: If verification fails or parameters are missing, redirect back
$_SESSION['error'] = 'Invalid election selection or you are not registered for it.';
header('location: choose_election.php');
exit;