<?php
 //db ���� �κ��Դϴ�.
include("./session.php");
//�Է���(write.php)���� ���۵� ������ ������ ����ϴ�.
$sql1="select nickname from member where userid = $user_check";
$result = sqlsrv_query($conn , $sql1);
$row = sqlsrv_fetch_array($result,SQLSRV_FETCH_ASSOC);
echo $login_session;
 $nickname = $row['nickname'];
 $number = $_POST['number'];
 $memo = $_POST['memo'];
  switch($_POST['List']){ //���� �˻��ϱ� ���� �κ�
	case "Free":
		$comment_name = "Fcomment";
		break;
	case "Image":
		$comment_name = "Icomment";
		break;
}
 //����Ʈ ���� �ʿ��� �������� ����Ʈ ���� �ֽ��ϴ�.
 $writetime = time();
 $sql = "delete from $comment_name where board_idx=$number and nickname=$nickname";
$params = array($nickname , $number , $memo , $writetime);
$res = sqlsrv_query($conn , $sql , $params);
if( $res === false) {
    die( print_r( sqlsrv_errors(), true) );
}
$msg = "�����Ǿ����ϴ�.";
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