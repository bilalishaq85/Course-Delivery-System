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
webMenuTab();
if      (isset($_POST['btnadd']))    { manAddEdit('add');      if( strtolower($gVar['EM_ForceShowList']) == "y") { manList(false); }
}elseif (isset($_POST['btnedit']))   { manAddEdit('edit');     if( strtolower($gVar['EM_ForceShowList']) == "y") { manList(false); }
}elseif (isset($_POST['btninsert'])) { manInsert();   			manList(true);
}elseif (isset($_POST['btnupdate'])) { manUpdate();   			manList(true);
}elseif (isset($_POST['btndel']))    { manDel();	  			manList(true);		#Manage single   row  delete
}elseif (isset($_POST['btndelete'])) { manDelete();   			manList(true);		#Manage multiple rows delete
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
	$qry = " SELECT manCode, manOrder, manTitle, manLink, manArgu, manAccess, manActive, manDetail";
	$qry.= " FROM sMainMenu";
	$qry.= " Order by manOrder"; 
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
		if (isset($_GET['menuselect'])) { $menuselect=$_GET['menuselect']; } else { $menuselect=0;}
		if (isset($_POST['rowcode'])) { foreach ($_POST['rowcode'] as $value) { $menuselect = $value; }}
		if (isset($_POST['txt0'])) { $menuselect=$_POST['txt0']; }
		while($row = mysql_fetch_assoc($result)) {
			//if ( $menuselect == 0 ) { $menuselect=$row['manCode'];}
			echo '<tr>';
				echo '<td class="colSL">' . '<input name="rowcode[]" type="checkbox" class="checkboxsmall" id="rowcode' . $i . '" value="' . $row['manCode'] . '"' . $btnControl .' '.manCheck($row['manCode'],$menuselect).' '.
					'onclick="chkCurrent(event, this)"' . '>'; 
				echo '<td class="colAC">';
					echo '<button type="submit" method="post" title="Edit Row"  class="btnnav" name="btnedit"' . $btnControl . 
					'onclick="checkAll(false); document.getElementById(\'rowcode'.$i.'\').checked=true;">E</button>';
					'>E</button>';
					echo '&nbsp';
					echo '<button type="submit" method="post" title="Delete Row" class="btnnav" name="btndel"' . $btnControl . 
					'onclick="checkAll(false); document.getElementById(\'rowcode'.$i.'\').checked=true; return (confirmDelete(\'btndel\'))">X</button>';
				echo '</td>';
				echo '<td class="colNC">' . $row['manOrder']   . '</td>';
				echo '<td class="colNR">' . $row['manTitle']  . '</td>';
				echo '<td class="colTT" style="max-width:200px;">' . $row['manLink'] . '</td>';
				echo '<td class="colTT" style="max-width:200px;">' . $row['manArgu'] . '</td>';
				echo '<td class="colNC">' . manAccess($row['manAccess']) . '</td>';
				echo '<td class="colNC">' . manActive($row['manActive']) . '</td>';
				echo '<td class="colLT">' . $row['manDetail'] . '</td>';
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
		$qry = "Select manCode, manOrder, manTitle, manLink, manArgu, manAccess, manActive, manDetail from sMainMenu where manCode = " . $editCode;
		$result = mysql_query($qry); if (!($result)) {errHandler($qry); return;}
		$numrows = mysql_num_rows($result);
		if ($numrows > 0){ 
			$row = mysql_fetch_assoc($result);
			$txt0 = $row['manCode'];
			$txt1 = $row['manOrder'];
			$txt2 = $row['manTitle'];
			$txt3 = $row['manLink'];
			$txt4 = $row['manArgu'];
			$txt5 = $row['manActive'];
			$txt6 = $row['manDetail'];
			$txt7 = $row['manAccess'];
		} else {
			echo "<div class='diverrormsg'>" . "Selected row not found, possibly deleted by some other user." . "</div>";
			return;
		}
	} else {
		$txt0=""; $txt1=""; $txt2=""; $txt3=""; $txt4=""; $txt5=""; $txt6=""; $txt7="";
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
			echo '<option value="'.constant('Super').'" '.		manSelect($txt7,constant('Super')).		'>Super</option>';
			echo '<option value="'.constant('Admin').'" '.		manSelect($txt7,constant('Admin')). 	'>Admin</option>';
			echo '<option value="'.constant('Operator').'" '.	manSelect($txt7,constant('Operator')). 	'>Operator</option>';
			echo '<option value="'.constant('Member').'" '.		manSelect($txt7,constant('Member')).	'>Member</option>';
			echo '<option value="'.constant('Student').'" '.	manSelect($txt7,constant('Student')).	'>Student</option>';
			echo '<option value="'.constant('Logged-in').'" '.	manSelect($txt7,constant('Logged-in')).	'>Logged-in</option>';
			echo '<option value="'.constant('Logged-out').'" '.	manSelect($txt7,constant('Logged-out')).'>Logged-out</option>';
			echo '<option value="'.constant('Everyone').'" '.	manSelect($txt7,constant('Everyone')). 	'>Everyone</option>';
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
		$qry = " Insert into sMainMenu (manCode, manOrder, manTitle, manLink, manArgu, manActive, manDetail, manAccess) VALUES (";
		$qry.= " NULL , ";
		$qry.= "'" . $_POST['txt1'] . "',";
		$qry.= "'" . mysql_real_escape_string($_POST['txt2']) . "',";
		$qry.= "'" . mysql_real_escape_string($_POST['txt3']) . "',";
		$qry.= "'" . mysql_real_escape_string($_POST['txt4']) . "',";
		$qry.= "'" . $_POST['txt5'] . "',";
		$qry.= "'" . mysql_real_escape_string($_POST['txt6']) . "',";
		$qry.= "'" . $_POST['txt7'] . "'";
		$qry.= ")";  	
		$result = mysql_query($qry); if (!($result)) {errHandler($qry); return;} else { $_POST['txt0']=mysql_insert_id(); }
	}
}
function manUpdate() {
	if (isset($_SESSION['actionsave']) && $_SESSION['actionsave']=="Y") { 
		$qry = " Update sMainMenu set";
		$qry.= " manOrder='".$_POST['txt1']."',";
		$qry.= " manTitle='".mysql_real_escape_string($_POST['txt2'])."',";
		$qry.= " manLink='".mysql_real_escape_string($_POST['txt3'])."',";
		$qry.= " manArgu='".mysql_real_escape_string($_POST['txt4'])."',";
		$qry.= " manActive='".$_POST['txt5']."',";
		$qry.= " manAccess='".$_POST['txt7']."',";
		$qry.= " manDetail='".mysql_real_escape_string($_POST['txt6'])."'";
		$qry.= " Where manCode=".$_POST['txt0'];  	
		$result = mysql_query($qry); if (!($result)) {errHandler($qry); return;}
	}
}
function manDel() {		#Manage single delete
	foreach ($_POST['rowcode'] as $value) { $delCode = $value; }
	$qry = "Delete from sMainMenu where manCode = " . $delCode;
	$result = mysql_query($qry); if (!($result)) {errHandler($qry); return;}
}
function manDelete() {	#Manage multiple deletes
	$delCodes="";
	foreach ($_POST['rowcode'] as $value) { if ($delCodes == "" ) { $delCodes = $value; }else{ $delCodes.= ", " . $value;}	}
	$qry = "Delete from sMainMenu where manCode in (" . $delCodes . ")";
	$result = mysql_query($qry); if (!($result)) {errHandler($qry); return;}
}
?>