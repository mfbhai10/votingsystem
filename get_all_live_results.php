<?php
include 'includes/conn.php';

header('Content-Type: application/json');

$output = [];

// 1. Get all active elections
$election_sql = "SELECT id, title FROM elections WHERE status = 'open'";
$election_query = $conn->query($election_sql);

while($election_row = $election_query->fetch_assoc()){
    $election_data = [
        'election_id' => $election_row['id'],
        'election_title' => $election_row['title'],
        'positions' => []
    ];

    // 2. For each election, get its positions
    $position_sql = "SELECT id, description FROM positions WHERE election_id = '".$election_row['id']."' ORDER BY priority ASC";
    $position_query = $conn->query($position_sql);

    while($position_row = $position_query->fetch_assoc()){
        $position_data = [
            'position_id' => $position_row['id'],
            'position' => $position_row['description'],
            'candidates' => []
        ];
        
        // 3. For each position, get its candidates and their vote counts
        $candidate_sql = "
            SELECT c.id, c.firstname, c.lastname, COUNT(v.id) as vote_count
            FROM candidates c
            LEFT JOIN votes v ON c.id = v.candidate_id
            WHERE c.position_id = '".$position_row['id']."'
            GROUP BY c.id
            ORDER BY vote_count DESC
        ";
        $candidate_query = $conn->query($candidate_sql);

        while($candidate_row = $candidate_query->fetch_assoc()){
            $position_data['candidates'][] = [
                'candidate' => $candidate_row['firstname'] . ' ' . $candidate_row['lastname'],
                'vote_count' => $candidate_row['vote_count']
            ];
        }
        $election_data['positions'][] = $position_data;
    }
    $output[] = $election_data;
}

echo json_encode($output);

?>