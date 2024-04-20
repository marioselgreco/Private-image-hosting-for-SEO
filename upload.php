<?php
// Function to generate a random string for file names
function generateRandomString($length = 10) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, strlen($characters) - 1)];
    }
    return $randomString;
}

// Check if the request method is POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $folder = filter_input(INPUT_POST, 'folder', FILTER_SANITIZE_STRING);
    $upload_dir = 'uploads/' . $folder . '/';

    // Create the folder if it doesn't exist
    if (!file_exists($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }

    // Loop through each uploaded file
    $urls = array();
    foreach ($_FILES['files']['tmp_name'] as $key => $tmp_name) {
        $file_name = $_FILES['files']['name'][$key]; // Use the original filename
        $file_size = $_FILES['files']['size'][$key];
        $file_ext = pathinfo($file_name, PATHINFO_EXTENSION);
        $file_dest = $upload_dir . $file_name; // Use the original filename

        // File validation
        $allowed_extensions = array('jpg', 'jpeg', 'webp', 'png', 'gif');
        if (!in_array(strtolower($file_ext), $allowed_extensions)) {
            echo "Error: Only JPG, JPEG, PNG, WEBP and GIF files are allowed.";
            exit();
        }
        if ($file_size > 10 * 1024 * 1024) { // 10 MB file size limit
            echo "Error: File size exceeds the limit of 100 MB.";
            exit();
        }

        // Move the uploaded file to the destination folder
        if (move_uploaded_file($tmp_name, $file_dest)) {
            $file_url = 'https://' . $_SERVER['HTTP_HOST'] . '/' . $file_dest;
            $urls[] = $file_url;
        } else {
            echo "Error: Failed to upload file.";
            exit();
        }
    }

    // Modify URLs to replace spaces with %20
    $urls_with_encoded_spaces = array_map(function($url) {
        return str_replace(' ', '%20', $url);
    }, $urls);

    // Prepare the text content with modified URLs for download
    $txt_content = implode(PHP_EOL, $urls_with_encoded_spaces);

    // Send headers to trigger download
    header('Content-Type: text/plain');
    header('Content-Disposition: attachment; filename="uploaded_urls.txt"');
    header('Content-Length: ' . strlen($txt_content));
    echo $txt_content;
    exit();
}
?>