<?php include 'includes/session.php';
if(isset($_POST['upload'])){
  $id = (int)$_POST['id'];
  $filename = '';
  if (!empty($_FILES['photo']['name'])){
    $filename = basename($_FILES['photo']['name']);
    move_uploaded_file($_FILES['photo']['tmp_name'],'../images/'.$filename);
  }
  $stmt = $conn->prepare('UPDATE voters SET photo=? WHERE id=?');
  $stmt->bind_param('si', $filename, $id);
  $_SESSION[$stmt->execute()?'success':'error'] = $stmt->execute() ? 'Photo updated' : 'Failed to update photo';
} else { $_SESSION['error'] = 'Select voter to update photo first'; }
header('Location: voters.php');
?>