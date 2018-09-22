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
if (isset($_POST['chpcode'])) { dispChapMaterial($_POST['chpcode']); }
elseif (isset($_GET['chpcode'])) { dispChapMaterial($_GET['chpcode']); }
ob_flush();
#
function dispChapMaterial($chpCode) {
	$qry = " SELECT chpCode, graCode, chpOrder, chpPreFix, chpTitle, chpMaterial";
	$qry.= " FROM cChapters";
	$qry.= " Where chpCode = " . $chpCode;
	$qry.= " And   chpActive = True";
	$result = mysql_query($qry) or die('Error: ' . mysql_error());
	$numrows = mysql_num_rows($result);
	if ($numrows > 0){
		$row = mysql_fetch_assoc($result);
		echo "<div class='mceCpHeading' style='text-align:left' >" . "Chapter " . $row['chpOrder'] . " - " . $row['chpTitle'] . "</div><hr>";
		echo '<div class="mceDiv">';
			echo "" . $row['chpMaterial'] . "";
		echo "</div>";
	}else{ showError("Error: Required chapter not found"); }
}
?>