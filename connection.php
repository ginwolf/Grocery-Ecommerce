<?php


$DATABASE_HOST= "znci.webhop.net";
$DATABASE_USER= "grocery";
$DATABASE_PASS= "Gr0c3ry^&*()";
$DATABASE_NAME= "grocery";

require_once "classes/DB.php";

$conn= mysqli_connect($DATABASE_HOST,$DATABASE_USER,$DATABASE_PASS,$DATABASE_NAME);

try {
    $db = new DB($DATABASE_NAME, $DATABASE_HOST, $DATABASE_USER, $DATABASE_PASS);
} catch (Exception $e) {
    die($e->getMessage());
}


if($conn){
	echo "";
}
else{
	die("Connection failed ".mysql_connect_error());
}

?>