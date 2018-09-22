<?php
ob_start(); ini_set('output_buffering','1'); 
require_once(str_replace('\\', '/', dirname(__FILE__)) . "/includes/browser.php");
require_once(str_replace('\\', '/', dirname(__FILE__)) . "/includes/dbcon.start.php");
require_once(str_replace('\\', '/', dirname(__FILE__)) . "/includes/libMain.php");
global $gVar; $gVar = array(); $gVar = getGlobalVar();
require_once(str_replace('\\', '/', dirname(__FILE__)) . "/includes/managesession.php");
require_once(str_replace('\\', '/', dirname(__FILE__)) . "/includes/libWeb.php");
manQuizVar("CLEAN"); manQuestionVar("CLEAN");
webHTMLHeader();
If (browserCheck()) { 
	webPageHeader();
	webMainMenu();
#~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*
	$_SESSION['Score']=0;
	$_SESSION['ScoreOld']=0;
	$_SESSION['Attempts']=0;
	$_SESSION['Corrects']=0;
	$_SESSION['PassCnt']=0;
	$_SESSION['Answered']=array();
	$_SESSION['resCode']=0;
	$Answered="";
if (isset($_GET['gracode']) || isset($_GET['qizcode'])) {
	if (!isset($_SESSION['qizCode'])) { $_SESSION['qizCode'] = ""; }
	//if (!isset($_SESSION['queList'])) { $_SESSION['queList'] = ""; }
	$_SESSION['queList'] = "";
	#############################################
	echo '<div class="mainContent" id="divmainContent">';
		echo '<article class="topcontent100">';
	$qry = " SELECT graCode, graDetail";
	$qry.= " FROM cGrades";
	$qry.= " Where graCode = " . $_GET['gracode'];
	$qry.= " And   graActive = True";
	$result = mysql_query($qry) or die('Error: ' . mysql_error());
	$numrows = mysql_num_rows($result);
	if ($numrows > 0){
		$row = mysql_fetch_assoc($result);
		//echo "<header><h1 id='ct'>" . $row['graDetail'] . "</h1></header>";
		echo "<div class='gradeHeading'>" . $row['graDetail'] . "</div>";
		
	}
//###############################################
    #Get previous results
	$qry = " SELECT resCode, resAttempts, resCorrects, resScore, resAnswered";
	$qry.= " FROM mResults";
	$qry.= " Where stdCode = " . $_SESSION['stdCode'];
	$qry.= " And qizCode = " . $_GET['qizcode'];
#   if ($_GET['rescode'] != NULL) {
#		$qry.= " Where resCode = " . $_GET['rescode'];
#	}else{
#		$qry.= " Where stdCode = " . $_SESSION['stdCode'];
#		$qry.= " And qizCode = " . $_GET['qizcode'];
#	}
	$result = mysql_query($qry) or die('Error: ' . mysql_error());
	$numrows = mysql_num_rows($result);
	if ($numrows > 0){
		$row = mysql_fetch_assoc($result);
		$_SESSION['resCode']=$row['resCode'];
		if ($row['resScore'] < 100) {
			$_SESSION['Score']=$row['resScore'];
			$_SESSION['ScoreOld']=$row['resScore'];
			$_SESSION['Attempts']=$row['resAttempts'];
			$_SESSION['Corrects']=$row['resCorrects'];
			$Answered=trim($row['resAnswered']);
			$_SESSION['Answered']=explode(",",$Answered);
		}else{
			$_SESSION['ScoreOld']=$row['resScore'];
		}
	}
//###############################################
	$qry = " SELECT qizTitle, qizPassCnt";
	$qry.= " FROM cQuizzes";
	$qry.= " Where qizCode = " . $_GET['qizcode'];
	$result = mysql_query($qry) or die('Error: ' . mysql_error());
	$numrows = mysql_num_rows($result);
	if ($numrows > 0){
		$row = mysql_fetch_assoc($result);
		echo "<header><h3 id='ct'>" . $row['qizTitle'] . "</h3></header>";
		$_SESSION['PassCnt']=$row['qizPassCnt'];
	}
	echo "<hr>";
	#############################################
    //if ($_SESSION['qizCode'] != $_GET['qizcode']) {
		$_SESSION['qizCode'] = $_GET['qizcode'];
		$_SESSION['queList'] = "";
		//echo "Fetching Question.";
		$qry = " SELECT queCode";
		$qry.= " FROM cQuestions";
		$qry.= " Where qizCode = ". $_SESSION['qizCode'];
		//if ($Answered != "") { $qry.= " And queCode NOT IN (".$Answered.")"; }
		$qry.= " And queActive = True";
		$result = mysql_query($qry) or die('Error: ' . mysql_error());
		$numrows = mysql_num_rows($result);
		//echo $qry . " -------> numrows:" . $numrows;
		if ($numrows > 0){
			if ($numrows < $_SESSION['PassCnt']) { $_SESSION['PassCnt'] = $numrows; }
			$row = mysql_fetch_assoc($result); $_SESSION['queList'] = $row['queCode'];
			while($row = mysql_fetch_assoc($result)) { $_SESSION["queList"].= "," . $row['queCode'];}
			$_SESSION['queList'] = explode(",",$_SESSION['queList']);
			shuffle($_SESSION['queList']);
			$_SESSION['queList']=array_diff($_SESSION['queList'], $_SESSION['Answered']);
			echo "<div id='divQue'>";
			require(str_replace('\\', '/', dirname(__FILE__)) . "/Quiz.Eng.php");
			echo "</div>";
		}else{
		    $_SESSION['qizCode'] = "";
			$_SESSION['queList'] = [];
			header('Location: ' . $_SERVER['HTTP_REFERER']); exit;
		}
//getSessionVar();
//######################################################################################					
		echo '</article>';
//		echo '</div>';
		showScore();
	echo '</div>';
}   // End of gracode if		
#~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~* 
webPageFooter();
	} 
webHTMLFooter(); ob_flush();

function showScore() {
	echo '<div id="scoreouter" class="divscoreouter">';
		//echo '&nbsp; &nbsp; &nbsp;';
		echo '<div id="score" class="divscore">';
			echo 'Score'.'<br>';
			echo '<label id="lblScore">'.$_SESSION['Score'].'</label>';
		echo '</div>';
		//echo '<div id="sep" class="divscoresep">'.'</div>';
		echo '<div id="score" class="divscorebot">';
			echo 'Attempts'.'<br>';
			echo '<label id="lblAttempts">'.$_SESSION['Attempts'].'</label>';
		echo '</div>';
	echo '</div>';
}
 ?>