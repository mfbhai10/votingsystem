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

    $resultsHtml .= "
        <div class='col-md-4 col-sm-6 mb-4'>
            <div class='card' style='border-radius: 15px; box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);'>
                <div class='card-header' style='background-color: #f0f8ff;'>
                    <h5 class='card-title' style='color: #0056b3;'>
                        <i class='fa fa-trophy'></i> " . htmlspecialchars($position['description']) . "
                    </h5>
                </div>
                <div class='card-body'>
                    <h6 class='card-subtitle mb-2 text-muted'>Winner:</h6>
                    <p class='card-text'>
                        <b>" . htmlspecialchars($winner['firstname'] . ' ' . $winner['lastname']) . "</b><br>
                        Votes: " . $winner['vote_count'] . "
                    </p>
                    
                </div>
            </div>
        </div>
    ";
}

// Output the results HTML inside a responsive container
echo "
<div class='container my-5'>
    <h2 class='text-center mb-4' style='color: #0056b3;'>Election Results</h2>
    <div class='row'>
        $resultsHtml
    </div>
</div>";
?>