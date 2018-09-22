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
	$qry = " SELECT gblCode, gblName, gblValue, gblDetail";
	$qry.= " FROM sGlobal";
	$qry.= " Order by gblName"; 
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
			echo '<th class="gbltableth">' . 'Name' . '</th>';
			echo '<th class="gbltableth">' . 'Value' . '</th>';
			echo '<th class="gbltableth">' . 'Description' . '</th>';
		echo '</tr>';
		$i=1;
		if (isset($_GET['gblselect'])) { $gblselect=$_GET['gblselect']; } else { $gblselect=0;}
		if (isset($_POST['rowcode'])) { foreach ($_POST['rowcode'] as $value) { $gblselect = $value; }}
		if (isset($_POST['txt0'])) { $gblselect=$_POST['txt0']; }
		while($row = mysql_fetch_assoc($result)) {
			echo '<tr>';
				echo '<td class="colSL">' . '<input name="rowcode[]" type="checkbox" class="checkboxsmall" id="rowcode' . $i . '" value="' . $row['gblCode'] . '"' . $btnControl  .' '.manCheck($row['gblCode'],$gblselect).' '.
					'onclick="chkCurrent(event, this)"' . '>'; 
				echo '<td class="colAC">';
					echo '<button type="submit" method="post" title="Edit Row"  class="btnnav" name="btnedit"' . $btnControl . 
					'onclick="checkAll(false); document.getElementById(\'rowcode'.$i.'\').checked=true;">E</button>';
					'>E</button>';
					echo '&nbsp';
					echo '<button type="submit" method="post" title="Delete Row" class="btnnav" name="btndel"' . $btnControl . 
					'onclick="checkAll(false); document.getElementById(\'rowcode'.$i.'\').checked=true; return (confirmDelete(\'btndel\'))">X</button>';
				echo '</td>';
				echo '<td class="colNR">' . $row['gblName']   . '</td>';
				echo '<td class="colNR">' . $row['gblValue']  . '</td>';
				echo '<td class="colLT">' . $row['gblDetail'] . '</td>';
			echo '</tr>';
			$i++;
		}
	echo '</table>';
	echo '<script type="text/javascript"> chkOneSelected(); </script>';
	if ($isActionable) { showbtnSelect(); showbtnAddDel(); }
	echo '</form>';
}
function manAddEdit($option) {
	$access="readonly";
	//$access="";
	if ($option == 'edit') {
		foreach ($_POST['rowcode'] as $value) { $editCode = $value; }
		$qry = "Select gblCode, gblName, gblValue, gblDetail from sGlobal where gblCode = " . $editCode;
		$result = mysql_query($qry); if (!($result)) {errHandler($qry); return;}
		$numrows = mysql_num_rows($result);
		if ($numrows > 0){ 
			$row = mysql_fetch_assoc($result);
			$txt0 = $row['gblCode'];
			$txt1 = $row['gblName'];
			$txt2 = $row['gblValue'];
			$txt3 = $row['gblDetail'];
		} else {
			echo "<div class='diverrormsg'>" . "Selected row not found, possibly deleted by some other user." . "</div>";
			return;
		}
	} else {
		$txt0=""; $txt1=""; $txt2=""; $txt3="";
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
    echo '<label for="txt1" style="width:120px">Name:</label>';
	echo '<input name="txt1" id="txt1" type="text" accesskey="1" tabindex="3" maxlength="80" style="width:300px;" value="'.$txt1.'" ' . $access . '><br>';
    echo '<label for="txt2" style="width:120px">Value:</label>';
	echo '<input name="txt2" id="txt2" type="text" accesskey="1" tabindex="4" maxlength="80" style="width:300px;" value="'.$txt2.'" ><br>';
    echo '<label for="txt3" style="width:120px">Description:</label>';
	echo '<textarea name="txt3" id="txt3" accesskey="1" tabindex="5" maxlength="200" style="width:400px;height:70px" ' . $access . '>'.$txt3.'</textarea><br>';
//********End Inputs********
	if ($option == 'edit') { showbtnUpdateCancel(); }else{ showbtnInsertCancel(); };
	echo '</form>';
	echo '</div>';
}
function manInsert() {
	if (isset($_SESSION['actionsave']) && $_SESSION['actionsave']=="Y") { 
		$qry = " Insert into sGlobal (gblCode, gblName, gblValue, gblDetail)";
		$qry.= " VALUES (NULL , '".$_POST['txt1']."', '".$_POST['txt2']."', '".mysql_real_escape_string($_POST['txt3'])."')";  	
		$result = mysql_query($qry); if (!($result)) {errHandler($qry); return;} else { $_POST['txt0']=mysql_insert_id(); }
	}
}
function manUpdate() {
	if (isset($_SESSION['actionsave']) && $_SESSION['actionsave']=="Y") { 
		$qry = " Update sGlobal set";
		$qry.= " gblName='".$_POST['txt1']."',";
		$qry.= " gblValue='".$_POST['txt2']."',";
		$qry.= " gblDetail='".mysql_real_escape_string($_POST['txt3'])."'";
		$qry.= " Where gblCode=".$_POST['txt0'];  	
		$result = mysql_query($qry); if (!($result)) {errHandler($qry); return;}
	}
}
function manDel() {		#Manage single delete
	foreach ($_POST['rowcode'] as $value) { $delCode = $value; }
	$qry = "Delete from sGlobal where gblCode = " . $delCode;
	$result = mysql_query($qry); if (!($result)) {errHandler($qry); return;}
}
function manDelete() {	#Manage multiple deletes
	$delCodes="";
	foreach ($_POST['rowcode'] as $value) { if ($delCodes == "" ) { $delCodes = $value; }else{ $delCodes.= ", " . $value;}	}
	$qry = "Delete from sGlobal where gblCode in (" . $delCodes . ")";
	$result = mysql_query($qry); if (!($result)) {errHandler($qry); return;}
}
?>