<?php
switch($dbtype) {
    case 'MySQL':
        include ("mysql.php");
        break;
}
$db = new sql_db($dbhost, $dbuname, $dbpass, $dbname, false);
if(!$db -> db_connect_id) {
    die("error connection db");
}
?>