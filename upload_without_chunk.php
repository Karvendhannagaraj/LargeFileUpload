<?php
// Directory where the file will be uploaded
$uploadDir = 'uploads/';

// Check if the directory exists and is writable
if (!file_exists($uploadDir)) {
    // If the directory does not exist, try to create it
    if (!mkdir($uploadDir, 0777, true)) {
        echo json_encode(['status' => 'error', 'message' => 'Failed to create upload directory']);
        exit;
    }
}

// Check if the directory is writable
if (!is_writable($uploadDir)) {
    echo json_encode(['status' => 'error', 'message' => 'Upload directory is not writable']);
    exit;
}

// Proceed with the file upload if permissions are okay
if (isset($_FILES['file'])) {
    $file = $_FILES['file'];
    $targetFilePath = $uploadDir . basename($file['name']);

    // Check if the file was uploaded without errors
    if ($file['error'] == UPLOAD_ERR_OK) {
        // Move the uploaded file to the target directory
        if (move_uploaded_file($file['tmp_name'], $targetFilePath)) {
            echo json_encode(['status' => 'success', 'message' => 'File uploaded successfully']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Failed to move the uploaded file']);
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Error uploading the file']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'No file uploaded']);
}
?>
