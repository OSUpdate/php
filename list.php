<!DOCTYPE html>
<?php 
include('./dbcon.php');
session_start(); // 세션 연결 
if(isset($_SESSION['login_user'])){
	$user_check = $_SESSION['login_user'];
	$sql = "select userid from member where userid = $user_check";
	$ses_sql = sqlsrv_query($conn,$sql);
	$row1=sqlsrv_fetch_array($ses_sql,SQLSRV_FETCH_ASSOC);
	$login_session=$row['userid'];
}
switch($_GET['List']){ //보드 검색하기 위한 부분
	case "Free":
		$board_name = "Freeboard";
		break;
	case "Image":
		$board_name = "Imageboard";
		break;
}
$src_name=$_POST['src_name'];
$src_value=$_POST['src_value'];
$page = $_GET['page'];
//게시판 목록보기에 필요한 각종 변수 초기값을 설정합니다. //테이블 이름
if ($src_value != '') {
	$where = "where $src_name like '%$src_value%'";
}

if($page == ''){$page = 1;} //페이지 번호가 없으면 1
$list_num = 20; //한 페이지에 보여줄 목록 갯수
$page_num = 20; //한 화면에 보여줄 페이지 링크(묶음) 갯수
$offset = $list_num*($page-1); //한 페이지의 시작 글 번호(listnum 수만큼 나누었을 때 시작하는 글의 번호)
 
//전체 글 수를 구합니다. (쿼리문을 사용하여 결과를 배열로 저장하는 일반적 인 방법)
$query="select * from $board_name $where"; // SQL 쿼리문을 문자열 변수에 일단 저장하고
$params = array();
$options =  array( "Scrollable" => SQLSRV_CURSOR_KEYSET );
$result = sqlsrv_query($conn,$query,$params,$options); // 위의 쿼리문을 실제로 실행하여 결과를 result에 대입
if( $result === false) {
    die( print_r( sqlsrv_errors(), true) );
}
$row = sqlsrv_num_rows($result); //위 결과 값을 하나하나 배열로 저장합니다 .
$total_no = $row; //배열의 첫번째 요소의 값, 즉 테이블의 전체 글 수를 저장합니다.
//전체 페이지 수와 현재 글 번호를 구합니다.
$total_page=ceil($total_no/$list_num); // 전체글수를 페이지당글수로 나눈 값의 올림 값을 구합니다.
$cur_num=$total_no - $list_num * ($page-1); //현재 글번호
 
