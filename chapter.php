<?php
ob_start(); ini_set('output_buffering','1'); 
require_once(str_replace('\\', '/', dirname(__FILE__)) . "/includes/browser.php");
require_once(str_replace('\\', '/', dirname(__FILE__)) . "/includes/dbcon.start.php");
require_once(str_replace('\\', '/', dirname(__FILE__)) . "/includes/libMain.php");
global $gVar; $gVar = array(); $gVar = getGlobalVar();
require_once(str_replace('\\', '/', dirname(__FILE__)) . "/includes/managesession.php");
require_once(str_replace('\\', '/', dirname(__FILE__)) . "/includes/libWeb.php");
//######## 10-SuperUser, 20-Admin, 30-Operator, 40-Member, 50-Student, 60-NoLogin ######
chkPermission();
webHTMLHeader();
If (browserCheck()) { 
	webPageHeader();
	webMainMenu();
	echo '<div id="divmainContent" class="mainContent">';
		echo '<article class="topcontent100" >';
#~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~* 			
if (isset($_GET['gracode']) && isset($_GET['chpcode'])) { dispSideChapLinks($_GET['gracode'], $_GET['chpcode']); }
echo '<div name="divMaterial" id="divMaterial" class="chapMaterial">';
	require_once(str_replace('\\', '/', dirname(__FILE__)) . "/chaptersub.php");
echo '</div>';
#~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~* 
		echo '</article>';
	echo '</div>';
	webPageFooter();
}
webHTMLFooter();
ob_flush();
#
function dispSideChapLinks($graCode, $chpCode) {
	echo '<div name="divChapList" id="divChapList" class="chapList">';
	$qry = " SELECT graTitle, graDetail";
	$qry.= " FROM cGrades";
	$qry.= " Where graCode = " . $graCode;
	$result = mysql_query($qry) or die('Error: ' . mysql_error());
	$numrows = mysql_num_rows($result);
	if ($numrows > 0){
		$row = mysql_fetch_assoc($result);
		echo '<div class="adminHeading1" style="text-align:center">'.$row['graDetail'].'&nbsp;</div>';
	}
	echo '<div class="adminHeading2">Chapters</div>';
	$qry = " SELECT chpCode, chpOrder, chpPreFix, chpTitle";
	$qry.= " FROM cChapters";
	$qry.= " Where graCode = " . $graCode;
	$qry.= " And   chpActive = True";
	$qry.= " Order by chpOrder";
	$result = mysql_query($qry) or die('Error: ' . mysql_error());
	$numrows = mysql_num_rows($result);
	if ($numrows > 0){
		while($row = mysql_fetch_assoc($result)) {
			//echo "<p><a href='chapter.php?chpcode=" . $row['chpCode'] . "'>" . $row['chpOrder']. ". " . $row['chpTitle'] . "</a></p>";
			echo "<p><a href='chaptersub.php?chpcode=" . $row['chpCode'] . "' class='aqizlink' >" . "<span class='aqizbold'>" . $row['chpOrder']. ". </span><span class='aqizuline'>" . $row['chpTitle']. "</span></a></p>";
		}
		echo "<p>&nbsp;</p>";
	}else{
		showError("Error: No chapter found");	
	}
	echo '</div>';
}
?>
