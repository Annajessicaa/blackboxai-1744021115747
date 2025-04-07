<?php
session_start();

// Validate input
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_FILES['encryptedFile']) || !isset($_FILES['privateKey'])) {
    $_SESSION['status'] = [
        'type' => 'error',
        'message' => 'Permintaan tidak valid'
    ];
    header('Location: decrypt.php');
    exit;
}

// Process encrypted file
$encryptedFile = $_FILES['encryptedFile'];
$privateKeyFile = $_FILES['privateKey'];

// Check for upload errors
if ($encryptedFile['error'] !== UPLOAD_ERR_OK || $privateKeyFile['error'] !== UPLOAD_ERR_OK) {
    $_SESSION['status'] = [
        'type' => 'error',
        'message' => 'Gagal mengunggah file'
    ];
    header('Location: decrypt.php');
    exit;
}

// Read encrypted data
$encryptedData = json_decode(file_get_contents($encryptedFile['tmp_name']), true);
if (!$encryptedData) {
    $_SESSION['status'] = [
        'type' => 'error',
        'message' => 'Format file tidak valid'
    ];
    header('Location: decrypt.php');
    exit;
}

// Decrypt AES key with RSA
$privateKey = openssl_pkey_get_private(file_get_contents($privateKeyFile['tmp_name']));
if (!openssl_private_decrypt(
    base64_decode($encryptedData['encrypted_key']),
    $aesKey,
    $privateKey
)) {
    $_SESSION['status'] = [
        'type' => 'error',
        'message' => 'Gagal mendekripsi kunci. Pastikan kunci pribadi yang benar'
    ];
    header('Location: decrypt.php');
    exit;
}

// Decrypt content with AES
$decryptedContent = openssl_decrypt(
    base64_decode($encryptedData['encrypted_content']),
    'aes-256-cbc',
    $aesKey,
    OPENSSL_RAW_DATA,
    base64_decode($encryptedData['iv'])
);

if ($decryptedContent === false) {
    $_SESSION['status'] = [
        'type' => 'error',
        'message' => 'Gagal mendekripsi konten'
    ];
    header('Location: decrypt.php');
    exit;
}

// Prepare output
$outputFilename = 'decrypted_' . time() . '.txt';
file_put_contents($outputFilename, $decryptedContent);

$_SESSION['status'] = [
    'type' => 'success',
    'message' => 'File berhasil didekripsi. <a href="' . $outputFilename . '" download>Download file</a>'
];
header('Location: decrypt.php');
exit;