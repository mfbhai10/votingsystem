<?php
require 'includes/session.php';

if (!isset($_GET['id'])) { 
    header('Location: choose_election.php'); 
    exit; 
}

$requestedId = (int)$_GET['id'];

$voterElectionId = (int)$voter['election_id'];
if ($voterElectionId <= 0) {
  $_SESSION['error'] = 'No election is assigned to your account. Please contact admin.';
  header('Location: choose_election.php'); 
  exit;
}

// If they try to pick a different election, warn and bounce back
if ($requestedId !== $voterElectionId) {
  $_SESSION['error'] = 'You can only vote in your assigned election.';
  header('Location: choose_election.php'); 
  exit;
}

// Verify the selected election is open now
$stmt = $conn->prepare("
  SELECT id FROM elections 
  WHERE id=? AND status='open' AND starts_at <= NOW() AND ends_at >= NOW()
");
$stmt->bind_param('i', $requestedId);
$stmt->execute();

if ($stmt->get_result()->num_rows === 1) {
  // Lock the session to the voter’s assigned election
  $_SESSION['election_id'] = $requestedId;
  header('Location: home.php');
} else {
  $_SESSION['error'] = 'The selected election is not open.';
  header('Location: choose_election.php');
}