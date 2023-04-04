<!DOCTYPE html>
<html>
<head>
  <title>Edit Router Configuration</title>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.0/umd/popper.min.js"></script>
  <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</head>
<body>
  <div class="container mt-5">
    <h1 class="text-center mb-4">Edit Router Configuration</h1>
    <div class="row justify-content-center mt-5">
      <div class="col-md-8">
        <?php
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
          // Simpan data yang diubah ke file
          $line_number = $_POST['line_number'];
          $host = $_POST['host'];
          $user_mikrotik = $_POST['user_mikrotik'];
          $password_mikrotik = $_POST['password_mikrotik'];
          $user_login = $_POST['user_login'];
          $password_login = $_POST['password_login'];
          $router_id = $_POST['router_id'];
          $file = file("router.txt");
          $file[$line_number - 1] = $host . "," . $user_mikrotik . "," . $password_mikrotik . "," . $user_login . "," . $password_login . "," . $router_id . "\n";
          file_put_contents("router.txt", implode("", $file));
          // Redirect ke halaman router_configuration.php setelah data disimpan
          header("Location: adminku.php");
          exit;
        } else {
          // Tampilkan form untuk mengedit data
          $line_number = $_GET['line_number'];
          $file = fopen("router.txt", "r");
          $i = 0;
          while (($line = fgets($file)) !== false) {
            $i++;
            if ($i == $line_number) {
              $data = explode(",", $line);
              $host = $data[0];
              $user_mikrotik = $data[1];
              $password_mikrotik = $data[2];
              $user_login = $data[3];
              $password_login = $data[4];
              $router_id = $data[5];
              break;
            }
          }
          fclose($file);
          ?>
          <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
            <div class="form-group">
              <label for="host">Host:</label>
              <input type="text" class="form-control" id="host" name="host" value="<?php echo $host; ?>">
            </div>
            <div class="form-group">
              <label for="user_mikrotik">User Mikrotik:</label>
              <input type="text" class="form-control" id="user_mikrotik" name="user_mikrotik" value="<?php echo $user_mikrotik; ?>">
            </div>
            <div class="form-group">
              <label for="password_mikrotik">Password Mikrotik:</label>
              <input type="text" class="form-control" id="password_mikrotik" name="password_mikrotik" value="<?php echo $password_mikrotik; ?>">
        </div>
        <div class="form-group">
          <label for="user_login">User Login:</label>
          <input type="text" class="form-control" id="user_login" name="user_login" value="<?php echo $user_login; ?>">
        </div>
        <div class="form-group">
          <label for="password_login">Password Login:</label>
          <input type="text" class="form-control" id="password_login" name="password_login" value="<?php echo $password_login; ?>">
        </div>
        <div class="form-group">
          <label for="router_id">Router ID:</label>
          <input type="text" class="form-control" id="router_id" name="router_id" value="<?php echo $router_id; ?>">
        </div>
        <input type="hidden" name="line_number" value="<?php echo $line_number; ?>">
        <button type="submit" class="btn btn-primary">Save Changes</button>
      </form>
    <?php } ?>
  </div>
</div>
  </div>
</body>
</html>