<?php
ob_start(); ini_set('output_buffering','1'); 
require_once(str_replace('\\', '/', dirname(__FILE__)) . "/includes/browser.php");
require_once(str_replace('\\', '/', dirname(__FILE__)) . "/includes/dbcon.start.php");
require_once(str_replace('\\', '/', dirname(__FILE__)) . "/includes/libMain.php");
global $gVar; $gVar = array(); $gVar = getGlobalVar();
require_once(str_replace('\\', '/', dirname(__FILE__)) . "/includes/managesession.php");
require_once(str_replace('\\', '/', dirname(__FILE__)) . "/includes/libWeb.php");
//chkPermission();
webHTMLHeader();
If (browserCheck()) { 
	webPageHeader();
	webMainMenu();
	echo '<div id="divmainContent" class="mainContent">';
		echo '<article class="topcontent100">'; 
#~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~* 			
//dispLeftMenu();     // Commented for demo
echo '<div name="divMaterial" id="divMaterial" class="chapMaterial" style="border-left:none; padding-left:0px;">';
dispPage(5);
//dispPage(2);
echo '</div>';
#~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~* 
		echo '</article>'; 
	echo '</div>';
	webPageFooter();
}
webHTMLFooter();
ob_flush();
function dispLeftMenu() {
	echo '<div name="divLeftMenu" id="divLeftMenu" class="leftMenu">';
		echo "Left Menu";
	
	echo '</div>';
}

function dispPage($pagCode) {
	$qry = " SELECT pagMaterial";
	$qry.= " FROM sPages";
	$qry.= " Where pagCode = " . $pagCode;
	$qry.= " And   pagActive = True";
	$result = mysql_query($qry) or die('Error: ' . mysql_error());
	$numrows = mysql_num_rows($result);
	if ($numrows > 0){
		$row = mysql_fetch_assoc($result);
		echo '<div class="mceDiv">' . $row['pagMaterial'] . "</div>";
	}else{ showError("Error: Page not found."); }
}



?>