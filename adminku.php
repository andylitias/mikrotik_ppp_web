<?php
// Jika form disubmit
if ($_SERVER["REQUEST_METHOD"] == "POST") {
  $content = $_POST['content'];

  // Simpan konten yang diubah ke file.txt
  $file = fopen("router.txt", "w");
  fwrite($file, $content);
  fclose($file);
}

// Baca konten file.txt
$file = fopen("router.txt", "r");
$content = fread($file, filesize("router.txt"));
fclose($file);

// Pisahkan setiap kolom dengan koma
$data = explode(',', $content);
$host = $data[0];
$user_mikrotik = $data[1];
$password_mikrotik = $data[2];
$user_login = $data[3];
$password_login = $data[4];
$router_id = $data[5];
?>

<!DOCTYPE html>
<html>
<head>
  <title>Router Configuration Table</title>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.0/umd/popper.min.js"></script>
  <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</head>
<body>
  <div class="container mt-5">
    <h1 class="text-center mb-4">Router Configuration Table</h1>
    <div class="row justify-content-center mt-5">
      <div class="col-md-10">
        <table class="table">
          <thead>
            <tr>
              <th>Host</th>
              <th>User Mikrotik</th>
              <th>Password Mikrotik</th>
              <th>User Login</th>
              <th>Password Login</th>
              <th>Router ID</th>
              <th>Action</th>
            </tr>
          </thead>
          <tbody>
            <?php
            // Baca file router.txt
            $file = fopen("router.txt", "r");
            if ($file) {
              $line_number = 0;
              while (($line = fgets($file)) !== false) {
                // Pisahkan setiap kolom dengan koma
                $data = explode(',', $line);
                $host = $data[0];
                $user_mikrotik = $data[1];
                $password_mikrotik = $data[2];
                $user_login = $data[3];
                $password_login = $data[4];
                $router_id = $data[5];
                $line_number++;
                // Tampilkan baris pada tabel
                echo "<tr>";
				echo "<td>" . $host . "</td>";
				echo "<td>" . $user_mikrotik . "</td>";
				echo "<td>" . $password_mikrotik . "</td>";
				echo "<td>" . $user_login . "</td>";
				echo "<td>" . $password_login . "</td>";
				echo "<td>" . $router_id . "</td>";
				echo "<td><a href='edit_router.php?line_number=" . $line_number . "' class='btn btn-primary'>Edit</a></td>";
				echo "<td><a href='delete_router.php?line_number=" . $line_number . "' class='btn btn-danger'
						onclick=\"return confirm('Are you sure you want to delete this router? This action cannot be undone.')\">Delete</a></td>";
				echo "</tr>";
              }
              fclose($file);
            }
            ?>
          </tbody>
        </table>
      </div>
    </div>
	<div class="row justify-content-center mt-3">
  <div class="col-md-4">
    <a href="add_router.php" class="btn btn-success btn-block">Add New Router</a>
  </div>
</div>
  </div>
</body>
</html>
