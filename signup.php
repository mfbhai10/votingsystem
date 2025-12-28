<?php
session_start();
include 'includes/conn.php'; // Use conn.php directly for transactions

// Fetch available elections
$stmt = $conn->prepare("SELECT * FROM elections WHERE status = 'open'");
$stmt->execute();
$elections = $stmt->get_result();

if (isset($_POST['signup'])) {
    $voters_id = trim($_POST['voters_id'] ?? '');
    $firstname = trim($_POST['firstname'] ?? '');
    $lastname  = trim($_POST['lastname'] ?? '');
    $password  = $_POST['password'] ?? '';
    $election_ids = $_POST['election_ids'] ?? []; // Array of selected elections

    if ($voters_id === '' || $firstname === '' || $lastname === '' || $password === '' || empty($election_ids)) {
        $_SESSION['error'] = 'All fields are required, and you must select at least one election.';
        header('Location: signup.php');
        exit;
    }

    $hashed = password_hash($password, PASSWORD_DEFAULT);
    $photoName = '';

    if (!empty($_FILES['photo']['name'])) {
        $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        $ext = strtolower(pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, $allowed)) {
            $_SESSION['error'] = 'Invalid photo format. Allowed: jpg, jpeg, png, gif, webp.';
            header('Location: signup.php');
            exit;
        }
        $photoName = 'voter_' . uniqid('', true) . '.' . $ext;
        $dest = 'images/' . $photoName;
        if (!move_uploaded_file($_FILES['photo']['tmp_name'], $dest)) {
            $_SESSION['error'] = 'Failed to upload photo.';
            header('Location: signup.php');
            exit;
        }
    }

    // Use a transaction to ensure data integrity
    $conn->begin_transaction();

    try {
        // 1. Insert the voter into the 'voters' table
        $stmt_voter = $conn->prepare('INSERT INTO voters (voters_id, firstname, lastname, password, photo) VALUES (?,?,?,?,?)');
        $stmt_voter->bind_param('sssss', $voters_id, $firstname, $lastname, $hashed, $photoName);
        $stmt_voter->execute();

        $voter_id = $conn->insert_id; // Get the ID of the new voter

        // 2. Insert into the 'voter_elections' junction table for each selected election
        $stmt_elections = $conn->prepare('INSERT INTO voter_elections (voter_id, election_id) VALUES (?, ?)');
        foreach ($election_ids as $election_id) {
            $stmt_elections->bind_param('ii', $voter_id, $election_id);
            $stmt_elections->execute();
        }

        $conn->commit(); // Finalize the transaction
        $_SESSION['success'] = 'Voter registered successfully. You can now log in.';
        header('Location: index.php');
        exit;

    } catch (mysqli_sql_exception $exception) {
        $conn->rollback(); // Revert changes on error
        if ($conn->errno == 1062) {
            $_SESSION['error'] = 'This Voter ID already exists. Please choose a different ID.';
        } else {
            $_SESSION['error'] = 'Registration failed due to a database error. Please try again.';
        }
        header('Location: signup.php');
        exit;
    }
}
?>

<?php include 'includes/header1.php'; ?>
<body class="hold-transition skin-blue layout-top-nav">
<div class="wrapper">
    <div class="content-wrapper">
        <div class="container">
            <section class="content">
                <h1 class="page-header text-center title"><b>Voter Registration</b></h1>

                <?php if (isset($_SESSION['error'])): ?>
                    <div class="alert alert-danger"><?= $_SESSION['error']; unset($_SESSION['error']); ?></div>
                <?php endif; ?>
                <?php if (isset($_SESSION['success'])): ?>
                    <div class="alert alert-success"><?= $_SESSION['success']; unset($_SESSION['success']); ?></div>
                <?php endif; ?>

                <form method="POST" action="signup.php" enctype="multipart/form-data">
                    <div class="form-group"><label for="voters_id">Voter ID</label><input type="text" class="form-control" name="voters_id" id="voters_id" required></div>
                    <div class="form-group"><label for="firstname">First Name</label><input type="text" class="form-control" name="firstname" id="firstname" required></div>
                    <div class="form-group"><label for="lastname">Last Name</label><input type="text" class="form-control" name="lastname" id="lastname" required></div>
                    <div class="form-group"><label for="password">Password</label><input type="password" class="form-control" name="password" id="password" required></div>
                    
                    <div class="form-group">
                        <label>Select Elections (Choose one or more)</label>
                        <?php mysqli_data_seek($elections, 0); // Reset pointer ?>
                        <?php while ($election = $elections->fetch_assoc()): ?>
                            <div class="checkbox">
                                <label>
                                    <input type="checkbox" name="election_ids[]" value="<?= $election['id'] ?>">
                                    <?= htmlspecialchars($election['title']) ?>
                                </label>
                            </div>
                        <?php endwhile; ?>
                    </div>

                    <div class="form-group"><label for="photo">Photo (optional)</label><input type="file" class="form-control" name="photo" id="photo" accept="image/*"></div>
                    <button type="submit" class="btn btn-primary" name="signup">Sign Up</button>
                    <a href="index.php" class="btn btn-default">Back to Login</a>
                </form>
            </section>
        </div>
    </div>
</div>
<?php include 'includes/scripts.php'; ?>
</body>
</html>