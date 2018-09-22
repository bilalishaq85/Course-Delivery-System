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
if      (isset($_POST['btnadd']))    { manAddEdit('add');	if( strtolower($gVar['EM_ForceShowList']) == "y") { manList(false); }
}elseif (isset($_POST['btnedit']))   { manAddEdit('edit');	if( strtolower($gVar['EM_ForceShowList']) == "y") { manList(false); }
}elseif (isset($_POST['btninsert'])) { manInsert();			manList(true);
}elseif (isset($_POST['btnupdate'])) { manUpdate();			manList(true);
}elseif (isset($_POST['btndel']))    { manDel();	  		manList(true);		#Manage single   row  delete
}elseif (isset($_POST['btndelete'])) { manDelete();   		manList(true);		#Manage multiple rows delete
}elseif (isset($_POST['btncancel'])) { manList(true);
}else{ 	manList(true); }
#~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*
			echo '</article>';
		echo '</article>';
	echo '</div>';
	webPageFooter();
}
webHTMLFooter();
ob_flush();
####################################################################
function manList($isActionable) {
	global $gVar;
	if ($isActionable) { $btnControl=" ";} else { $btnControl=' disabled="disabled" '; }
	echo '<form action="" method="post" name="list" id="list">';
	echo '<div class="divselect">'; 
		$gradeselect=webGradeSelect(); 
		$chapterselect=webChapterSelect($gradeselect);
		$quizselect=webQuizSelect($chapterselect);
	echo '</div>'; //>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>
	if ($gradeselect==0)   { echo "<div class='diverrormsg'>" . "Zero (0) levels/grades defined, go back and define levels/grades first." . "</div>"; return; }
	if ($chapterselect==0) { echo "<div class='diverrormsg'>" . "Zero (0) chapters defined, go back and define chapter under selected level/grade." . "</div>"; return; }
	if ($quizselect==0) { echo "<div class='diverrormsg'>" . "Zero (0) Quiz defined, go back and define quiz under selected chapter." . "</div>"; return; }
	$qry = " SELECT a.queCode, a.queQuestion, a.typCode, b.typTitle, a.queActive";
	$qry.= " FROM cQuestions a, cTypes b";
	$qry.= " Where a.typCode = b.typCode"; 
	$qry.= " And qizCode = " . $quizselect;
	$qry.= " Order by queCode"; 
	//---------------Paging Header--------------------*
	require_once(str_replace('\\', '/', dirname(__FILE__)) . "/includes/paging.header.php");
	//------------------------------------------------*
	$result = mysql_query($qry." LIMIT " . $from . "," . $gVar['MaxTablesRows']);
	//---------------Paging Footer--------------------*
	if ($isActionable) {require_once(str_replace('\\', '/', dirname(__FILE__)) . "/includes/paging.footer.php"); }
	//------------------------------------------------*
	echo '<table class="gbltable">';
		echo '<tr>';
			echo '<th class="gbltableth">' . '*' . '</th>';
			echo '<th class="gbltableth">' . 'Action' . '</th>';
			echo '<th class="gbltableth">' . 'Question' . '</th>';
			echo '<th class="gbltableth">' . 'Type' . '</th>';
			echo '<th class="gbltableth">' . 'Active' . '</th>';
		echo '</tr>';
		$i=1;
		if (isset($_GET['questionselect'])) { $questionselect=$_GET['questionselect']; } else { $questionselect=0;}
		if (isset($_SESSION['queCode'])) { $questionselect=$_SESSION['queCode'];}
		if (isset($_POST['rowcode'])) { foreach ($_POST['rowcode'] as $value) { $questionselect = $value; }}
		if (isset($_POST['txtqueCode'])) { $questionselect=$_POST['txtqueCode']; }
		while($row = mysql_fetch_assoc($result)) {
			echo '<tr>';
				echo '<td class="colSL">'.'<input name="rowcode[]" type="checkbox" class="checkboxsmall" id="rowcode'.$i.'" value="'.$row['queCode'].'"'.$btnControl.' '.manCheck($row['queCode'],$questionselect).' '.
					'onclick="chkCurrent(event, this)"' . '>';  
				echo '<td class="colAC">';
					echo '<button type="submit" method="post" title="Edit Row"  class="btnnav" name="btnedit"' . $btnControl . ' ' .
					'onclick="checkAll(false); document.getElementById(\'rowcode'.$i.'\').checked=true;">E</button>';
					'>E</button>';
					echo '&nbsp';
					echo '<button type="submit" method="post" title="Delete Row" class="btnnav" name="btndel"' . $btnControl . ' ' .
					'onclick="checkAll(false); document.getElementById(\'rowcode'.$i.'\').checked=true; return (confirmDelete(\'btndel\'))">X</button>';
				echo '</td>';
				echo '<td class="colTT" style="max-width:920px;">' . $row['queQuestion']  . '</td>';
				echo '<td class="colNR">' . $row['typCode'] . '. ' . $row['typTitle'] . '</td>';
				echo '<td class="colNC">' . manActive($row['queActive']) . '</td>';
			echo '</tr>';
			$i++;
		}
	echo '</table>';
	echo '<script type="text/javascript"> chkOneSelected(); </script>';
	if ($isActionable) { showbtnSelect(); showbtnAddDel(); }
	echo '</form>';
}
function manAddEdit($option) {
	global $gVar;
	if ($option == 'edit') {
		foreach ($_POST['rowcode'] as $value) { $editCode = $value; }
		$qry = "Select queCode, typCode, queWeight, queQuestion, queAnswer, queShowInline, queActive from cQuestions where queCode = " . $editCode;
		$result = mysql_query($qry); if (!($result)) {errHandler($qry); return;}
		$numrows = mysql_num_rows($result);
		if ($numrows > 0){ 
			$row = mysql_fetch_assoc($result);
			$txtCode 		= $row['queCode'];
			$txttypeCode 	= $row['typCode'];
			$txtWeight 		= $row['queWeight'];
			$txtQuestion 	= $row['queQuestion'];
			$txtAnswer 		= $row['queAnswer'];
			$txtShowInline 	= $row['queShowInline'];
			$txtActive 		= $row['queActive'];
		} else {
			echo "<div class='diverrormsg'>" . "Selected row not found, possibly deleted by some other user." . "</div>";
			return;
		}
		
	} else {
		$txtCode=""; $txttypeCode=""; $txtWeight=""; $txtQuestion=""; $txtAnswer=""; $txtShowInline=""; $txtShowInline="";
	}
	echo '<div class="divDetail">';
	echo '<input name="QuestionOptionMax" id="QuestionOptionMax" type="hidden" value="'. $gVar['QuestionOptionMax'].'">';
	echo '<form action="" method="post" name="detail" enctype="multipart/form-data" onsubmit="return ValidateForm(this)">';
	echo '<input name="txtimgs" id="txtimgs" type="hidden" value="'.$gVar['EM_ImageMaxSizeKB'].'">';
	echo '<input name="txtimgt" id="txtimgt" type="hidden" value="'.$gVar['EM_ImageTypes'].'">';
	echo '<input name="gradeselectH"   id="gradeselectH"   type="hidden" value="'. $_POST["gradeselect"].'">';
	echo '<input name="chapterselectH" id="chapterselectH" type="hidden" value="'. $_POST["chapterselect"].'">';
	echo '<input name="quizselectH"    id="quizselectH"    type="hidden" value="'. $_POST["quizselect"].'">'; //>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>
	echo '<input name="txtoptCodeDel"  id="txtoptCodeDel"  type="hidden" value="">';
	echo '<input name="txtmatCodeDel"  id="txtmatCodeDel"  type="hidden" value="">';
	
	if ($option == 'edit') { 
		echo '<div class="adminHeading2">Edit Row' . '</div>';
		echo '<input name="txtqueCode" id="txtqueCode" type="hidden" value="'.$txtCode.'">';
		echo '<input name="typeselectH" id="typeselectH" type="hidden" value="'.$txttypeCode.'">';
		echo '<div id="divQuestion">';   //Start of divQuestion
		webQTypeSelect($txttypeCode);
		echo '<label for="txtWeight" style="width:120px">Weightage:</label>';
			echo '<input name="txtWeight" id="txtWeight" type="text" accesskey="1" tabindex="1" style="width:35px" maxlength="3" value="'.$txtWeight.'" >';
		echo '<label for="txtActive" style="width:882px">Active:</label>';
			echo '<select name="txtActive" id="txtActive" style="width:60px" tabindex="2" title="Select Action">';
				echo '<option value="1" '.manSelect($txtActive,1). ' >Yes</option>';
				echo '<option value="0" '.manSelect($txtActive,0). ' >No</option>';
			echo '</select> <br>';
		echo '<label for="txtQuestion" style="width:120px">Question:</label>';
			echo '<input name="txtQuestion" id="txtQuestion" type="text" accesskey="1" tabindex="3" style="width:977px" maxlength="300" value="'.$txtQuestion.'" > <br>';
		switch ($txttypeCode) {
		case 1: 
			echo '<label for="txtAnswer" style="width:120px">Answer:</label>';
				echo '<input name="txtAnswer" id="txtAnswer" type="text" accesskey="1" tabindex="4" style="width:100px" maxlength="30" value="'.$txtAnswer.'" > <br>';
		break;
		case 2:
			echo '<label for="txtShowInline" style="width:120px">Display:</label>';
				echo '<select name="txtShowInline" id="txtShowInline" style="width:200px" tabindex="2" title="Select Action">';
					echo '<option value="0" '.manSelect($txtShowInline,0). ' >Options in Multiple rows</option>';
					echo '<option value="1" '.manSelect($txtShowInline,1). ' >Options in Single row</option>';
				echo '</select> <br>';
			echo '<div id="divQuestionDetail" class="divQuestionDetail">';
				getQOption2to4($editCode, $txttypeCode);
				showOptionBtn();
			echo '</div>'; // End of divQuestionDetail				
		break;
		case 3:
			echo '<label for="txtShowInline" style="width:120px">Display:</label>';
				echo '<select name="txtShowInline" id="txtShowInline" style="width:200px" tabindex="2" title="Select Action">';
					echo '<option value="0" '.manSelect($txtShowInline,0). ' >Options in Multiple rows</option>';
					echo '<option value="1" '.manSelect($txtShowInline,1). ' >Options in Single row</option>';
				echo '</select> <br>';
			echo '<div id="divQuestionDetail" class="divQuestionDetail">';
				getQOption2to4($editCode, $txttypeCode);
				showOptionBtn();
			echo '</div>'; // End of divQuestionDetail
		break;		
		case 4:
			echo '<label for="txtShowInline" style="width:120px">Display:</label>';
				echo '<select name="txtShowInline" id="txtShowInline" style="width:200px" tabindex="2" title="Select Action">';
					echo '<option value="0" '.manSelect($txtShowInline,0). ' >Options in Multiple rows</option>';
					echo '<option value="1" '.manSelect($txtShowInline,1). ' >Options in Single row</option>';
				echo '</select> <br>';
			echo '<div id="divQuestionDetail" class="divQuestionDetail">';
				getQOption2to4($editCode, $txttypeCode);
				showOptionBtn();
			echo '</div>'; // End of divQuestionDetail		
		break;
		case 5:
			echo '<div id="divQuestionDetail" class="divQuestionDetail">';
				getQOption5($editCode);
				getQMatch5($editCode);
				showOptionBtn();
			echo '</div>'; // End of divQuestionDetail	
		break;
		default:
		break;
		}
		echo '</div>'; // End of divQuestion
		//echo '<br>';
	} else {
		echo '<div class="adminHeading2">Add Row' . '</div>';	
		webQTypeSelect(0);
		echo '<div id="divQuestion"> </div>';
		echo '<div id="divQuestionDetail" class="divQuestionDetail">';
			echo '<div id="divQuestionLeft" class="divQuestionLeft"> </div>';
			echo '<div id="divQuestionRight" class="divQuestionRight"> </div>';
			echo '<br>';
			echo '<div id="divOptButton" class="divOptButton"> </div>';
		echo '</div>';
	}
	//********End Inputs********
	if ($option == 'edit') { showbtnUpdateCancel(); }else{ showbtnInsertCancel(); };
	echo '</form>';
	echo '</div>';
}
function manInsert() {
	if (isset($_SESSION['actionsave']) && $_SESSION['actionsave']=="Y") {
		$queCode='NULL';
		$graCode=$_POST['gradeselectH'];
		$chpCode=$_POST['chapterselectH'];
		$qizCode=$_POST['quizselectH'];
		if (isset($_POST['typeselectH'])) {
			$typCode=$_POST['typeselectH'];
			$queWeight=$_POST['txtWeight'];
			$queActive=$_POST['txtActive'];
			$queQuestion=mysql_real_escape_string($_POST['txtQuestion']);
		}else{$typCode=0;}
		switch ($typCode) {
		case 1:
			$queShowInline=0;
			$queAnswer=mysql_real_escape_string($_POST['txtAnswer']);
			$qry = " Insert into cQuestions (queCode, qizCode, typCode, queQuestion, queShowInline, queAnswer, queWeight, queActive)";
			$qry.= " VALUES (".$queCode.",".$qizCode.",".$typCode.",'".$queQuestion."',".$queShowInline.",'".$queAnswer."',".$queWeight.",".$queActive.")";
			$result = mysql_query($qry); if (!($result)) {errHandler($qry); return;} else { $queCode=mysql_insert_id(); $_POST['txtqueCode']=$queCode; }
		break;
		case 2: 	
			$queShowInline=$_POST['txtShowInline'];
			$queAnswer='NULL';
			$qry = " Insert into cQuestions (queCode, qizCode, typCode, queQuestion, queShowInline, queAnswer, queWeight, queActive)";
			$qry.= " VALUES (".$queCode.",".$qizCode.",".$typCode.",'".$queQuestion."',".$queShowInline.",'".$queAnswer."',".$queWeight.",".$queActive.")";
			$result = mysql_query($qry); if (!($result)) {errHandler($qry); return;} else { $queCode=mysql_insert_id(); $_POST['txtqueCode']=$queCode; }
			$i=1; $option="txtoptCode".$i;
			while (isset($_POST[$option])) {
				$optCode='NULL';
				$optNumber=$i;
				$optDetail=mysql_real_escape_string($_POST['txtDetail'.$i]);
				$optImage=getFilename('txtImage'.$i);
				$optImageW=ZtoN($_POST['txtWidth'.$i]);
				$optImageH=ZtoN($_POST['txtHeight'.$i]);
				if (isset($_POST['txtCorrect']) && $_POST['txtCorrect'] == $i) {$optCorrect=1;} else { $optCorrect=0;};
				$optActive=$_POST['txtActive'.$i];;
				$qry = " Insert into cOptions (optCode, queCode, optNumber, optDetail, optImage, optImageW, optImageH, optCorrect, optActive)";
				$qry.= " VALUES (".$optCode.",".$queCode.",".$optNumber.",".EtoN($optDetail).",".EtoN($optImage).",".$optImageW.",".$optImageH.",".$optCorrect.",".$optActive.")";
				$result = mysql_query($qry); if (!($result)) {errHandler($qry); return;} else { $optCode=mysql_insert_id(); }
				if ($optImage !== "") {						// Manage Option Image
					$dstDir = "images/G".$graCode."/C".$chpCode."/Q".$qizCode;
					manImage($optCode, 'optCode', 'optImage', 'cOptions', $dstDir, 'txtImage'.$i);
				}
				$i++;$option="txtoptCode".$i;
			} 
		break;
		case 3:
			$queAnswer='NULL';
			$queShowInline=$_POST['txtShowInline'];
			$qry = " Insert into cQuestions (queCode, qizCode, typCode, queQuestion, queShowInline, queAnswer, queWeight, queActive)";
			$qry.= " VALUES (".$queCode.",".$qizCode.",".$typCode.",'".$queQuestion."',".$queShowInline.",'".$queAnswer."',".$queWeight.",".$queActive.")";
			$result = mysql_query($qry); if (!($result)) {errHandler($qry); return;} else { $queCode=mysql_insert_id(); $_POST['txtqueCode']=$queCode; }
			$i=1; $option="txtoptCode".$i;
			while (isset($_POST[$option])) {
				$optCode='NULL';
				$optNumber=$i;
				$optDetail=mysql_real_escape_string($_POST['txtDetail'.$i]);
				$optImage=getFilename('txtImage'.$i);
				$optImageW=ZtoN($_POST['txtWidth'.$i]);
				$optImageH=ZtoN($_POST['txtHeight'.$i]);
				if (isset($_POST['txtCorrect'.$i]) && $_POST['txtCorrect'.$i] == $i) {$optCorrect=1;} else { $optCorrect=0;};
				$optActive=$_POST['txtActive'.$i];
				$qry = " Insert into cOptions (optCode, queCode, optNumber, optDetail, optImage, optImageW, optImageH, optCorrect, optActive)";
				$qry.= " VALUES (".$optCode.",".$queCode.",".$optNumber.",".EtoN($optDetail).",".EtoN($optImage).",".$optImageW.",".$optImageH.",".$optCorrect.",".$optActive.")";
				$result = mysql_query($qry); if (!($result)) {errHandler($qry); return;} else { $optCode=mysql_insert_id();}
				if ($optImage !== "") {						// Manage Option Image
					$dstDir = "images/G".$graCode."/C".$chpCode."/Q".$qizCode;
					manImage($optCode, 'optCode', 'optImage', 'cOptions', $dstDir, 'txtImage'.$i);
				}
				$i++;$option="txtoptCode".$i;
			} 
		break;
		case 4:
			$queAnswer='NULL';
			$queShowInline=$_POST['txtShowInline'];
			$qry = " Insert into cQuestions (queCode, qizCode, typCode, queQuestion, queShowInline, queAnswer, queWeight, queActive)";
			$qry.= " VALUES (".$queCode.",".$qizCode.",".$typCode.",'".$queQuestion."',".$queShowInline.",'".$queAnswer."',".$queWeight.",".$queActive.")";
			$result = mysql_query($qry); if (!($result)) {errHandler($qry); return;} else { $queCode=mysql_insert_id(); $_POST['txtqueCode']=$queCode; }
			$i=1; $option="txtoptCode".$i;
			while (isset($_POST[$option])) {
				$optCode='NULL';
				$optNumber=$_POST['txtOrder'.$i];
				$optDetail=mysql_real_escape_string($_POST['txtDetail'.$i]);
				$optImage=getFilename('txtImage'.$i);
				$optImageW=ZtoN($_POST['txtWidth'.$i]);
				$optImageH=ZtoN($_POST['txtHeight'.$i]);
				$optCorrect='NULL';
				$optActive=$_POST['txtActive'.$i];
				$qry = " Insert into cOptions (optCode, queCode, optNumber, optDetail, optImage, optImageW, optImageH, optCorrect, optActive)";
				$qry.= " VALUES (".$optCode.",".$queCode.",".$optNumber.",".EtoN($optDetail).",".EtoN($optImage).",".$optImageW.",".$optImageH.",".$optCorrect.",".$optActive.")";
				$result = mysql_query($qry); if (!($result)) {errHandler($qry); return;} else { $optCode=mysql_insert_id(); }
				if ($optImage !== "") {						// Manage Option Image
					$dstDir = "images/G".$graCode."/C".$chpCode."/Q".$qizCode;
					manImage($optCode, 'optCode', 'optImage', 'cOptions', $dstDir, 'txtImage'.$i);
				}
				$i++;$option="txtoptCode".$i;
			} 		
		break;
		case 5;
			$queAnswer='NULL';
			$queShowInline=0;
			$qry = " Insert into cQuestions (queCode, qizCode, typCode, queQuestion, queShowInline, queAnswer, queWeight, queActive)";
			$qry.= " VALUES (".$queCode.",".$qizCode.",".$typCode.",'".$queQuestion."',".$queShowInline.",'".$queAnswer."',".$queWeight.",".$queActive.")";
			$result = mysql_query($qry); if (!($result)) {errHandler($qry); return;} else { $queCode=mysql_insert_id(); $_POST['txtqueCode']=$queCode; }
			// Manage Heading
			if (isset($_POST['txtOptionTitle']) && isset($_POST['txtMatchTitle']) ) {
				if (!$_POST['txtOptionTitle'] == '' && !$_POST['txtMatchTitle'] == '') {
					$optCode='NULL'; $optNumber=0; $optActive=1; 
					$optDetail=mysql_real_escape_string($_POST['txtOptionTitle']); $optImage=''; $optImageW='NULL'; $optImageH='NULL'; $optCorrect='NULL'; $optActive=1;
					$qry = " Insert into cOptions (optCode, queCode, optNumber, optDetail, optImage, optImageW, optImageH, optCorrect, optActive)";
					$qry.= " VALUES (".$optCode.",".$queCode.",".$optNumber.",".EtoN($optDetail).",".EtoN($optImage).",".$optImageW.",".$optImageH.",".$optCorrect.",".$optActive.")";				
					$result = mysql_query($qry); if (!($result)) {errHandler($qry); return;} else { $optCode=mysql_insert_id(); }
					//
					$matCode='NULL';
					$matDetail=mysql_real_escape_string($_POST['txtMatchTitle']); $matImage=''; $matImageW='NULL'; $matImageH='NULL';
					$qry = " Insert into cMatch (matCode, optCode, matDetail, matImage, matImageW, matImageH)";
					$qry.= " VALUES (".$matCode.",".$optCode.",".EtoN($matDetail).",".EtoN($matImage).",".$matImageW.",".$matImageH.")";
					$result = mysql_query($qry); if (!($result)) {errHandler($qry); return;} else { $matCode=mysql_insert_id(); }
				}
			}
			$i=1; $option="txtoptCode".$i;
			while (isset($_POST[$option])) {
				$optCode='NULL';
				$optNumber=$i;
				$optDetail=mysql_real_escape_string($_POST['txtDetail'.$i]);
				$optImage=getFilename('txtImage'.$i);
				$optImageW=ZtoN($_POST['txtWidth'.$i]);
				$optImageH=ZtoN($_POST['txtHeight'.$i]);
				$optCorrect='NULL';
				$optActive=$_POST['txtActive'.$i];
				// Match Vars
				$matCode='NULL';
				$matDetail=mysql_real_escape_string($_POST['txtDetailm'.$i]);
				$matImage=getFilename('txtImagem'.$i);
				$matImageW=ZtoN($_POST['txtWidthm'.$i]);
				$matImageH=ZtoN($_POST['txtHeightm'.$i]);
								
				$qry = " Insert into cOptions (optCode, queCode, optNumber, optDetail, optImage, optImageW, optImageH, optCorrect, optActive)";
				$qry.= " VALUES (".$optCode.",".$queCode.",".$optNumber.",".EtoN($optDetail).",".EtoN($optImage).",".$optImageW.",".$optImageH.",".$optCorrect.",".$optActive.")";
				$result = mysql_query($qry); if (!($result)) {errHandler($qry); return;} else { $optCode=mysql_insert_id(); }
				
				$qry = " Insert into cMatch (matCode, optCode, matDetail, matImage, matImageW, matImageH)";
				$qry.= " VALUES (".$matCode.",".$optCode.",".EtoN($matDetail).",".EtoN($matImage).",".$matImageW.",".$matImageH.")";
				$result = mysql_query($qry); if (!($result)) {errHandler($qry); return;} else { $matCode=mysql_insert_id(); }				
				
				if ($optImage !== "") {						// Manage Option Image
					$dstDir = "images/G".$graCode."/C".$chpCode."/Q".$qizCode;
					manImage($optCode, 'optCode', 'optImage', 'cOptions', $dstDir, 'txtImage'.$i);
				}
				if ($matImage !== "") {						// Manage Match Image
					$dstDir = "images/G".$graCode."/C".$chpCode."/Q".$qizCode;
					manImage($matCode, 'matCode', 'matImage', 'cMatch', $dstDir, 'txtImagem'.$i);
				}
				$i++;$option="txtoptCode".$i;	
			}	
		break;
		default:
			showError('Invalid Question Type for save, exited without save.');
		}
	}
}
function manUpdate() {
	if (isset($_SESSION['actionsave']) && $_SESSION['actionsave']=="Y") {
		$graCode=$_POST['gradeselectH'];
		$chpCode=$_POST['chapterselectH'];
		$qizCode=$_POST['quizselectH'];
		$queCode=$_POST['txtqueCode'];
		$typCode=$_POST['typeselectH'];
		$queWeight=$_POST['txtWeight'];
		$queActive=$_POST['txtActive'];
		$queQuestion=mysql_real_escape_string($_POST['txtQuestion']);
		switch ($typCode) {
		case 1:
			$queAnswer=mysql_real_escape_string($_POST['txtAnswer']);
			$qry = " Update cQuestions set";
			$qry.= " queWeight=" . $queWeight . ",";
			$qry.= " queQuestion='" . $queQuestion . "',";
			$qry.= " queAnswer='". $queAnswer . "',";
			$qry.= " queActive=" . $queActive;
			$qry.= " Where queCode=" . $queCode;  	
			$result = mysql_query($qry); if (!($result)) {errHandler($qry); return;} 
		break;
		case 2: 
			$queShowInline=$_POST['txtShowInline'];
			$qry = " Update cQuestions set";
			$qry.= " queWeight=" . $queWeight . ",";
			$qry.= " queQuestion='" . $queQuestion . "',";
			$qry.= " queShowInline=". $queShowInline . ",";
			$qry.= " queActive=" . $queActive;
			$qry.= " Where queCode=" . $queCode;  	
			$result = mysql_query($qry); if (!($result)) {errHandler($qry); return;} 
			$i=1; $option="txtoptCode".$i;
			while (isset($_POST[$option])) {
				$optCode=''; $optNumber=$i; $optDetail=mysql_real_escape_string($_POST['txtDetail'.$i]);
				$optImageW=ZtoN($_POST['txtWidth'.$i]); $optImageH=ZtoN($_POST['txtHeight'.$i]);
				$optImage=getFilename('txtImage'.$i);
				if (isset($_POST['txtCorrect']) && $_POST['txtCorrect'] == $i) {$optCorrect=1;} else { $optCorrect=0;};
				if ($_POST['txtoptCode'.$i] !== "") {		//Update old Option 
					$optCode=$_POST['txtoptCode'.$i];
					$qry = " Update cOptions set";
					$qry.= " optNumber=" . $optNumber . ",";
					$qry.= " optDetail=" . EtoN($optDetail) . ",";										
					if ($optImage !== "")  { $qry.= " optImage=" . EtoN($optImage) . ","; }
					$qry.= " optImageW=" . $optImageW . ",";
					$qry.= " optImageH=" . $optImageH . ",";
					$qry.= " optCorrect=" . $optCorrect . ",";
					$qry.= " optActive=" . $_POST['txtActive'.$i];
					$qry.= " Where optCode=" . $optCode;  	
					$result = mysql_query($qry); if (!($result)) {errHandler($qry); return;}
				} else {									//Add new option
					$optActive=$_POST['txtActive'.$i];
					$qry = " Insert into cOptions (optCode, queCode, optNumber, optDetail, optImage, optImageW, optImageH, optCorrect, optActive) VALUES";
					$qry.= " (".EtoN($optCode).",".$queCode.",".$optNumber.",".EtoN($optDetail).",".EtoN($optImage).",".$optImageW.",".$optImageH.",".$optCorrect.",".$optActive.")";
					$result = mysql_query($qry); if (!($result)) {errHandler($qry); return;} else { $optCode=mysql_insert_id(); }
				}
				if ($optImage !== "") {
					$dstDir = "images/G".$graCode."/C".$chpCode."/Q".$qizCode;
					manImage($optCode, 'optCode', 'optImage', 'cOptions', $dstDir, 'txtImage'.$i);
				}
				$i++;$option="txtoptCode".$i;
			} 		
			if ($_POST['txtoptCodeDel'] !== "") {
				$qry = "Delete from cOptions where optCode in (" . $_POST['txtoptCodeDel'] . ")";
				$result = mysql_query($qry); if (!($result)) {errHandler($qry); return;}
				$dstDir = "images/G".$graCode."/C".$chpCode."/Q".$qizCode;
				delImage($_POST['txtoptCodeDel'], 'optCode', $dstDir);
			}
		break;
		case 3:
			$queShowInline=$_POST['txtShowInline'];
			$qry = " Update cQuestions set";
			$qry.= " queWeight=" . $queWeight . ",";
			$qry.= " queQuestion='" . $queQuestion . "',";
			$qry.= " queShowInline=". $queShowInline . ",";
			$qry.= " queActive=" . $queActive;
			$qry.= " Where queCode=" . $queCode;  	
			$result = mysql_query($qry); if (!($result)) {errHandler($qry); return;} 
			$i=1; $option="txtoptCode".$i;
			while (isset($_POST[$option])) {
				$optCode=''; $optNumber=$i; $optDetail=mysql_real_escape_string($_POST['txtDetail'.$i]);
				$optImageW=ZtoN($_POST['txtWidth'.$i]); $optImageH=ZtoN($_POST['txtHeight'.$i]);
				$optImage=getFilename('txtImage'.$i);
				if (isset($_POST['txtCorrect'.$i]) && $_POST['txtCorrect'.$i] == $i) {$optCorrect=1;} else { $optCorrect=0;};
				if ($_POST['txtoptCode'.$i] !== "") {		//Update old Option 
					$optCode=$_POST['txtoptCode'.$i];
					$qry = " Update cOptions set";
					$qry.= " optNumber=" . $optNumber . ",";
					$qry.= " optDetail=" . EtoN($optDetail) . ",";										
					if ($optImage !== "")  { $qry.= " optImage=" . EtoN($optImage) . ","; }
					$qry.= " optImageW=" . $optImageW . ",";
					$qry.= " optImageH=" . $optImageH . ",";
					$qry.= " optCorrect=" . $optCorrect . ",";
					$qry.= " optActive=" . $_POST['txtActive'.$i];
					$qry.= " Where optCode=" . $optCode;  	
					$result = mysql_query($qry); if (!($result)) {errHandler($qry); return;}
				} else {									//Add new option
					$optActive=$_POST['txtActive'.$i];
					$qry = " Insert into cOptions (optCode, queCode, optNumber, optDetail, optImage, optImageW, optImageH, optCorrect, optActive) VALUES";
					$qry.= " (".EtoN($optCode).",".$queCode.",".$optNumber.",".EtoN($optDetail).",".EtoN($optImage).",".$optImageW.",".$optImageH.",".$optCorrect.",".$optActive.")";
					$result = mysql_query($qry); if (!($result)) {errHandler($qry); return;} else { $optCode=mysql_insert_id(); }
				}
				if ($optImage !== "") {
					$dstDir = "images/G".$graCode."/C".$chpCode."/Q".$qizCode;
					manImage($optCode, 'optCode', 'optImage', 'cOptions', $dstDir, 'txtImage'.$i);
				}
				$i++;$option="txtoptCode".$i;
			} 		
			if ($_POST['txtoptCodeDel'] !== "") {
				$qry = "Delete from cOptions where optCode in (" . $_POST['txtoptCodeDel'] . ")";
				$result = mysql_query($qry); if (!($result)) {errHandler($qry); return;}
				$dstDir = "images/G".$graCode."/C".$chpCode."/Q".$qizCode;
				delImage($_POST['txtoptCodeDel'], 'optCode', $dstDir);
			}		
		break;
		case 4:
			$queShowInline=$_POST['txtShowInline'];
			$qry = " Update cQuestions set";
			$qry.= " queWeight=" . $queWeight . ",";
			$qry.= " queQuestion='" . $queQuestion . "',";
			$qry.= " queShowInline=". $queShowInline . ",";
			$qry.= " queActive=" . $queActive;
			$qry.= " Where queCode=" . $queCode;  	
			$result = mysql_query($qry); if (!($result)) {errHandler($qry); return;} 
			$i=1; $option="txtoptCode".$i;
			while (isset($_POST[$option])) {
				$optCode=''; $optNumber=$i; $optDetail=mysql_real_escape_string($_POST['txtDetail'.$i]);
				$optImageW=ZtoN($_POST['txtWidth'.$i]); $optImageH=ZtoN($_POST['txtHeight'.$i]);
				$optImage=getFilename('txtImage'.$i);
				$optNumber=$_POST['txtOrder'.$i];
				$optCorrect='NULL';
				if ($_POST['txtoptCode'.$i] !== "") {		//Update old Option 
					$optCode=$_POST['txtoptCode'.$i];
					$qry = " Update cOptions set";
					$qry.= " optNumber=" . $optNumber . ",";
					$qry.= " optDetail=" . EtoN($optDetail) . ",";										
					if ($optImage !== "")  { $qry.= " optImage=" . EtoN($optImage) . ","; }
					$qry.= " optImageW=" . $optImageW . ",";
					$qry.= " optImageH=" . $optImageH . ",";
					$qry.= " optCorrect=" . $optCorrect . ",";
					$qry.= " optActive=" . $_POST['txtActive'.$i];
					$qry.= " Where optCode=" . $optCode;  	
					$result = mysql_query($qry); if (!($result)) {errHandler($qry); return;}	
				} else {									//Add new option
					$optActive=$_POST['txtActive'.$i];
					$qry = " Insert into cOptions (optCode, queCode, optNumber, optDetail, optImage, optImageW, optImageH, optCorrect, optActive) VALUES";
					$qry.= " (".EtoN($optCode).",".$queCode.",".$optNumber.",".EtoN($optDetail).",".EtoN($optImage).",".$optImageW.",".$optImageH.",".$optCorrect.",".$optActive.")";
					$result = mysql_query($qry); if (!($result)) {errHandler($qry); return;} else { $optCode=mysql_insert_id(); }
				}
				if ($optImage !== "") {
					$dstDir = "images/G".$graCode."/C".$chpCode."/Q".$qizCode;
					manImage($optCode, 'optCode', 'optImage', 'cOptions', $dstDir, 'txtImage'.$i);
				}
				$i++;$option="txtoptCode".$i;
			} 		
			if ($_POST['txtoptCodeDel'] !== "") {
				$qry = "Delete from cOptions where optCode in (" . $_POST['txtoptCodeDel'] . ")";
				$result = mysql_query($qry); if (!($result)) {errHandler($qry); return;}
				$dstDir = "images/G".$graCode."/C".$chpCode."/Q".$qizCode;
				delImage($_POST['txtoptCodeDel'], 'optCode', $dstDir);
			}		
		break;
		case 5:
			$queShowInline=0;
			$qry = " Update cQuestions set";
			$qry.= " queWeight=" . $queWeight . ",";
			$qry.= " queQuestion='" . $queQuestion . "',";
			$qry.= " queShowInline=". $queShowInline . ",";
			$qry.= " queActive=" . $queActive;
			$qry.= " Where queCode=" . $queCode;  	
			$result = mysql_query($qry); if (!($result)) {errHandler($qry); return;} 
			$i=1; $option="txtoptCode".$i;
			// Manage Title
			$optCode='NULL'; $optNumber=0; $optImage='NULL'; $optImageW='NULL'; $optImageH='NULL'; $optCorrect='NULL'; $optActive=1;
			$optDetail=mysql_real_escape_string($_POST['txtOptionTitle']);
			$matCode='NULL'; $matImage='NULL'; $matImageW='NULL'; $matImageH='NULL'; 
			$matDetail=mysql_real_escape_string($_POST['txtMatchTitle']);
			if ($_POST['txtOptionTitleCode'] == "" && $_POST['txtOptionTitle'] !== "")	{					// insert new title	
				$qry = " Insert into cOptions (optCode, queCode, optNumber, optDetail, optImage, optImageW, optImageH, optCorrect, optActive) VALUES";
				$qry.= " (".$optCode.",".$queCode.",".$optNumber.",".EtoN($optDetail).",".$optImage.",".$optImageW.",".$optImageH.",".$optCorrect.",".$optActive.")";
				$result = mysql_query($qry); if (!($result)) {errHandler($qry); return;} else { $optCode=mysql_insert_id(); }
				$qry = " Insert into cMatch (matCode, optCode, matDetail, matImage, matImageW, matImageH) VALUES";
				$qry.= " (".$matCode.",".$optCode.",".EtoN($matDetail).",".$matImage.",".$matImageW.",".$matImageH.")";
				$result = mysql_query($qry); if (!($result)) {errHandler($qry); return;}
			}elseif ($_POST['txtOptionTitleCode'] !== "" && $_POST['txtOptionTitle'] !== "") {				// Update title
				$optCode=$_POST['txtOptionTitleCode'];
				$qry = " Update cOptions set optDetail=".EtoN($optDetail)." Where optCode=" . $optCode;  	
				$result = mysql_query($qry); if (!($result)) {errHandler($qry); return;}
				$matCode=$_POST['txtMatchTitleCode'];
				$qry = " Update cMatch set matDetail=".EtoN($matDetail)." Where matCode=" . $matCode;  	
				$result = mysql_query($qry); if (!($result)) {errHandler($qry); return;}				
			}elseif ($_POST['txtOptionTitleCode'] !== "" && $_POST['txtOptionTitle'] == "" && $_POST['txtMatchTitle'] == "" ) {		//Del Title
				$optCode=$_POST['txtOptionTitleCode'];
				$qry = " Delete from cOptions where optCode = ".$optCode;
				$result = mysql_query($qry); if (!($result)) {errHandler($qry); return;}
				$matCode=$_POST['txtMatchTitleCode'];
				$qry = " Delete from cMatch where matCode = ".$matCode;
				$result = mysql_query($qry); if (!($result)) {errHandler($qry); return;}	
			}
			while (isset($_POST[$option])) {
				$optCode=''; $optNumber=$i; $optDetail=mysql_real_escape_string($_POST['txtDetail'.$i]);
				$optImageW=ZtoN($_POST['txtWidth'.$i]); $optImageH=ZtoN($_POST['txtHeight'.$i]);
				$optImage=getFilename('txtImage'.$i);
				$optCorrect='NULL';
				$matCode=''; $matDetail=mysql_real_escape_string($_POST['txtDetailm'.$i]);
				$matImageW=ZtoN($_POST['txtWidthm'.$i]); $matImageH=ZtoN($_POST['txtHeightm'.$i]);
				$matImage=getFilename('txtImagem'.$i);
				$matNumber=$i;
				$matCorrect='NULL'; 				
				if ($_POST['txtoptCode'.$i] !== "") {		//Update old Option 
					$optCode=$_POST['txtoptCode'.$i];
					$qry = " Update cOptions set";
					$qry.= " optNumber=" . $optNumber . ",";
					$qry.= " optDetail=" . EtoN($optDetail) . ",";										
					if ($optImage !== "")  { $qry.= " optImage=" . EtoN($optImage) . ","; }
					$qry.= " optImageW=" . $optImageW . ",";
					$qry.= " optImageH=" . $optImageH . ",";
					$qry.= " optCorrect=" . $optCorrect . ",";
					$qry.= " optActive=" . $_POST['txtActive'.$i];
					$qry.= " Where optCode=" . $optCode;  	
					$result = mysql_query($qry); if (!($result)) {errHandler($qry); return;}
					$matCode=$_POST['txtmatCode'.$i];		//Update old Match
					$qry = " Update cMatch set";
					$qry.= " matDetail=" . EtoN($matDetail) . ",";										
					if ($matImage !== "")  { $qry.= " matImage=" . EtoN($matImage) . ","; }
					$qry.= " matImageW=" . $matImageW . ",";
					$qry.= " matImageH=" . $matImageH;
					$qry.= " Where matCode=" . $matCode;  	
					$result = mysql_query($qry); if (!($result)) {errHandler($qry); return;}
				} else {									//Add new option  &&& Match
					$optActive=$_POST['txtActive'.$i];
					$qry = " Insert into cOptions (optCode, queCode, optNumber, optDetail, optImage, optImageW, optImageH, optCorrect, optActive) VALUES";
					$qry.= " (".EtoN($optCode).",".$queCode.",".$optNumber.",".EtoN($optDetail).",".EtoN($optImage).",".$optImageW.",".$optImageH.",".$optCorrect.",".$optActive.")";
					$result = mysql_query($qry); if (!($result)) {errHandler($qry); return;} else { $optCode=mysql_insert_id(); }
					$qry = " Insert into cMatch (matCode, optCode, matDetail, matImage, matImageW, matImageH) VALUES";
					$qry.= " (".EtoN($matCode).",".$optCode.",".EtoN($matDetail).",".EtoN($matImage).",".$matImageW.",".$matImageH.")";
					$result = mysql_query($qry); if (!($result)) {errHandler($qry); return;} else { $matCode=mysql_insert_id(); }					
				}
				if ($optImage !== "") {						// Manage Option Image
					$dstDir = "images/G".$graCode."/C".$chpCode."/Q".$qizCode;
					manImage($optCode, 'optCode', 'optImage', 'cOptions', $dstDir, 'txtImage'.$i);
				}
				if ($matImage !== "") {						// Manage Match Image
					$dstDir = "images/G".$graCode."/C".$chpCode."/Q".$qizCode;
					manImage($matCode, 'matCode', 'matImage', 'cMatch', $dstDir, 'txtImagem'.$i);
				}
				$i++;$option="txtoptCode".$i;
			} 		
			if ($_POST['txtoptCodeDel'] !== "") {			// Delete Option Image
				$qry = "Delete from cOptions where optCode in (" . $_POST['txtoptCodeDel'] . ")";
				$result = mysql_query($qry); if (!($result)) {errHandler($qry); return;}
				$dstDir = "images/G".$graCode."/C".$chpCode."/Q".$qizCode;
				delImage($_POST['txtoptCodeDel'], 'optCode', $dstDir);
			}
			if ($_POST['txtmatCodeDel'] !== "") {			// Delete Match Image
				$qry = "Delete from cMatch where matCode in (" . $_POST['txtmatCodeDel'] . ")";
				$result = mysql_query($qry); if (!($result)) {errHandler($qry); return;}
				$dstDir = "images/G".$graCode."/C".$chpCode."/Q".$qizCode;
				delImage($_POST['txtmatCodeDel'], 'matCode', $dstDir);
			}					
		break;
		default:
			showError('Invalid Question Type for update');
		}
	}
}
function delImage($dstCodes, $dstCodeF, $dstDir) {
	$Array=explode(",", $dstCodes);
	foreach($Array as $dstCode) {
		$dstFilename=$dstCodeF . $dstCode;
		$dstFilenameFQ_Del = $dstDir . "/" . $dstFilename . "_*";
		foreach (glob($dstFilenameFQ_Del) as $filetobedeleted){unlink($filetobedeleted);}
		//showMessage($dstFilenameFQ_Del);
	}
}

