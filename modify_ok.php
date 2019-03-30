<?php
include("./session.php");
//입력폼(write.php)에서 전송된 내용을 변수에 담습니다.
$sql1="select nickname from member where userid = $user_check";
$result = sqlsrv_query($conn , $sql1);

$row = sqlsrv_fetch_array($result,SQLSRV_FETCH_ASSOC);
$upfile = $_FILES['upfile'];
$upfile_name = $_FILES['upfile']['name']; 
$img_file = $_FILES['upfile']['tmp_name']; 
 $nickname = $row['nickname'];
 $subject = $_POST['subject'];
 $memo = $_POST['memo'];
 //디폴트 값이 필요한 변수에는 디폴트 값을 넣습니다.
 $writetime = time();
 $ip = getenv("REMOTE_ADDR");
 switch($_GET['List']){ //보드 검색하기 위한 부분
	case "Free":
		$board_name = "Freeboard";
		$list_name="Free";
		break;
	case "Image":
		$board_name = "Imageboard";
		$list_name="Image";
		break;
}
 $count = 0;
################파일 업로드를 위해 추가된 부분 : 시작 ######################### 

// 업로드한 파일이 저장될 디렉토리 정의
 $target_dir = "./upload/";  // 서버에 up 이라는 디렉토리가 있어야 한다.
 
// if(strcmp($upfile,"none")) {   // 파일이 업로드되었을 경우
 if($img_file) {   // 파일이 업로드되었을 경우
 
// 업로드 금지 파일 식별 부분
    $filename = explode(".", $upfile_name);
    $extension = strtolower(substr(strrchr($_FILES['upfile']['name'],"."),1));
    if(!strcmp($extension,"html") || 
       !strcmp($extension,"htm") ||
       !strcmp($extension,"php") ||      
       !strcmp($extension,"inc")&& 
		($_FILES["upfile"]["size"] < 10485760)){
  
       $msg = "업로드가 금지된 파일입니다.";
    }
// 동일한 파일이 있는지 확인하는 부분
    $target = $target_dir.$upfile_name;
    if(file_exists($target)) {
	   $msg = "동일한 파일이 있습니다.";
    }
 else{
	 move_uploaded_file($img_file, $target);
	}
}

################파일 업로드를 위해 추가된 부분 : 끝 ######################### 
$sql = "update $board_name set nickname='$name',title='$subject',content='$memo', file_name='$target',s_file_name='$upfile_name' where number=$number"; 
$res = sqlsrv_query($conn , $sql);

if ($msg=='') {
	$msg = "성공적으로 수정되었습니다"; 
	 echo " <html><head> 
                 <script name=javascript> 
                  if('$msg' != '') { 
                         self.window.alert('$msg'); 
                 } 
                 location.href='list.php?List=$list_name'; 
                 </script> 
                 </head> 
                 </html> "; 
	sqlsrv_query($conn,$sql);
} else {
	 echo " <html><head> 
                 <script name=javascript> 
                 if('$msg' != '') { 
                         self.window.alert('$msg'); 
                 } 
                 history.go(-1);
                 </script> 
                 </head> 
                 </html> "; 
}

?>