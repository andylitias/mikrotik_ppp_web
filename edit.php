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

// Get username from query parameter
$username = $_GET["username"];

// Get user information
$user_info = $api->comm("/ppp/secret/print", [
    "?name" => $username,
]);

// Get PPP profiles
$profiles = $api->comm("/ppp/profile/print");
$profile_options = "";
foreach ($profiles as $profile) {
    $profile_name = $profile["name"];
    $selected = "";
    if ($profile_name == $user_info[0]["profile"]) {
        $selected = "selected";
    }
    $profile_options .=
        '<option value="' .
        $profile_name .
        '" ' .
        $selected .
        ">" .
        $profile_name .
        "</option>";

    // Get Local Address, Remote Address, and Rate Limit from profile
    $profile_info = $api->comm("/ppp/profile/print", [
        "?name" => $profile_name,
        ".proplist" => "local-address,remote-address,rate-limit",
    ]);
    $local_address = $profile_info[0]["local-address"];
    $remote_address = $profile_info[0]["remote-address"];
    $rate_limit = $profile_info[0]["rate-limit"];

    // Add data attribute to option element with Local Address, Remote Address, and Rate Limit
    $profile_options = str_replace(
        '<option value="' . $profile_name . '">',
        '<option value="' .
            $profile_name .
            '" data-local-address="' .
            $local_address .
            '" data-remote-address="' .
            $remote_address .
            '" data-rate-limit="' .
            $rate_limit .
            '">',
        $profile_options
    );
}

// Disconnect from MikroTik router
$api->disconnect();
?>
<!DOCTYPE html>
<html>
<head>
<?php // Check if message parameter exists

if (isset($_GET["pesan"])) {
    $pesan = $_GET["pesan"];
    echo "<script>alert('$pesan');</script>";
} ?>
<title>Edit User PPP Information</title>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/css/bootstrap-datepicker.min.css">
<style>
.form-group label {
display: block;
margin-bottom: 5px;
}
.table td {
padding: 0;
}
.table td textarea {
border: 0;
width: 100%;
}
</style>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/js/bootstrap.min.js"></script>
</head>
<body>
<div class="container">
    <h2>Edit User PPP Information</h2>
    <form action="ubah.php" method="post">
        <div class="form-group">
    <label for="username">User PPPoE:</label>
    <input type="text" class="form-control" id="username" name="username" value="<?php echo $username; ?>" readonly>
</div>
<div class="form-group">
    <label for="password">Password PPPoE:</label>
    <input type="text" class="form-control" id="password" name="password" value="<?php echo $user_info[0][
        "password"
    ]; ?>">
</div>
<div class="form-group">
    <label for="profile">Profile:</label>
    <select class="form-control" id="profile" name="profile">
        <?php echo $profile_options; ?>
    </select>
</div>
<div class="form-group">
    <label for="local_address">Local Address:</label>
    <input type="text" class="form-control" id="local_address" name="local_address" value="" readonly>
</div>
<div class="form-group">
    <label for="remote_address">Remote Address:</label>
    <input type="text" class="form-control" id="remote_address" name="remote_address" value="" readonly>
</div>
<div class="form-group">
    <label for="rate_limit">Rate Limit:</label>
    <input type="text" class="form-control" id="rate_limit" value="" readonly>
</div>
<div class="form-group">
    <label for="status">Status:</label>
    <select class="form-control" id="status" name="status">
        <?php if ($user_info[0]["disabled"] == "true") {
            // user disabled
            echo '<option value="false">Enable</option>';
            echo '<option value="true" selected>Disable</option>';
        } else {
            // user enable
            echo '<option value="false" selected>Enable</option>';
            echo '<option value="true">Disable</option>';
        } ?>
    </select>
</div>
<button type="submit" class="btn btn-success">Simpan</button>
<a href="index.php" class="btn btn-warning">Kembali</a>
        <a href="hapus.php?username=<?php echo $username; ?>" class="btn btn-danger" onclick="return confirm('Apakah Anda yakin ingin menghapus user <?php echo $username; ?>?')">Hapus</a>
    </form>
    <br>
</div>
<script type="text/javascript">
function fillAddressFields() {
    var selectedProfile = $("#profile").val();
    var profiles = <?php echo json_encode($profiles); ?>;
    var localAddress = "<?php echo isset($user_info[0]["local-address"])
        ? $user_info[0]["local-address"]
        : ""; ?>";
    var remoteAddress = "<?php echo isset($user_info[0]["remote-address"])
        ? $user_info[0]["remote-address"]
        : ""; ?>";
    
    if (localAddress != "" && remoteAddress != "") {
        // If Local Address and Remote Address are set in "/ppp/secret", display them and make the fields editable
        $("#local_address").val(localAddress).prop('readonly', false);
        $("#remote_address").val(remoteAddress).prop('readonly', false);
        var rate_limit = "<?php echo isset($user_info[0]["rate-limit"])
            ? $user_info[0]["rate-limit"]
            : "Unlimited"; ?>";
        $("#rate_limit").val(rate_limit);
    } else {
        // If Local Address and Remote Address are not set in "/ppp/secret", get them from selected PPP profile in "/ppp/profile" and make the fields read-only
        for (var i = 0; i < profiles.length; i++) {
            if (profiles[i]["name"] == selectedProfile) {
                $("#local_address").val(profiles[i]["local-address"]).prop('readonly', true);
                $("#remote_address").val(profiles[i]["remote-address"]).prop('readonly', true);
                var rate_limit = profiles[i]["rate-limit"] || "Unlimited";
                $("#rate_limit").val(rate_limit);
                break;
            }
        }
    }
}

// Call function on page load
fillAddressFields();

// Call function on select change
$("#profile").change(function() {
    fillAddressFields();
});
</script>
</body>
</html>