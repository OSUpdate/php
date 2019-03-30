<!DOCTYPE html>
<?php 
include('dbcon.php');
session_start();
$page = $_GET['page'];
//게시판 목록보기에 필요한 각종 변수 초기값을 설정합니다. //테이블 이름
if($page == ''){$page = 1;} //페이지 번호가 없으면 1
$list_num = 5; //Free 게시판 한 페이지에 보여줄 목록 갯수
$image_list_num = 9; //한 페이지에 보여줄 목록 갯수
$offset = $list_num*($page-1); //한 페이지의 시작 글 번호(listnum 수만큼 나누었을 때 시작하는 글의 번호)
 
//전체 글 수를 구합니다. (쿼리문을 사용하여 결과를 배열로 저장하는 일반적 인 방법)
$query="select * from Freeboard board_idx "; // SQL 쿼리문을 문자열 변수에 일단 저장하고
$params = array();
$options =  array( "Scrollable" => SQLSRV_CURSOR_KEYSET );
$result = sqlsrv_query($conn,$query,$params,$options); // 위의 쿼리문을 실제로 실행하여 결과를 result에 대입
$row = sqlsrv_num_rows($result); //위 결과 값을 하나하나 배열로 저장합니다 .
if( $result === false) {
    die( print_r( sqlsrv_errors(), true) );
}
$user_check1 = $_SESSION['login_user'];
$nick_sql="select nickname from member where userid = $user_check1";
$nick1_sql = sqlsrv_query($conn , $nick_sql);
 if( $result === false) {
    die( print_r( sqlsrv_errors(), true) );
}
$nick_row = sqlsrv_fetch_array($nick1_sql,SQLSRV_FETCH_ASSOC);

if(isset($_SESSION['login_user'])){
	$user_check = $_SESSION['login_user'];
	$sql = "select userid from member where userid = $user_check";
	$ses_sql = sqlsrv_query($conn,$sql);
	$row1=sqlsrv_fetch_array($ses_sql,SQLSRV_FETCH_ASSOC);
	$login_session=$row1['userid'];
}

$total_no = $row; //배열의 첫번째 요소의 값, 즉 테이블의 전체 글 수를 저장합니다.
//전체 페이지 수와 현재 글 번호를 구합니다.
$total_page=ceil($total_no/$list_num); // 전체글수를 페이지당글수로 나눈 값의 올림 값을 구합니다.
$cur_num=$total_no - $list_num * ($page-1); //현재 글번호
 
//bbs테이블에서 목록을 가져옵니다. (위의 쿼리문 사용예와 비슷합니다 .)
$Free_query="select TOP $list_num * from Freeboard where board_idx not in (select top $offset board_idx from Freeboard order by board_idx desc)order by board_idx desc"; // SQL 쿼리문
$result1 = sqlsrv_query($conn, $Free_query); // 쿼리문을 실행 결과
if( $result1 === false) {
    die( print_r( sqlsrv_errors(), true) );
}
$Image_query="select TOP $image_list_num * from Imageboard where board_idx not in (select top $offset board_idx from Imageboard order by board_idx desc)order by board_idx desc"; // SQL 쿼리문
$Image_result = sqlsrv_query($conn, $Image_query); // 쿼리문을 실행 결과
if( $result1 === false) {
    die( print_r( sqlsrv_errors(), true) );
}
?>
<head>
    <meta charset=UTF-8>
<title>Beadysite - Free HTML5 Template</title>
<link href="./style.css" rel="stylesheet" type="text/css" media="screen" />
<link rel="stylesheet" href="./login/css/style.css">
<style>
body {
    font-family: "맑은 고딕"
}

/*-- POPUP common style S ======================================================================================================================== --*/
#mask {
    position: absolute;
    left: 0;
    top: 0;
    z-index: 999;
    background-color: #000000;
    display: none;
}

.layerpop {
    display: none;
	position: fixed;
    z-index: 1000;
    border: 2px solid #ccc;
    background: #fff;
    cursor: move;
}

.layerpop_area .title {
    padding: 10px 10px 10px 10px;
    border: 0px solid #aaaaaa;
    background: #f1f1f1;
    color: #3eb0ce;
    font-size: 1.3em;
	position: fixed;
    font-weight: bold;
    line-height: 24px;
}

