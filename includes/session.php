<?php
require __DIR__.'/conn.php';
session_start();

// Debugging: Check if the session variable 'voter' is set
if (isset($_SESSION['voter'])) {
    $stmt = $conn->prepare('SELECT * FROM voters WHERE id = ?');
    $stmt->bind_param('i', $_SESSION['voter']);
    $stmt->execute();
    $voter = $stmt->get_result()->fetch_assoc();

    // Debugging: Check if voter data is being fetched
    if (!$voter) {
        echo 'Error: No voter data found.';
        exit;
    }

    // Ensure a chosen election for voter pages except index/login/chooser
    $script = basename($_SERVER['PHP_SELF']);
    $public = ['index.php','login.php'];
    $chooser = ['choose_election.php','set_election.php'];

    if (!in_array($script, $public) && !in_array($script, $chooser)) {
        if (empty($_SESSION['election_id'])) {
            header('Location: choose_election.php');
            exit();
        } else {
            // Load election for templates
            $electionId = (int)$_SESSION['election_id'];
            $estmt = $conn->prepare('SELECT * FROM elections WHERE id=?');
            $estmt->bind_param('i', $electionId);
            $estmt->execute();
            $current_election = $estmt->get_result()->fetch_assoc();
        }
    }
} else {
    echo 'Error: Voter not logged in!';
    header('Location: index.php');
    exit();
}
?>