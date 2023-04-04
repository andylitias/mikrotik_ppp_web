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
// Logout function
if (isset($_GET["action"]) && $_GET["action"] == "logout") {
    // Hapus cookie dan redirect ke halaman login
    setcookie("router_host", "", time() - 3600, "/");
    setcookie("router_username", "", time() - 3600, "/");
    setcookie("router_password", "", time() - 3600, "/");
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

// Get PPP user information
$user_info = $api->comm("/ppp/secret/print");

// Get active PPP users
$active_users = $api->comm("/ppp/active/print");

// Get system identity information
$identity_info = $api->comm("/system/identity/print");
$nama_router = $identity_info[0]["name"];

// Convert time string to human-readable format
function format_time($time_string)
{
    $weeks = 0;
    $days = 0;
    $hours = 0;
    $minutes = 0;
    $seconds = 0;

    // Split time string into parts
    $parts = explode("w", $time_string);
    if (count($parts) > 1) {
        $weeks = intval($parts[0]);
        $time_string = $parts[1];
    }

    $parts = explode("d", $time_string);
    if (count($parts) > 1) {
        $days = intval($parts[0]);
        $time_string = $parts[1];
    }

    $parts = explode("h", $time_string);
    if (count($parts) > 1) {
        $hours = intval($parts[0]);
        $time_string = $parts[1];
    }

    $parts = explode("m", $time_string);
    if (count($parts) > 1) {
        $minutes = intval($parts[0]);
        $time_string = $parts[1];
    }

    $parts = explode("s", $time_string);
    if (count($parts) > 0) {
        $seconds = intval($parts[0]);
    }

    // Calculate total number of seconds
    $total_seconds =
        $weeks * 7 * 24 * 60 * 60 +
        $days * 24 * 60 * 60 +
        $hours * 60 * 60 +
        $minutes * 60 +
        $seconds;

    // Convert seconds to human-readable format
    $days = floor($total_seconds / (24 * 60 * 60));
    $hours = floor(($total_seconds % (24 * 60 * 60)) / (60 * 60));
    $minutes = floor(($total_seconds % (60 * 60)) / 60);

    $result = "";
    if ($days > 0) {
        $result .= $days . " hari ";
    }
    if ($hours > 0) {
        $result .= $hours . " jam ";
    }
    if ($minutes > 0) {
        $result .= $minutes . " menit";
    }
    if ($result == "") {
        $result = "-";
    }

    return $result;
}

// Function to compare usernames for sorting
function compare_username($a, $b, $column)
{
    if ($column == "status") {
        return compare_status($a, $b);
    } else {
        return strcmp($a[$column], $b[$column]);
    }
}

// Function to compare status for sorting
function compare_status($a, $b)
{
    $a_status = getStatus($a["name"]);
    $b_status = getStatus($b["name"]);

    // Define status order
    $status_order = ["Online", "Disabled", "Offline"];

    // Get status order index for each user
    $a_index = array_search($a_status, $status_order);
    $b_index = array_search($b_status, $status_order);

    // Compare status order index of $a and $b
    if ($a_index == $b_index) {
        return strcmp($a["name"], $b["name"]);
    } else {
        return $a_index < $b_index ? -1 : 1;
    }
}

// Function to get the status of a user
function getStatus($username)
{
    global $active_users, $user_info;
    // Check if user is disabled
    foreach ($user_info as $user) {
        if ($user["name"] == $username) {
            if (isset($user["disabled"]) && $user["disabled"] == "true") {
                return "Disabled";
            } else {
                break;
            }
        }
    }

    // Check if user is online
    foreach ($active_users as $active_user) {
        if ($active_user["name"] == $username) {
            return "Online";
        }
    }

    // User is offline
    return "Offline";
}
?>

<!DOCTYPE html>
<html>
<head>
<?php // Check if message parameter exists

if (isset($_GET["pesan"])) {
    $pesan = $_GET["pesan"];
    echo "<script>alert('$pesan');</script>";
} ?>
    <title>User PPP Information</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
    .search-container {
        display: flex;
        align-items: center;
    }
    .search-container input[type=text] {
        padding: 10px;
        margin-right: 10px;
        border: none;
        border-radius: 4px;
        font-size: 17px;
        width: 100%;
        background: #f1f1f1;
    }
    .search-container input[type=text]:focus {
        background-color: #ddd;
        outline: none;
    }
    .search-container button {
        border: none;
        color: white;
        padding: 10px 16px;
        font-size: 17px;
        border-radius: 4px;
        cursor: pointer;
        background-color: #4CAF50;
    }
    .search-container button:hover {
        background-color: #45a049;
    }
    @media screen and (max-width: 600px) {
        .search-container {
            flex-direction: column;
            align-items: stretch;
        }
        .search-container button {
            margin-top: 10px;
        }
    }
</style>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/js/bootstrap.min.js"></script>
    <script>
        $(document).ready(function() {
            // Add search box
            $("#myInput").on("keyup", function() {
                var value = $(this).val().toLowerCase();
                $("#myTable tr").filter(function() {
                    $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
                });
            });

            // Add sort functionality
            $("th.sortable").click(function() {
                var column = $(this).attr("data-column");
                var order = $(this).attr("data-order");
                var text = $(this).text();

                // Remove arrow from all other headers
                $("th.sortable").each(function() {
                    if ($(this).text() != text) {
                        $(this).html($(this).text());
                        $(this).attr("data-order", "desc");
                    }
                });

                // Toggle arrow for clicked header
                if (order == "desc") {
                    $(this).html(text + " <span class='glyphicon glyphicon-chevron-up'></span>");
                    $(this).attr("data-order", "asc");
                } else {
                    $(this).html(text + " <span class='glyphicon glyphicon-chevron-down'></span>");
                    $(this).attr("data-order", "desc");
                }

                // Sort table
                var table = $("#myTable");
                var rows = table.find("tr:gt(0)").toArray();
                rows.sort(sortByColumn(column, order));
                for (var i = 0; i < rows.length; i++) {
                    table.append(rows[i]);
                }
            });

            function sortByColumn(column, order) {
                return function(a, b) {
                    var aVal = $(a).find("td:eq(" + column + ")").text();
                    var bVal = $(b).find("td:eq(" + column + ")").text();
                    if ($.isNumeric(aVal) && $.isNumeric(bVal)) {
                        aVal = parseFloat(aVal);
                        bVal = parseFloat(bVal);
                    }
                    if (order == "asc") {
                        return (aVal > bVal) ? 1 : -1;
                    } else {
                        return (aVal < bVal) ? 1 : -1;
                    }
                };
            }
        });
    </script>
</head>
<body>

<div class="container">
	<div class="row">
		<div class="col-md-6">
		<h2>User PPP Information</h2>
		</div>
		<div class="col-md-6 text-right">
		<br>
			<p><a href="?action=logout" class="btn btn-danger">Logout</a></p>
		</div>
	</div>
	<hr>
	<p>System Identity: <?php echo $nama_router; ?></p>
    <p>Total Secret: <?php echo count($user_info); ?></p>
    <p>Total Active: <?php echo count($active_users); ?></p>
    <p>Terputus: <?php echo count($user_info) - count($active_users); ?></p>
    <hr>
    <div class="search-container">
        <input type="text" id="myInput" placeholder="Search..">
    </div>
    <div class="row">
        <div class="col-md-2">
			<br>
            <button class="btn btn-primary" onclick="location.href='add.php'">Tambah User</button>
        </div>
		<div class="col-md-2">
			<br>
            <button class="btn btn-success" onclick="location.href='config.php'">Configuration</button>
        </div>
    </div>
    <br>
    <table class="table table-striped">
        <thead>
            <tr>
                <th class="sortable" data-column="0" data-order="desc">User</th>
                <th class="sortable" data-column="1" data-order="desc">IP Address</th>
                <th class="sortable" data-column="1" data-order="desc">Profile</th>
                <th class="sortable" data-column="1" data-order="desc">Last Logged Out</th>
                <th class="sortable" data-column="2" data-order="desc">Uptime</th>
                <th class="sortable" data-column="4" data-order="desc">Status</th>
<th>Action</th>
</tr>
</thead>
<tbody id="myTable">
<?php // Loop through user information and display user profile, username, and comment

foreach ($user_info as $user) {
    $service_profile = $user["profile"];
    $username = $user["name"];
    $lastloggedout = $user["last-logged-out"];
    $secretenable = $comment = $user["comment"];
    $remote_address = $user["remote-address"];
    // Check the status of the user and get the IP address
    $status = "Offline";
    $ip_address = "-";
    foreach ($active_users as $active_user) {
        if ($active_user["name"] == $username) {
            $status = "Online";
            $ip_address = $active_user["address"];
            break;
        }
    }

    $uptime = "-";
    foreach ($active_users as $active_user) {
        if ($active_user["name"] == $username) {
            $status = "Online";
            $uptime = $active_user["uptime"];
            break;
        }
    }

    // Set the style of the status column based on the user's status
    if (getStatus($username) == "Online") {
        $status_style = "success";
    } elseif (getStatus($username) == "Disabled") {
        $status_style = "warning";
    } else {
        $status_style = "danger";
    }

    if (!empty($comment)) {
        echo "<td>" . $username . " / " . $comment . "</td>";
    } else {
        echo "<td>" . $username . "</td>";
    }
    echo "<td>" . $ip_address . "</td>";
    echo "<td>" . $service_profile . "</td>";
    echo "<td>" . $lastloggedout . "</td>";
    echo "<td>" . format_time($uptime) . "</td>";
    echo '<td><span class="label label-' .
        $status_style .
        '">' .
        getStatus($username) .
        "</span></td>";
    echo "<td><form action='edit.php' method='get'>
<input type='hidden' name='username' value='" .
        $username .
        "'>
<button type='submit' class='btn btn-primary'>Edit</button>
</form></td>";
    echo "</tr>";
} ?>
</tbody>
</table>

</div>
<script type="text/javascript">
function sortByColumn(column, order) {
    return function(a, b) {
        var aVal = $(a).find("td:eq(" + column + ")").text();
        var bVal = $(b).find("td:eq(" + column + ")").text();
        if ($.isNumeric(aVal) && $.isNumeric(bVal)) {
            aVal = parseFloat(aVal);
            bVal = parseFloat(bVal);
        }
        if (column == 4) { // If sorting by status, use compare_status() function
            return compare_username(a.dataset, b.dataset, "status") * (order == "asc" ? 1 : -1);
        } else {
            return compare_username(a.dataset, b.dataset, column) * (order == "asc" ? 1 : -1);
        }
    };
}
</script>
</body>
</html>
<?php // Disconnect from MikroTik router

$api->disconnect();
?>
