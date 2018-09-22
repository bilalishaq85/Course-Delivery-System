<?php
ob_start(); ini_set('output_buffering','1'); 
//require_once(str_replace('\\', '/', dirname(__FILE__)) . "/includes/browser.php");
require_once(str_replace('\\', '/', dirname(__FILE__)) . "/includes/dbcon.start.php");
require_once(str_replace('\\', '/', dirname(__FILE__)) . "/includes/libMain.php");
//require_once(str_replace('\\', '/', dirname(__FILE__)) . "/includes/libWeb.php");
if (!isset($gVar)) {global $gVar; $gVar = array(); $gVar = getGlobalVar(); }
require_once(str_replace('\\', '/', dirname(__FILE__)) . "/includes/managesession.php");
require_once(str_replace('\\', '/', dirname(__FILE__)) . "/quiz.Eng.Lib.php");
###############################################
if (isset($_POST['btnNav'])) {
	if     ($_POST['btnNav'] == "NEX") { $a=array_pop($_SESSION['queList']);   array_unshift($_SESSION['queList'], $a); $_SESSION['queCode']=$_SESSION['queList'][0]; }
	elseif ($_POST['btnNav'] == "PRE") { $a=array_shift($_SESSION['queList']); array_push($_SESSION['queList'], $a);    $_SESSION['queCode']=$_SESSION['queList'][0]; }
}

