<?php include 'includes/session.php';
if(isset($_POST['upload'])){
  $id=(int)$_POST['id']; $filename=$_FILES['photo']['name']; if(!empty($filename)){ move_uploaded_file($_FILES['photo']['tmp_name'],'../images/'.$filename);} else { $filename=''; }
  $upd=$conn->prepare('UPDATE candidates SET photo=? WHERE id=? AND election_id=?');
  $upd->bind_param('sii',$filename,$id,$adminElectionId);
  $_SESSION[$upd->execute()?'success':'error']=$upd->execute()?'Photo updated':'Failed to update photo';
}else{ $_SESSION['error']='Select candidate to update photo first'; }
header('Location: candidates.php');
?>