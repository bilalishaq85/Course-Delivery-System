<?php
ob_start(); ini_set('output_buffering','1'); 
$_GET['mancode']=1;
require_once(str_replace('\\', '/', dirname(__FILE__)) . "/includes/browser.php");
require_once(str_replace('\\', '/', dirname(__FILE__)) . "/includes/dbcon.start.php");
require_once(str_replace('\\', '/', dirname(__FILE__)) . "/includes/libMain.php");
global $gVar; $gVar = array(); $gVar = getGlobalVar();
require_once(str_replace('\\', '/', dirname(__FILE__)) . "/includes/managesession.php");
require_once(str_replace('\\', '/', dirname(__FILE__)) . "/includes/libWeb.php");
manQuizVar("CLEAN"); manQuestionVar("CLEAN");
webHTMLHeader();
If (browserCheck()) { 
	webPageHeader();
	webMainMenu();
#~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*
echo '<aside class="top-leftbar"> <article>';
	echo '<h2>' . 'Top sidebar' . '</h2>';
	echo '<p>' . 'This is test' . '</p>';
echo '</article> </aside>';
//echo '<aside class="middle-sidebar"> <article>';
//	echo '<h2>' . 'Middle sidebar' . '</h2>';
//	echo '<p>' . 'This is test' . '</p>';
//echo '</article> </aside>';
//echo '<aside class="bottom-sidebar"> <article>';
//	echo '<h2>' . 'Bottom sidebar' . '</h2>';
//	echo '<p>' . 'This is test' . '</p>';
//echo '</article> </aside>';


echo '<div class="mainContentR"><div class="content">';
	echo '<article class="topcontent">';
		echo '<header> <a class="articleLink" href="#" title="' . 'First post' . '"><span class="articleLinkText">'. 'First post' . '</span></a> </header>';
		echo '<footer> <p class="post-info">' . 'This post is written by unknown WRITER' . '</p> </footer>';
		echo '<content> <p>' . 'Test Article: This is just a TESTING text matter' . '</p> </content>';
	echo '</article>';	
		
	echo '<article class="bottomcontent">';
		echo '<header> <a class="articleLink" href="#" title="' . 'Second post' . '"><span class="articleLinkText">' . 'Second Post' . '</span></a> </header>';
		echo '<footer> <p class="post-info">' . 'This post is written by unknown WRITER' . '</p> </footer>';
		echo '<content> <p>' . 'Test Article: This is just a TESTING text matter' . '</p> </content>';
	echo '</article>';
	
	echo '<article class="bottomcontent">';
		echo '<header> <a class="articleLink" href="#" title="'.  'Third post' . '"><span class="articleLinkText">' . 'Third post' . '</span></a> </header>';
		echo '<footer> <p class="post-info">' . 'This post is written by unknown WRITER' . '</p> </footer>';
		echo '<content> <p>' . 'Test Article: This is just a TESTING text matter' . '</p> </content>';
	echo '</article>';
echo '</div></div>';
	
	




#~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*
webPageFooter();
	} 
webHTMLFooter();
ob_flush();
 ?>