$showCorrection = "N"; $showGreeting="N";
if (isset($_POST['chkanswer']) && $_POST['chkanswer'] == 'Y' ) {
	if ( strtoupper($_SESSION['queAnswer']) == strtoupper($_POST['answer']) ) {
		$_SESSION['Corrects']+=$_SESSION['queWeight'];
		$_SESSION['Score']=round($_SESSION['Corrects']/$_SESSION['PassCnt']*100); 
		$_SESSION['Attempts']+=1;
		array_push($_SESSION['Answered'], array_shift($_SESSION['queList']));
		$showGreeting="Y"; $showCorrection = "N"; 
		if ($_SESSION['Score'] >= 100) { $_SESSION['Score']=100; unset($_SESSION['queList']); $_SESSION['queList']=array(); } 
		#stdUpdateResult();     #Does not update failed attemps	
	}else{ 
		$showGreeting="N"; $showCorrection="Y"; shuffle($_SESSION['queList']); 
		$_SESSION['Attempts']+=1;
		if (strtoupper($gVar['QuizNegativeMarking']) == 'Y') {
			$_SESSION['Corrects']-=$_SESSION['queWeight'];
			$_SESSION['Score']=round($_SESSION['Corrects']/$_SESSION['PassCnt']*100);
			if ($_SESSION['Score'] < 0) { $_SESSION['Score']=0;} 
		}
	}
	stdUpdateResult();
	stdAddLog($showGreeting);
}
if (isset($_SESSION['Score']) && isset($_SESSION['Attempts']) ) {
	echo '<input id="txtScore"    type="hidden" value='.$_SESSION['Score'].'>';
	echo '<input id="txtAttempts" type="hidden" value='.$_SESSION['Attempts'].'>';
}
if ( $showCorrection == "Y" ) {
	echo "<div class='quizincorrect'>Sorry, incorrect...</div>";
	echo "<div class='quizque'>" . $_SESSION['queQuestion'] . "</div>";
	echo "<div class='quizlinemsg'>The correct answer is:</div>";	
	switch ($_SESSION['typCode']) {
	case 1: //echo "Question Type was: 1";
		dispQuestion1($_SESSION['queAnswer']);
		echo "<div class='quizlinemsg'>You Answered:</div>";
		dispQuestion1($_POST['answer']);
		break;
	case 2: //echo "Question Type: 2";
		dispQuestion2($_SESSION['queAnswer'], "radCorrect");
		echo "<div class='quizlinemsg'>You Answered:</div>";
		dispQuestion2($_POST['answer'], "radIncorrect");
		break;
	case 3: //echo "Question Type: 3";
		dispQuestion3($_SESSION['queAnswer']);
		echo "<div class='quizlinemsg'>You Answered:</div>";
		dispQuestion3($_POST['answer']);
		break;
	case 4: //echo "Question Type: 4";
		dispQuestion4($_SESSION['queAnswer']);
		echo "<div class='quizlinemsg'>You Answered:</div>";
		dispQuestion4($_POST['answer']);			
		break;
	case 5: //echo "Question Type: 5";
		dispQuestion5($_SESSION['queAnswer']);
		echo "<div class='quizlinemsg'>You Answered:</div>" ;
		dispQuestion5($_POST['answer']);
		break;
	}		
	echo "<div class='quizquebtn'><button class='quizbutton' id='btnGotit'>Got it</button></div>";	
}else{
	if ($showGreeting=="Y") { 
		echo "<script type='text/javascript'>setTimeout(hideDivGreet, " . $gVar['QuizCorrectAlertSec'] * 1000 . "); </script>";
		echo "<div id='divGreet' class='divquefinish' >Correct !<button id='btnFinish' class='divquizfinishbtn'></button></div>";
		echo "<div id='divQueInner' style='display:none'>";
		$showGreeting="N";
	}else{
		echo "<div id='divQueInner' style='display:block'>";
	}
	##### Temp line below#######
	//$_SESSION['queList']= array(12,13,15); shuffle($_SESSION['queList']);
	###############################################
StartOfQuestion:
	#if (!empty($_SESSION['queList'])) {
	if (sizeof($_SESSION['queList']) > 0) {
		$_SESSION['queOption'] = array();
		$_SESSION['queMatch'] = array();
		$_SESSION['optTitle'] = "";
		$_SESSION['matTitle'] = "";
		$_SESSION['queShowInline'] = "";
		$_SESSION['queAnswer'] = "";
		$_SESSION['queWeight'] = "";
		$qry = " SELECT queCode, typCode, queQuestion, queShowInline, queAnswer, queWeight";
		$qry.= " FROM cQuestions";
		$qry.= " Where queCode = ". getIstElement($_SESSION['queList']);
		$result = mysql_query($qry) or die('Error: ' . mysql_error());
		$numrows = mysql_num_rows($result);
		if ($numrows > 0){
			$row = mysql_fetch_assoc($result);
			$_SESSION['queCode'] = $row['queCode'];
			$_SESSION['typCode'] = $row['typCode'];
			$_SESSION['queQuestion'] = $row['queQuestion'];
			$_SESSION['queShowInline'] = $row['queShowInline'];
			$_SESSION['queAnswer'] = NtoE($row['queAnswer']);
			$_SESSION['queWeight'] = $row['queWeight'];
			echo "<input type='hidden' id='txthide' value=1 />";
			echo "<div class='quizque'>" . $_SESSION['queQuestion'] . "</div>";
			switch ($_SESSION['typCode']) {
			case 1: //echo "Question Type: 1";
				dispQuestion1("NEW");
				break;
			case 2: //echo "Question Type: 2";
				$qry = " SELECT optCode, optNumber, optDetail, optImage, optImageH, optImageW, optCorrect";
				$qry.= " FROM cOptions";
				$qry.= " Where queCode = ". $_SESSION['queCode'];
				$qry.= " And optActive = True";
				$result = mysql_query($qry) or die('Error: ' . mysql_error());
				$numrows = mysql_num_rows($result);
				if ($numrows > 0){
					while($row = mysql_fetch_assoc($result)) {
						$_SESSION['queOption'][] = array('optCode' => $row['optCode'], 
														'optNumber' => $row['optNumber'], 
														'optDetail' => $row['optDetail'], 
														'optImage' => $row['optImage'], 
														'optImageH' => iif($row['optImageH'] > 0, "height='".$row['optImageH']."'", ""), 
														'optImageW' => iif($row['optImageW'] > 0,  "width='".$row['optImageW']."'", ""), 
														'optCorrect' => $row['optCorrect']);	
					}
					shuffle($_SESSION['queOption']); 
					$i = 1;
					foreach ( $_SESSION['queOption'] as $option ){ if ($option['optCorrect'] == 1) { $_SESSION['queAnswer'] = $i; } $i++; }
					dispQuestion2("NEW", "radOption");
				}
				break;
			case 3: //echo "Question Type: 3";
				$qry = " SELECT optCode, optNumber, optDetail, optImage, optImageH, optImageW, optCorrect";
				$qry.= " FROM cOptions";
				$qry.= " Where queCode = ". $_SESSION['queCode'];
				$qry.= " And optActive = True";
				$result = mysql_query($qry) or die('Error: ' . mysql_error());
				$numrows = mysql_num_rows($result);
				if ($numrows > 0){
					while($row = mysql_fetch_assoc($result)) {
						$_SESSION['queOption'][] = array('optCode' => $row['optCode'], 
														'optNumber' => $row['optNumber'], 
														'optDetail' => $row['optDetail'], 
														'optImage' => $row['optImage'], 
														'optImageH' => iif($row['optImageH'] > 0, "height='".$row['optImageH']."'", ""), 
														'optImageW' => iif($row['optImageW'] > 0,  "width='".$row['optImageW']."'", ""), 
														'optCorrect' => $row['optCorrect']);
					}
					shuffle($_SESSION['queOption']);
					$i = 1;
					foreach ( $_SESSION['queOption'] as $option ){
						if ($option['optCorrect'] == 1) { $_SESSION['queAnswer'].= $i . ","; } else { $_SESSION['queAnswer'].= "0,"; }
						$i++; 
					}
					dispQuestion3("NEW"); 
				}
				break;
			case 4: //echo "Question Type: 4";
				$qry = " SELECT optCode, optNumber, optDetail, optImage, optImageH, optImageW, optCorrect";
				$qry.= " FROM cOptions";
				$qry.= " Where queCode = ". $_SESSION['queCode'];
				$qry.= " And optActive = True";
				$result = mysql_query($qry) or die('Error: ' . mysql_error());
				$numrows = mysql_num_rows($result);
				if ($numrows > 0){
					$txtListWidth=1; if ($numrows > 9)  { $txtListWidth = 2; }; if ($numrows > 99) { $txtListWidth = 3; }
					while($row = mysql_fetch_assoc($result)) {
						$_SESSION['queOption'][] = array('optCode' => $row['optCode'], 
														'optNumber' => $row['optNumber'], 
														'optDetail' => $row['optDetail'],
														'optImage' => $row['optImage'], 
														'optImageH' => iif($row['optImageH'] > 0, "height='".$row['optImageH']."'", ""), 
														'optImageW' => iif($row['optImageW'] > 0,  "width='".$row['optImageW']."'", ""), 
														'optCorrect' => $row['optCorrect']);	
					}
					shuffle($_SESSION['queOption']);
					foreach ( $_SESSION['queOption'] as $option ){ $_SESSION['queAnswer'].= $option['optNumber'] . ","; } 
					dispQuestion4("NEW");
				}
				break;
			case 5: //echo "Question Type: 5";
				$qry = " SELECT a.optCode, a.optNumber, a.optDetail, a.optImage, a.optImage, a.optImageH, a.optImageW, b.matDetail, b.matImage, b.matImageH, b.matImageW";
				$qry.= " From cOptions a, cMatch b";
				$qry.= " Where queCode = ". $_SESSION['queCode'];
				$qry.= " And a.optCode = b.optCode";
				$qry.= " And a.optActive = True";
				$result = mysql_query($qry) or die('Error: ' . mysql_error());
				$numrows = mysql_num_rows($result);
				if ($numrows > 0){
					$txtListWidth=1; if ($numrows > 9)  { $txtListWidth = 2; }; if ($numrows > 99) { $txtListWidth = 3; }
					while($row = mysql_fetch_assoc($result)) {
						if ($row['optNumber'] == 0) {
							$_SESSION['optTitle'] = $row['optDetail']; $_SESSION['matTitle'] = $row['matDetail'];
						}else{
							$_SESSION['queOption'][] = array('optCode' => $row['optCode'], 
															'optNumber' => $row['optNumber'], 
															'optDetail' => $row['optDetail'], 
															'optImage' => $row['optImage'], 
															'optImageH' => iif($row['optImageH'] > 0, "height='".$row['optImageH']."'", ""), 
															'optImageW' => iif($row['optImageW'] > 0,  "width='".$row['optImageW']."'", ""));
							$_SESSION['queMatch'][] = array('optNumber' => $row['optNumber'], 
															'matDetail' => $row['matDetail'], 
															'matImage' => $row['matImage'], 
															'matImageH' => iif($row['matImageH'] > 0, "height='".$row['matImageH']."'", ""), 
															'matImageW' => iif($row['matImageW'] > 0,  "width='".$row['matImageW']."'", ""));	
						}
					}
					shuffle($_SESSION['queOption']); 
					shuffle($_SESSION['queMatch']);
					foreach ( $_SESSION['queMatch'] as $match ){
						$i = 1;
						foreach ( $_SESSION['queOption'] as $option ){
							if ( $option['optNumber'] == $match['optNumber'] ) { $_SESSION['queAnswer'].= $i . ","; }
							$i++;
						}
					}
					dispQuestion5("NEW");
				}
				break;
			}		
			echo "<div class='quizquebtn'><button class='quizbutton' id='btnSubmit'>Submit</button></div>";
		}else{ array_shift($_SESSION['queList']); goto StartOfQuestion; }
	}else{
		$_SESSION['qizCode'] = "";		
		echo "<script type='text/javascript'>setTimeout(goback, " . $gVar['QuizFinishAlertSec'] * 1000 . "); </script>";
		echo "<div class='divquizfinish' >Well done !<br><br>Quiz is now complete<button id='btnFinish' class='divquizfinishbtn'></button></div>";
	}
	echo "</div>";	
}
?>

	<script type="text/javascript">
	if (document.getElementById('lblScore')) {
		document.getElementById('lblScore').innerHTML=document.getElementById('txtScore').value;
		document.getElementById('lblAttempts').innerHTML=document.getElementById('txtAttempts').value;
	}
	//alert("divQue loaded.");
	</script>




