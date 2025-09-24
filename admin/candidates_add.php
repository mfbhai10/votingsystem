<?php
// admin/candidates_add.php
include 'includes/session.php';

if (isset($_POST['add'])) {
    $electionId = (int)($_SESSION['admin_election_id'] ?? 0);
    $firstname  = trim($_POST['firstname'] ?? '');
    $lastname   = trim($_POST['lastname']  ?? '');
    $positionId = (int)($_POST['position'] ?? 0);
    $platform   = trim($_POST['platform']  ?? '');

    if ($electionId <= 0 || $firstname === '' || $lastname === '' || $positionId <= 0) {
        $_SESSION['error'] = 'Invalid input or no election selected.';
        header('Location: candidates.php'); exit;
    }

    // সেফটি: দেয়া position টি বর্তমান election-এর কি না যাচাই করি
    $chk = $conn->prepare("SELECT id FROM positions WHERE id=? AND election_id=?");
    $chk->bind_param("ii", $positionId, $electionId);
    $chk->execute();
    if ($chk->get_result()->num_rows !== 1) {
        $_SESSION['error'] = 'Position does not belong to the selected election.';
        header('Location: candidates.php'); exit;
    }

    // ফটো আপলোড (optional)
    $photoName = '';
    if (!empty($_FILES['photo']['name'])) {
        // ইউনিক ফাইলনেম বানাই (টাইমস্ট্যাম্প + র‍্যান্ডম)
        $ext = pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION);
        $safeExt = preg_replace('/[^a-zA-Z0-9]/', '', $ext);
        $photoName = 'cand_'.time().'_'.bin2hex(random_bytes(4));
        if ($safeExt) $photoName .= '.'.$safeExt;

        // images/ ফোল্ডারে সেভ
        $dest = '../images/'.$photoName;
        if (!move_uploaded_file($_FILES['photo']['tmp_name'], $dest)) {
            $_SESSION['error'] = 'Photo upload failed.';
            header('Location: candidates.php'); exit;
        }
    }

    // ক্যান্ডিডেট ইনসার্ট (এই ইলেকশনে)
    $ins = $conn->prepare("
        INSERT INTO candidates (election_id, position_id, firstname, lastname, photo, platform)
        VALUES (?,?,?,?,?,?)
    ");
    $ins->bind_param("iissss", $electionId, $positionId, $firstname, $lastname, $photoName, $platform);

    if ($ins->execute()) {
        $_SESSION['success'] = 'Candidate added successfully.';
    } else {
        // ফটো আপলোড হয়ে থাকলে রোলব্যাক হিসেবে ডিলিট করা যেতে পারে (ইচ্ছা হলে)
        if ($photoName && file_exists('../images/'.$photoName)) {
            @unlink('../images/'.$photoName);
        }
        $_SESSION['error'] = 'Failed to add candidate.';
    }
} else {
    $_SESSION['error'] = 'Fill up add form first.';
}

header('Location: candidates.php');
?>