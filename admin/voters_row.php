<?php
include 'includes/session.php';

if (isset($_POST['id'])) {
    $id = (int)$_POST['id'];

    // Prepare the query to fetch voter details along with election information
    $stmt = $conn->prepare('
        SELECT v.*, e.title AS election_title 
        FROM voters v
        LEFT JOIN elections e ON v.election_id = e.id
        WHERE v.id = ?
    ');
    $stmt->bind_param('i', $id);
    $stmt->execute();

    // Fetch the result and return as JSON
    $result = $stmt->get_result()->fetch_assoc();
    echo json_encode($result);
}
?>