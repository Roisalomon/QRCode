<?php
header('Content-Type: application/json');

$response = [
    'success' => false,
    'imageUrl' => '',
    'error' => ''
];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['image'])) {
    $targetDir = "uploads/";
    
    // Création du dossier uploads s'il n'existe pas
    if (!file_exists($targetDir)) {
        mkdir($targetDir, 0777, true);
    }
    
    $fileName = time() . '_' . basename($_FILES["image"]["name"]);
    $targetFilePath = $targetDir . $fileName;
    $fileType = strtolower(pathinfo($targetFilePath, PATHINFO_EXTENSION));
    
    // Extensions autorisées
    $allowTypes = array('jpg', 'png', 'jpeg', 'gif', 'webp');
    
    if (in_array($fileType, $allowTypes)) {
        if (move_uploaded_file($_FILES["image"]["tmp_name"], $targetFilePath)) {
            // Construction de l'URL absolue de l'image
            $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
            $domain = $_SERVER['HTTP_HOST'];
            $scriptPath = dirname($_SERVER['PHP_SELF']);
            $scriptPath = rtrim($scriptPath, '/\\');
            
            $response['success'] = true;
            $response['imageUrl'] = $protocol . "://" . $domain . $scriptPath . "/" . $targetFilePath;
        } else {
            $response['error'] = "Erreur lors du transfert du fichier.";
        }
    } else {
        $response['error'] = "Format non supporté. (JPG, PNG, GIF, WEBP uniquement)";
    }
} else {
    $response['error'] = "Aucun fichier reçu.";
}

echo json_encode($response);
exit;