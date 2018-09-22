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
webMenuTab(); //>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>
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
	echo '<div class="divselect">'; $menuselect=webMenuSelect(); echo '</div>'; //>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>
	if ($menuselect==0)   { echo "<div class='diverrormsg'>" . "Zero (0) Main-Menu items defined, go back and define Main-Menu first." . "</div>"; return; }
	$qry = " SELECT subCode, manCode, subOrder, subTitle, subLink, subArgu, subActive, subDetail, subAccess";
	$qry.= " FROM sSubMenu";
	$qry.= " Where manCode = " . $menuselect;
	$qry.= " Order by subOrder"; 
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
			echo '<th class="gbltableth">' . 'Title' . '</th>';
			echo '<th class="gbltableth">' . 'Link' . '</th>';
			echo '<th class="gbltableth">' . 'Argurments' . '</th>';
			echo '<th class="gbltableth">' . 'Access-To' . '</th>';
			echo '<th class="gbltableth">' . 'Active' . '</th>';
			echo '<th class="gbltableth">' . 'Description' . '</th>';
		echo '</tr>';
		$i=1;
		if (isset($_GET['submenuselect'])) { $submenuselect=$_GET['submenuselect']; } else { $submenuselect=0;}
		if (isset($_POST['rowcode'])) { foreach ($_POST['rowcode'] as $value) { $submenuselect = $value; }}
		if (isset($_POST['txt0'])) { $submenuselect=$_POST['txt0']; }
		while($row = mysql_fetch_assoc($result)) {
			echo '<tr>';
				echo '<td class="colSL">' . '<input name="rowcode[]" type="checkbox" class="checkboxsmall" id="rowcode' . $i . '" value="' . $row['subCode'] . '"' . $btnControl .' '.manCheck($row['subCode'],$submenuselect).' '.
					'onclick="chkCurrent(event, this)"' . '>';
				echo '<td class="colAC">';
					echo '<button type="submit" method="post" title="Edit Row"  class="btnnav" name="btnedit"' . $btnControl . 
					'onclick="checkAll(false); document.getElementById(\'rowcode'.$i.'\').checked=true;">E</button>';
					'>E</button>';
					echo '&nbsp';
					echo '<button type="submit" method="post" title="Delete Row" class="btnnav" name="btndel"' . $btnControl . 
					'onclick="checkAll(false); document.getElementById(\'rowcode'.$i.'\').checked=true; return (confirmDelete(\'btndel\'))">X</button>';
				echo '</td>';
				echo '<td class="colNC">' . $row['subOrder']   . '</td>';
				echo '<td class="colNR">' . $row['subTitle']  . '</td>';
				echo '<td class="colTT" style="max-width:200px;">' . $row['subLink'] . '</td>';
				echo '<td class="colTT" style="max-width:200px;">' . $row['subArgu'] . '</td>';
				echo '<td class="colNC">' . manAccess($row['subAccess']) . '</td>';
				echo '<td class="colNC">' . manActive($row['subActive']) . '</td>';
				echo '<td class="colLT">' . $row['subDetail'] . '</td>';
			echo '</tr>';
			$i++;
		}
	echo '</table>';
	echo '<script type="text/javascript"> chkOneSelected(); </script>';
	if ($isActionable) { showbtnSelect(); showbtnAddDel(); }
	echo '</form>';
}
function manAddEdit($option) {
	if ($option == 'edit') {
		foreach ($_POST['rowcode'] as $value) { $editCode = $value; }
		$qry = "Select subCode, manCode, subOrder, subTitle, subLink, subArgu, subActive, subDetail, subAccess from sSubMenu where subCode = " . $editCode;
		$result = mysql_query($qry); if (!($result)) {errHandler($qry); return;}
		$numrows = mysql_num_rows($result);
		if ($numrows > 0){ 
			$row = mysql_fetch_assoc($result);
			$txt0 = $row['subCode'];
			$txt1 = $row['subOrder'];
			$txt2 = $row['subTitle'];
			$txt3 = $row['subLink'];
			$txt4 = $row['subArgu'];
			$txt5 = $row['subActive'];
			$txt6 = $row['subDetail'];
			$txt7 = $row['subAccess'];
		} else {
			echo "<div class='diverrormsg'>" . "Selected row not found, possibly deleted by some other user." . "</div>";
			return;
		}
	} else {
		$txt0=""; $txt1=""; $txt2=""; $txt3=""; $txt4=""; $txt5=""; $txt6=""; $txt7="";
	}
	echo '<div class="divDetail">';
	echo '<form action="" method="post" name="detail" onsubmit="return ValidateForm(this)">';
	echo '<input name="menuselect" id="meunselect" type="hidden" value="'. $_POST["menuselect"].'">'; //>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>
	if ($option == 'edit') { 
		echo '<div class="adminHeading2">Edit Row' . '</div>';
		echo '<input name="txt0" id="txt0" type="hidden" value="'.$txt0.'">';
	} else {
		echo '<div class="adminHeading2">Add Row' . '</div>';
	}
//*********Inputs**********
    echo '<label for="txt1" style="width:120px">Order:</label>';
		echo '<input name="txt1" id="txt1" type="text" accesskey="1" tabindex="3" style="width:25px" maxlength="2" value="'.$txt1.'" > ';
    echo '<label for="txt5" style="width:400px">Active:</label>';
		echo '<select name="txt5" id="txt5" style="width:75px" tabindex="4" title="Select Action">';
			echo '<option value="1" '.manSelect($txt5,1). ' >Yes</option>';
			echo '<option value="0" '.manSelect($txt5,0). ' >No</option>';
		echo '</select> <br>';
		
    echo '<label for="txt2" style="width:120px">Title:</label>';
		echo '<input name="txt2" id="txt2" type="text" accesskey="1" tabindex="5" style="width:200px" maxlength="50" value="'.htmlentities($txt2).'" >';
    echo '<label for="txt7" style="width:190px">Access-To:</label>';
		echo '<select name="txt7" id="txt7" style="width:110px" tabindex="6" title="Select Action">';
			echo '<option value="10" '.manSelect($txt7,10). ' >Super</option>';
			echo '<option value="20" '.manSelect($txt7,20). ' >Admin</option>';
			echo '<option value="30" '.manSelect($txt7,30). ' >Operator</option>';
			echo '<option value="40" '.manSelect($txt7,40). ' >Member</option>';
			echo '<option value="50" '.manSelect($txt7,50). ' >Student</option>';
			echo '<option value="60" '.manSelect($txt7,60). ' >Logged-in</option>';
			echo '<option value="70" '.manSelect($txt7,70). ' >Logged-out</option>';
			echo '<option value="80" '.manSelect($txt7,80). ' >Everyone</option>';
		echo '</select> <br>';		
		
    echo '<label for="txt3" style="width:120px">Page-Link:</label>';
		echo '<input name="txt3" id="txt3" type="text" accesskey="1" tabindex="6" style="width:500px" maxlength="500" value="'.htmlentities($txt3).'" > <br>';
    echo '<label for="txt4" style="width:120px">Arguments:</label>';
		echo '<input name="txt4" id="txt4" type="text" accesskey="1" tabindex="7" style="width:500px" maxlength="500" value="'.htmlentities($txt4).'" > <br>';
    echo '<label for="txt6" style="width:120px">Description:</label>';
		echo '<input name="txt6" id="txt6" type="text" accesskey="1" tabindex="8" style="width:500px" maxlength="100" value="'.htmlentities($txt6).'" > ';
//********End Inputs********
	if ($option == 'edit') { showbtnUpdateCancel(); }else{ showbtnInsertCancel(); };
	echo '</form>';
	echo '</div>';
}
function manInsert() {
	if (isset($_SESSION['actionsave']) && $_SESSION['actionsave']=="Y") { 
		$qry = " Insert into sSubMenu (subCode, manCode, subOrder, subTitle, subLink, subArgu, subActive, subDetail, subAccess) VALUES (";
		$qry.= " NULL , ";
		$qry.= $_POST['menuselect'] . ", ";
		$qry.= "'" . $_POST['txt1'] . "',";
		$qry.= "'" . mysql_real_escape_string($_POST['txt2']) . "',";
		$qry.= "'" . mysql_real_escape_string($_POST['txt3']) . "',";
		$qry.= "'" . mysql_real_escape_string($_POST['txt4']) . "',";
		$qry.= "'" . $_POST['txt5'] . "',";
		$qry.= "'" . mysql_real_escape_string($_POST['txt6']) . "',";
		$qry.= "'" . $_POST['txt7'] . "'";
		$qry.= ")";  	
		$result = mysql_query($qry); if (!($result)) {errHandler($qry); return;}
	}
}
function manUpdate() {
	if (isset($_SESSION['actionsave']) && $_SESSION['actionsave']=="Y") { 
		$qry = " Update sSubMenu set";
		$qry.= " subOrder='".$_POST['txt1']."',";
		$qry.= " subTitle='".mysql_real_escape_string($_POST['txt2'])."',";
		$qry.= " subLink='".mysql_real_escape_string($_POST['txt3'])."',";
		$qry.= " subArgu='".mysql_real_escape_string($_POST['txt4'])."',";
		$qry.= " subAccess='".$_POST['txt7']."',";
		$qry.= " subActive='".$_POST['txt5']."',";
		$qry.= " subDetail='".mysql_real_escape_string($_POST['txt6'])."'";
		$qry.= " Where subCode=".$_POST['txt0'];  	
		$result = mysql_query($qry); if (!($result)) {errHandler($qry); return;}
	}
}
function manDel() {		#Manage single delete
	foreach ($_POST['rowcode'] as $value) { $delCode = $value; }
	$qry = "Delete from sSubMenu where subCode = " . $delCode;
	$result = mysql_query($qry); if (!($result)) {errHandler($qry); return;}
}
function manDelete() {	#Manage multiple deletes
	$delCodes="";
	foreach ($_POST['rowcode'] as $value) { if ($delCodes == "" ) { $delCodes = $value; }else{ $delCodes.= ", " . $value;}	}
	$qry = "Delete from sSubMenu where subCode in (" . $delCodes . ")";
	$result = mysql_query($qry); if (!($result)) {errHandler($qry); return;}
}
?>