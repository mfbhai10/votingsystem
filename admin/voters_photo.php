<?php
include 'includes/session.php';

if (isset($_POST['upload'])) {
    $id = (int)$_POST['id'];
    $filename = '';

    // Validate file input
    if (!empty($_FILES['photo']['name'])) {
        $filename = basename($_FILES['photo']['name']);
        $filetype = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

        // Validate file type (allow only jpg, jpeg, png, gif)
        $allowed_types = ['jpg', 'jpeg', 'png', 'gif'];
        if (!in_array($filetype, $allowed_types)) {
            $_SESSION['error'] = 'Invalid file type. Only JPG, JPEG, PNG, GIF files are allowed.';
            header('Location: voters.php');
            exit;
        }

        // Move the uploaded file to the correct directory
        $upload_dir = '../images/';
        $target_file = $upload_dir . $filename;

        if (move_uploaded_file($_FILES['photo']['tmp_name'], $target_file)) {
            // Successfully uploaded the file, now update the database
            $stmt = $conn->prepare('UPDATE voters SET photo=? WHERE id=?');
            $stmt->bind_param('si', $filename, $id);

            if ($stmt->execute()) {
                $_SESSION['success'] = 'Photo updated successfully.';
            } else {
                $_SESSION['error'] = 'Failed to update photo.';
            }
        } else {
            $_SESSION['error'] = 'Failed to upload photo.';
        }
    } else {
        $_SESSION['error'] = 'Please select a photo to upload.';
    }
} else {
    $_SESSION['error'] = 'Select voter to update photo first.';
}

header('Location: voters.php');