.layerpop_area .layerpop_close {
    width: 25px;
    height: 25px;
    display: block;
    position: absolute;
    top: 10px;
    right: 10px;
    background: transparent url('./images/btn_exit_off.png') no-repeat;
}

.layerpop_area .layerpop_close:hover {
    background: transparent url('./images/btn_exit_on.png') no-repeat;
    cursor: pointer;
}

.layerpop_area .content {
    width: 96%;    
    margin: 2%;
    color: #828282;
}
/*-- POPUP common style E --*/

</style>
<script src="http://code.jquery.com/jquery-1.11.0.min.js"></script>
<script src="http://code.jquery.com/ui/1.11.0/jquery-ui.js"></script> 
<!--드래그 할수 있는 기능을 사용하기 위해서 draggable();-->

<script>

    function wrapWindowByMask() {
        //화면의 높이와 너비를 구한다.
        var maskHeight = $(document).height(); 
        var maskWidth = $(window).width();

        //문서영역의 크기 
        console.log( "document 사이즈:"+ $(document).width() + "*" + $(document).height()); 
        //브라우저에서 문서가 보여지는 영역의 크기
        console.log( "window 사이즈:"+ $(window).width() + "*" + $(window).height());        

        //마스크의 높이와 너비를 화면 것으로 만들어 전체 화면을 채운다.
        $('#mask').css({
            'width' : maskWidth,
            'height' : maskHeight
        });

        //애니메이션 효과
        //$('#mask').fadeIn(1000);      
        $('#mask').fadeTo("slow", 0.5);
    }

    function popupOpen(pop) {
		
        $('.layerpop').css("position", "absolute");
        //영역 가운에데 레이어를 뛰우기 위해 위치 계산 
        $('.layerpop').css("top",(($(window).height() - $('.layerpop').outerHeight()) / 2) + $(window).scrollTop());
        $('.layerpop').css("left",(($(window).width() - $('.layerpop').outerWidth()) / 2) + $(window).scrollLeft());
        $('.layerpop').draggable();
		if(pop == 0){
			$('#layerbox').show();
		}
		else{
			$('#layerbox1').show();
		}
    }
    function popupClose(pop) {
		if(pop == 0){
			$('#layerbox').hide();
		}
		else{
			$('#layerbox1').hide();
		}
        
        $('#mask').hide();
    }

    function goDetail(pop) {
        /*팝업 오픈전 별도의 작업이 있을경우 구현*/ 

        popupOpen(pop); //레이어 팝업창 오픈 
        wrapWindowByMask(); //화면 마스크 효과 
    }
