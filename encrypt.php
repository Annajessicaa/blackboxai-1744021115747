<?php 
include 'header.php';
session_start();
?>

<div class="container mx-auto p-8">
    <h2 class="text-2xl mb-4">Enkripsi File</h2>
    
    <?php if (isset($_SESSION['status'])): ?>
        <div class="p-4 mb-4 text-white <?= $_SESSION['status']['type'] === 'success' ? 'bg-green-500' : 'bg-red-500' ?>">
            <?= $_SESSION['status']['message'] ?>
        </div>
        <?php unset($_SESSION['status']); ?>
    <?php endif; ?>

    <form action="process_encrypt.php" method="POST" enctype="multipart/form-data" class="max-w-md">
        <div class="mb-4">
            <label class="block text-gray-700 mb-2">Pilih File Teks:</label>
            <input type="file" name="fileToEncrypt" class="w-full p-2 border rounded" accept=".txt,.text" required>
        </div>
        <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">
            Enkripsi File
        </button>
    </form>
</div>

<?php include 'footer.php'; ?>