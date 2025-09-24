<?php
include 'includes/session.php';

if (isset($_POST['id'])) {
    $id = $_POST['id'];

    // Initialize output to handle errors
    $output = array('error' => false);

    // Fetch the current position's data
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

    // Get the position below the current one (priority + 1)
    $new_priority = $current_priority + 1;

    // Get the total number of positions (to check if we are at the bottom)
    $stmt = $conn->prepare('SELECT COUNT(*) AS total_positions FROM positions');
    $stmt->execute();
    $total_positions_result = $stmt->get_result();
    $total_positions = $total_positions_result->fetch_assoc()['total_positions'];

    // If the current position is already at the bottom (last position), return an error
    if ($current_priority == $total_positions) {
        $output['error'] = true;
        $output['message'] = 'This position is already at the bottom';
    } else {
        // Move the position below (increase its priority by 1)
        $stmt = $conn->prepare('UPDATE positions SET priority = priority - 1 WHERE priority = ?');
        $stmt->bind_param('i', $new_priority);
        $stmt->execute();

        // Move the current position down (set its priority to the new priority)
        $stmt = $conn->prepare('UPDATE positions SET priority = ? WHERE id = ?');
        $stmt->bind_param('ii', $new_priority, $id);
        $stmt->execute();
    }

    echo json_encode($output);
}
?>