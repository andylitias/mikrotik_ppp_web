<?php
// Mendapatkan nomor baris yang akan dihapus dari parameter URL
$line_number = $_GET['line_number'];

// Baca file router.txt
$file = file("router.txt");

// Hapus baris yang dipilih dari file
unset($file[$line_number-1]);

// Tulis ulang file tanpa baris yang dihapus
file_put_contents("router.txt", implode("", $file));

// Redirect ke halaman admin.php
header("Location: adminku.php");
exit();
?>
