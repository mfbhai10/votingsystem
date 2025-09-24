<?php
include 'includes/session.php';

if (isset($_POST['id'])) {
    $id = $_POST['id'];

    // Initialize output to handle errors
    $output = array('error' => false);

    // Fetch the position's current priority and the position data
    $stmt = $conn->prepare('SELECT * FROM positions WHERE id = ?');
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    // If no result is found, return error
    if ($result->num_rows == 0) {
        $output['error'] = true;
        $output['message'] = 'Position not found';
        echo json_encode($output);
        exit;
    }

    $row = $result->fetch_assoc();
    $current_priority = $row['priority'];

    // Check if the current position is already at the top (priority 1)
    if ($current_priority == 1) {
        $output['error'] = true;
        $output['message'] = 'This position is already at the top';
    } else {
        // Get the position above (priority - 1)
        $new_priority = $current_priority - 1;

        // Swap positions: Move the position above down and current position up
        // Move the position above the current position (increase its priority by 1)
        $stmt = $conn->prepare('UPDATE positions SET priority = priority + 1 WHERE priority = ?');
        $stmt->bind_param('i', $new_priority);
        $stmt->execute();

        // Move the current position up (set its priority to the new priority)
        $stmt = $conn->prepare('UPDATE positions SET priority = ? WHERE id = ?');
        $stmt->bind_param('ii', $new_priority, $id);
        $stmt->execute();
    }

    echo json_encode($output);
}
?>