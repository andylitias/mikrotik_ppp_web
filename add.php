<?php
// Cek apakah cookie sudah diset atau belum
if (
    !isset($_COOKIE["router_host"]) ||
    !isset($_COOKIE["router_username"]) ||
    !isset($_COOKIE["router_password"])
) {
    header("Location: index.php");
    exit();
}

$host = $_COOKIE["router_host"];
$username = $_COOKIE["router_username"];
$password = $_COOKIE["router_password"];

// Load API MikroTik PHP
require "RouterOSAPI.php";

// Connect to MikroTik router
$api = new RouterosAPI();
$api->connect($host, $username, $password);

// Load PPP profiles
$profiles = $api->comm("/ppp/profile/print");
$profile_options = "";
foreach ($profiles as $profile) {
    $profile_name = $profile["name"];
    $profile_options .= '<option value="' . $profile_name . '">' . $profile_name . '</option>';
    
    // Get Local Address and Remote Address from profile
    $profile_info = $api->comm("/ppp/profile/print", [
        "?name" => $profile_name,
        ".proplist" => "local-address,remote-address"
    ]);
    $local_address = $profile_info[0]["local-address"];
    $remote_address = $profile_info[0]["remote-address"];
    
    // Add data attribute to option element with Local Address and Remote Address
    $profile_options = str_replace(
        '<option value="' . $profile_name . '">',
        '<option value="' . $profile_name . '" data-local-address="' . $local_address . '" data-remote-address="' . $remote_address . '">',
        $profile_options
    );
}

// Retrieve input data
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST["username"];
    $password = $_POST["password"];
    $profile = $_POST["profile"];
$user_profile = $_POST["profile"];

    // Check if username is already used
    $existing_users = $api->comm("/ppp/secret/print", [
        "?name" => $username,
    ]);
    if (count($existing_users) > 0) {
        die("Error: Username already used.");
    }

    // Add new PPP user
    $api->comm("/ppp/secret/add", [
        "name" => $username,
        "password" => $password,
        "profile" => $profile,
    ]);

    // Redirect back to user information page
    header("Location: edit.php?username=" . $username);
    exit();
}

// Disconnect from MikroTik router
$api->disconnect();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Add PPP User</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css">
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/css/bootstrap-datepicker.min.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/js/bootstrap.min.js"></script>
</head>
<body>
<div class="container">
    <h2>Add PPP User</h2>
    <form action="add.php" method="post">
        <div class="form-group">
            <label for="username">Username:</label>
            <input type="text" class="form-control" id="username" name="username" autocomplete="false" required>
        </div>
        <div class="form-group">
            <label for="password">Password:</label>
            <input type="password" class="form-control" id="password" name="password" autocomplete="false">
        </div>
        <div class="form-group">
            <label for="profile">Profile:</label>
            <select class="form-control" id="profile" name="profile">
                <?php echo $profile_options; ?>
            </select>
        </div>
        <div class="form-group">
            <label for="local_address">Local Address:</label>
            <input type="text" class="form-control" id="local_address" value="" readonly>
        </div>
        <div class="form-group">
            <label for="remote_address">Remote Address:</label>
            <input type="text" class="form-control" id="remote_address" value="" readonly>
        </div>
        <div class="form-group">
            <label for="rate_limit">Rate Limit:</label>
            <input type="text" class="form-control" id="rate_limit" value="" readonly>
        </div>
        <button type="submit" class="btn btn-primary">Add User</button>
        <a href="index.php" class="btn btn-warning">Back</a>
    </form>
</div>
<script type="text/javascript">
// Function to fill Local Address, Remote Address, and Rate Limit based on selected PPP profile
function fillFields() {
    var selectedProfile = $("#profile").val();
    var profiles = <?php echo json_encode($profiles); ?>;
    for (var i = 0; i < profiles.length; i++) {
        if (profiles[i]["name"] == selectedProfile) {
            $("#local_address").val(profiles[i]["local-address"]);
            $("#remote_address").val(profiles[i]["remote-address"]);
            var rate_limit = profiles[i]["rate-limit"] || "Unlimited";
            $("#rate_limit").val(rate_limit);
            break;
        }
    }
}

// Call function on page load
fillFields();

// Call function on select change
$("#profile").change(function() {
    fillFields();
});

</script>
</body>
</html>
