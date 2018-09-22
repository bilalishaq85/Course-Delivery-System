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
If (browserCheck()) { 
	webPageHeader();
	webMainMenu();
	echo '<div id="divmainContent" class="mainContent">';
		echo '<article class="topcontentAdmin">';
			$heading=webAdminMenu();
			echo '<article class="topcontentAdminInner">';
			echo '<div class="adminHeading1">'. $heading . '</div>';
#~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*
webArticleTab(); //>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>
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
	echo '<div class="divselect">'; $catselect=webCategorySelect(); echo '</div>'; //>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>
	if ($catselect==0)   { echo "<div class='diverrormsg'>" . "Zero (0) Atricle-Categories defined, go back and define Categories first." . "</div>"; return; }
	$qry = " SELECT artCode, catCode, artTitle, artAuthor, DATE_FORMAT(artPostDate,'%m/%d/%Y') as artPostDate, DATE_FORMAT(artWrittenDate,'%m/%d/%Y') as artWrittenDate, artActive";
	$qry.= " FROM pArticles";
	$qry.= " Where catCode = " . $catselect;
	$qry.= " Order by artCode"; 
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
			echo '<th class="gbltableth">' . 'Title' . '</th>';
			echo '<th class="gbltableth">' . 'Author' . '</th>';
			echo '<th class="gbltableth">' . 'Date-Posted' . '</th>';
			echo '<th class="gbltableth">' . 'Date-Written' . '</th>';
			echo '<th class="gbltableth">' . 'Active' . '</th>';
		echo '</tr>';
		$i=1;
		if (isset($_GET['artselect'])) { $artselect=$_GET['artselect']; } else { $artselect=0;}
		if (isset($_POST['rowcode'])) { foreach ($_POST['rowcode'] as $value) { $artselect = $value; }}
		if (isset($_POST['txt0'])) { $artselect=$_POST['txt0']; }
		while($row = mysql_fetch_assoc($result)) {
			echo '<tr>';
				echo '<td class="colSL">' . '<input name="rowcode[]" type="checkbox" class="checkboxsmall" id="rowcode' . $i . '" value="' . $row['artCode'] . '"' . $btnControl .' '.manCheck($row['artCode'],$artselect).' '.
					'onclick="chkCurrent(event, this)"' . '>';
				echo '<td class="colAC">';
					echo '<button type="submit" method="post" title="Edit Row"  class="btnnav" name="btnedit"' . $btnControl . 
					'onclick="checkAll(false); document.getElementById(\'rowcode'.$i.'\').checked=true;">E</button>';
					'>E</button>';
					echo '&nbsp';
					echo '<button type="submit" method="post" title="Delete Row" class="btnnav" name="btndel"' . $btnControl . 
					'onclick="checkAll(false); document.getElementById(\'rowcode'.$i.'\').checked=true; return (confirmDelete(\'btndel\'))">X</button>';
				echo '</td>';
				echo '<td class="colTT" style="max-width:662px;">' . $row['artTitle']   . '</td>';
				echo '<td class="colTT" style="max-width:200px;">' . $row['artAuthor']  . '</td>';
				echo '<td class="colNC" style="min-width:90px;">' . $row['artPostDate'] . '</td>';
				echo '<td class="colNC" style="min-width:100px;">' . $row['artWrittenDate'] . '</td>';
				echo '<td class="colNC">' . manActive($row['artActive']) . '</td>';
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
		$qry = "Select artCode, catCode, artAuthor, artPostDate, DATE_FORMAT(artPostDate,'%Y-%m-%d') as artPostDate, DATE_FORMAT(artWrittenDate,'%Y-%m-%d') as artWrittenDate, 
		artTitle, artActive, artArticle from pArticles where artCode = " . $editCode;
		$result = mysql_query($qry); if (!($result)) {errHandler($qry); return;}
		$numrows = mysql_num_rows($result);
		if ($numrows > 0){ 
			$row = mysql_fetch_assoc($result);
			$txt0 = $row['artCode'];
			$txt1 = $row['artAuthor'];
			$txt2 = $row['artPostDate'];
			$txt3 = $row['artWrittenDate']; if ($txt3 == "0000-00-00") {$txt3="";}
			$txt4 = $row['artTitle'];
			$txt5 = $row['artActive'];
			$txt6 = $row['artArticle'];
		} else {
			echo "<div class='diverrormsg'>" . "Selected row not found, possibly deleted by some other user." . "</div>";
			return;
		}
	} else {
		$txt0=""; $txt1=""; $txt2=date('Y-m-d'); $txt3=""; $txt4=""; $txt5=""; $txt6="";
	}
	echo '<div class="divDetail">';
	echo '<form action="" method="post" name="detail" onsubmit="return ValidateForm(this)">';
	echo '<input name="catselect" id="catselect" type="hidden" value="'. $_POST["catselect"].'">'; //>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>
	if ($option == 'edit') { 
		echo '<div class="adminHeading2">Edit Row' . '</div>';
		echo '<input name="txt0" id="txt0" type="hidden" value="'.$txt0.'">';
	} else {
		echo '<div class="adminHeading2">Add Row' . '</div>';
	}
	echo '<input name="txtimgs" id="txtimgs" type="hidden" value="'.$gVar['EM_ImageMaxSizeKB'].'">';
	echo '<input name="txtimgt" id="txtimgt" type="hidden" value="'.$gVar['EM_ImageTypes'].'">';
	echo '<input name="txtimgd" id="txtimgd" type="hidden" value="'. 'AR' .'">';
//*********Inputs**********
    echo '<label for="txt1" style="width:120px">Author:</label>';
		echo '<input name="txt1" id="txt1" type="text" accesskey="1" tabindex="3" style="width:300px" maxlength="100" value="'.$txt1.'" > ';
    echo '<label for="txt5" style="width:400px">Active:</label>';
		echo '<select name="txt5" id="txt5" style="width:75px" tabindex="4" title="Select Action">';
			echo '<option value="1" '.manSelect($txt5,1). '>Yes</option>';
			echo '<option value="0" '.manSelect($txt5,0). '>No</option>';
		echo '</select><br>';
    echo '<label for="txt2" style="width:120px">Date-Posted:</label>';
		echo '<input name="txt2" id="txt2" type="date" accesskey="1" tabindex="5" style="width:140px" maxlength="10" value="'.$txt2.'" readonly >';
    echo '<label for="txt3" style="width:560px">Date-Written:</label>';
		echo '<input name="txt3" id="txt3" type="date" accesskey="1" tabindex="6" style="width:140px" maxlength="10" value="'.$txt3.'" > <br>';
    echo '<label for="txt4" style="width:120px; padding:0 0 margin:0 0; border:0">Title:</label>';
		echo '<input name="txt4" id="txt4" type="text" accesskey="1" tabindex="7" style="width:1055px" maxlength="500" value="'.htmlentities($txt4).'" ><br>';
	echo '<label style="width:120px">Material:</label>&nbsp;<br>';
		echo '<textarea name="txt6" id="txt6" accesskey="1" tabindex="5" style="resize:vertical; width:1062px">'.htmlentities($txt6).'</textarea>';
//********End Inputs********
	if ($option == 'edit') { showbtnUpdateCancel(); }else{ showbtnInsertCancel(); };
	echo '</form>';
	echo '</div>';
}
function manInsert() {
	if (isset($_SESSION['actionsave']) && $_SESSION['actionsave']=="Y") { 
		$qry = " Insert into pArticles (artCode, catCode, artAuthor, artPostDate, artWrittenDate, artTitle, artActive, artArticle) VALUES (";
		$qry.= " NULL , ";
		$qry.= $_POST['catselect'] . ", ";
		$qry.= ETON($_POST['txt1']) . ",";
		$qry.= "'" . $_POST['txt2'] . "',";
		$qry.= "'" . $_POST['txt3'] . "',";
		$qry.= "'" . mysql_real_escape_string($_POST['txt4']) . "',";
		$qry.= "'" . $_POST['txt5'] . "',";
		$qry.= "'" . mysql_real_escape_string($_POST['txt6']) . "'";
		$qry.= ")";  	
		$result = mysql_query($qry); if (!($result)) {errHandler($qry); return;} else { $_POST['txt0']=mysql_insert_id(); }
	}
}
function manUpdate() {
	if (isset($_SESSION['actionsave']) && $_SESSION['actionsave']=="Y") { 
		$qry = " Update pArticles set";
		$qry.= " artAuthor=".ETON($_POST['txt1']).",";
		$qry.= " artPostDate='".$_POST['txt2']."',";
		$qry.= " artWrittenDate='".$_POST['txt3']."',";
		$qry.= " artTitle='".mysql_real_escape_string($_POST['txt4'])."',";
		$qry.= " artActive='".$_POST['txt5']."',";
		$qry.= " artArticle='".mysql_real_escape_string($_POST['txt6'])."'";
		$qry.= " Where artCode=".$_POST['txt0'];  	
		$result = mysql_query($qry); if (!($result)) {errHandler($qry); return;}
	}
}
function manDel() {		#Manage single delete
	foreach ($_POST['rowcode'] as $value) { $delCode = $value; }
	$qry = "Delete from pArticles where artCode = " . $delCode;
	$result = mysql_query($qry); if (!($result)) {errHandler($qry); return;}
}
function manDelete() {	#Manage multiple deletes
	$delCodes="";
	foreach ($_POST['rowcode'] as $value) { if ($delCodes == "" ) { $delCodes = $value; }else{ $delCodes.= ", " . $value;}	}
	$qry = "Delete from pArticles where artCode in (" . $delCodes . ")";
	$result = mysql_query($qry); if (!($result)) {errHandler($qry); return;}
}
?>