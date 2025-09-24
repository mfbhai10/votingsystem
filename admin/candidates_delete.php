<?php include 'includes/session.php';
if(isset($_POST['delete'])){
  $id=(int)$_POST['id'];
  $del=$conn->prepare('DELETE FROM candidates WHERE id=? AND election_id=?');
  $del->bind_param('ii',$id,$adminElectionId);
  $_SESSION[$del->execute()?'success':'error']=$del->execute()?'Candidate deleted':'Failed to delete candidate';
}else{ $_SESSION['error']='Select item to delete first'; }
header('Location: candidates.php');
	
?>