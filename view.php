<!DOCTYPE html>
<?php 
include('./dbcon.php');
session_start();
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
		$list_name="Free";
		$comment_name="Fcomment";
		break;
	case "Image":
		$board_name = "Imageboard";
		$list_name="Image";
		$comment_name="Icomment";
		break;
}
$number = $_GET['number'];
//테이블에서 글을 가져옵니다. 
$query = "select * from $board_name where board_idx = $number"; // 글 번호를 가지고 조회를 합니다.
$result = sqlsrv_query($conn,$query);
if( $result === false) {
  die( print_r( sqlsrv_errors(), true) );
}
$array = sqlsrv_fetch_array($result,SQLSRV_FETCH_ASSOC);
 $user_check1 = $_SESSION['login_user'];
$nick_sql="select nickname from member where userid = $user_check1";
$nick1_sql = sqlsrv_query($conn , $nick_sql);
 if( $result === false) {
    die( print_r( sqlsrv_errors(), true) );
}
$nick_row = sqlsrv_fetch_array($nick1_sql , SQLSRV_FETCH_ASSOC);

$nickname = $nick_row['nickname'];
$file_size = round(filesize($array['file_name'])/1024,2); 

//백슬래쉬 제거, 특수문자 변환(HTML용), 개행(<br>)처리 등
$array['nickname'] = stripslashes($array['nickname']);
$array['title'] = stripslashes($array['title']);
$array['content'] = stripslashes($array['content']);
$array['title'] = htmlspecialchars($array['title']);
//$array['memo'] = htmlspecialchars($array['memo']);
$array['content'] = nl2br($array['content']);
$nickname1 = $array['nickname'];

$com_query = "select * from $comment_name where board_idx = $number";
$com_result = sqlsrv_query($conn,$com_query);
if( $com_result === false) {
  die( print_r( sqlsrv_errors(), true) );
}
// 조회수 카운터 증가
$query1 = "update $board_name set count = count + 1 where board_idx = $number";
$result1 = sqlsrv_query($conn,$query1);
if( $result1 === false) {
  die( print_r( sqlsrv_errors(), true) );
}
 $date = date("Y/m/d", $array['Date']);
 $image=$array['file_name'];
function extExtract($filename) 
{ 
    if(strlen($filename) > 255) 
    { 
        return false; 
    } 

    $basename = trim(basename($filename)); 
    $resource = explode(".", $basename); 
    $i = count($resource)-1; 
    $resource[$i] = trim($resource[$i]); 

    if($resource[$i] === "") 
    { 
        while($i > 0) 
        { 
            $i--; 
            $resource[$i] = trim($resource[$i]); 
            if(!empty($resource[$i])) 
            { 
                return strtolower($resource[$i]); 
            } 
        } 
        return false; 
    } 
    elseif(!empty($resource[$i])) 
    { 
        return strtolower($resource[$i]); 
    } 
    else 
    { 
        return false; 
    } 
} 
$img_type= extExtract($image);
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
<div id="content2">
<table border=0 cellspacing=0 cellpadding=0 width=774>
<tr>
  <td width=1></td>
  <td>
 
  </td>
</tr>
</table>
<table border=0 cellspacing=0 cellpadding=0 width=774>
<tr><td hegiht=5></td></tr></table>

<table border=0 cellspacing=0 cellpadding=0 width=774>
  <tr height=10><td></td></tr>
</table>

		 <table border=0 cellspacing=0 cellpadding=0 width=774>
  <tr>
    <td height=2 colspan=2 bgcolor=888888></td>
  </tr>
  <tr>
    <td colspan=2>
      <table width=100% border=0 cellspacing=0 cellpadding=0>
        <tr height=30>
          <td width=100 align=center valign=middle>
            <span style=cursor:hand><?echo $array['nickname'];?></span>&nbsp;<br>
			<font color=red class='view_warning'></font><span class="view_username" style="display:none;"><?echo $array['nickname'];?></span>          </td>
          <td width=1 valign=top><img src=./images/v_line.gif border=0></td>
          <td valign=middle style='word-break:break-all;'>&nbsp;&nbsp;<!--DCM_TITLE--><b class="view_subject"><?php echo $array['title'];?></b><!--/DCM_TITLE--></td>
          <td width=1 valign=top><img src=./images/v_line.gif border=0></td>
          <td align=center valign=middle width=70 class=r_s_font valign=bottom><span><?echo $date?></span></td>
        </tr>
      </table>
    </td>
  </tr>
  
  <tr>
    <td colspan=2 height=4 background=./images/v_bg1.gif></td>
  </tr>
  <? 
