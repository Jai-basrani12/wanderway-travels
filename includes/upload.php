<?php
// WanderWay Travels - Image Upload Helper

/**
 * Uploads an image file to the uploads/ directory.
 *
 * @param array $file The uploaded file array from $_FILES['fieldName'].
 * @param array &$errors Array to store any error messages.
 * @return string|null Returns the new filename on success, or null if no file was uploaded / upload failed.
 */
function uploadImage(array $file, array &$errors): ?string {
    // If no file was selected or uploaded
    if (!isset($file['error']) || $file['error'] === UPLOAD_ERR_NO_FILE) {
        return null;
    }

    // Check for PHP upload errors
    if ($file['error'] !== UPLOAD_ERR_OK) {
        switch ($file['error']) {
            case UPLOAD_ERR_INI_SIZE:
            case UPLOAD_ERR_FORM_SIZE:
                $errors[] = 'Image exceeds the maximum allowed file size of 2MB.';
                break;
            case UPLOAD_ERR_PARTIAL:
                $errors[] = 'The image was only partially uploaded. Please try again.';
                break;
            default:
                $errors[] = 'An error occurred while uploading the image (Code: ' . (int)$file['error'] . ').';
                break;
        }
        return null;
    }

    // Check maximum file size (2MB = 2097152 bytes)
    $max_size = 2 * 1024 * 1024;
    if ($file['size'] > $max_size) {
        $errors[] = 'Image file size exceeds the 2MB limit.';
        return null;
    }

    // Validate file extension
    $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $allowed_extensions = ['jpg', 'jpeg', 'png'];

    if (!in_array($extension, $allowed_extensions, true)) {
        $errors[] = 'Invalid file extension. Only JPG, JPEG, and PNG images are permitted.';
        return null;
    }

    // Verify image authenticity using getimagesize()
    $image_info = @getimagesize($file['tmp_name']);
    if ($image_info === false) {
        $errors[] = 'The uploaded file is not a valid image.';
        return null;
    }

    // Verify image MIME type
    $allowed_mimes = ['image/jpeg', 'image/pjpeg', 'image/png'];
    $mime_type = $image_info['mime'] ?? '';
    if (!in_array($mime_type, $allowed_mimes, true)) {
        $errors[] = 'Invalid image MIME type.';
        return null;
    }

    // Generate a unique random filename
    $normalized_ext = ($extension === 'jpeg') ? 'jpg' : $extension;
    $random_name = bin2hex(random_bytes(16)) . '.' . $normalized_ext;
    
    $target_dir = dirname(__DIR__) . '/uploads/';
    $target_path = $target_dir . $random_name;

    // Ensure uploads directory exists
    if (!is_dir($target_dir)) {
        mkdir($target_dir, 0755, true);
    }

    // Move uploaded file to uploads directory
    if (!move_uploaded_file($file['tmp_name'], $target_path)) {
        $errors[] = 'Failed to save the uploaded image to the server.';
        return null;
    }

    return $random_name;
}
