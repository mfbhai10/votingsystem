<?php
require 'includes/session.php';
require 'includes/slugify.php';

$output = ['error'=>false,'list'=>''];
$electionId = (int)$current_election['id'];

$pstmt = $conn->prepare('SELECT * FROM positions WHERE election_id=? ORDER BY priority ASC');
$pstmt->bind_param('i', $electionId);
$pstmt->execute();
$positions = $pstmt->get_result();

while($row = $positions->fetch_assoc()){
    $position = slugify($row['description']);
    if(isset($_POST[$position])){
        if($row['max_vote'] > 1){
            if(count($_POST[$position]) > $row['max_vote']){
                $output['error'] = true;
                $output['message'][] = '<li>You can only choose '.$row['max_vote'].' candidates for '.htmlspecialchars($row['description']).'</li>';
            } else {
                foreach($_POST[$position] as $values){
                    $cs = $conn->prepare('SELECT firstname, lastname FROM candidates WHERE election_id=? AND id=?');
                    $cid = (int)$values; $cs->bind_param('ii', $electionId, $cid); $cs->execute(); $cmrow = $cs->get_result()->fetch_assoc();
                    $output['list'] .= "<div class='row votelist'><span class='col-sm-4'><span class='pull-right'><b>".htmlspecialchars($row['description'])." :</b></span></span><span class='col-sm-8'>".htmlspecialchars($cmrow['firstname'].' '.$cmrow['lastname'])."</span></div>";
                }
            }
        } else {
            $cid = (int)$_POST[$position];
            $cs = $conn->prepare('SELECT firstname, lastname FROM candidates WHERE election_id=? AND id=?');
            $cs->bind_param('ii', $electionId, $cid); $cs->execute(); $csrow = $cs->get_result()->fetch_assoc();
            $output['list'] .= "<div class='row votelist'><span class='col-sm-4'><span class='pull-right'><b>".htmlspecialchars($row['description'])." :</b></span></span><span class='col-sm-8'>".htmlspecialchars($csrow['firstname'].' '.$csrow['lastname'])."</span></div>";
        }
    }
}

echo json_encode($output);

?>