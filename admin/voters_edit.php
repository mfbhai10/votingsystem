<?php include 'includes/session.php';
if(isset($_POST['edit'])){
  $id = (int)$_POST['id'];
  $voters_id = trim($_POST['voters_id']);
  $firstname = trim($_POST['firstname']);
  $lastname  = trim($_POST['lastname']);

  if (!empty($_POST['password'])) {
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $stmt = $conn->prepare('UPDATE voters SET voters_id=?, firstname=?, lastname=?, password=? WHERE id=?');
    $stmt->bind_param('ssssi', $voters_id, $firstname, $lastname, $password, $id);
  } else {
    $stmt = $conn->prepare('UPDATE voters SET voters_id=?, firstname=?, lastname=? WHERE id=?');
    $stmt->bind_param('sssi', $voters_id, $firstname, $lastname, $id);
  }

  $_SESSION[$stmt->execute()?'success':'error'] = $stmt->execute() ? 'Voter updated' : 'Failed to update voter';
} else { $_SESSION['error'] = 'Fill up edit form first'; }
header('Location: voters.php');

?>