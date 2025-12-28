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

// Check if there are any positions before starting the loop
if ($positions->num_rows === 0) {
    $resultsHtml = "<div class='col-xs-12'><p class='text-center text-muted'>No positions found for this election.</p></div>";
}

while ($position = $positions->fetch_assoc()) {
    $pos_id = $position['id'];
    
    // Fetching the candidate with the most votes for each position
    // NOTE: This logic only retrieves the winner (LIMIT 1) dichi ei jonno
    $cstmt = $conn->prepare('
        SELECT c.id, c.firstname, c.lastname, COUNT(v.candidate_id) AS vote_count
        FROM candidates c
        LEFT JOIN votes v ON c.id = v.candidate_id AND v.position_id = ? AND v.election_id = ?
        WHERE c.position_id = ? AND c.election_id = ?
        GROUP BY c.id
        ORDER BY vote_count DESC
        LIMIT 1
    ');
    $cstmt->bind_param('iiii', $pos_id, $electionId, $pos_id, $electionId);
    $cstmt->execute();
    $winner = $cstmt->get_result()->fetch_assoc();
    
    // Determine the winner's name and vote count safely
    $winner_name = $winner ? htmlspecialchars($winner['firstname'] . ' ' . $winner['lastname']) : 'No votes recorded.';
    $vote_count = $winner ? $winner['vote_count'] : 0;
    
    // --- START OF NEW UI FOR EACH POSITION ---
    $resultsHtml .= "
        <div class='col-md-4 col-sm-6'>
            <div class='box box-widget widget-user-2'>
                <div class='widget-user-header bg-aqua-active'> 
                    <div class='widget-user-image'>
                        <i class='fa fa-gavel fa-4x' style='color: white; padding-top: 10px;'></i>
                    </div>
                    <h3 class='widget-user-username'>" . htmlspecialchars($position['description']) . "</h3>
                    <h5 class='widget-user-desc'>Election Result</h5>
                </div>
                <div class='box-footer no-padding'>
                    <ul class='nav nav-stacked'>
                        <li>
                            <a href='#' style='font-size: 1.2em; color: #333;'>
                                <i class='fa fa-trophy text-yellow'></i> <strong>WINNER:</strong> 
                                <span class='pull-right badge bg-green'>$winner_name</span>
                            </a>
                        </li>
                        <li>
                            <a href='#' style='font-size: 1.1em; color: #555;'>
                                <i class='fa fa-check-circle'></i> Total Votes:
                                <span class='pull-right badge bg-blue'>$vote_count</span>
                            </a>
                        </li>
                        <li style='border-top: 1px solid #eee;'>
                             <a href='full_results.php?election_id=$electionId&position_id=$pos_id' class='text-center' style='padding-top: 8px; padding-bottom: 8px; background-color: #f7f7f7;'>
                                <i class='fa fa-bar-chart'></i> View Detailed Breakdown
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    ";
}

// Output the results HTML inside a responsive container
echo "
<div class='container' style='padding-top: 20px;'>
    <h2 class='text-center page-header'>ELECTION WINNERS SUMMARY</h2>
    <div class='row' style='margin-bottom: 30px;'>
        $resultsHtml
    </div>
</div>";
?>