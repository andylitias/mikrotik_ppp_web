<!DOCTYPE html>
<html>
<head>
  <title>Add Router Configuration</title>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.0/umd/popper.min.js"></script>
  <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</head>
<body>
  <div class="container mt-5">
    <h1 class="text-center mb-4">Add Router Configuration</h1>
    <div class="row justify-content-center mt-5">
      <div class="col-md-8">
        <?php
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
          // Simpan data baru ke dalam file
          $host = $_POST['host'];
          $user_mikrotik = $_POST['user_mikrotik'];
          $password_mikrotik = $_POST['password_mikrotik'];
          $user_login = $_POST['user_login'];
          $password_login = $_POST['password_login'];
          $router_id = $_POST['router_id'];
          $file = fopen("router.txt", "a");
          fwrite($file, $host . "," . $user_mikrotik . "," . $password_mikrotik . "," . $user_login . "," . $password_login . "," . $router_id . "\n");
          fclose($file);
          // Redirect ke halaman router_configuration.php setelah data disimpan
          header("Location: adminku.php");
          exit;
        } else {
          // Tampilkan form untuk menambah data baru
          ?>
          <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
            <div class="form-group">
              <label for="host">Host:</label>
              <input type="text" class="form-control" id="host" name="host" required>
            </div>
            <div class="form-group">
              <label for="user_mikrotik">User Mikrotik:</label>
              <input type="text" class="form-control" id="user_mikrotik" name="user_mikrotik" required>
            </div>
            <div class="form-group">
              <label for="password_mikrotik">Password Mikrotik:</label>
              <input type="text" class="form-control" id="password_mikrotik" name="password_mikrotik" required>
            </div>
            <div class="form-group">
              <label for="user_login">User Login:</label>
              <input type="text" class="form-control" id="user_login" name="user_login" required>
            </div>
            <div class="form-group">
              <label for="password_login">Password Login:</label>
              <input type="text" class="form-control" id="password_login" name="password_login" required>
            </div>
            <div class="form-group">
              <label for="router_id">Router ID:</label>
              <input type="text" class="form-control" id="router_id" name="router_id" required>
</div>
<button type="submit" class="btn btn-primary">Add Configuration</button>
</form>
<?php } ?>
</div>
</div>

  </div>
</body>
</html>