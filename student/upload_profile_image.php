<?php
/*
groupe 01
zighed imen 232335330411
Dekrah lakehal 242431577219
Bearcia Issam eddine 232331412506
Ramoul Meriem 242431422801
*/
require_once '../auth/session.php';
if ($_SESSION['user_role'] !== 'students') {
    http_response_code(403);
    exit(json_encode(['error' => 'Accès non autorisé']));
}

require_once '../config/db.php';

$student_id = $_SESSION['user_id'];
$response = ['success' => false, 'message' => ''];

// Validate upload
if (!isset($_FILES['profile_image'])) {
    $response['message'] = 'Aucun fichier fourni';
    http_response_code(400);
    echo json_encode($response);
    exit;
}

$file = $_FILES['profile_image'];
$max_size = 5 * 1024 * 1024; // 5MB
$allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
$allowed_exts = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

// Validate file size
if ($file['size'] > $max_size) {
    $response['message'] = 'Le fichier est trop volumineux (max 5MB)';
    http_response_code(400);
    echo json_encode($response);
    exit;
}

// Validate file type
if (!in_array($file['type'], $allowed_types)) {
    $response['message'] = 'Type de fichier non autorisé. Utilisez JPG, PNG, GIF ou WebP.';
    http_response_code(400);
    echo json_encode($response);
    exit;
}

// Validate file extension
$file_ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
if (!in_array($file_ext, $allowed_exts)) {
    $response['message'] = 'Extension de fichier non autorisée';
    http_response_code(400);
    echo json_encode($response);
    exit;
}

// Check upload errors
if ($file['error'] !== UPLOAD_ERR_OK) {
    $response['message'] = 'Erreur lors du téléchargement du fichier';
    http_response_code(400);
    echo json_encode($response);
    exit;
}

// Verify it's a real image
$image_info = getimagesize($file['tmp_name']);
if ($image_info === false) {
    $response['message'] = 'Le fichier n\'est pas une image valide';
    http_response_code(400);
    echo json_encode($response);
    exit;
}

try {
    // Generate unique filename
    $filename = 'student_' . $student_id . '_' . time() . '.' . $file_ext;
    $upload_dir = realpath(__DIR__ . '/../assets/uploads/student_profiles');
    $upload_path = $upload_dir . '/' . $filename;

    // Move uploaded file
    if (!move_uploaded_file($file['tmp_name'], $upload_path)) {
        throw new Exception('Impossible de déplacer le fichier téléchargé');
    }

    // Delete old profile image if it exists
    $stmt = $pdo->prepare("SELECT profile_image FROM students WHERE id = ?");
    $stmt->execute([$student_id]);
    $result = $stmt->fetch();
    
    if ($result && $result['profile_image']) {
        $old_file = $upload_dir . '/' . $result['profile_image'];
        if (is_file($old_file)) {
            unlink($old_file);
        }
    }

    // Update database
    $stmt = $pdo->prepare("UPDATE students SET profile_image = ? WHERE id = ?");
    $stmt->execute([$filename, $student_id]);

    $response['success'] = true;
    $response['message'] = 'Image de profil mise à jour avec succès';
    $response['image_url'] = '../assets/uploads/student_profiles/' . $filename;

    http_response_code(200);
    echo json_encode($response);

} catch (Exception $e) {
    $response['message'] = 'Erreur: ' . $e->getMessage();
    http_response_code(500);
    echo json_encode($response);
}
exit;
