<?php
//변수
include ("dbcon.php");
$number = $_GET['number'];
$page = $_GET['page'];
 switch($_GET['List']){ //보드 검색하기 위한 부분
	case "Free":
		$board_name = "Freeboard";
		$list_name="Free";
		$comment_name="Fcomment";
		break;
	case "Image":
		$board_name = "Imageboard";
		$list_name="Image";
		$comment_name="Icomment";
		break;
}
$query = "select * from $board_name where board_idx = $number"; // 글 번호를 가지고 조회를 합니다.
$result = sqlsrv_query($conn,$query);
$array = sqlsrv_fetch_array($result,SQLSRV_FETCH_ASSOC);



$sql = "select board_idx from $board_name where board_idx = ?";
$params = array($number);
$options =  array( "Scrollable" => SQLSRV_CURSOR_KEYSET );
$result1 = sqlsrv_query($conn , $sql , $params, $options);
$row_count = sqlsrv_num_rows($result1);
if($row_count === false){
  die( print_r( sqlsrv_errors(), true));
}
else {  
    $sql1 = "delete from $board_name where board_idx = $number";
    sqlsrv_query($conn,$sql1); 
    $msg = "삭제하였습니다.";
}
 
//메시지를 출력하고 목록 페이지로 이동합니다.
echo " <html><head>
                <script name=javascript>
 
                if('$msg' != '') {
                        self.window.alert('$msg');
               }
 
                location.href='list.php?List=$list_name';
 
                </script>
                </head>
                </html> ";
?>