function manDel() {		#Manage single delete
	foreach ($_POST['rowcode'] as $value) { $delCode = $value; }
	$txtoptCodeDel=""; $txtmatCodeDel="";
	$qry = " Select a.queCode, a.optCode, b.matCode";
	$qry.= " From cOptions a LEFT OUTER JOIN cMatch b ON a.optCode = b.optCode";
	$qry.= " Where a.queCode=" . $delCode;
	$result = mysql_query($qry); if (!($result)) {errHandler($qry); return;}
	while($row = mysql_fetch_assoc($result)) {
		$txtoptCodeDel.= $row['optCode'] . ',';
		if ($row['matCode'] > 0) { $txtmatCodeDel.= $row['matCode'] . ','; }
	}
	$txtoptCodeDel=rtrim($txtoptCodeDel, ',');
	$txtmatCodeDel=rtrim($txtmatCodeDel, ',');
	$qry = "Delete from cQuestions where queCode = " . $delCode;
	$result = mysql_query($qry); if (!($result)) {errHandler($qry); return;}
	
	$dstDir = "images/G".$_POST['gradeselect']."/C".$_POST['chapterselect']."/Q".$_POST['quizselect'];
	delImage($txtoptCodeDel, 'optCode', $dstDir);
	if ($txtmatCodeDel !== "") { delImage($txtmatCodeDel, 'matCode', $dstDir); }
//	showMessage('Dir:'.$dstDir.'  Options:'.$txtoptCodeDel . '  Matches:' . $txtmatCodeDel);
//	showMessage($qry);
}
function manDelete() {	#Manage multiple deletes
	$delCodes="";
	foreach ($_POST['rowcode'] as $value) { if ($delCodes == "" ) { $delCodes = $value; }else{ $delCodes.= ", " . $value;}	}
	$txtoptCodeDel=""; $txtmatCodeDel="";
	$qry = " Select a.queCode, a.optCode, b.matCode";
	$qry.= " From cOptions a LEFT OUTER JOIN cMatch b ON a.optCode = b.optCode";
	$qry.= " Where a.queCode in (" . $delCodes . ")";
	$result = mysql_query($qry); if (!($result)) {errHandler($qry); return;}
	while($row = mysql_fetch_assoc($result)) {
		$txtoptCodeDel.= $row['optCode'] . ',';
		if ($row['matCode'] > 0) { $txtmatCodeDel.= $row['matCode'] . ','; }
	}
	$txtoptCodeDel=rtrim($txtoptCodeDel, ',');
	$txtmatCodeDel=rtrim($txtmatCodeDel, ',');
	$qry = "Delete from cQuestions where queCode in (" . $delCodes . ")";
	$result = mysql_query($qry); if (!($result)) {errHandler($qry); return;}
	$dstDir = "images/G".$_POST['gradeselect']."/C".$_POST['chapterselect']."/Q".$_POST['quizselect'];
	delImage($txtoptCodeDel, 'optCode', $dstDir);
	if ($txtmatCodeDel !== "") { delImage($txtmatCodeDel, 'matCode', $dstDir); }
//	showMessage('Dir:'.$dstDir.'  Options:'.$txtoptCodeDel . '  Matches:' . $txtmatCodeDel);
//	showMessage($qry);
}

