<?php require 'includes/session.php';
if($_POST){
  $stmt=$conn->prepare('UPDATE elections SET title=?, description=?, starts_at=?, ends_at=?, status=? WHERE id=?');
  $id=(int)$_POST['id'];
  $stmt->bind_param('sssssi', $_POST['title'], $_POST['description'], $_POST['starts_at'], $_POST['ends_at'], $_POST['status'], $id);
  $ok=$stmt->execute(); $_SESSION[$ok?'success':'error']=$ok?'Election updated':'Failed to update election';
}
header('Location: elections.php');

?>