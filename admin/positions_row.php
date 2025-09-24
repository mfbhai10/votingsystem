<?php include 'includes/session.php';
if(isset($_POST['id'])){
  $id=(int)$_POST['id'];
  $stmt=$conn->prepare('SELECT * FROM positions WHERE id=? AND election_id=?');
  $stmt->bind_param('ii',$id,$adminElectionId); $stmt->execute();
  echo json_encode($stmt->get_result()->fetch_assoc());
}
?>