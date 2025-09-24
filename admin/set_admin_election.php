<?php require 'includes/session.php';
if(!isset($_GET['id'])){ header('Location: elections.php'); exit; }
$_SESSION['admin_election_id'] = (int)$_GET['id'];
header('Location: elections.php');
?>