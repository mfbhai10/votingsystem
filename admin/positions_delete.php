<?php include 'includes/session.php';
if(isset($_POST['delete'])){
  $id=(int)$_POST['id'];
  $del=$conn->prepare('DELETE FROM positions WHERE id=? AND election_id=?');
  $del->bind_param('ii',$id,$adminElectionId);
  $_SESSION[$del->execute()?'success':'error']=$del->execute()?'Position deleted':'Failed to delete position';
}else{ $_SESSION['error']='Select item to delete first'; }
header('Location: positions.php');
	
?>