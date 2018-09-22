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
	echo '<div class="divselect">'; $gradeselect=webGradeSelect(); echo '</div>'; //>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>
	$qry = " SELECT chpCode, chpOrder, chpPrefix, chpTitle, chpActive";
	$qry.= " FROM cChapters";
	$qry.= " Where graCode = " . $gradeselect;
	$qry.= " Order by chpOrder"; 
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
			echo '<th class="gbltableth">' . 'Order' . '</th>';
			echo '<th class="gbltableth">' . 'PreFix' . '</th>';
			echo '<th class="gbltableth">' . 'Title' . '</th>';
			echo '<th class="gbltableth">' . 'Active' . '</th>';
		echo '</tr>';
		$i=1;
		if (isset($_GET['chapterselect'])) { $chapterselect=$_GET['chapterselect']; } else { $chapterselect=0;}
		if (isset($_POST['rowcode'])) { foreach ($_POST['rowcode'] as $value) { $chapterselect = $value; }}
		if (isset($_POST['txt0'])) { $chapterselect=$_POST['txt0']; }
		while($row = mysql_fetch_assoc($result)) {
			echo '<tr>';
				echo '<td class="colSL">' . '<input name="rowcode[]" type="checkbox" class="checkboxsmall" id="rowcode'.$i.'" value="'.$row['chpCode'].'"' . $btnControl.' '.manCheck($row['chpCode'],$chapterselect).' '.
					'onclick="chkCurrent(event, this)"' . '>'; 
				echo '<td class="colAC">';
					echo '<button type="submit" method="post" title="Edit Row"  class="btnnav" name="btnedit"' . $btnControl . ' ' .
					'onclick="checkAll(false); document.getElementById(\'rowcode'.$i.'\').checked=true;">E</button>';
					'>E</button>';
					echo '&nbsp';
					echo '<button type="submit" method="post" title="Delete Row" class="btnnav" name="btndel"' . $btnControl . ' ' .
					'onclick="checkAll(false); document.getElementById(\'rowcode'.$i.'\').checked=true; return (confirmDelete(\'btndel\'))">X</button>';
				echo '</td>';
				echo '<td class="colNC">' . $row['chpOrder']   . '</td>';
				echo '<td class="colNR">' . $row['chpPrefix']  . '</td>';
				echo '<td class="colNR">' . $row['chpTitle'] . '</td>';
				echo '<td class="colNC">' . manActive($row['chpActive']) . '</td>';
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
		$qry = "Select chpCode, chpOrder, chpPrefix, chpTitle, chpMaterial, chpActive from cChapters where chpCode = " . $editCode;
		$result = mysql_query($qry); if (!($result)) {errHandler($qry); return;}
		$numrows = mysql_num_rows($result);
		if ($numrows > 0){ 
			$row = mysql_fetch_assoc($result);
			$txt0 = $row['chpCode'];
			$txt1 = $row['chpOrder'];
			$txt2 = $row['chpPrefix'];
			$txt3 = $row['chpTitle'];
			$txt4 = $row['chpMaterial'];
			$txt5 = $row['chpActive'];
		} else {
			echo "<div class='diverrormsg'>" . "Selected row not found, possibly deleted by some other user." . "</div>";
			return;
		}
	} else {
		$txt0=""; $txt1=""; $txt2=""; $txt3=""; $txt4=""; $txt5="";
	}
	echo '<div class="divDetail">';
	echo '<form action="" method="post" name="detail" onsubmit="return ValidateForm(this)">';
	echo '<input name="gradeselect" id="gradeselect" type="hidden" value="'. $_POST["gradeselect"].'">'; //>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>
	if ($option == 'edit') { 
		echo '<div class="adminHeading2">Edit Row' . '</div>';
		echo '<input name="txt0" id="txt0" type="hidden" value="'.$txt0.'">';
	} else {
		echo '<div class="adminHeading2">Add Row' . '</div>';
	}
	echo '<input name="txtimgs" id="txtimgs" type="hidden" value="'.$gVar['EM_ImageMaxSizeKB'].'">';
	echo '<input name="txtimgt" id="txtimgt" type="hidden" value="'.$gVar['EM_ImageTypes'].'">';
	echo '<input name="txtimgd" id="txtimgd" type="hidden" value="'. 'GR' . $_POST['gradeselect'] .'">';
