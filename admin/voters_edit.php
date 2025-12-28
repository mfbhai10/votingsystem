<?php
include 'includes/session.php';

if (isset($_POST['edit'])) {
    $id = (int)$_POST['id'];
    $voters_id = trim($_POST['voters_id']);
    $firstname = trim($_POST['firstname']);
    $lastname  = trim($_POST['lastname']);
    $password_field = $_POST['password'] ?? '';
    
    // NOTE: If you add election editing to your voters.php modal, uncomment this line:
    // $elections_to_assign = $_POST['election_id'] ?? []; 
    $success = false;
    $error_message = '';

    try {
        // --- 1. Prepare and Execute the appropriate UPDATE statement for the 'voters' table ---
        if (!empty($password_field)) {
            $password = password_hash($password_field, PASSWORD_DEFAULT);
            $stmt = $conn->prepare('UPDATE voters SET voters_id=?, firstname=?, lastname=?, password=? WHERE id=?');
            $stmt->bind_param('ssssi', $voters_id, $firstname, $lastname, $password, $id);
        } else {
            $stmt = $conn->prepare('UPDATE voters SET voters_id=?, firstname=?, lastname=? WHERE id=?');
            $stmt->bind_param('sssi', $voters_id, $firstname, $lastname, $id);
        }

        $success = $stmt->execute();
        $stmt->close();

        /*
        // --- 2. LOGIC FOR UPDATING 'voter_elections' (UNCOMMENT IF YOU ADD EDITING TO MODAL) ---
        if ($success && !empty($elections_to_assign)) {
            // A. Delete existing assignments
            $delete_stmt = $conn->prepare("DELETE FROM voter_elections WHERE voter_id = ?");
            $delete_stmt->bind_param('i', $id);
            $delete_stmt->execute();
            $delete_stmt->close();

            // B. Insert new assignments
            $insert_stmt = $conn->prepare("INSERT INTO voter_elections (voter_id, election_id) VALUES (?, ?)");
            foreach ($elections_to_assign as $election_id) {
                $election_id = (int)$election_id;
                $insert_stmt->bind_param('ii', $id, $election_id);
                $insert_stmt->execute();
            }
            $insert_stmt->close();
        }
        */

        if ($success) {
            $_SESSION['success'] = 'Voter details updated successfully.';
        } else {
            $error_message = 'Failed to update voter: ' . $conn->error;
        }

    } catch (mysqli_sql_exception $e) {
        if ($conn->errno == 1062) {
            $error_message = 'Failed to update voter. Voter ID already exists: ' . $voters_id;
        } else {
            $error_message = 'Database error: ' . $e->getMessage();
        }
    }

    if (!empty($error_message)) {
        $_SESSION['error'] = $error_message;
    }

} else {
    $_SESSION['error'] = 'Fill up edit form first';
}

header('Location: voters.php');
exit();
?>