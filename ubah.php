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

// Get username from form submission
$username = $_POST["username"];
$password = $_POST["password"];
$profile = $_POST["profile"];
$status = $_POST["status"];
// Get the values from the form submission
$user_profile = $_POST["profile"];

// Get user information
$user_info = $api->comm("/ppp/secret/print", [
    "?name" => $username,
]);

// Update user's password and profile
$api->comm("/ppp/secret/set", [
    ".id" => $user_info[0][".id"],
    "password" => $password,
    "profile" => $profile,
    "disabled" => $status == "true" ? "true" : "false",
]);

// If status is true, remove user from /ppp/active
if ($status == "true") {
    // Find all active users with the same name
    $active_users = $api->comm("/ppp/active/print", [
        "?name" => $username,
        ".proplist" => ".id",
    ]);

    foreach ($active_users as $active_user) {
        // Remove user from /ppp/active
        $api->comm("/ppp/active/remove", [
            ".id" => $active_user[".id"],
        ]);
    }
}

// Disconnect from MikroTik router
$api->disconnect();

// Redirect back to edit page
header("Location: edit.php?username=$username");
exit();
?>