function webQTypeSelect($typeselect) {  //>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>
	$elementselect=$typeselect; $elementfound=false; $elementone=0; $scontrol="";
	if ($typeselect > 0) { $scontrol=" disabled='disabled' "; }
	$qry = " SELECT typCode, typTitle";
	$qry.= " FROM cTypes";
	$qry.= " Order by typCode"; 
	$result = mysql_query($qry); if (!($result)) {errHandler($qry); return;}
	echo '<label for="typeselect" style="width:120px">Question-Type:</label>';
	echo '<select name="typeselect" id="typeselect" style="width:200px; margin-right:115px;" tabindex="4" title="Select Question Type" onchange="setQuestionType(this.form);" ' .
		  $scontrol . '>';
	echo '<option value="" selected disabled>Select Question type</option>';
	while($row = mysql_fetch_assoc($result)) {
		if ($elementone == 0) { $elementone = $row['typCode']; }
		if ($elementselect == $row['typCode']) { $elementfound = true; } 
		echo '<option value="'.$row['typCode'].'" '.manSelect($row['typCode'],$elementselect). ' >'.$row['typCode'].'. '.$row['typTitle'].'</option>';
	}
	echo '</select> <br>';
	if (! $elementfound) { $elementselect = $elementone; }
	return $elementselect;
}

