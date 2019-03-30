<?php

include('dbcon.php');

session_start();

$user_check=$_SESSION['login_user'];

 
$sql="select nickname from member where userid = $user_check";
$ses_sql = sqlsrv_query($conn , $sql);
 
$row=sqlsrv_fetch_array($ses_sql,SQLSRV_FETCH_ASSOC);

$login_session=$row['nickname'];

 
if(!isset($login_session))

{

header("Location: ./index.php");

}

 

?>

