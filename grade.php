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
if (isset($_GET['gracode'])) {
	$graCode = $_GET['gracode'];
	echo '<div id="divmainContent" class="mainContent">';
		echo '<article class="topcontent100">';
//#####################################################################################
	$qry = " SELECT graCode, graDetail";
	$qry.= " FROM cGrades";
	$qry.= " Where graCode = " . $graCode;
	$qry.= " And   graActive = True";
	$result = mysql_query($qry) or die('Error: ' . mysql_error());
	$numrows = mysql_num_rows($result);
	if ($numrows > 0){
		$row = mysql_fetch_assoc($result);
		//echo "<header><h1 id='ct'>" . $row['graDetail'] . "</h1></header>";
		echo "<div class='gradeHeading'>" . $row['graDetail'] . "</div>";
	}
	echo '<p>';
	echo 'Here is the list of all Education skills that student learn in ' .$row['graDetail'] . '. ';
	echo 'These skills are organized into chapters. Click on chapter title for chapter material. ';
	echo 'To start practising, just click on any link. "' . $gVar['WebPageTitle'] . '" will track your score, your performance updates will be ';
	echo 'automatically sent via emails!';
	echo '</p>';
	$qry = " Select count(*) rowCount";
	$qry.= " from cGrades a, cChapters b, cQuizzes c";
	$qry.= " Where a.graCode = b.graCode";
	$qry.= " And b.chpCode = c.chpCode";
	$qry.= " And a.graActive = True";
	$qry.= " And b.chpActive = True";
	$qry.= " And c.qizActive = True";
	$result = mysql_query($qry) or die('Error: ' . mysql_error());
	$row = mysql_fetch_assoc($result);
	$rowcount =  $row['rowCount'];
	#
	$outqry = " SELECT chpCode, chpPrefix, chpTitle";
	$outqry.= " FROM cChapters";
	$outqry.= " Where graCode = " . $graCode;
	$outqry.= " And   chpActive = True";
	$outqry.= " Order by chpOrder";
	$outresult = mysql_query($outqry) or die('Error: ' . mysql_error());
	$outnumrows = mysql_num_rows($outresult);
	#
	$colrows = ($rowcount + ($outnumrows * 2)) /4;
	//$colrows = ($rowcount) / 3;
	//echo $colrows;
	$DispRowCnt=0;
	$DispColCnt=1;
	if ($outnumrows > 0){
//	    echo "<section class='secqiz' >";
			echo "<div class='divqizout' >";
				echo "<div class='divqizcol' >";
		while($outrow = mysql_fetch_assoc($outresult)) {
					echo "<div class='divqizchp' >";
						echo "<ul id='ct'>";
							//echo "<header><h3 id='ct'>" . $outrow['chpTitle'] . "</h3></header>";
							//echo "<header><h3 id='ct'>" . "<a href='chapter.php?chpcode=" . $outrow['chpCode'] . "'>" . $outrow['chpTitle'] . "</a>". "</h3></header>";
							echo "<a class='chplink' href='chapter.php?gracode=". $graCode ."&chpcode=" . $outrow['chpCode'] . "'>" . $outrow['chpTitle'] . "</a>";
			$DispRowCnt = $DispRowCnt + 2;
	/*		$inrqry = " SELECT qizCode, qizOrder, qizTitle";
			$inrqry.= " FROM cQuizzes";
			$inrqry.= " Where chpcode = " . $outrow['chpCode'];
			$inrqry.= " And   qizActive = True";
			$inrqry.= " Order by qizOrder";			*/		
			$inrqry = " SELECT a.qizCode, a.qizOrder, a.qizTitle, b.resCode, b.resScore";
			$inrqry.= " FROM cQuizzes as a Left Outer Join mresults as b on  a.qizCode = b.qizCode And b.stdCode =".$_SESSION['stdCode'];
			$inrqry.= " Where chpcode = " . $outrow['chpCode'];
			$inrqry.= " And   a.qizActive = True";
			$inrqry.= " Order by qizOrder";
			$inrresult = mysql_query($inrqry) or die('Error: ' . mysql_error());
			$inrnumrows = mysql_num_rows($inrresult);
			if ($inrnumrows > 0){
				$i = 1;
				echo "<ul class='aquizul'>";
				while($inrrow = mysql_fetch_assoc($inrresult)) {
					if ($inrrow['resScore'] == NULL) { 
						$resScore=""; 
					}else{
						if ($inrrow['resScore'] == 100) {
							$resScore="<span class='aqizscore100'>(".$inrrow['resScore'].")</span>";
						}else{
							$resScore="<span class='aqizscore'>(".$inrrow['resScore'].")</span>";
						} 
					}
					echo "<li class='aquizli'><a href='quiz.php?mancode=" . $manCode . "&subcode=" . $subCode . "&gracode=" . $graCode . "&qizcode=" . $inrrow['qizCode'] . 
					"&rescode=" . $inrrow['resCode']. "'";  
						echo " class='aqizlink'><span class='aqizbold'>" . $outrow['chpPrefix'] . "." . $i . " </span>";
						echo "<span class='aqizuline'>" . $inrrow['qizTitle'] . $resScore . "</span></a></li>";						
					$i++; $DispRowCnt++;
				} // End of Quiz while
						echo "</ul>";
					echo "</div>"; // divqizchp (based on calc)
			} // End of Quix if exist
			//if ($DispRowCnt > $colrows && $DispColCnt < $gVar['QuizListColCnt']) { echo "</div>"; $DispRowCnt=0; $DispColCnt++; echo "<div class='divqizcol' >";  }// divqizcol
			if ($DispRowCnt > $colrows) {
				if ($DispColCnt < $gVar['QuizListColCnt'] - 1) { echo "</div>"; $DispRowCnt=0; $DispColCnt++; echo "<div class='divqizcol' >";  }// divqizcol
				elseif ($DispColCnt < $gVar['QuizListColCnt'] ) { echo "</div>"; $DispRowCnt=0; $DispColCnt++; echo "<div class='divqizcollast' >";  }
			}
		} // End of Chp while
				echo "</div>"; // divqizcol 	
			echo "</div>"; // divqizout
//		echo "</section>"; // secqiz 
	} // End of Chp if exist	
//######################################################################################					
		echo '</article>';
//		echo '</div>';
	echo '</div>';
}   // End of gracode if		
#~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~* 
webPageFooter();
	} 
webHTMLFooter();
ob_flush();
 ?>