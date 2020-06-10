<?php


$servername= "znci.webhop.net";
$username= "grocery";
$password= "Gr0c3ry^&*()";
$dbname= "grocery";


$conn= mysqli_connect($servername,$username,$password,$dbname);

if($conn){
	echo "";
}
else{
	die("Connection failed ".mysql_connect_error());
}

?>