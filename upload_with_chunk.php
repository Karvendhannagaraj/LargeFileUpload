<?php
// Define the directory where chunks will be temporarily stored
$uploadDir = 'uploads/'; // Make sure this directory exists and has write permissions

// Make sure the directory exists
if (!file_exists($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

// Get the file ID, chunk index, total chunks, and file extension from the POST request
$fileId = isset($_POST['fileId']) ? $_POST['fileId'] : '';
$chunkIndex = isset($_POST['chunkIndex']) ? $_POST['chunkIndex'] : '';
$totalChunks = isset($_POST['totalChunks']) ? $_POST['totalChunks'] : '';
$fileExtension = isset($_POST['fileExtension']) ? $_POST['fileExtension'] : ''; // Get the file extension
$file = isset($_FILES['file']) ? $_FILES['file'] : null;

if (!$fileId || !$chunkIndex || !$file || $chunkIndex <= 0 || $chunkIndex > $totalChunks) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request parameters']);
    exit;
}

// Temporary file path where the chunk will be stored
$chunkFilePath = $uploadDir . $fileId . '_chunk_' . $chunkIndex;

// Move the uploaded chunk to the server
if (move_uploaded_file($file['tmp_name'], $chunkFilePath)) {
    echo json_encode(['status' => 'success', 'message' => 'Chunk uploaded successfully']);

    // Check if all chunks are uploaded
    if (checkAllChunksUploaded($fileId, $totalChunks)) {
        // Combine the chunks into the final file
        combineChunks($fileId, $totalChunks, $fileExtension);
        echo json_encode(['status' => 'success', 'message' => 'All chunks uploaded and file combined successfully']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Failed to upload chunk']);
}

// Function to check if all chunks have been uploaded
function checkAllChunksUploaded($fileId, $totalChunks)
{
    global $uploadDir;

    for ($i = 1; $i <= $totalChunks; $i++) {
        $chunkPath = $uploadDir . $fileId . '_chunk_' . $i;
        if (!file_exists($chunkPath)) {
            return false; // Not all chunks are uploaded
        }
    }
    return true; // All chunks are uploaded
}

// Function to combine the uploaded chunks into a final file
function combineChunks($fileId, $totalChunks, $fileExtension)
{
    global $uploadDir;

    $finalFilePath = $uploadDir . $fileId . '_final.' . $fileExtension; // The final file after combining all chunks
    $finalFile = fopen($finalFilePath, 'wb'); // Open the final file in write-binary mode

    // Append each chunk to the final file
    for ($i = 1; $i <= $totalChunks; $i++) {
        $chunkFilePath = $uploadDir . $fileId . '_chunk_' . $i;
        $chunkData = file_get_contents($chunkFilePath);
        fwrite($finalFile, $chunkData); // Write the chunk to the final file
        //unlink($chunkFilePath); // Delete the chunk after appending it
    }

    fclose($finalFile); // Close the final file
}
?>
