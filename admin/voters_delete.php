<?php include 'includes/session.php';
if(isset($_POST['delete'])){
  $id = (int)$_POST['id'];
  $stmt = $conn->prepare('DELETE FROM voters WHERE id=?');
  $stmt->bind_param('i', $id);
  $_SESSION[$stmt->execute()?'success':'error'] = $stmt->execute() ? 'Voter deleted' : 'Failed to delete voter';
} else { $_SESSION['error'] = 'Select voter to delete first'; }
header('Location: voters.php');
	
?>