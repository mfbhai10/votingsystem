<?php
// admin/voters_add.php
session_start();
include 'includes/session.php';

// Fetch available elections
$stmt = $conn->prepare("SELECT * FROM elections WHERE status = 'open'"); // Only open elections
$stmt->execute();
$elections = $stmt->get_result();

if (isset($_POST['add'])) {
    // 1) Input collection and basic validation
    $voters_id = trim($_POST['voters_id'] ?? '');
    $firstname = trim($_POST['firstname'] ?? '');
    $lastname  = trim($_POST['lastname']  ?? '');
    $password  = $_POST['password'] ?? '';
    $election_id = $_POST['election_id'] ?? 0;

    if ($voters_id === '' || $firstname === '' || $lastname === '' || $password === '' || $election_id === 0) {
        $_SESSION['error'] = 'All fields (except photo) are required, including election selection.';
        header('Location: voters.php');
        exit;
    }

    // 2) Password Hashing
    $hashed = password_hash($password, PASSWORD_DEFAULT);

    // 3) Photo Upload (Optional)
    $photoName = '';
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

    // 4) Insert into the database (prepared statement)
    $stmt = $conn->prepare('INSERT INTO voters (voters_id, firstname, lastname, password, photo, election_id) VALUES (?,?,?,?,?,?)');
    $stmt->bind_param('sssssi', $voters_id, $firstname, $lastname, $hashed, $photoName, $election_id);

    if ($stmt->execute()) {
        $_SESSION['success'] = 'New voter added successfully';
    } else {
        // Handle duplicate voters_id (MySQL error code 1062)
        if ($conn->errno == 1062) {
            // Rollback by deleting the uploaded photo if there's a duplicate voter ID
            if ($photoName && file_exists('../images/' . $photoName)) {
                @unlink('../images/' . $photoName);
            }
            $_SESSION['error'] = 'This Voter ID already exists. Use a different ID.';
        } else {
            $_SESSION['error'] = 'Failed to add voter.';
        }
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
            <label for="election_id">Election</label>
            <select class="form-control" name="election_id" id="election_id" required>
                <option value="">Select Election</option>
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