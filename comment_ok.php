<?php
 //db 연결 부분입니다.
include("./session.php");
//입력폼(write.php)에서 전송된 내용을 변수에 담습니다.
$sql1="select nickname from member where userid = $user_check";
$result = sqlsrv_query($conn , $sql1);
$row = sqlsrv_fetch_array($result,SQLSRV_FETCH_ASSOC);
echo $login_session;
 $nickname = $row['nickname'];
 $number = $_POST['number'];
 $memo = $_POST['memo'];
  switch($_POST['List']){ //보드 검색하기 위한 부분
	case "Free":
		$comment_name = "Fcomment";
		break;
	case "Image":
		$comment_name = "Icomment";
		break;
}
 //디폴트 값이 필요한 변수에는 디폴트 값을 넣습니다.
 $writetime = time();
 $sql = "insert into $comment_name(nickname , board_idx , content , Date) values ( ? , ? , ? , ?)";
$params = array($nickname , $number , $memo , $writetime);
$res = sqlsrv_query($conn , $sql , $params);
if( $res === false) {
    die( print_r( sqlsrv_errors(), true) );
}
$msg = "성공적으로 등록되었습니다";
echo " <html><head>
                 <script name=javascript>
 
                 if('$msg' != '') {
                         self.window.alert('$msg');
                 }

                 history.back();
 
                 </script>
                 </head>
                 </html> ";

?>