<?php include 'includes/session.php';
if(isset($_POST['edit'])){
  $id=(int)$_POST['id']; $desc=$_POST['description']; $max=(int)$_POST['max_vote'];
  $upd=$conn->prepare('UPDATE positions SET description=?, max_vote=? WHERE id=? AND election_id=?');
  $upd->bind_param('siii',$desc,$max,$id,$adminElectionId);
  $_SESSION[$upd->execute()?'success':'error']=$upd->execute()?'Position updated':'Failed to update position';
}else{ $_SESSION['error']='Fill up edit form first'; }
header('Location: positions.php');

?>