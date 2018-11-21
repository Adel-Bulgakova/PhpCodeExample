<?php
session_start();
error_reporting(E_ALL);

include "/www/config.php";
global $project_options;

$api_url_path = "https://" . $_SERVER["HTTP_HOST"];
$base_storage_server = $project_options["storage_servers"][0];

if (($_SESSION["api_user"] != "api_user")) :

    header("Location: /api/desc/");

else :
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Api v1 PROGECT_NAME</title>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <link rel="stylesheet" href="//netdna.bootstrapcdn.com/bootstrap/3.1.1/css/bootstrap.min.css">
    <script src="/assets/js/jquery-2.1.4.min.js"></script>
    <script src="https://google-code-prettify.googlecode.com/svn/loader/run_prettify.js?lang=php&skin=sunburst"></script>
    <script src="//netdna.bootstrapcdn.com/bootstrap/3.1.1/js/bootstrap.min.js"></script>

    <style>
        .navbar-default {
            background-color: rgba(94,3,150, 0.25);
            border: none;
        }

        .navbar-default .navbar-nav>li>a:hover, .navbar-default .navbar-nav>li>a:focus {
            color: #666;
            background-color: transparent;
        }

        .navbar-default .navbar-nav>li>a {
            color: #fff;
        }

        h2 {
            margin-top: 70px;
        }

        .table td {
            word-wrap: break-word;
            word-break: break-all;
        }

        .table>thead>tr>th, .table>tbody>tr>th, .table>tfoot>tr>th, .table>thead>tr>td, .table>tbody>tr>td, .table>tfoot>tr>td {
            padding: 25px 8px;
        }

        .table-striped>tbody>tr:nth-child(odd)>td, .table-striped>tbody>tr:nth-child(odd)>th {
            padding: 15px 8px;
            background-color: #8e9090;
            color: white;
        }
    </style>

</head>

<body>
<header class="header">
    <nav class="navbar navbar-default navbar-fixed-top">
        <div class="container">

            <ul class="nav navbar-nav">
                <li><a href="#streams">Streams</a></li>
                <li><a href="#users">Users</a></li>
                <li><a href="#actions">Actions</a></li>
                <li><a href="#users_chat">Users chat</a></li>
                <li><a href="#media">Media</a></li>
                <li><a href="#other">Other</a></li>
            </ul>
        </div>
    </nav>
</header>
<div style="width: 90%; margin: auto;">
    <h2 align=center>API v1</h2>
    

</div>
</body>
</html>
<?php endif; ?>