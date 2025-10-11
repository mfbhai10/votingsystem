<?php
require 'includes/session.php';

// Check if the voter is authorized
if (!isset($_SESSION['voter'])) {
    $_SESSION['error'][] = 'You do not have permission to view election results.';
    exit;
}

// Get the election_id from the request
$electionId = (int)$_GET['election_id'];

// Fetch positions and candidates for the given election
$stmt = $conn->prepare('SELECT id, description FROM positions WHERE election_id=? ORDER BY priority ASC');
$stmt->bind_param('i', $electionId);
$stmt->execute();
$positions = $stmt->get_result();

$resultsHtml = '';

while ($position = $positions->fetch_assoc()) {
    // Fetch the candidate with the most votes for each position
    $cstmt = $conn->prepare('
        SELECT c.id, c.firstname, c.lastname, COUNT(v.candidate_id) AS vote_count
        FROM candidates c
        LEFT JOIN votes v ON c.id = v.candidate_id AND v.position_id = ? AND v.election_id = ?
        WHERE c.position_id = ? AND c.election_id = ?
        GROUP BY c.id
        ORDER BY vote_count DESC
        LIMIT 1
    ');
    $cstmt->bind_param('iiii', $position['id'], $electionId, $position['id'], $electionId);
    $cstmt->execute();
    $winner = $cstmt->get_result()->fetch_assoc();

    $resultsHtml .= "<h5>" . htmlspecialchars($position['description']) . ":</h5>";
    $resultsHtml .= "<p><b>" . htmlspecialchars($winner['firstname'] . ' ' . $winner['lastname']) . "</b> with " . $winner['vote_count'] . " votes</p>";
}

// Output the results HTML
echo $resultsHtml;
?>