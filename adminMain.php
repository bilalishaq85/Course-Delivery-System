<?php
ob_start(); ini_set('output_buffering','1'); 
require_once(str_replace('\\', '/', dirname(__FILE__)) . "/includes/browser.php");
require_once(str_replace('\\', '/', dirname(__FILE__)) . "/includes/dbcon.start.php");
require_once(str_replace('\\', '/', dirname(__FILE__)) . "/includes/libMain.php");
global $gVar; $gVar = array(); $gVar = getGlobalVar();
require_once(str_replace('\\', '/', dirname(__FILE__)) . "/includes/managesession.php");
require_once(str_replace('\\', '/', dirname(__FILE__)) . "/includes/libWeb.php");
require_once(str_replace('\\', '/', dirname(__FILE__)) . "/includes/libAdmin.php");
//######## 10-SuperUser, 20-Admin, 30-Operator, 40-Member, 50-Student, 60-NoLogin ######
chkPermission();
manQuizVar("CLEAN"); manQuestionVar("CLEAN");
webHTMLHeader('admin');
If (browserCheck()) { 
	webPageHeader();
	webMainMenu();
	echo '<div id="divmainContent" class="mainContent">';
		echo '<article class="topcontentAdmin">';
			webAdminMenu();
			echo '<article class="topcontentAdminInner">';
#~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*
	echo '<p>' . '<div class="gradeHeading">Admin Panel for "'  . $gVar['WebPageTitle'] . '"</div>' . '</p>';
	echo '<p>' . 'Consider following before making any changes:-' . '</p>';
	echo '<p>';
		echo '1. Select your options from menu above in order to manage entire site contents.' . '<br>' ;
		echo '2. Changes made to any of above options, once saved, are irreversible.' . '<br>' ;
		echo '3. Save changes before navigating to any other page, otherwise your changes will be lost.' . '<br>';
#~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*
			echo '</article>';
		echo '</article>';
	echo '</div>';
	webPageFooter();
}
webHTMLFooter();
ob_flush();
?>