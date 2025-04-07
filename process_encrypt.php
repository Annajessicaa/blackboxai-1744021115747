<?php
session_start();

// Validate file upload
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_FILES['fileToEncrypt'])) {
    $_SESSION['status'] = [
        'type' => 'error',
        'message' => 'Permintaan tidak valid'
    ];
    header('Location: encrypt.php');
    exit;
}

$file = $_FILES['fileToEncrypt'];

// Check for upload errors
if ($file['error'] !== UPLOAD_ERR_OK) {
    $_SESSION['status'] = [
        'type' => 'error',
        'message' => 'Gagal mengunggah file: ' . $file['error']
    ];
    header('Location: encrypt.php');
    exit;
}

// Validate file type
$allowedTypes = ['text/plain'];
if (!in_array($file['type'], $allowedTypes)) {
    $_SESSION['status'] = [
        'type' => 'error',
        'message' => 'Hanya file teks (.txt) yang diperbolehkan'
    ];
    header('Location: encrypt.php');
    exit;
}

// Generate AES key and IV
$aesKey = openssl_random_pseudo_bytes(32); // 256-bit key
$iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length('aes-256-cbc'));

// Encrypt the file content
$fileContent = file_get_contents($file['tmp_name']);
$encryptedContent = openssl_encrypt(
    $fileContent,
    'aes-256-cbc',
    $aesKey,
    OPENSSL_RAW_DATA,
    $iv
);

// Encrypt AES key with RSA
$publicKey = openssl_pkey_get_public(file_get_contents('keys/public.pem'));
openssl_public_encrypt($aesKey, $encryptedKey, $publicKey);

// Prepare output
$output = [
    'iv' => base64_encode($iv),
    'encrypted_key' => base64_encode($encryptedKey),
    'encrypted_content' => base64_encode($encryptedContent)
];

// Save encrypted file
$outputFilename = 'encrypted_' . time() . '.enc';
file_put_contents($outputFilename, json_encode($output));

$_SESSION['status'] = [
    'type' => 'success',
    'message' => 'File berhasil dienkripsi. <a href="' . $outputFilename . '" download>Download file</a>'
];
header('Location: encrypt.php');
exit;