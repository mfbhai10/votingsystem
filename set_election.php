<?php
require 'includes/session.php';
if (!isset($_GET['id'])) { header('Location: choose_election.php'); exit; }
$electionId = (int)$_GET['id'];

$stmt = $conn->prepare("SELECT id FROM elections WHERE id=? AND status='open' AND starts_at <= NOW() AND ends_at >= NOW()");
$stmt->bind_param('i', $electionId);
$stmt->execute();
if ($stmt->get_result()->num_rows === 1) {
  $_SESSION['election_id'] = $electionId;
  header('Location: home.php');
} else {
  $_SESSION['error'] = 'Election is not open.';
  header('Location: choose_election.php');
}

?>