if ($array['file_name'] != '') { 
	echo "
                <tr>
	  <td width=100>
                        <p align=right><b>첨부파일 &nbsp;</b></p>
	  </td>
	  <td colspan=3>
                        <p><a href={$array['file_name']}>{$array['s_file_name']}</a>({$file_size}KB)</p>
	  </td>
                </tr>
		";
}
?>
</table>
<table border=0 cellspacing=0 cellpadding=0 width=774>
  <tr>
    <td colspan=2 style='word-break:break-all;padding:10px;' bgcolor=#ffffff height=100 valign=top><span style=line-height:160% class="view_content">
	 	      <br>
     <!--DCM_BODY--><table border=0 cellspacing=0 cellpadding=0 width=100% style="table-layout:fixed;"><col width=100%></col><tr><td valign=top>	 <?php 
if ($img_type == 'png' ||
	$img_type == 'jpg' ||
	$img_type == 'gif' ||
	$img_type == 'bmp') { 
	echo "<img src={$image}>";}?></br><?echo $array['content'];?></table><!--/DCM_BODY-->
	 <img src=./images/t.gif border=0 height=0><br>
     <div align=right class=r_s_font></div>
	 <div align=left style=padding-left:0px>
	 <script async src="//pagead2.googlesyndication.com/pagead/js/adsbygoogle.js"></script>
<!-- 728 -->
<ins class="adsbygoogle"
     style="display:inline-block;width:728px;height:90px"
     data-ad-client="ca-pub-2502440020039236"
     data-ad-slot="6136737702"></ins>
<script>
(adsbygoogle = window.adsbygoogle || []).push({});
</script>	 </div>
     </span>
    </td>
   </tr> 
  <tr>
    <td colspan=2 height=4 background=./images/v_bg1.gif></td>
  </tr>
</table>

            <p align="center"><a href="list.php?List=<?echo $list_name?>&page=<? echo $page; ?>">[목록]</a> &nbsp;
			<? if( $nickname1 == $nickname){ ?>
			<a href="modify.php?List=<?echo $list_name?>&number=<? echo $number; ?>&page=<? echo $page; ?>">[수정]</a> &nbsp;
			<a href="delete_ok.php?List=<?echo $list_name?>&number=<? echo $number; ?>&page=<? echo $page; ?>">[삭제]</a>
			<?}?></p>
          </td>

    </tr>
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
<div id="content2">
<table border=0 cellspacing=0 cellpadding=0 width=774>
<tr>
  <td width=1></td>
  <td>
 
  </td>
</tr>
</table>
<table border=0 cellspacing=0 cellpadding=0 width=774>
<tr><td hegiht=5></td></tr></table>

<table border=0 cellspacing=0 cellpadding=0 width=774>
  <tr height=10><td></td></tr>
</table>

		 <table border=0 cellspacing=0 cellpadding=0 width=774>
  <tr>
    <td height=2 colspan=2 bgcolor=888888></td>
  </tr>
  <tr>
    <td colspan=2>
      <table width=100% border=0 cellspacing=0 cellpadding=0>
        <tr height=30>
          <td width=100 align=center valign=middle>
            <span style=cursor:hand><?echo $array['nickname'];?></span>&nbsp;<br>
			<font color=red class='view_warning'></font><span class="view_username" style="display:none;"><?echo $array['nickname'];?></span>          </td>
          <td width=1 valign=top><img src=./images/v_line.gif border=0></td>
          <td valign=middle style='word-break:break-all;'>&nbsp;&nbsp;<!--DCM_TITLE--><b class="view_subject"><?php echo $array['title'];?></b><!--/DCM_TITLE--></td>
          <td width=1 valign=top><img src=./images/v_line.gif border=0></td>
          <td align=center valign=middle width=70 class=r_s_font valign=bottom><span><?echo $date?></span></td>
        </tr>
      </table>
    </td>
  </tr>
  
  <tr>
    <td colspan=2 height=4 background=./images/v_bg1.gif></td>
  </tr>
   <? 
if ($array['file_name'] != '') { 
	echo "
                <tr>
	  <td width=100>
                        <p align=right><b>첨부파일 &nbsp;</b></p>
	  </td>
	  <td colspan=3>
                        <p><a href={$array['file_name']}>{$array['s_file_name']}</a>({$file_size}KB)</p>
	  </td>
                </tr>
		";
}
?>
</table>
<table border=0 cellspacing=0 cellpadding=0 width=774>
  <tr>
    <td colspan=2 style='word-break:break-all;padding:10px;' bgcolor=#ffffff height=100 valign=top><span style=line-height:160% class="view_content">
	 	      <br>
     <!--DCM_BODY--><table border=0 cellspacing=0 cellpadding=0 width=100% style="table-layout:fixed;"><col width=100%></col><tr><td valign=top>
	 <?php 