function getQOption2to4($editCode, $txttypeCode) {
	echo '<div id="divQuestionLeft" class="divQuestionLeft">'; ///////////////////////////////////////////////////////////////////>>>>>>
	$qry = " Select optCode, optNumber, optDetail, optImage, optImageW, optImageH, optCorrect, optActive from cOptions where queCode = " . $editCode;
	$qry.= " order by optNumber";
	$result = mysql_query($qry); if (!($result)) {errHandler($qry); return;}
	//$numrows = mysql_num_rows($result);
	//if ($numrows > 0){
	//$haveTitle=false; 
	while($row = mysql_fetch_assoc($result)) {
		$imageDisp='style="margin:5px 30px;"'; $imageSize="";
		if (! $row['optImage'] == "") {
			if ($row['optImageW'] > 0 && $row['optImageH'] > 0) { $imageSize = 'background-size: '.$row['optImageW'].'px '.$row['optImageH'].'px;'; }
			$imageDisp = ' style="margin:5px 30px; background:url('. $row['optImage'] .' ) bottom right no-repeat; '.$imageSize.'" ';			
		}
		if ($txttypeCode==2) { $txtnameext=""; } else { $txtnameext=$row['optNumber']; } 
		echo '<div class="divOptionOldLong" id="divoption'.$row['optNumber'].'" ' . $imageDisp . ' >';
		echo '<input name="txtoptCode'.$row['optNumber'].'" id="txtoptCode'.$row['optNumber'].'" type="hidden" value="'.$row['optCode'].'">';
		showtxtDetail('Option', $row['optNumber'], $row['optDetail']);
		showImageFile($row['optNumber'], $row['optImage']);
		showImageDim($row['optNumber'], $row['optImageW'], $row['optImageH'] );
		//////---->
		if ($txttypeCode==2) {
			echo '<div style="display:table-row"><label for="txtCorrect" style="width:80px">Correct:</label>';
				echo '<input name="txtCorrect'.$txtnameext.'" id="txtCorrect'.$row['optNumber'].
				 '" type="radio" accesskey="1" tabindex="6" class="quizradio" '.manRadio($row['optCorrect'],1). ' value="'. 
					 $row['optNumber'].'"></div>';
		}elseif ($txttypeCode==3) {
			echo '<div style="display:table-row"><label for="txtCorrect" style="width:80px">Correct:</label>';
				echo '<input name="txtCorrect'.$txtnameext.'" id="txtCorrect'.$txtnameext.
				 '" type="checkbox" accesskey="1" tabindex="6" class="quizcheckbox" '.manRadio($row['optCorrect'],1). ' value="'. 
					 $row['optNumber'].'"></div>';		
		}elseif ($txttypeCode==4) {
			echo '<div style="display:table-row"><label for="txtOrder" style="width:80px">Order No:</label>';
				echo '<input name="txtOrder'.$row['optNumber'].'" id="txtOrder'.$row['optNumber'].
				 '" type="text" accesskey="1" tabindex="7" style="width:25px" maxlength="2" value="'. 
					 $row['optNumber'].'"></div>';
		}				 
		//////
		showSelectActive($row['optNumber'], $row['optActive'] );
		echo '</div>';  // End of divOption+No					
	}			
	echo '</div>';  // End of divQuestionLeft
}

