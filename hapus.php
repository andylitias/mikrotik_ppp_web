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

// Remove PPP user
$api->comm("/ppp/secret/remove", [
    ".id" => $username,
]);

// Disconnect from MikroTik router
$api->disconnect();

// Redirect back to user information page
header("Location: index.php");
exit();
?>
