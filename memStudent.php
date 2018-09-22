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
			$heading='Manage Students';
			echo '<article class="topcontentAdminInner">';
			echo '<div class="adminHeading1">'. $heading . '</div>';
			echo 'Maximum number of students allowed are: '.$_SESSION["memStudents"];
#~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*
if      (isset($_POST['btnadd']))    { manAddEdit('add');	if( strtolower($gVar['EM_ForceShowList']) == "y") { manList(false); }
}elseif (isset($_POST['btnedit']))   { manAddEdit('edit');	if( strtolower($gVar['EM_ForceShowList']) == "y") { manList(false); }
}elseif (isset($_POST['btninsert'])) { $result=manInsert();	manList($result);
}elseif (isset($_POST['btnupdate'])) { $result=manUpdate();	manList($result);
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
	$qry = " SELECT stdCode, stdFirstName, stdLastName, stdLogin, stdPassword, stdActive";
	$qry.= " FROM mStudent";
	$qry.= " WHERE memCode=".$_SESSION['memCode'];
	$qry.= " Order by stdFirstName, stdLastName"; 
	//---------------Paging Header--------------------*
	require_once(str_replace('\\', '/', dirname(__FILE__)) . "/includes/paging.header.php");
	//------------------------------------------------*
	$result = mysql_query($qry." LIMIT " . $from . "," . $gVar['MaxTablesRows']);
	//---------------Paging Footer--------------------*
	if ($isActionable) {require_once(str_replace('\\', '/', dirname(__FILE__)) . "/includes/paging.footer.php"); }
	//------------------------------------------------*
	echo '<table class="gbltable" style="width:60%">';
		echo '<tr>';
			echo '<th class="gbltableth">' . '*' . '</th>';
			echo '<th class="gbltableth">' . 'Action' . '</th>';
			echo '<th class="gbltableth">' . 'Name' . '</th>';
			//echo '<th class="gbltableth">' . 'Last Name' . '</th>';
			echo '<th class="gbltableth">' . 'Login' . '</th>';
			echo '<th class="gbltableth">' . 'Password' . '</th>';
		echo '</tr>';
		$i=1;
		while($row = mysql_fetch_assoc($result)) {
			echo '<tr>';
				echo '<td class="colSL">' . '<input name="rowcode[]" type="checkbox" class="checkboxsmall" id="rowcode' . $i . '" value="' . $row['stdCode'] . '"' . $btnControl . '>'; 
				echo '<td class="colAC">';
					echo '<button type="submit" method="post" title="Edit Row"  class="btnnav" name="btnedit"' . $btnControl . 
					'onclick="checkAll(false); document.getElementById(\'rowcode'.$i.'\').checked=true;">E</button>';
					'>E</button>';
					echo '&nbsp';
					echo '<button type="submit" method="post" title="Delete Row" class="btnnav" name="btndel"' . $btnControl . 
					'onclick="checkAll(false); document.getElementById(\'rowcode'.$i.'\').checked=true; return (confirmDelete(\'btndel\'))">X</button>';
				echo '</td>';
				echo '<td class="colNR">' . $row['stdFirstName'] . ' ' . $row['stdLastName'] . '</td>';
				//echo '<td class="colNR">' . $row['stdLastName']  . '</td>';
				echo '<td class="colNR">' . $row['stdLogin']  . '</td>';
				echo '<td class="colLT">' . $row['stdPassword'] . '</td>';
			echo '</tr>';
			$i++;
		}
	echo '</table>';
	if ($isActionable) { showbtnSelect(); showbtnAddDel('60%'); }
	echo '</form>';
}
function manAddEdit($option) {
	$txt0=""; $txt1=""; $txt2=""; $txt3=""; $txt4="";
	if ($option == 'edit') {
		foreach ($_POST['rowcode'] as $value) { $editCode = $value; }
		$qry = "Select stdCode, stdFirstName, stdLastName, stdLogin, stdPassword, stdActive from mStudent where stdCode = " . $editCode;
		$result = mysql_query($qry); if (!($result)) {errHandler($qry); return;}
		$numrows = mysql_num_rows($result);
		if ($numrows > 0){ 
			$row = mysql_fetch_assoc($result);
			$txt0 = $row['stdCode'];
			$txt1 = $row['stdFirstName'];
			$txt2 = $row['stdLastName'];
			$txt3 = $row['stdLogin'];
			$txt4 = $row['stdPassword'];
		} else {
			echo "<div class='diverrormsg'>" . "Selected row not found, possibly deleted by some other user." . "</div>";
			return;
		}
	}
	if ($option == 'edit2') {
		$txt0=$_POST['txt0']; $txt1=$_POST['txt1']; $txt2=$_POST['txt2']; $txt3=$_POST['txt3']; $txt4=$_POST['txt4'];
		$option='edit';
	}
	if ($option == 'add2') {
		$txt0=""; $txt1=$_POST['txt1']; $txt2=$_POST['txt2']; $txt3=$_POST['txt3']; $txt4=$_POST['txt4'];
		$option='add';
	} 
 
	echo '<div class="divDetail">';
	echo '<form action="" method="post" name="detail" onsubmit="return ValidateForm(this)">';
	if ($option == 'edit') { 
		echo '<div class="adminHeading2">Edit Row' . '</div>';
		echo '<input name="txt0" id="txt0" type="hidden" value="'.$txt0.'">';
	} else {
		echo '<div class="adminHeading2">Add Row' . '</div>';
	}
//*********Inputs**********
    echo '<label for="txt1" style="width:130px">First Name:</label>';
	echo '<input name="txt1" id="txt1" type="text" style="width:230px" accesskey="1" tabindex="3" maxlength="30" value="'.$txt1.'" ><br>';
    echo '<label for="txt2" style="width:130px">Last Name:</label>';
	echo '<input name="txt2" id="txt2" type="text" style="width:230px" accesskey="1" tabindex="4" maxlength="30" value="'.$txt2.'" ><br>';
    echo '<label for="txt3" style="width:130px">Login-ID:</label>';
	echo '<input name="txt3" id="txt3" type="text" style="width:175px" accesskey="1" tabindex="5" maxlength="30" value="'.$txt3.'" ><br>';
    echo '<label for="txt4" style="width:130px">Password:</label>';
	echo '<input name="txt4" id="txt4" type="text" style="width:175px" accesskey="1" tabindex="6" maxlength="30" value="'.$txt4.'" ><br>';

//********End Inputs********
	if ($option == 'edit') { showbtnUpdateCancel(); }else{ showbtnInsertCancel(); };
	echo '</form>';
	echo '</div>';
}
function manInsert() {
	if (isset($_SESSION['actionsave']) && $_SESSION['actionsave']=="Y") { 
		$qry = "Select count(1) as stdCount FROM `mStudent` WHERE memCode=" . $_SESSION['memCode'];
		$result = mysql_query($qry) or die('Error: ' . mysql_error());
		$numrows = mysql_num_rows($result);
		if ($numrows > 0){
			$row = mysql_fetch_assoc($result);
			$stdCount=$row['stdCount'];
		}else{ $stdCount=0; }
		if ($stdCount >= $_SESSION['memStudents']) { showError( 'Error: You cannot add more students, maximum number of allowed limit has reached.'); return true; }
		$qry = " Insert into mStudent (stdCode, memCode, stdFirstName, stdLastName, stdLogin, stdPassword)  VALUES (";
		$qry.= "Null,";
		$qry.= $_SESSION['memCode'].",";
		$qry.= "'".mysql_real_escape_string($_POST['txt1'])."',";
		$qry.= "'".mysql_real_escape_string($_POST['txt2'])."',";
		$qry.= "'".mysql_real_escape_string($_POST['txt3'])."',";
		$qry.= "'".mysql_real_escape_string($_POST['txt4'])."')";
		$result = mysql_query($qry);
		if (!($result)) {
			$errorno=mysql_errno();
			if ($errorno == 1062) {
				$msg="Student Login-ID:".$_POST['txt3']." is already in use, please use different ID and try again.";
				showError($msg);
				manAddEdit('add2');
				return false;
			}else{ errHandler($qry); return false; }
		}
	}
	return true;
}
function manUpdate() {
	if (isset($_SESSION['actionsave']) && $_SESSION['actionsave']=="Y") { 
		$qry = " Update mStudent set";
		$qry.= " stdFirstName='".mysql_real_escape_string($_POST['txt1'])."',";
		$qry.= " stdLastName='".mysql_real_escape_string($_POST['txt2'])."',";
		$qry.= " stdLogin='".mysql_real_escape_string($_POST['txt3'])."',";
		$qry.= " stdPassword='".mysql_real_escape_string($_POST['txt4'])."'";
		$qry.= " Where stdCode=".$_POST['txt0'];  	
		$result = mysql_query($qry); 
		if (!($result)) {
			$errorno=mysql_errno();
			if ($errorno == 1062) {
				$msg="Student Login-ID:".$_POST['txt3']." is already in use, please use different ID and try again.";
				showError($msg);
				manAddEdit('edit2');
				return false;
			}else{ errHandler($qry); return false; }
		}
	}
	return true;
}
function manDel() {		#Manage single delete
	foreach ($_POST['rowcode'] as $value) { $delCode = $value; }
	$qry = "Delete from mStudent where stdCode = " . $delCode;
	$result = mysql_query($qry); if (!($result)) {errHandler($qry); return;}
}
function manDelete() {	#Manage multiple deletes
	$delCodes="";
	foreach ($_POST['rowcode'] as $value) { if ($delCodes == "" ) { $delCodes = $value; }else{ $delCodes.= ", " . $value;}	}
	$qry = "Delete from mStudent where stdCode in (" . $delCodes . ")";
	$result = mysql_query($qry); if (!($result)) {errHandler($qry); return;}
}
?>