function getQOption5($editCode) {
	echo '<div id="divQuestionLeft" class="divQuestionLeft">'; ///////////////////////////////////////////////////////////////////>>>>>>
	$qry = " Select optCode, optNumber, optDetail, optImage, optImageW, optImageH, optActive from cOptions where queCode = " . $editCode;
	$qry.= " order by optNumber";
	$result = mysql_query($qry); if (!($result)) {errHandler($qry); return;}
	//$numrows = mysql_num_rows($result);
	//if ($numrows > 0){
	$haveTitle=false; 
	while($row = mysql_fetch_assoc($result)) {
		if (! $haveTitle){
			$txtCodeTitle=""; $txtDetailTitle="";
			if ($row['optNumber'] == 0) { $txtCodeTitle=$row['optCode']; $txtDetailTitle=$row['optDetail']; }
			echo '<div id="divoptionTitle" class="divOptionTL">';
				echo '<input name="txtOptionTitleCode" id="txtOptionTitleCode" type="hidden" value="'.$txtCodeTitle.'">';
				echo '<label for="txtOptionTitle" style="width:80px">Title(L):</label>';
				echo '<input name="txtOptionTitle" id="txtOptionTitle" type="text" accesskey="1" tabindex="4" style="width:445px" maxlength="50" value="' . $txtDetailTitle.'">';
			echo '</div>';
			$haveTitle=true; if ($row['optNumber'] == 0) { continue; }
		}
		$imageDisp=""; $imageSize="";
		if (! $row['optImage'] == "") {
			if ($row['optImageW'] > 0 && $row['optImageH'] > 0) { $imageSize = 'background-size: '.$row['optImageW'].'px '.$row['optImageH'].'px;'; }
			$imageDisp = ' style="background:url('. $row['optImage'] .' ) bottom right no-repeat; '.$imageSize.'" ';
		}
		echo '<div class="divOptionOld" id="divoption'.$row['optNumber'].'" ' . $imageDisp . '>';
		echo '<input name="txtoptCode'.$row['optNumber'].'" id="txtoptCode'.$row['optNumber'].'" type="hidden" value="'.$row['optCode'].'">';
		showtxtDetail('Option', $row['optNumber'], $row['optDetail']);
		showImageFile($row['optNumber'], $row['optImage']);
		showImageDim($row['optNumber'], $row['optImageW'], $row['optImageH'] );
		showSelectActive($row['optNumber'], $row['optActive'] );
		echo '</div>';  // End of divOption+No					
	}			
	echo '</div>';  // End of divQuestionLeft
}
function getQMatch5($editCode) {
	echo '<div id="divQuestionRight" class="divQuestionRight">';
	$qry = " Select a.optCode, a.optNumber,";
	$qry.= " b.matCode, b.matDetail, b.matImage, b.matImageW, b.matImageH";
	$qry.= " from cOptions a, cMatch b"; 
	$qry.= " where a.optCode = b.optCode"; 
	$qry.= " And a.queCode = ". $editCode;
	$qry.= " order by optNumber";
	$result = mysql_query($qry); if (!($result)) {errHandler($qry); return;}
	//$numrows = mysql_num_rows($result);
	//if ($numrows > 0){
	$haveTitle=false;
	while($row = mysql_fetch_assoc($result)) {
		if (! $haveTitle){
			$txtCodeTitle=""; $txtDetailTitle="";
			if ($row['optNumber'] == 0) { $txtCodeTitle=$row['matCode']; $txtDetailTitle=$row['matDetail']; }
			echo '<div id="divmatchTitle" class="divOptionTR">';
				echo '<input name="txtMatchTitleCode" id="txtMatchTitleCode" type="hidden" value="'.$txtCodeTitle.'">';
				echo '<label for="txtMatchTitle" style="width:80px">Title(R):</label>';
				echo '<input name="txtMatchTitle" id="txtMatchTitle" type="text" accesskey="1" tabindex="4" style="width:445px" maxlength="50" value="'. $txtDetailTitle.'">';
			echo '</div>';
			$haveTitle=true; if ($row['optNumber'] == 0) { continue; }
		}
		$imageDisp=""; $imageSize="";
		if (! $row['matImage'] == "") {
			if ($row['matImageW'] > 0 && $row['matImageH'] > 0) { $imageSize = 'background-size: '.$row['matImageW'].'px '.$row['matImageH'].'px;'; }
			$imageDisp = ' style="background:url('. $row['matImage'] .' ) bottom right no-repeat; '.$imageSize.'" ';
		}
		echo '<div class="divMatchOld" id="divmatch'.$row['optNumber'].'" ' . $imageDisp . '>';
		echo '<input name="txtmatCode'.$row['optNumber'].'" id="txtmatCode'.$row['optNumber'].'" type="hidden" value="'.$row['matCode'].'">';
		showtxtDetail('Match', $row['optNumber'], $row['matDetail']);
		showImageFile('m'.$row['optNumber'], $row['matImage']);
		showImageDim('m'.$row['optNumber'], $row['matImageW'], $row['matImageH']);
		echo '<div style="display:table-row"><br>';
		echo '</div>';	
		//////
		echo '</div>';  // End of divMatch+No			 		
	}			
	//**********************************************************************************************************************************************************8
	echo '</div>'; // End of divQuestionRight
}
function showOptionBtn() {
    echo '<div id="divOptButton" class="divOptButton">'; 
	echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;" .
		 "<button type='button' alt='Add'    class='btnnav' style='width:30px; height:27px; font-size: 16px; font-weight:bold;' onclick='addOption();'>+</button> " .
		 "<button type='button' alt='Remove' class='btnnav' style='width:30px; height:27px; font-size: 16px; font-weight:bold;' onclick='remOption();'>-</button>&nbsp;&nbsp;&nbsp;&nbsp;".
		 "<button type='button' alt='Remove' class='btnnav' style='width:30px; height:27px; font-size: 16px;' onclick='eImageW();'>W</button> ".
		 "<button type='button' alt='Remove' class='btnnav' style='width:30px; height:27px; font-size: 16px;' onclick='eImageH();'>H</button> ";
	echo "</div>";
}
function showtxtDetail($Type, $seqNumber, $val) {
	if ($Type=="Match") { $seqNo='m'.$seqNumber; } else { $seqNo=$seqNumber; }
	echo '<div style="display:table-row"><label for="txtDetail" style="width:80px">'.$Type.'-' . $seqNumber . ':</label>';
	echo '<textarea name="txtDetail'.$seqNo.'" id="txtDetail'.$seqNo.
		 '" accesskey="1" tabindex="5" style="width:445px;height:55px;resize:none;opacity:0.8;" maxlength="200">'.$val.'</textarea></div>';
}
function showImageFile($seqNumber, $val) {
	echo '<div style="display:table-row"><label for="txtImage" style="width:80px">Image:</label>';
		echo '<input name="txtImage'.$seqNumber.'" id="txtImage'.$seqNumber.
		 '" type="file" accesskey="1" tabindex="6" style="width:185px" maxlength="50" accept="image/*" onchange="setDivImage(event, this)" value="'. 
			 $val.'"></div>';
}
function showImageDim($seqNumber, $imgWidth, $imgHeight ) {
	echo '<div style="display:table-col;"><label for="txtWidth" style="width:80px">Size(w:h):</label>';
		echo '<input name="txtWidth'.$seqNumber.'" id="txtWidth'.$seqNumber.
		 '" type="number" accesskey="1" tabindex="7" style="width:40px" min="25" max="999" value="'. 
			 $imgWidth.'" onchange="setImageSize(this)">';
		echo ':';
		echo '<input name="txtHeight'.$seqNumber.'" id="txtHeight'.$seqNumber.
		 '" type="number" accesskey="1" tabindex="7" style="width:40px" min="25" max="999" value="'. 
			 $imgHeight.'" onchange="setImageSize(this)"></div>';		
}
function showSelectActive($seqNumber, $val) {
	echo '<div style="display:table-row"><label for="txtActive" style="width:80px">Active:</label>';
	echo '<select name="txtActive'.$seqNumber.'" id="txtActive'.$seqNumber.'" style="width:60px" tabindex="8" title="Select Action">';
		echo '<option value="1" '.manSelect($val,1). ' >Yes</option>';
		echo '<option value="0" '.manSelect($val,0). ' >No</option>';
	echo '</select></div>';	
}
?>
