<?php

 
    include("dbcon.php");  //DB연결을 위한 config.php를 로딩합니다.
	$HTTP_REFERER = $_SERVER['HTTP_REFERER'];
	if(!$HTTP_REFERER) 
		$HTTP_REFERER = "./index.php";
    session_start();   //세션의 시작

 
    if($_SERVER["REQUEST_METHOD"] == "POST"){

 
    $myusername=$_POST['userid']; 

    $mypassword=$_POST['password']; 

 
    $sql="SELECT * FROM member WHERE userid= ? and password= ?";
	$params = array($myusername,$mypassword);
    $options = array( "Scrollable" => SQLSRV_CURSOR_KEYSET ); 
    $result=sqlsrv_query($conn , $sql , $params , $options);

    $count=sqlsrv_num_rows($result);


    if($count == 1)  //count가 1이라는 것은 아이디와 패스워드가 일치하는 db가 하나 있음을 의미합니다. 

    {

        $_SESSION['login_user']=$myusername;
	header(Location:$HTTP_REFERER);

    }

    else

    {
	echo "<script>
alert('아이디 혹은 비밀번호가 틀렸습니다.');
history.back();
</script>";

    }

}

?>


