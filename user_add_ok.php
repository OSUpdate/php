<?php
//DB 연결
include_once ("dbcon.php");
 //등록된 회원인지를 검사
$userid = $_POST['userid'];
$password = $_POST['password'];
$password_re = $_POST['password_re'];
$name =  $_POST['nickname'];
$email = $_POST['email'];

$sql = "SELECT * FROM member WHERE userid = ?";
$params = array($userid);
$options = array( "Scrollable" => SQLSRV_CURSOR_KEYSET ); 
$res = sqlsrv_query($conn , $sql , $params , $options);
$row_count = sqlsrv_num_rows ( $res );
 
if($row_count == 1) {
echo "<script>
alert('이미 등록된 아이디 입니다.');
history.back();
</script>";
die;
}
//필수 입력 항목을 모두 입력했는지 검사
if( $userid == "" || $password == "" || $password_re == "" || $password != $password_re || $name == "" || $email == ""){
echo"<script>
alert('필수입력란을 정확히 입력하십시오.');
history.back();
</script>";
die;
}

else{// DB에 등록

$sql1 = "insert into member ( userid, nickname, password, email) values ( ?, ?, ?, ?)"; 
$params1 = array( $userid , $name , $password , $email);
$res1 = sqlsrv_query($conn , $sql1 , $params1 );

$row_count1 = sqlsrv_rows_affected ( $res1 ); 
	if($row_count1 == false) {
		echo "<script>
		  alert('서버 에러');
		  history.back();
		  </script>";
	}elseif($row_count1 == -1){
		echo "<script>
		alert('정보가 없습니다.');
		history.back();
		</script>";
	 } else {
		echo "<script>
		 alert('가입 완료');
		location.replace('index.php');
		</script>";
 }
 }
 

 ?> 