if ($img_type == 'png' ||
	$img_type == 'jpg' ||
	$img_type == 'gif' ||
	$img_type == 'bmp') { 
	echo "<img src={$image}>";}?></br><?echo $array['content'];?></table><!--/DCM_BODY-->
	 <img src=./images/t.gif border=0 height=0><br>
     <div align=right class=r_s_font></div>
	 <div align=left style=padding-left:0px>
	 <script async src="//pagead2.googlesyndication.com/pagead/js/adsbygoogle.js"></script>
<!-- 728 -->
<ins class="adsbygoogle"
     style="display:inline-block;width:728px;height:90px"
     data-ad-client="ca-pub-2502440020039236"
     data-ad-slot="6136737702"></ins>
<script>
(adsbygoogle = window.adsbygoogle || []).push({});
</script>	 </div>
     </span>
    </td>

   </tr> 
   	 <tr>
<td colspan=2 height=4 background=./images/v_bg1.gif></td>
  </tr>
</table>
<?while($com_array = sqlsrv_fetch_array($com_result,SQLSRV_FETCH_ASSOC)){
$com_date1 = date("Y-m-d", $com_array['Date']);
$com_date2 = date("H:i:s", $com_array['Date']);?>

<img src=./images/t.gif border=0 height=8><br>
<table border=0 cellspacing=0 cellpadding=0 height=1 width=774 style="margin-left:1px;">
<tr><td height=1 class=line1 style=height:1px><img src=./images/t.gif border=0 height=0></td></tr>
</table>
<img src=/images/t.gif border=0 height=7><br>
<table width=774 cellspacing=1 cellpadding=4>
<col width=100></col><col width=8></col><col width=></col><col width=100></col>
<tr valign=top bgcolor=white>
	<td>
		<table width="90" border=0 cellspacing=0 cellpadding=0 style=table-layout:fixed>
		<tr>
			<td><span><font class='list_han commentname'><?echo $com_array['nickname'];?></span><br></td>
		</tr>
		</table>
		</td>
	</td>
	<td width=8 class=line2 style=padding:0px><img src=/images/t.gif border=0 width=8></td>
	<td style='word-break:break-all;'><?echo $com_array['content'];?></td>
	<td align=right><?echo $com_date1;?><br><?echo $com_date2;?><br>&nbsp;<?if($com_array['nickname']==$nickname){?><form method=post action=./comment_del.php>
	<input type="hidden" name="number" value="<?echo $number;?>">
	<input type="hidden" name="List" value="<?echo $list_name;?>">
	<input type=image src=.images/X-button.gif border=0 valign=absmiddle><?}?></td>
</tr>
</table>
<?}?>
<table border=0 cellspacing=1 cellpadding=1 class=line1 width=774 style="margin-left:7px;">
<tr>
	<td bgcolor=white>
		<table border=0 cellspacing=1 cellpadding=8 width=100% height=120 bgcolor=white>
		<script>
			function check_comment_submit(obj) {
				if(obj.memo.value.length<1) {
					alert("내용을 적어 주세요");
					obj.memo.focus();
					return false;
				}
				return true;
			}
		</script>
		<form method=post action=./comment_ok.php onSubmit="return check_comment_submit(this)">
		<input type="hidden" name="number" value="<?echo $number;?>">
		<input type="hidden" name="List" value="<?echo $list_name;?>">
		
		<col width=95 align=right style=padding-right:10px></col><col width=></col>
		<tr>	
			<td class=list1>
				<table border=0 cellspacing=2 cellpadding=0 width=100% height=100%>
				<col width=></col><col width=100></col>
				<tr>
					<td width=100%><textarea name=memo cols=20 rows=5 class=textarea style=width:95%></textarea></td>
					<td width=100> 
                <input type=image src=./images/btn_reg_reply.gif value="" accesskey="s" style=height:60%> 
 </td>
				</tr>
				</table>
			</td>
		</tr>
		</form>
		</table>
	</td>
</tr>
</table>
 
            <p align="center"><a href="list.php?List=<?echo $list_name?>&page=<? echo $page; ?>">[목록]</a> &nbsp;
			<? if( $nickname1 == $nickname){ ?>
			<a href="modify.php?List=<?echo $list_name?>&number=<? echo $number; ?>&page=<? echo $page; ?>">[수정]</a> &nbsp;
			<a href="delete_ok.php?List=<?echo $list_name?>&number=<? echo $number; ?>&page=<? echo $page; ?>">[삭제]</a>
			<? } ?></p>
          </td>
    </tr>
	<br>

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
<?}?>
