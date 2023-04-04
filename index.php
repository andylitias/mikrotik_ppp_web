<?php
if (isset($_COOKIE['router_host']) || isset($_COOKIE['router_username']) || isset($_COOKIE['router_password'])) {
    header("Location: home.php");
    exit();
}
// Jika form login disubmit
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Baca nilai username dan password dari form
    $input_username = trim($_POST["username"]);
    $input_password = trim($_POST["password"]);

    // Buka file router.txt dan baca baris per baris
    $file = fopen("router.txt", "r");
    while (!feof($file)) {
        $line = fgets($file);
        $parts = explode(",", $line);
        $host = trim($parts[0]);
        $usermikrotik = trim($parts[1]);
        $passmikrotik = trim($parts[2]);
        $user = trim($parts[3]);
        $pass = trim($parts[4]);

        // Jika username dan password benar, redirect ke file lain dengan cookie
        if ($input_username == $user && $input_password == $pass) {
            setcookie("router_host", $host, time() + (86400 * 30), "/"); // Set cookie untuk 30 hari
            setcookie("router_username", $usermikrotik, time() + (86400 * 30), "/"); // Set cookie untuk 30 hari
            setcookie("router_password", $passmikrotik, time() + (86400 * 30), "/"); // Set cookie untuk 30 hari
            fclose($file);
            header("Location: home.php");
            exit();
        }
    }
    fclose($file);

    // Jika username atau password salah, tampilkan pesan error
    $error = "Username atau password salah.";
}
?>

<!DOCTYPE html>
<html>
<head>
	<title>Form Login</title>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
	<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.0/umd/popper.min.js"></script>
	<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</head>
<body>
	<div class="container mt-5">
		<h1 class="text-center mb-4">Form Login</h1>
		<div class="row justify-content-center">
			<div class="col-md-6">
				<?php if (isset($error)) { echo '<div class="alert alert-danger">'.$error.'</div>'; } ?>
				<form method="POST">
					<div class="form-group">
						<label for="username">Username:</label>
						<input type="text" class="form-control" name="username" value="">
					</div>
					<div class="form-group">
						<label for="password">Password:</label>
						<input type="password" class="form-control" name="password" value="">
					</div>
					<button type="submit" class="btn btn-primary">Login</button>
				</form>
			</div>
		</div>
	</div>
</body>
</html>
