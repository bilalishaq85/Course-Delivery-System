<?php
ob_start(); ini_set('output_buffering','1'); 
require_once(str_replace('\\', '/', dirname(__FILE__)) . "/includes/browser.php");
require_once(str_replace('\\', '/', dirname(__FILE__)) . "/includes/dbcon.start.php");
require_once(str_replace('\\', '/', dirname(__FILE__)) . "/includes/libMain.php");
global $gVar; $gVar = array(); $gVar = getGlobalVar();
require_once(str_replace('\\', '/', dirname(__FILE__)) . "/includes/managesession.php");
require_once(str_replace('\\', '/', dirname(__FILE__)) . "/includes/libWeb.php");
require_once(str_replace('\\', '/', dirname(__FILE__)) . "/includes/libAdmin.php");
chkPermission();
webHTMLHeader();
//getPostVar();
If (browserCheck()) { 
	webPageHeader();
	webMainMenu();
	echo '<div id="divmainContent" class="mainContent">';
		echo '<article class="topcontentAdmin">';
			$heading=webAdminMenu();
			echo '<article class="topcontentAdminInner">';
			echo '<div class="adminHeading1">'. $heading . '</div>';
#~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*
webGradeTab(); //>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>
	echo '<form action="" method="post" name="list" id="list">';
	echo '<div class="divselect">'; 
		$gradeselect=webGradeSelect(); 
		$chapterselect=webChapterSelect($gradeselect);
		$quizselect=webQuizSelect($chapterselect);
	echo '</div>'; //>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>
	if 	   ($gradeselect==0)   	{ echo "<div class='diverrormsg'>" . "Zero (0) levels/grades defined, go back and define levels/grades first." . "</div>"; }
	elseif ($chapterselect==0) 	{ echo "<div class='diverrormsg'>" . "Zero (0) chapters defined, go back and define chapter under selected level/grade." . "</div>"; }
	elseif ($quizselect==0) 	{ echo "<div class='diverrormsg'>" . "Zero (0) Quiz defined, go back and define quiz under selected chapter." . "</div>"; }
	else {
		manQueArray($quizselect);
		if (!empty($_SESSION['queList'])) {
			if ($quizselect = $_GET['quizselect']) { manAlignArray(); }
			showQuesNavBtn(); 
			dispQuestion(); 
		}
	}
	echo '</form>';
#~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*
			echo '</article>';
		echo '</article>';
	echo '</div>';
	webPageFooter();
}
webHTMLFooter();
ob_flush();
####################################################################
function showQuesNavBtn() {
    echo "<div id='divOptButton' class='divOptButton' style='padding:0 0 ;'>"; 
	echo "" .
	"<button type='button' id='btnPreQue' alt='Previous' class='btnnav' style='width:80px; height:27px; font-size: 14px;'> Previous </button> " .
	"<button type='button' id='btnNexQue' alt='Next'     class='btnnav' style='width:80px; height:27px; font-size: 14px;'> Next </button>";
	echo "<p> <hr>";
	echo "</div>";
}
function manQueArray($quizselect) {
	$qry = " SELECT queCode";
	$qry.= " FROM cQuestions";
	$qry.= " Where qizCode = " . $quizselect;
	$result = mysql_query($qry) or die('Error: ' . mysql_error());
	$numrows = mysql_num_rows($result);
	if ($numrows > 0){
		$row = mysql_fetch_assoc($result); $_SESSION['queList'] = $row['queCode'];
		while($row = mysql_fetch_assoc($result)) { $_SESSION["queList"].= "," . $row['queCode'];}
		$_SESSION['queList'] = explode(",",$_SESSION['queList']);
		$_SESSION['queCode']=$_SESSION['queList'][0];
	}else{
		$_SESSION['qizCode'] = "";
		$_SESSION['queList'] = [];
		//header('Location: ' . $_SERVER['HTTP_REFERER']); exit;
		echo "<div class='diverrormsg'>" . "No Questions defined in this Chapter/Quiz." . "</div>";
	}
}
function manAlignArray() {
	$i=0;
	if ($_GET['questionselect'] > 0 && $_GET['questionselect'] != $_SESSION['queCode']) {
		while ($_GET['questionselect'] != $_SESSION['queCode']) {
			$a=array_shift($_SESSION['queList']);
			array_push($_SESSION['queList'], $a);
			$_SESSION['queCode']=$_SESSION['queList'][0];
			$i+=1;
			if ($i > sizeof($_SESSION['queList'])) {break;}
		}
	}
}					
function dispQuestion() {
	echo "<div id='divQue' style='padding:0 0;' >";
	require(str_replace('\\', '/', dirname(__FILE__)) . "/Quiz.Eng.php");
	echo "</div>";
}
?>