//각 게시판 테이블에서 목록을 가져옵니다. (위의 쿼리문 사용예와 비슷합니다 .)
$query1="select TOP $list_num * from $board_name where board_idx not in (select top $offset board_idx from $board_name $where order by board_idx desc)order by board_idx desc"; // SQL 쿼리문
$result1 = sqlsrv_query($conn, $query1); // 쿼리문을 실행 결과
if( $result1 === false) {
    die( print_r( sqlsrv_errors(), true) );
}
$user_check1 = $_SESSION['login_user'];
$nick_sql="select nickname from member where userid = $user_check1";
$nick1_sql = sqlsrv_query($conn , $nick_sql);
 if( $result === false) {
    die( print_r( sqlsrv_errors(), true) );
}
$nick_row = sqlsrv_fetch_array($nick1_sql,SQLSRV_FETCH_ASSOC);
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
switch($board_name){
	case "Freeboard":
	if(!isset( $_SESSION['login_user'])){ ?>
<body>
<div id="page1">
<header>
 <img src="./images/logo.png" width="303" height="82" alt="logo" />
  <div id="right3"><a href="javascript:goDetail(1);">회원가입</a></div>
	<br />
      <img src="./images/bg_banner_grey.gif" width="980" height="5" alt="logo_banner" /><!-- <div style="height:0px;"> </div>-->
        <!-- 팝업뜰때 배경 -->
    <div id="mask" ></div>
        <!--Popup Start -->
        <section id="layerbox1" class="layerpop">
          <div class="Join"">
          <a href="javascript:popupClose(1);" class="layerpop_close" id="layerbox_close"><img src="./images/x-mark-16.png" align="right"></a><br>
          <center>
            <h1>Join Form</h1>
          </center>
          <p>
		  <form method="post" action="./user_add_ok.php">
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
<div id="content1">

		 <table border=0 cellspacing=0 cellpadding=0 width=100%>
<tr><td hegiht=5></td></tr></table><table border=0 cellspacing=0 cellpadding=0 width=774 style="margin-left:0px;">
  <tr height=3><td></td></tr>
</table>
<table border=0 cellspacing=0 cellpadding=0 width=774 style="margin-left:0px;">
<tr><td width=1>


<tr><td width=100% >
<div id="right2">
<table border=0 cellspacing=0 cellpadding=0 width=100%>
<tr>
  <td height=2 colspan=9 bgcolor=888888></td>
</tr>


<tr height=30 align=center>
<td width=1>
</td>


<td width=40>
<img src=./images/t_no.gif border=0>
</td>

<td>
<table width=100% height=28 border=0 cellspacing=0 cellpadding=0>
<tr>
<td width=1>
<img src=./images/v_line.gif border=0>
</td>
<td align=center>
<img src=./images/t_subject.gif border=0>
</td></tr></table>
</td>



<td width=100>
<table width=100% border=0 cellspacing=0 cellpadding=0>
<tr>
<td width=1>
<img src=./images/v_line.gif border=0>
</td>
<td align=center>
<img src=./images/t_name.gif border=0>
</td></tr></table>
</td>



<td width=70>
<table width=100% border=0 cellspacing=0 cellpadding=0>
<tr>
<td width=1>
<img src=./images/v_line.gif border=0>
</td>
<td align=center>
<img src=./images/t_date.gif border=0>
</td></tr></table>
</td>




<td width=40>
<table width=100% border=0 cellspacing=0 cellpadding=0>
<tr>
<td width=1>
<img src=./images/v_line.gif border=0>
</td>
<td align=center>
<img src=./images/t_hit.gif border=0>
</td></tr></table>
</td>

<td width=1>
</td>

</tr>
<tr>
  <td height=4 colspan=9 background=./images/v_bg1.gif></td>
</tr>

<?php
while($array = sqlsrv_fetch_array($result1,SQLSRV_FETCH_ASSOC)){
        $date = date("Y/m/d", $array['Date']);
		$board_idx = $array['board_idx'];
		 $com_params = array();
			$com_query="select * from Fcomment where board_idx = $board_idx"; // SQL 쿼리문을 문자열 변수에 일단 저장하고
			$com_options =  array( "Scrollable" => SQLSRV_CURSOR_KEYSET );
			$com_result = sqlsrv_query($conn,$com_query,$com_params,$com_options); // 위의 쿼리문을 실제로 실행하여 결과를 result에 대입
			$row = sqlsrv_num_rows($com_result); //위 결과 값을 하나하나 배열로 저장합니다 .
			if( $com_result === false) {
			 die( print_r( sqlsrv_errors(), true) );
			}//글쓴시각을 Y/m/d 형식에 맞게 문자열로 바꿉니다 .
?>
<tr align=center onMouseOver=this.style.backgroundColor="fafafa" onMouseOut=this.style.backgroundColor="">
  <td width=1 height=30></td>
  <td width=40><?php echo $array['board_idx'];?></td>

       <td align=left>&nbsp;<img src=./images/new_head.gif border=0 align=absmiddle>&nbsp;<a href='view.php?List=<?echo $_GET['List']?>&page=<?echo $page;?>&number=<?echo $array['board_idx'];?>&src_name=<?echo src_name;?>&src_value=<?echo src_value;?>'  ><?php echo $array['title'];?>[<?echo $row;?>]</a><font class="r_s_font comment_count">&nbsp;&nbsp;</font></td>

			
 
	   <td width=100 ><?php echo $array['nickname'];?>
        </td>
        <td width=70 ><?php echo $date;?>
        </td>
          <td width=40 ><?php echo $array['count'];?>
        </td>
		<td width=1></td>
    </tr>

 <?php
}
 ?>
    <tr>
        <td width=95% colspan=5>
<?
//여기서부터 각종 페이지 링크
//먼저, 한 화면에 보이는 블록($page_num 기본값 이상일 때 블록으로 나뉘어짐 )
$total_block = ceil($total_page/$page_num);
$block = ceil($page/$page_num); //현재 블록
 
$first = ($block-1) * $page_num; // 페이지 블록이 시작하는 첫 페이지 
$last = $block * $page_num; //페이지 블록의 끝 페이지
 
	if($block >= $total_block){
		    $last=$total_page;
	}?>


             &nbsp;       <p align=center>
<?//[처음][*개앞]
if($block > 1) {
        $prev=$first-1;?>
       <a href='list.php?List=<?echo $_GET['List']?>&page=1&src_name=<?echo $src_name;?>&src_value=<?echo $src_value;?>'> <? echo "처음";?></a>&nbsp; 
       <a href='list.php?List=<?echo $_GET['List']?>&page=<?echo $prev;?>&src_name=<?echo $src_name;?>&src_value=<?echo $src_value;?>'><?echo "$page_num 개 앞"?></a>
<?}
 
//[이전]
if($page > 1) {
        $go_page=$page-1;?>
        <a href='list.php?List=<?echo $_GET['List']?>&page=<?echo $go_page;?>&src_name=<?echo $src_name;?>&src_value=<?echo $src_value;?>'><?echo "이전"?></a>
<?}
 
//페이지 링크
for ($page_link=$first+1; $page_link <= $last; $page_link++) {
        if($page_link==$page) {?>
               <font color=green><b><?echo $page_link;?></b></font>
        <?}
        else {?>
                <a href='list.php?List=<?echo $_GET['List']?>&page=<?echo $page_link;?>&src_name=<?echo $src_name;?>&src_value=<?echo $src_value;?>'><?echo "$page_link"?></a>
        <?}
}
 
//[다음]
if($total_page > $page) {
        $go_page=$page+1;?>
        <a href='list.php?List=<?echo $_GET['List']?>&page=<?echo $go_page;?>&src_name=<?echo $src_name;?>&src_value=<?echo $src_value;?>'><?echo "다음"?></a>
<?}
 
//[*개뒤][마지막]
if($block < $total_block) {
        $next=$last+1;?>
       <a href='list.php?List=<?echo $_GET['List']?>&page=<?echo $netxt;?>&src_name=<?echo $src_name;?>&src_value=<?echo $src_value;?>'><?echo "$page_num 개 뒤"?></a>
       <a href='list.php?List=<?echo $_GET['List']?>&page=<?echo $total_page;?>&src_name=<?echo $src_name;?>&src_value=<?echo $src_value;?>'><?echo "마지막"?></a></p>
<?}?>
 
      </td>
    </tr>
            <td colspan=6>
          <p align=left><a href='write.php?page=<?echo $page;?>&src_name=<?echo $src_name;?>&src_value=<?echo $src_value;?>' style="text-decoration:none;">글쓰기</a></p>
		  </td>
		  	<form method=post action='list.php?List=<?echo $_GET['List']?>&page=<?echo $page;?>&src_name=<?echo $src_name;?>&src_value=<?echo $src_value;?>'>
	<tr>
        <td width=100% colspan=5 align=center>
			<input type=hidden name=page value=<? echo "$page" ; ?>>


			<select name=src_name>
			<option value=nickname>이름</option>
			<option value=title selected>제목</option>
			<option value=content>내용</option>
			</select>

			<input type=text name=src_value size=30>
			<input type=submit value=검색>

		</td>
	</tr>
	</form>
</table>
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

    <br>
    </div>
  <div style="clear: both;">&nbsp;<img src="./images/bg_banner_grey.gif" width="980" height="5" alt="logo_banner" /></div>

  <footer>
 Your Site Name 
				© 2011 | Design by <a href="http://www.html5-templates.co.uk" target="_blank">HTML5 Templates</a>
</footer>

</div>
</body>
</html>


<?}else{?>
<body>
<div id="page1">
<header>
 <img src="./images/logo.png" width="303" height="82" alt="logo" />
  <div id="right1"><?echo $nick_row['nickname'];?>님 <a href="./logout.php">로그아웃</a></div>
	<br />
      <img src="./images/bg_banner_grey.gif" width="980" height="5" alt="logo_banner" /><!-- <div style="height:0px;"> </div>-->
        <!-- 팝업뜰때 배경 -->
    <div id="mask" ></div>
        <!--Popup Start -->
        <section id="layerbox1" class="layerpop">
          <div class="Join"">
          <a href="javascript:popupClose(1);" class="layerpop_close" id="layerbox_close"><img src="./images/x-mark-16.png" align="right"></a><br>
          <center>
            <h1>Join Form</h1>
          </center>
          <p>
		  <form method="post" action="./user_add_ok.php">
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
<div id="content1">

		 <table border=0 cellspacing=0 cellpadding=0 width=100%>
<tr><td hegiht=5></td></tr></table><table border=0 cellspacing=0 cellpadding=0 width=774 style="margin-left:0px;">
  <tr height=3><td></td></tr>
</table>
<table border=0 cellspacing=0 cellpadding=0 width=774 style="margin-left:0px;">
<tr><td width=1>


<tr><td width=100% >
<div id="right2">
<table border=0 cellspacing=0 cellpadding=0 width=100%>
<tr>
  <td height=2 colspan=9 bgcolor=888888></td>
</tr>


<tr height=30 align=center>
<td width=1>
</td>


<td width=40>
<img src=./images/t_no.gif border=0>
</td>

<td>
<table width=100% height=28 border=0 cellspacing=0 cellpadding=0>
<tr>
<td width=1>
<img src=./images/v_line.gif border=0>
</td>
<td align=center>
<img src=./images/t_subject.gif border=0>
</td></tr></table>
</td>



<td width=100>
<table width=100% border=0 cellspacing=0 cellpadding=0>
<tr>
<td width=1>
<img src=./images/v_line.gif border=0>
</td>
<td align=center>
<img src=./images/t_name.gif border=0>
</td></tr></table>
</td>



<td width=70>
<table width=100% border=0 cellspacing=0 cellpadding=0>
<tr>
<td width=1>
<img src=./images/v_line.gif border=0>
</td>
<td align=center>
<img src=./images/t_date.gif border=0>
</td></tr></table>
</td>




<td width=40>
<table width=100% border=0 cellspacing=0 cellpadding=0>
<tr>
<td width=1>
<img src=./images/v_line.gif border=0>
</td>
<td align=center>
<img src=./images/t_hit.gif border=0>
</td></tr></table>
</td>

<td width=1>
</td>

</tr>
<tr>
  <td height=4 colspan=9 background=./images/v_bg1.gif></td>
</tr>

<?php
while($array = sqlsrv_fetch_array($result1,SQLSRV_FETCH_ASSOC)){
        $date = date("Y/m/d", $array['Date']); //글쓴시각을 Y/m/d 형식에 맞게 문자열로 바꿉니다 .
?>
<tr align=center onMouseOver=this.style.backgroundColor="fafafa" onMouseOut=this.style.backgroundColor="">
  <td width=1 height=30></td>
  <td width=40><?php echo $array['board_idx'];?></td>

       <td align=left>&nbsp;<img src=./images/new_head.gif border=0 align=absmiddle>&nbsp;<a href='view.php?List=<?echo $_GET['List']?>&number=<?echo $array['board_idx'];?>'  ><?php echo $array['title'];?></a><font class="r_s_font comment_count">&nbsp;&nbsp;</font></td>

			
 
	   <td width=100 ><?php echo $array['nickname'];?>
        </td>
        <td width=70 ><?php echo $date;?>
        </td>
          <td width=40 ><?php echo $array['count'];?>
        </td>
		<td width=1></td>
    </tr>

 <?php
}
 ?>
    <tr>
        <td width=95% colspan=5>
<?
//여기서부터 각종 페이지 링크
//먼저, 한 화면에 보이는 블록($page_num 기본값 이상일 때 블록으로 나뉘어짐 )
$total_block = ceil($total_page/$page_num);
$block = ceil($page/$page_num); //현재 블록
 
$first = ($block-1) * $page_num; // 페이지 블록이 시작하는 첫 페이지 
$last = $block * $page_num; //페이지 블록의 끝 페이지
 
	if($block >= $total_block){
		    $last=$total_page;
	}?>


             &nbsp;       <p align=center>
<?//[처음][*개앞]
if($block > 1) {
        $prev=$first-1;?>
       <a href='list.php?List=<?echo $_GET['List']?>&page=1&src_name=<?echo $src_name;?>&src_value=<?echo $src_value;?>'> <? echo "처음";?></a>&nbsp; 
       <a href='list.php?List=<?echo $_GET['List']?>&page=<?echo $prev;?>&src_name=<?echo $src_name;?>&src_value=<?echo $src_value;?>'><?echo "$page_num 개 앞"?></a>
<?}
 
//[이전]
if($page > 1) {
        $go_page=$page-1;?>
        <a href='list.php?List=<?echo $_GET['List']?>&page=<?echo $go_page;?>&src_name=<?echo $src_name;?>&src_value=<?echo $src_value;?>'><?echo "이전"?></a>
<?}
 
//페이지 링크
for ($page_link=$first+1; $page_link <= $last; $page_link++) {
        if($page_link==$page) {?>
               <font color=green><b><?echo $page_link;?></b></font>
        <?}
        else {?>
                <a href='list.php?List=<?echo $_GET['List']?>&page=<?echo $page_link;?>&src_name=<?echo $src_name;?>&src_value=<?echo $src_value;?>'><?echo "$page_link"?></a>
        <?}
}
 
//[다음]
if($total_page > $page) {
        $go_page=$page+1;?>
        <a href='list.php?List=<?echo $_GET['List']?>&page=<?echo $go_page;?>&src_name=<?echo $src_name;?>&src_value=<?echo $src_value;?>'><?echo "다음"?></a>
<?}
 
//[*개뒤][마지막]
if($block < $total_block) {
        $next=$last+1;?>
       <a href='list.php?List=<?echo $_GET['List']?>&page=<?echo $netxt;?>&src_name=<?echo $src_name;?>&src_value=<?echo $src_value;?>'><?echo "$page_num 개 뒤"?></a>
       <a href='list.php?List=<?echo $_GET['List']?>&page=<?echo $total_page;?>&src_name=<?echo $src_name;?>&src_value=<?echo $src_value;?>'><?echo "마지막"?></a></p>
<?}?>
 
      </td>
    </tr>
            <td colspan=6>
          <p align=left><a href='write.php?List=Free' style="text-decoration:none;">글쓰기</a></p>
		  </td>
		  	  	<form method=post action='list.php?List=<?echo $_GET['List']?>&page=<?echo $page;?>&src_name=<?echo $src_name;?>&src_value=<?echo $src_value;?>'>
	<tr>
        <td width=100% colspan=5 align=center>
			<input type=hidden name=page value=<? echo "$page" ; ?>>


			<select name=src_name>
			<option value=nickname>이름</option>
			<option value=subject selected>제목</option>
			<option value=memo>내용</option>
			</select>

			<input type=text name=src_value size=30>
			<input type=submit value=검색>

		</td>
	</tr>
	</form>
</table>
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

    <br>
    </div>
  <div style="clear: both;">&nbsp;<img src="./images/bg_banner_grey.gif" width="980" height="5" alt="logo_banner" /></div>

  <footer>
 Your Site Name 
				© 2011 | Design by <a href="http://www.html5-templates.co.uk" target="_blank">HTML5 Templates</a>
</footer>

</div>
</body>
</html>
<?}
break;
case "Imageboard":
if(!isset( $_SESSION['login_user'])){ ?>
<body>
<div id="page1">
<header>
 <img src="./images/logo.png" width="303" height="82" alt="logo" />
  <div id="right3"><a href="javascript:goDetail(1);">회원가입</a></div>
	<br />
      <img src="./images/bg_banner_grey.gif" width="980" height="5" alt="logo_banner" /><!-- <div style="height:0px;"> </div>-->
        <!-- 팝업뜰때 배경 -->
    <div id="mask" ></div>
        <!--Popup Start -->
        <section id="layerbox1" class="layerpop">
          <div class="Join"">
          <a href="javascript:popupClose(1);" class="layerpop_close" id="layerbox_close"><img src="./images/x-mark-16.png" align="right"></a><br>
          <center>
            <h1>Join Form</h1>
          </center>
          <p>
		  <form method="post" action="./user_add_ok.php">
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
<div id="content1">

		 <table border=0 cellspacing=0 cellpadding=0 width=100%>
<tr><td hegiht=5></td></tr></table><table border=0 cellspacing=0 cellpadding=0 width=774 style="margin-left:0px;">
  <tr height=3><td></td></tr>
</table>
<table border=0 cellspacing=0 cellpadding=0 width=774 style="margin-left:0px;">
<tr><td width=1>


<tr><td width=100% >
<div id="right2">
<table border=0 cellspacing=0 cellpadding=0 width=100%>
<tr>
  <td height=2 colspan=9 bgcolor=888888></td>
</tr>


<tr height=30 align=center>
<td width=1>
</td>


<td width=40>
<img src=./images/t_no.gif border=0>
</td>

<td>
<table width=100% height=28 border=0 cellspacing=0 cellpadding=0>
<tr>
<td width=1>
<img src=./images/v_line.gif border=0>
</td>
<td align=center>
<img src=./images/t_subject.gif border=0>
</td></tr></table>
</td>



<td width=100>
<table width=100% border=0 cellspacing=0 cellpadding=0>
<tr>
<td width=1>
<img src=./images/v_line.gif border=0>
</td>
<td align=center>
<img src=./images/t_name.gif border=0>
</td></tr></table>
</td>



<td width=70>
<table width=100% border=0 cellspacing=0 cellpadding=0>
<tr>
<td width=1>
<img src=./images/v_line.gif border=0>
</td>
<td align=center>
<img src=./images/t_date.gif border=0>
</td></tr></table>
</td>




<td width=40>
<table width=100% border=0 cellspacing=0 cellpadding=0>
<tr>
<td width=1>
<img src=./images/v_line.gif border=0>
</td>
<td align=center>
<img src=./images/t_hit.gif border=0>
</td></tr></table>
</td>

<td width=1>
</td>

</tr>
<tr>
  <td height=4 colspan=9 background=./images/v_bg1.gif></td>
</tr>

<?php
while($array = sqlsrv_fetch_array($result1,SQLSRV_FETCH_ASSOC)){
        $date = date("Y/m/d", $array['Date']); //글쓴시각을 Y/m/d 형식에 맞게 문자열로 바꿉니다 .
?>
<tr align=center onMouseOver=this.style.backgroundColor="fafafa" onMouseOut=this.style.backgroundColor="">
  <td width=1 height=30></td>
  <td width=40><?php echo $array['board_idx'];?></td>

       <td align=left>&nbsp;<img src=./images/new_head.gif border=0 align=absmiddle>&nbsp;<a href='view.php?List=<?echo $_GET['List']?>&page=<?echo $page;?>&number=<?echo $array['board_idx'];?>&src_name=<?echo src_name;?>&src_value=<?echo src_value;?>'  ><?php echo $array['title'];?></a><font class="r_s_font comment_count">&nbsp;&nbsp;</font></td>

			
 
	   <td width=100 ><?php echo $array['nickname'];?>
        </td>
        <td width=70 ><?php echo $date;?>
        </td>
          <td width=40 ><?php echo $array['count'];?>
        </td>
		<td width=1></td>
    </tr>

 <?php
}
 ?>
    <tr>
        <td width=95% colspan=5>
<?
//여기서부터 각종 페이지 링크
//먼저, 한 화면에 보이는 블록($page_num 기본값 이상일 때 블록으로 나뉘어짐 )
$total_block = ceil($total_page/$page_num);
$block = ceil($page/$page_num); //현재 블록
 
$first = ($block-1) * $page_num; // 페이지 블록이 시작하는 첫 페이지 
$last = $block * $page_num; //페이지 블록의 끝 페이지
 
	if($block >= $total_block){
		    $last=$total_page;
	}?>


             &nbsp;       <p align=center>
<?//[처음][*개앞]
if($block > 1) {
        $prev=$first-1;?>
       <a href='list.php?List=<?echo $_GET['List']?>&page=1&src_name=<?echo $src_name;?>&src_value=<?echo $src_value;?>'> <? echo "처음";?></a>&nbsp; 
       <a href='list.php?List=<?echo $_GET['List']?>&page=<?echo $prev;?>&src_name=<?echo $src_name;?>&src_value=<?echo $src_value;?>'><?echo "$page_num 개 앞"?></a>
<?}
 
//[이전]
if($page > 1) {
        $go_page=$page-1;?>
        <a href='list.php?List=<?echo $_GET['List']?>&page=<?echo $go_page;?>&src_name=<?echo $src_name;?>&src_value=<?echo $src_value;?>'><?echo "이전"?></a>
<?}
 
//페이지 링크
for ($page_link=$first+1; $page_link <= $last; $page_link++) {
        if($page_link==$page) {?>
               <font color=green><b><?echo $page_link;?></b></font>
        <?}
        else {?>
                <a href='list.php?List=<?echo $_GET['List']?>&page=<?echo $page_link;?>&src_name=<?echo $src_name;?>&src_value=<?echo $src_value;?>'><?echo "$page_link"?></a>
        <?}
}
 
//[다음]
if($total_page > $page) {
        $go_page=$page+1;?>
        <a href='list.php?List=<?echo $_GET['List']?>&page=<?echo $go_page;?>&src_name=<?echo $src_name;?>&src_value=<?echo $src_value;?>'><?echo "다음"?></a>
<?}
 
//[*개뒤][마지막]
if($block < $total_block) {
        $next=$last+1;?>
       <a href='list.php?List=<?echo $_GET['List']?>&page=<?echo $netxt;?>&src_name=<?echo $src_name;?>&src_value=<?echo $src_value;?>'><?echo "$page_num 개 뒤"?></a>
       <a href='list.php?List=<?echo $_GET['List']?>&page=<?echo $total_page;?>&src_name=<?echo $src_name;?>&src_value=<?echo $src_value;?>'><?echo "마지막"?></a></p>
<?}?>
 
      </td>
    </tr>
            <td colspan=6>
          <p align=left><a href='write.php?page=<?echo $page;?>&src_name=<?echo $src_name;?>&src_value=<?echo $src_value;?>' style="text-decoration:none;">글쓰기</a></p>
		  </td>
		  	<form method=post action='list.php?List=<?echo $_GET['List']?>&page=<?echo $page;?>&src_name=<?echo $src_name;?>&src_value=<?echo $src_value;?>'>
	<tr>
        <td width=100% colspan=5 align=center>
			<input type=hidden name=page value=<? echo "$page" ; ?>>


			<select name=src_name>
			<option value=nickname>이름</option>
			<option value=subject selected>제목</option>
			<option value=memo>내용</option>
			</select>

			<input type=text name=src_value size=30>
			<input type=submit value=검색>

		</td>
	</tr>
	</form>
</table>
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

    <br>
    </div>
  <div style="clear: both;">&nbsp;<img src="./images/bg_banner_grey.gif" width="980" height="5" alt="logo_banner" /></div>

  <footer>
 Your Site Name 
				© 2011 | Design by <a href="http://www.html5-templates.co.uk" target="_blank">HTML5 Templates</a>
</footer>

</div>
</body>
</html>


<?}else{?>
<body>
<div id="page1">
<header>
 <img src="./images/logo.png" width="303" height="82" alt="logo" />
  <div id="right1"><?echo $nick_row['nickname'];?>님 <a href="./logout.php">로그아웃</a></div>
	<br />
      <img src="./images/bg_banner_grey.gif" width="980" height="5" alt="logo_banner" /><!-- <div style="height:0px;"> </div>-->
        <!-- 팝업뜰때 배경 -->
    <div id="mask" ></div>
        <!--Popup Start -->
        <section id="layerbox1" class="layerpop">
          <div class="Join"">
          <a href="javascript:popupClose(1);" class="layerpop_close" id="layerbox_close"><img src="./images/x-mark-16.png" align="right"></a><br>
          <center>
            <h1>Join Form</h1>
          </center>
          <p>
		  <form method="post" action="./user_add_ok.php">
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
<div id="content1">

		 <table border=0 cellspacing=0 cellpadding=0 width=100%>
<tr><td hegiht=5></td></tr></table><table border=0 cellspacing=0 cellpadding=0 width=774 style="margin-left:0px;">
  <tr height=3><td></td></tr>
</table>
<table border=0 cellspacing=0 cellpadding=0 width=774 style="margin-left:0px;">
<tr><td width=1>


<tr><td width=100% >
<div id="right2">
<table border=0 cellspacing=0 cellpadding=0 width=100%>
<tr>
  <td height=2 colspan=9 bgcolor=888888></td>
</tr>


<tr height=30 align=center>
<td width=1>
</td>


<td width=40>
<img src=./images/t_no.gif border=0>
</td>

<td>
<table width=100% height=28 border=0 cellspacing=0 cellpadding=0>
<tr>
<td width=1>
<img src=./images/v_line.gif border=0>
</td>
<td align=center>
<img src=./images/t_subject.gif border=0>
</td></tr></table>
</td>



<td width=100>
<table width=100% border=0 cellspacing=0 cellpadding=0>
<tr>
<td width=1>
<img src=./images/v_line.gif border=0>
</td>
<td align=center>
<img src=./images/t_name.gif border=0>
</td></tr></table>
</td>



<td width=70>
<table width=100% border=0 cellspacing=0 cellpadding=0>
<tr>
<td width=1>
<img src=./images/v_line.gif border=0>
</td>
<td align=center>
<img src=./images/t_date.gif border=0>
</td></tr></table>
</td>




<td width=40>
<table width=100% border=0 cellspacing=0 cellpadding=0>
<tr>
<td width=1>
<img src=./images/v_line.gif border=0>
</td>
<td align=center>
<img src=./images/t_hit.gif border=0>
</td></tr></table>
</td>

<td width=1>
</td>

</tr>
<tr>
  <td height=4 colspan=9 background=./images/v_bg1.gif></td>
</tr>

<?php
while($array = sqlsrv_fetch_array($result1,SQLSRV_FETCH_ASSOC)){
        $date = date("Y/m/d", $array['Date']); //글쓴시각을 Y/m/d 형식에 맞게 문자열로 바꿉니다 .
?>
<tr align=center onMouseOver=this.style.backgroundColor="fafafa" onMouseOut=this.style.backgroundColor="">
  <td width=1 height=30></td>
  <td width=40><?php echo $array['board_idx'];?></td>

       <td align=left>&nbsp;<img src=./images/new_head.gif border=0 align=absmiddle>&nbsp;<a href='view.php?List=<?echo $_GET['List']?>&number=<?echo $array['board_idx'];?>'  ><?php echo $array['title'];?></a><font class="r_s_font comment_count">&nbsp;&nbsp;</font></td>

			
 
	   <td width=100 ><?php echo $array['nickname'];?>
        </td>
        <td width=70 ><?php echo $date;?>
        </td>
          <td width=40 ><?php echo $array['count'];?>
        </td>
		<td width=1></td>
    </tr>

 <?php
}
 ?>
    <tr>
        <td width=95% colspan=5>
<?
//여기서부터 각종 페이지 링크
//먼저, 한 화면에 보이는 블록($page_num 기본값 이상일 때 블록으로 나뉘어짐 )
$total_block = ceil($total_page/$page_num);
$block = ceil($page/$page_num); //현재 블록
 
$first = ($block-1) * $page_num; // 페이지 블록이 시작하는 첫 페이지 
$last = $block * $page_num; //페이지 블록의 끝 페이지
 
	if($block >= $total_block){
		    $last=$total_page;
	}?>


             &nbsp;       <p align=center>
<?//[처음][*개앞]
if($block > 1) {
        $prev=$first-1;?>
       <a href='list.php?List=<?echo $_GET['List']?>&page=1&src_name=<?echo $src_name;?>&src_value=<?echo $src_value;?>'> <? echo "처음";?></a>&nbsp; 
       <a href='list.php?List=<?echo $_GET['List']?>&page=<?echo $prev;?>&src_name=<?echo $src_name;?>&src_value=<?echo $src_value;?>'><?echo "$page_num 개 앞"?></a>
<?}
 
//[이전]
if($page > 1) {
        $go_page=$page-1;?>
        <a href='list.php?List=<?echo $_GET['List']?>&page=<?echo $go_page;?>&src_name=<?echo $src_name;?>&src_value=<?echo $src_value;?>'><?echo "이전"?></a>
<?}
 
//페이지 링크
for ($page_link=$first+1; $page_link <= $last; $page_link++) {
        if($page_link==$page) {?>
               <font color=green><b><?echo $page_link;?></b></font>
        <?}
        else {?>
                <a href='list.php?List=<?echo $_GET['List']?>&page=<?echo $page_link;?>&src_name=<?echo $src_name;?>&src_value=<?echo $src_value;?>'><?echo "$page_link"?></a>
        <?}
}
 
//[다음]
if($total_page > $page) {
        $go_page=$page+1;?>
        <a href='list.php?List=<?echo $_GET['List']?>&page=<?echo $go_page;?>&src_name=<?echo $src_name;?>&src_value=<?echo $src_value;?>'><?echo "다음"?></a>
<?}
 
//[*개뒤][마지막]
if($block < $total_block) {
        $next=$last+1;?>
       <a href='list.php?List=<?echo $_GET['List']?>&page=<?echo $netxt;?>&src_name=<?echo $src_name;?>&src_value=<?echo $src_value;?>'><?echo "$page_num 개 뒤"?></a>
       <a href='list.php?List=<?echo $_GET['List']?>&page=<?echo $total_page;?>&src_name=<?echo $src_name;?>&src_value=<?echo $src_value;?>'><?echo "마지막"?></a></p>
<?}?>
 
      </td>
    </tr>
            <td colspan=6>
          <p align=left><a href='write.php?List=Image' style="text-decoration:none;">글쓰기</a></p>
		  </td>
		  	  	<form method=post action='list.php?List=<?echo $_GET['List']?>&page=<?echo $page;?>&src_name=<?echo $src_name;?>&src_value=<?echo $src_value;?>'>
	<tr>
        <td width=100% colspan=5 align=center>
			<input type=hidden name=page value=<? echo "$page" ; ?>>


			<select name=src_name>
			<option value=nickname>이름</option>
			<option value=subject selected>제목</option>
			<option value=memo>내용</option>
			</select>

			<input type=text name=src_value size=30>
			<input type=submit value=검색>

		</td>
	</tr>
	</form>
</table>
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

    <br>
    </div>
  <div style="clear: both;">&nbsp;<img src="./images/bg_banner_grey.gif" width="980" height="5" alt="logo_banner" /></div>

  <footer>
 Your Site Name 
				© 2011 | Design by <a href="http://www.html5-templates.co.uk" target="_blank">HTML5 Templates</a>
</footer>

</div>
</body>
</html>
<?}
break;
}?>
