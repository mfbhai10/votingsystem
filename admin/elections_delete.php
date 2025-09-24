<?php require 'includes/session.php';
if(isset($_GET['id'])){
  $id=(int)$_GET['id'];
  $stmt=$conn->prepare('DELETE FROM elections WHERE id=?');
  $stmt->bind_param('i',$id);
  $ok=$stmt->execute(); $_SESSION[$ok?'success':'error']=$ok?'Election deleted':'Failed to delete election';
}
header('Location: elections.php');

?>