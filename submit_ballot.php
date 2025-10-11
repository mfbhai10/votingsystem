<?php
require 'includes/session.php';
require 'includes/slugify.php';

// Ensure the voter has selected at least one candidate
if (!isset($_POST['vote'])) { 
    $_SESSION['error'][] = 'Select candidates to vote first'; 
    header('Location: home.php'); 
    exit; 
}
if (count($_POST) == 1) { 
    $_SESSION['error'][] = 'Please vote at least one candidate'; 
    header('Location: home.php'); 
    exit; }

// Ensure that the voter is voting in their assigned election
$electionId = (int)$_SESSION['election_id'];  // Election the voter is registered to vote in
if ($electionId <= 0) {
    $_SESSION['error'][] = 'No election selected or you are not registered for this election.';
    header('Location: choose_election.php');
    exit;
}

$_SESSION['post'] = $_POST;  // Store post data for later reference

$error = false; 
$sql_inserts = [];

// Fetch all positions for the election
$pstmt = $conn->prepare('SELECT id, description, max_vote FROM positions WHERE election_id=? ORDER BY priority ASC');
$pstmt->bind_param('i', $electionId);
$pstmt->execute();
$positions = $pstmt->get_result();

// Loop through positions to validate the votes
while ($row = $positions->fetch_assoc()) {
    $position = slugify($row['description']);
    $pos_id = (int)$row['id'];

    if (isset($_POST[$position])) {
        if ($row['max_vote'] > 1) {
            if (count($_POST[$position]) > $row['max_vote']) {
                $error = true;
                $_SESSION['error'][] = 'You can only choose '.$row['max_vote'].' candidates for '.$row['description'];
            } else {
                foreach ($_POST[$position] as $cid) {
                    $sql_inserts[] = [(int)$cid, $pos_id];
                }
            }
        } else {
            $cid = (int)$_POST[$position];
            $sql_inserts[] = [$cid, $pos_id];
        }
    }
}

// If any validation error occurred, redirect to home
if ($error) { 
    header('Location: home.php'); 
    exit; 
}

$conn->begin_transaction();

try {
    // Check if the voter has already voted in this election
    $check = $conn->prepare('SELECT 1 FROM votes WHERE election_id=? AND voters_id=? LIMIT 1');
    $check->bind_param('ii', $electionId, $voter['id']);
    $check->execute();
    if ($check->get_result()->num_rows) {
        throw new Exception('You have already voted in this election.');
    }

    // Insert the votes into the database
    $ins = $conn->prepare('INSERT INTO votes (election_id, voters_id, candidate_id, position_id) VALUES (?,?,?,?)');
    foreach ($sql_inserts as [$cid, $pid]) {
        $ins->bind_param('iiii', $electionId, $voter['id'], $cid, $pid);
        $ins->execute();
    }
    
    $conn->commit();
    unset($_SESSION['post']);
    $_SESSION['success'] = 'Ballot Submitted';
} catch (Exception $e) {
    $conn->rollback();
    $_SESSION['error'][] = 'Submission failed: '.$e->getMessage();
}

header('Location: home.php');
?>