//*********Inputs**********
    echo '<label for="txt1" style="width:120px">Order:</label>';
		echo '<input name="txt1" id="txt1" type="text" accesskey="1" tabindex="3" style="width:40px" maxlength="3" value="'.$txt1.'" > ';
    echo '<label for="txt5" style="width:400px">Active:</label>';
		echo '<select name="txt5" id="txt5" style="width:60px" tabindex="4" title="Select Action">';
			echo '<option value="1" '.manSelect($txt5,1). ' >Yes</option>';
			echo '<option value="0" '.manSelect($txt5,0). ' >No</option>';
		echo '</select> <br>';
		
    echo '<label for="txt2" style="width:120px">PreFix:</label>';
		echo '<input name="txt2" id="txt2" type="text" accesskey="1" tabindex="5" style="width:100px" maxlength="5" value="'.htmlentities($txt2).'" > <br>';
    echo '<label for="txt3" style="width:120px">Title:</label>';
		echo '<input name="txt3" id="txt3" type="text" accesskey="1" tabindex="6" style="width:1062px" maxlength="100" value="'.htmlentities($txt3).'" ><br> ';
	echo '<label style="width:120px">Material:</label><br>';
		echo '<textarea name="txt4" id="txt4" accesskey="1" tabindex="5" style="resize:vertical;">'.htmlentities($txt4).'</textarea>';
		//echo '<input name="txt4" id="txt4" type="text" accesskey="1" tabindex="7" style="width:350px" maxlength="500" value="'.$txt4.'" > <br>';	
//********End Inputs********
	if ($option == 'edit') { showbtnUpdateCancel(); }else{ showbtnInsertCancel(); };
	echo '</form>';
	echo '</div>';
}
function manInsert() {
	if (isset($_SESSION['actionsave']) && $_SESSION['actionsave']=="Y") { 
		$qry = " Insert into cChapters (chpCode, graCode, chpOrder, chpPrefix, chpTitle, chpMaterial, chpActive) VALUES (";
		$qry.= " NULL , ";
		$qry.= $_POST['gradeselect'] . ", ";
		$qry.= "'" . $_POST['txt1'] . "',";
		$qry.= "'" . mysql_real_escape_string(trim($_POST['txt2'])) . "',";
		$qry.= "'" . mysql_real_escape_string(trim($_POST['txt3'])) . "',";
		$qry.= "'" . mysql_real_escape_string($_POST['txt4']) . "',";
		$qry.= "'" . $_POST['txt5'] . "'";
		$qry.= ")";  	
		$result = mysql_query($qry); if (!($result)) {errHandler($qry); return;} else { $_POST['txt0']=mysql_insert_id(); }
	}
}
function manUpdate() {
	if (isset($_SESSION['actionsave']) && $_SESSION['actionsave']=="Y") { 
		$qry = " Update cChapters set";
		$qry.= " chpOrder='" . $_POST['txt1'] . "',";
		$qry.= " chpPreFix='" . mysql_real_escape_string(trim($_POST['txt2'])) . "',";
		$qry.= " chpTitle='".mysql_real_escape_string(trim($_POST['txt3'])) . "',";
		$qry.= " chpMaterial='".mysql_real_escape_string($_POST['txt4']) . "',";
		$qry.= " chpActive='" . $_POST['txt5'] . "'";
		$qry.= " Where chpCode=" . $_POST['txt0'];  	
		$result = mysql_query($qry); if (!($result)) {errHandler($qry); return;}
	}
}
function manDel() {		#Manage single delete
	foreach ($_POST['rowcode'] as $value) { $delCode = $value; }
	$qry = "Delete from cChapters where chpCode = " . $delCode;
	$result = mysql_query($qry); if (!($result)) {errHandler($qry); return;}
}
function manDelete() {	#Manage multiple deletes
	$delCodes="";
	foreach ($_POST['rowcode'] as $value) { if ($delCodes == "" ) { $delCodes = $value; }else{ $delCodes.= ", " . $value;}	}
	$qry = "Delete from cChapters where chpCode in (" . $delCodes . ")";
	$result = mysql_query($qry); if (!($result)) {errHandler($qry); return;}
}

?>