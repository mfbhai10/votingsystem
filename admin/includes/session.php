<?php
include __DIR__.'/../includes/conn.php';
session_start();

if (!isset($_SESSION['admin'])) {
  header('Location: index.php');
  exit();
}

// Load admin user
$stmt = $conn->prepare('SELECT * FROM admin WHERE id=?');
$stmt->bind_param('i', $_SESSION['admin']);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

// Ensure admin election context
if (!isset($_SESSION['admin_election_id'])) {
  // Default to newest open election, else newest any
  $res = $conn->query("SELECT id FROM elections WHERE status='open' ORDER BY created_at DESC LIMIT 1");
  if ($res && $res->num_rows) {
    $_SESSION['admin_election_id'] = (int)$res->fetch_assoc()['id'];
  } else {
    $res2 = $conn->query("SELECT id FROM elections ORDER BY created_at DESC LIMIT 1");
    if ($res2 && $res2->num_rows) {
      $_SESSION['admin_election_id'] = (int)$res2->fetch_assoc()['id'];
    }
  }
}

$adminElectionId = isset($_SESSION['admin_election_id']) ? (int)$_SESSION['admin_election_id'] : null;
$admin_election = null;
if ($adminElectionId) {
  $e = $conn->prepare('SELECT * FROM elections WHERE id=?');
  $e->bind_param('i', $adminElectionId);
  $e->execute();
  $admin_election = $e->get_result()->fetch_assoc();
}
	
?>