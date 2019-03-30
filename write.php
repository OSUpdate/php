<!DOCTYPE html>
<?php 
include('./dbcon.php');
session_start();
if(isset($_SESSION['login_user'])){
	$user_check = $_SESSION['login_user'];
	$sql = "select userid from member where userid = $user_check";
	$ses_sql = sqlsrv_query($conn,$sql);
	$row=sqlsrv_fetch_array($ses_sql,SQLSRV_FETCH_ASSOC);
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
?>
<head>
    <meta charset=UTF-8>
<title>Beadysite - Free HTML5 Template</title>
<link href="./style.css" rel="stylesheet" type="text/css" media="screen" />
<link rel="stylesheet" href="./login/css/style.css">
<script type="text/javascript" src="./se/js/HuskyEZCreator.js" charset="utf-8"></script>
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
	if(!isset( $_SESSION['login_user'])){ 
		echo "<script>alert('로그인 후 이용바랍니다');history.back();</script>"?>
<body>
<div id="page">
<header>
 <img src="./images/logo.png" width="303" height="82" alt="logo" />
  <div id="right1"><a href="javascript:goDetail(1);">회원가입</a></div>
	<br />
      <img src="./images/bg_banner_grey.gif" width="980" height="5" alt="logo_banner" /><!-- <div style="height:0px;"> </div>-->
        <!-- 팝업뜰때 배경 -->
    <div id="mask"></div>
        <!--Popup Start -->
        <section id="layerbox1" class="layerpop">
          <div class="Join"">
          <a href="javascript:popupClose(1);" class="layerpop_close" id="layerbox_close"><img src="../images/x-mark-16.png" align="right"></a><br>
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
<div id="content">
<table border=0 width=700 cellsapcing=1 cellpadding=0>
<form method=post name=myForm id=myForm enctype=multipart/form-data>
<col width=80 align=right style=padding-right:10px;height:28px class=list1></col>
<col class=list0 style=padding-left:10px;height:28px width=></col>
<tr class=title>
	<td colspan=2 class=title_han align=center>&nbsp;&nbsp;새로 글 쓰기</td>
</tr>

<tr valign=top>
  <td><font class=list_eng><b>제목</b></font></td>
  <td><input type=text name=subject value="" id=subject size=33  maxlength=200 style=width:95% class=input></td>
</tr>

<tr>
 <td valign="top"><p>내용▼</p></td>
  <td style=padding-top:8px;padding-bottom:8px;><textarea name=memo id=memo cols=54  rows=20 class=textarea style=width:99%></textarea></td>
</tr>

<tr>
  <td><font class=list_eng>첨부 #</font></td>
  <td class=list_eng><input type=file name=upfile  size=30  maxlength=255 class=input style=width:99%> </td>
</tr>
<tr>
 <table border=0 width=670>

 <tr><td align="right">

<a href="#" onclick="submitContents(this);"><img src="./se/img/photoQuickPopup/btn_confirm2.png"></a>
 <a href="#" onclick="history.back();"><img src="./se/img/photoQuickPopup/btn_cancel.png"></a>
 </td></tr>
 </table>
</form>
</table>
</div>
  <h2></h2>

			

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
                        <li><a href="./list.php?List=Imgae">Image board</a></li>
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
<!--<footer>
 Your Site Name 
				© 2011 | Design by <a href="http://www.html5-templates.co.uk" target="_blank">HTML5 Templates</a>
</footer>-->

</body>
</html>


<?}else{?>
<body>
<div id="page">
<header>
  <img src="./images/logo.png" width="303" height="82" alt="logo"/>
  <div id="right1"><a href="../logout.php">로그아웃</a></div>
  <br />
      <img src="./images/bg_banner_grey.gif" width="980" height="5" alt="logo_banner" /><!-- <div style="height:0px;"> </div>-->
  </header>
<div id="content">

<table border=0 width=700 cellsapcing=1 cellpadding=0>
<form method=post name=myForm id=myForm enctype=multipart/form-data>
<col width=80 align=right style=padding-right:10px;height:28px class=list1></col>
<col class=list0 style=padding-left:10px;height:28px width=></col>
<tr class=title>
	<td colspan=2 class=title_han align=center>&nbsp;&nbsp;새로 글 쓰기</td>
</tr>

<tr valign=top>
  <td><font class=list_eng><b>제목</b></font></td>
  <td><input type=text name="subject" value="" id="subject" size=33  maxlength=200 style=width:95% class=input></td>
</tr>

<tr>
  <td style=cursor:hand><font class=list_eng><b>내용</b></font> <font class=list_eng>▼</font></td>
  <td style=padding-top:8px;padding-bottom:8px;><textarea name="memo" id="memo" cols=54  rows=20 class=textarea style=width:99%></textarea></td>
</tr>

<tr>
  <td><font class=list_eng>첨부 #</font></td>
  <td class=list_eng><input type="file" name="upfile"  size=30  maxlength=255 class=input style=width:99%> </td>
</tr>
<tr>

 <table border=0 width=670>

 <tr><td align="right">

<a href="#" onclick="submitContents(this);"><img src="./se/img/photoQuickPopup/btn_confirm2.png"></a>
 <a href="#" onclick="history.back();"><img src="./se/img/photoQuickPopup/btn_cancel.png"></a>
 </td></tr>
 </table>
</form>
</table>
</div>
  <h2></h2>
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
  <img src="./images/bg_banner_grey.gif" width="980" height="5" alt="logo_banner" />
<footer>
</footer>

</body>
</html>
<?}?>
<script type="text/javascript">
var oEditors = [];

nhn.husky.EZCreator.createInIFrame({

    oAppRef: oEditors,

    elPlaceHolder: "memo",

    sSkinURI: "./se/SmartEditor2Skin.html",

    fCreator: "createSEditor2"

});

function submitContents(elClickedObj) {
	oEditors.getById["memo"].exec("UPDATE_CONTENTS_FIELD", []);	// 에디터의 내용이 textarea에 적용됩니다.
	
	if(!document.getElementById("subject").value) {
		alert("제목을 입력하세요");
		return;
	}
	if(!document.getElementById("memo").value){
		alert("내용을 입력하세요");
		return;
	}
	else{
		document.myForm.action = "./insert.php?List=<?echo $_GET['List']?>";
		document.myForm.submit();
	}

	try {
		elClickedObj.form.submit();
	} catch(e) {}
}

</script>
