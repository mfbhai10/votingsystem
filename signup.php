<?php
include 'includes/session.php';  // Include session for managing sessions

// Fetch available elections
$stmt = $conn->prepare("SELECT * FROM elections WHERE status = 'open'"); // Only open elections
$stmt->execute();
$elections = $stmt->get_result();

// Handle the form submission when the "Sign Up" button is clicked
if (isset($_POST['signup'])) {
    // 1) Input collection and basic validation
    $voters_id = trim($_POST['voters_id'] ?? '');
    $firstname = trim($_POST['firstname'] ?? '');
    $lastname  = trim($_POST['lastname'] ?? '');
    $password  = $_POST['password'] ?? '';
    $election_id = $_POST['election_id'] ?? 0;

    if ($voters_id === '' || $firstname === '' || $lastname === '' || $password === '' || $election_id === 0) {
        $_SESSION['error'] = 'All fields (except photo) are required, including election selection.';
        header('Location: signup.php');
        exit;
    }

    // Password Hashing
    $hashed = password_hash($password, PASSWORD_DEFAULT);

    // Photo Upload (Optional)
    $photoName = '';
    if (!empty($_FILES['photo']['name'])) {
        // File type safety
        $allowed = ['jpg','jpeg','png','gif','webp'];
        $ext = strtolower(pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, $allowed)) {
            $_SESSION['error'] = 'Invalid photo format. Allowed formats: jpg, jpeg, png, gif, webp.';
            header('Location: signup.php');
            exit;
        }

        // Unique filename for the photo
        $photoName = 'voter_' . uniqid('', true) . '.' . $ext;
        $dest = '../images/' . $photoName;

        if (!move_uploaded_file($_FILES['photo']['tmp_name'], $dest)) {
            $_SESSION['error'] = 'Failed to upload photo.';
            header('Location: signup.php');
            exit;
        }
    }

    // Insert the voter into the database
    $stmt = $conn->prepare('INSERT INTO voters (voters_id, firstname, lastname, password, photo, election_id) VALUES (?,?,?,?,?,?)');
    $stmt->bind_param('sssssi', $voters_id, $firstname, $lastname, $hashed, $photoName, $election_id);

    if ($stmt->execute()) {
        // Registration successful
        $_SESSION['success'] = 'Voter registered successfully';
        $_SESSION['voter_id'] = $conn->insert_id;  // Store the voter ID in session
        header('Location: login.php');  // Redirect to choose election page
        exit;
    } else {
        // Handle duplicate voters_id or other errors
        if ($conn->errno == 1062) {
            $_SESSION['error'] = 'This Voter ID already exists. Please choose a different ID.';
        } else {
            $_SESSION['error'] = 'Registration failed. Please try again later.';
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

            <button type="submit" class="btn btn-primary" name="signup">Sign Up</button>
          </form>
        </section>
      </div>
    </div>
  </div>

  <?php include 'includes/scripts.php'; ?>
</body>

</html>