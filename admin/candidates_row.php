<?php include 'includes/session.php';
if(isset($_POST['id'])){
  $id=(int)$_POST['id'];
  $sql='SELECT c.*, p.description FROM candidates c JOIN positions p ON p.id=c.position_id WHERE c.id=? AND c.election_id=? AND p.election_id=?';
  $st=$conn->prepare($sql); $st->bind_param('iii',$id,$adminElectionId,$adminElectionId); $st->execute();
  echo json_encode($st->get_result()->fetch_assoc());
}
?>