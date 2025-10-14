<?php
require 'includes/session.php';

// Fetch all open elections
$stmt = $conn->prepare("SELECT id, title FROM elections WHERE status='open' AND starts_at <= NOW() AND ends_at >= NOW() ORDER BY ends_at ASC");
$stmt->execute();
$elections = $stmt->get_result();

$results = [];

while ($election = $elections->fetch_assoc()) {
    $electionId = $election['id'];

    // Fetch positions for the election
    $stmt_pos = $conn->prepare('SELECT id, description FROM positions WHERE election_id=?');
    $stmt_pos->bind_param('i', $electionId);
    $stmt_pos->execute();
    $positions = $stmt_pos->get_result();

    $electionData = [
        'election_title' => htmlspecialchars($election['title']),
        'positions' => []
    ];

    while ($position = $positions->fetch_assoc()) {
        // Fetch candidates and their vote count
        $cstmt = $conn->prepare('
            SELECT c.id, c.firstname, c.lastname, COUNT(v.candidate_id) AS vote_count
            FROM candidates c
            LEFT JOIN votes v ON c.id = v.candidate_id AND v.position_id = ? AND v.election_id = ?
            WHERE c.position_id = ? AND c.election_id = ?
            GROUP BY c.id
            ORDER BY vote_count DESC
        ');
        $cstmt->bind_param('iiii', $position['id'], $electionId, $position['id'], $electionId);
        $cstmt->execute();
        $candidates = $cstmt->get_result();

        $positionData = [
            'position' => htmlspecialchars($position['description']),
            'candidates' => []
        ];

        while ($candidate = $candidates->fetch_assoc()) {
            $positionData['candidates'][] = [
                'candidate' => htmlspecialchars($candidate['firstname'] . ' ' . $candidate['lastname']),
                'vote_count' => $candidate['vote_count']
            ];
        }

        $electionData['positions'][] = $positionData;
    }

    $results[] = $electionData;
}

echo json_encode($results);
?>