</script>
</head>
<?php 
	if(!isset( $_SESSION['login_user'])){ ?>
<body>
<div id="page">
<header>
 <img src="./images/logo.png" width="303" height="82" alt="logo" />
  <div id="right1"><a href="javascript:goDetail(1);">회원가입</a></div>
	<br />
      <img src="images/bg_banner_grey.gif" width="980" height="5" alt="logo_banner" /><!-- <div style="height:0px;"> </div>-->
        <!-- 팝업뜰때 배경 -->
    <div id="mask"></div>
        <!--Popup Start -->
        <section id="layerbox1" class="layerpop">
          <div class="Join"">
          <a href="javascript:popupClose(1);" class="layerpop_close" id="layerbox_close"><img src="images/x-mark-16.png" align="right"></a><br>
          <center>
            <h1>Join Form</h1>
          </center>
          <p>
		  <form method="post" action="user_add_ok.php">
            <input type="text" name="userid" value="" placeholder="User ID" >
          </p>
          <p>
            <input type="password" name="password" value="" placeholder="Password">
          </p>
          <p>
            <input type="password" name="password_re" value="" placeholder="Password	Check">
          </p>
          <p>
            <input type="text" name="nickname" value="" placeholder="Nickname">
          </p>
          <p>
            <input type="text" name="email" value="" placeholder="example@google.com">
          </p>

          <p class="submit">
            <input type="submit" name="" value="가입">
          </p>
		  		  </form>
  </section>
   </header>
<div id="content">

		  <div class="post">
				<h2></h2>

			

		
		

<p>
Bla bla bla a load of old nonsense goes here in this area, move on nothing of interests to read here just a load of old nonsense.Bla bla bla a load of old nonsense goes here in this area, move on nothing of interests to read here just a load of old nonsense. </p>

<p>
Bla bla bla a load of old nonsense goes here in this area, move on nothing of interests to read here just a load of old nonsense.Bla bla bla a load of old nonsense goes here in this area, move on nothing of interests to read here just a load of old nonsense.
</p>
		  </div>
          
		  <div id="left">
		  <? while($array = sqlsrv_fetch_array($result1,SQLSRV_FETCH_ASSOC)){
			  $board_idx = $array['board_idx'];
			  $com_params = array();
			$com_query="select * from Fcomment where board_idx = $board_idx"; // SQL 쿼리문을 문자열 변수에 일단 저장하고
			$com_options =  array( "Scrollable" => SQLSRV_CURSOR_KEYSET );
			$com_result = sqlsrv_query($conn,$com_query,$com_params,$com_options); // 위의 쿼리문을 실제로 실행하여 결과를 result에 대입
			$row = sqlsrv_num_rows($com_result); //위 결과 값을 하나하나 배열로 저장합니다 .
			if( $com_result === false) {
			 die( print_r( sqlsrv_errors(), true) );
			}
			?>
			<table border=0 width=250 cellspacing=0 cellpadding=0>


<tr>
	<td>
		<!--<span style="font-size:9pt;">-->
		<img src=./images/t.gif border=0 width=300 height=3><br>
		<img src=./images/news_listicon.gif board=0><a href='./view.php?List=Free&number=<?echo $array['board_idx'];?>'><?echo $array['title'];?></font></b></a> </span><font color=333333 style=font-family:tahoma;font-size:6pt>[<?echo $row?>]</font><br>
		<img src=./images/underline.gif border=0 width=100% height=1><br>
	</td>
	</table>
	<?}?>
</div>
<div id="right">
<table cellpadding="0" cellspacing="0" style="width:300;">
<?php
while($Image_array = sqlsrv_fetch_array($Image_result,SQLSRV_FETCH_ASSOC)){
	$i=$i+1;
?>
<td align="center">
<table cellpadding="0" cellspacing="0">
<tr>
<td style="padding:0px;border:0px solid #d1d1d1;">
<?if($Image_array['file_name'] == NULL){?>
<a href="./view.php?List=Image&number=<?echo $Image_array['board_idx'];?>"><img src="./images/untitled.png" width="121" height="96" style="vertical-align:middle;border:0px" alt="" /></a>
<?}else{?>
<a href="./view.php?List=Image&number=<?echo $Image_array['board_idx'];?>"><img src="<?echo $Image_array['file_name'];?>" width="101" height="76" style="vertical-align:middle;border:0px" alt="" /></a>
<?}?> 
</td>
</tr>
</table>
</td>
<?if($i%3 == 0){?>
</tr>
<tr>
<td style="height: 10px"></td>
</tr>
<?		}?>
<?	}?>

</table>
	</div>

  </div>
  <div id="sidebar">


	<ul>

    <li>
	<form method="post" action="./login.php">
        <p><input type="text" name="userid" value="" placeholder="Username or Email" ></p>
        <p><input type="password" name="password" value="" placeholder="Password"></p>
        <p class="submit">
          <input type="submit" name="commit" value="Login">
    </form>
    </p>
    <h2>Menu </h2>

					<ul>


						<li><a href="./index.php">home</a></li>
                        <li><a href="./list.php?List=Free">Free board</a></li>
                        <li><a href="./list.php?List=Image">Image board</a></li>
                        <li><a href="#">arcade</a></li>
                        <li><a href="#">gallery</a></li>
                        <li><a href="#">links</a></li>
                        <li><a href="#">contact us</a></li>
                        <li><a href="#">forums</a></li>
					</ul>
	  </li>

    </ul>
    <br />
    

     

  </div>

  <div style="clear: both;">&nbsp;</div>
  <img src="./images/bg_banner_grey.gif" width="980" height="5" alt="logo_banner" />
<footer>
 Your Site Name 
				© 2011 | Design by <a href="http://www.html5-templates.co.uk" target="_blank">HTML5 Templates</a>
</footer>
</div>

</body>
</html>


<?}else{?>
<body>
<div id="page">
<header>
  <img src="./images/logo.png" width="303" height="82" alt="logo"/>
  <div id="right1"><?echo $nick_row['nickname'];?>님 <a href="./logout.php">로그아웃</a></div>
  <br />
      <img src="images/bg_banner_grey.gif" width="980" height="5" alt="logo_banner" /><!-- <div style="height:0px;"> </div>-->
  </header>
<div id="content">

		  <div class="post">
				<h2></h2>

			

		
		

<p>
Bla bla bla a load of old nonsense goes here in this area, move on nothing of interests to read here just a load of old nonsense.Bla bla bla a load of old nonsense goes here in this area, move on nothing of interests to read here just a load of old nonsense. </p>

<p>
Bla bla bla a load of old nonsense goes here in this area, move on nothing of interests to read here just a load of old nonsense.Bla bla bla a load of old nonsense goes here in this area, move on nothing of interests to read here just a load of old nonsense.
</p>
		  </div>
          
		  <div id="left">
		  <? while($array = sqlsrv_fetch_array($result1,SQLSRV_FETCH_ASSOC)){
			  $board_idx = $array['board_idx'];
			  $com_params = array();
			$com_query="select * from Fcomment where board_idx = $board_idx"; // SQL 쿼리문을 문자열 변수에 일단 저장하고
			$com_options =  array( "Scrollable" => SQLSRV_CURSOR_KEYSET );
			$com_result = sqlsrv_query($conn,$com_query,$com_params,$com_options); // 위의 쿼리문을 실제로 실행하여 결과를 result에 대입
			$row = sqlsrv_num_rows($com_result); //위 결과 값을 하나하나 배열로 저장합니다 .
			if( $com_result === false) {
			 die( print_r( sqlsrv_errors(), true) );
			}
			?>
			<table border=0 width=250 cellspacing=0 cellpadding=0>


<tr>
	<td>
		<!--<span style="font-size:9pt;">-->
		<img src=./images/t.gif border=0 width=300 height=3><br>
		<img src=./images/news_listicon.gif board=0><a href='./view.php?List=Free&number=<?echo $array['board_idx'];?>'><?echo $array['title'];?></font></b></a> </span><font color=333333 style=font-family:tahoma;font-size:6pt>[<?echo $row?>]</font><br>
		<img src=./images/underline.gif border=0 width=100% height=1><br>
	</td>
	</table>
	<?}?>
</div>
<div id="right">
<table cellpadding="0" cellspacing="0" style="width:300;">
<?php
while($Image_array = sqlsrv_fetch_array($Image_result,SQLSRV_FETCH_ASSOC)){
	$i=$i+1;
?>
<td align="center">
<table cellpadding="0" cellspacing="0">
<tr>
<td style="padding:0px;border:0px solid #d1d1d1;">
<?if($Image_array['file_name'] == NULL){?>
<a href="./view.php?List=Image&number=<?echo $Image_array['board_idx'];?>"><img src="./images/untitled.png" width="121" height="96" style="vertical-align:middle;border:0px" alt="" /></a>
<?}else{?>
<a href="./view.php?List=Image&number=<?echo $Image_array['board_idx'];?>"><img src="<?echo $Image_array['file_name'];?>" width="101" height="76" style="vertical-align:middle;border:0px" alt="" /></a>
<?}?> 
</td>
</tr>
</table>
</td>
<?if($i%3 == 0){?>
</tr>
<tr>
<td style="height: 10px"></td>
</tr>
<?		}?>
<?	}?>

</table>
	</div>

  </div>
  <div id="sidebar">


	<ul>

    <li>
            

    </p>
    <h2>Menu </h2>

					<ul>


						<li><a href="./index.php">home</a></li>
                        <li><a href="./list.php?List=Free">Free board</a></li>
                        <li><a href="./list.php?List=Image">Image board</a></li>
                        <li><a href="#">arcade</a></li>
                        <li><a href="#">gallery</a></li>
                        <li><a href="#">links</a></li>
                        <li><a href="#">contact us</a></li>
                        <li><a href="#">forums</a></li>
					</ul>
	  </li>

    </ul>
    <br />
    

     

  </div>

  <div style="clear: both;">&nbsp;</div>
  <img src="images/bg_banner_grey.gif" width="980" height="5" alt="logo_banner" />
<footer>
 Your Site Name © 2011 | Design by <a href="http://www.html5-templates.co.uk" target="_blank">HTML5 Templates</a>
</footer>
</div>

</body>
</html>
<?}?>
