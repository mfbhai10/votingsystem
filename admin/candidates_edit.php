<?php include 'includes/session.php';
if(isset($_POST['edit'])){
  $id=(int)$_POST['id']; $firstname=$_POST['firstname']; $lastname=$_POST['lastname']; $position=(int)$_POST['position']; $platform=$_POST['platform'];
  $upd=$conn->prepare('UPDATE candidates SET firstname=?, lastname=?, position_id=?, platform=? WHERE id=? AND election_id=?');
  $upd->bind_param('ssisis',$firstname,$lastname,$position,$platform,$id,$adminElectionId);
  $_SESSION[$upd->execute()?'success':'error']=$upd->execute()?'Candidate updated':'Failed to update candidate';
}else{ $_SESSION['error']='Fill up edit form first'; }
header('Location: candidates.php');

?>