<?php
include("./session.php");
//�Է���(write.php)���� ���۵� ������ ������ ����ϴ�.
$sql1="select nickname from member where userid = $user_check";
$result = sqlsrv_query($conn , $sql1);

$row = sqlsrv_fetch_array($result,SQLSRV_FETCH_ASSOC);
$upfile = $_FILES['upfile'];
$upfile_name = $_FILES['upfile']['name']; 
$img_file = $_FILES['upfile']['tmp_name']; 
 $nickname = $row['nickname'];
 $subject = $_POST['subject'];
 $memo = $_POST['memo'];
 //����Ʈ ���� �ʿ��� �������� ����Ʈ ���� �ֽ��ϴ�.
 $writetime = time();
 $ip = getenv("REMOTE_ADDR");
 switch($_GET['List']){ //���� �˻��ϱ� ���� �κ�
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
################���� ���ε带 ���� �߰��� �κ� : ���� ######################### 

// ���ε��� ������ ����� ���丮 ����
 $target_dir = "./upload/";  // ������ up �̶�� ���丮�� �־�� �Ѵ�.
 
// if(strcmp($upfile,"none")) {   // ������ ���ε�Ǿ��� ���
 if($img_file) {   // ������ ���ε�Ǿ��� ���
 
// ���ε� ���� ���� �ĺ� �κ�
    $filename = explode(".", $upfile_name);
    $extension = strtolower(substr(strrchr($_FILES['upfile']['name'],"."),1));
    if(!strcmp($extension,"html") || 
       !strcmp($extension,"htm") ||
       !strcmp($extension,"php") ||      
       !strcmp($extension,"inc")&& 
		($_FILES["upfile"]["size"] < 10485760)){
  
       $msg = "���ε尡 ������ �����Դϴ�.";
    }
// ������ ������ �ִ��� Ȯ���ϴ� �κ�
    $target = $target_dir.$upfile_name;
    if(file_exists($target)) {
	   $msg = "������ ������ �ֽ��ϴ�.";
    }
 else{
	 move_uploaded_file($img_file, $target);
	}
}

################���� ���ε带 ���� �߰��� �κ� : �� ######################### 
$sql = "update $board_name set nickname='$name',title='$subject',content='$memo', file_name='$target',s_file_name='$upfile_name' where number=$number"; 
$res = sqlsrv_query($conn , $sql);

if ($msg=='') {
	$msg = "���������� �����Ǿ����ϴ�"; 
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