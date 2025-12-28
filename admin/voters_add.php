<?php
// admin/voters_add.php
session_start();
include 'includes/session.php';
// Note: The admin session check should be included here if not in includes/session.php

// Fetch available elections
$stmt = $conn->prepare("SELECT * FROM elections WHERE status = 'open'");
$stmt->execute();
$elections = $stmt->get_result();

if (isset($_POST['add'])) {
    // 1) Input collection and basic validation
    $voters_id = trim($_POST['voters_id'] ?? '');
    $firstname = trim($_POST['firstname'] ?? '');
    $lastname  = trim($_POST['lastname']  ?? '');
    $password  = $_POST['password'] ?? '';
    // CHANGE: Collect array of elections
    $election_ids = $_POST['election_id'] ?? []; 
    $election_ids = is_array($election_ids) ? array_map('intval', $election_ids) : [(int)$election_ids];

    if ($voters_id === '' || $firstname === '' || $lastname === '' || $password === '' || empty($election_ids)) {
        $_SESSION['error'] = 'All personal fields (except photo) are required, and you must select at least one election.';
        header('Location: voters.php');
        exit;
    }

    // 2) Password Hashing
    $hashed = password_hash($password, PASSWORD_DEFAULT);

    // 3) Photo Upload (Optional) - Logic remains the same
    $photoName = '';
    // ... (Your photo upload logic here - unchanged) ...
    if (!empty($_FILES['photo']['name'])) {
        // File type safety
        $allowed = ['jpg','jpeg','png','gif','webp'];
        $ext = strtolower(pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, $allowed)) {
            $_SESSION['error'] = 'Invalid photo format. Allowed formats: jpg, jpeg, png, gif, webp.';
            header('Location: voters.php'); exit;
        }

        // Unique filename
        $photoName = 'voter_' . uniqid('', true) . '.' . $ext;
        $dest = '../images/' . $photoName;

        if (!move_uploaded_file($_FILES['photo']['tmp_name'], $dest)) {
            $_SESSION['error'] = 'Failed to upload photo.';
            header('Location: voters.php'); exit;
        }
    }


    $conn->begin_transaction();
    $error_occurred = false;
    $new_voter_id = null;
    
    try {
        // 4A) Insert into the voters table (REMOVED election_id column)
        $stmt = $conn->prepare('INSERT INTO voters (voters_id, firstname, lastname, password, photo) VALUES (?,?,?,?,?)');
        $stmt->bind_param('sssss', $voters_id, $firstname, $lastname, $hashed, $photoName);

        if (!$stmt->execute()) {
             // Handle duplicate voters_id 
             if ($conn->errno == 1062) {
                 throw new Exception('This Voter ID already exists. Use a different ID.');
             } else {
                 throw new Exception('Failed to add voter to the voters table.');
             }
        }
        
        // Get the ID of the newly inserted voter
        $new_voter_id = $conn->insert_id;

        // 4B) Insert into voter_elections table
        $ins_ve = $conn->prepare('INSERT INTO voter_elections (voter_id, election_id) VALUES (?,?)');
        foreach ($election_ids as $eid) {
            $ins_ve->bind_param('ii', $new_voter_id, $eid);
            if (!$ins_ve->execute()) {
                 throw new Exception('Failed to assign election(s) to voter.');
            }
        }
        
        $conn->commit();
        $_SESSION['success'] = 'New voter added successfully and assigned to ' . count($election_ids) . ' election(s).';

    } catch (Exception $e) {
        $conn->rollback();
        // Rollback by deleting the uploaded photo if a database error occurred
        if ($photoName && file_exists('../images/' . $photoName)) {
            @unlink('../images/' . $photoName);
        }
        $_SESSION['error'] = $e->getMessage();
    }


    header('Location: voters.php');
    exit;
}
?>

<?php include 'includes/header.php'; ?>

<div class="container">
    <h1>Add New Voter</h1>

    <form method="POST" action="voters_add.php" enctype="multipart/form-data">
        <div class="form-group">
            <label for="voters_id">Voter ID</label>
            <input type="text" class="form-control" name="voters_id" id="voters_id" required>
        </div>

        <div class="form-group">
            <label for="firstname">First Name</label>
            <input type="text" class="form-control" name="firstname" id="firstname" required>
        </div>

        <div class="form-group">
            <label for="lastname">Last Name</label>
            <input type="text" class="form-control" name="lastname" id="lastname" required>
        </div>

        <div class="form-group">
            <label for="password">Password</label>
            <input type="password" class="form-control" name="password" id="password" required>
        </div>

        <div class="form-group">
            <label for="election_id">Assign to Election(s) (Hold Ctrl/Cmd to select multiple)</label>
            <select class="form-control" name="election_id[]" id="election_id" required multiple>
                <?php $elections->data_seek(0); // Reset result pointer ?> 
                <?php while ($election = $elections->fetch_assoc()): ?>
                    <option value="<?= $election['id'] ?>"><?= htmlspecialchars($election['title']) ?></option>
                <?php endwhile; ?>
            </select>
        </div>

        <div class="form-group">
            <label for="photo">Photo (optional)</label>
            <input type="file" class="form-control" name="photo" id="photo" accept="image/*">
        </div>

        <button type="submit" class="btn btn-primary" name="add">Add Voter</button>
    </form>
</div>

<?php include 'includes/footer.php'; ?>