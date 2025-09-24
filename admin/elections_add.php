<?php require 'includes/session.php';
if($_POST){
  $stmt=$conn->prepare('INSERT INTO elections (title, description, starts_at, ends_at, status) VALUES (?,?,?,?,?)');
  $stmt->bind_param('sssss', $_POST['title'], $_POST['description'], $_POST['starts_at'], $_POST['ends_at'], $_POST['status']);
  $ok=$stmt->execute(); $_SESSION[$ok?'success':'error']=$ok?'Election created':'Failed to create election';
}
header('Location: elections.php');

?>