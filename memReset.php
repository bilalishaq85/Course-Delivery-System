<?php
ob_start(); ini_set('output_buffering','1'); 
require_once(str_replace('\\', '/', dirname(__FILE__)) . "/includes/browser.php");
require_once(str_replace('\\', '/', dirname(__FILE__)) . "/includes/dbcon.start.php");
require_once(str_replace('\\', '/', dirname(__FILE__)) . "/includes/libMain.php");
global $gVar; $gVar = array(); $gVar = getGlobalVar();
require_once(str_replace('\\', '/', dirname(__FILE__)) . "/includes/managesession.php");
require_once(str_replace('\\', '/', dirname(__FILE__)) . "/includes/libWeb.php");
//######## 10-SuperUser, 20-Admin, 30-Operator, 40-Member, 50-Student, 60-NoLogin ######
$_SESSION["LoginMsg"]="";
webHTMLHeader();
If (browserCheck()) { 
	webPageHeader(false);
	webMainMenu();
######################	
	echo '<div id="divmainContent" class="mainContent">';
		echo '<article class="topcontent100">';
			echo "<div class='adminHeading1'>" . "Reset Password" . "</div>";
			echo "<p>";
			echo "Welcome to ".$gVar['WebPageTitle'] . ", a new learning experience!";
			echo "<p>";
#~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~* 		
if     (isset($_POST['btnreset']))  { manReset(); }
elseif (isset($_POST['btncancel'])) { unset($_SESSION['actionsave']); header('Location: '.$gVar['WebURL']); ob_flush(); exit;}
else   { manInput(); } 

#~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~* 
		echo '</article>';
	echo '</div>';
	webPageFooter();
}
webHTMLFooter();
ob_flush();
######################
function manInput() {
	#$_SESSION['actionsave']="Y";
	$email="";	
	if (isset($_POST['email'])) { $email=$_POST['email'];}
	echo "In order to reset your password, please enter your email account, a link will be emailed to you. Use that link to reset your password.";
	echo '<div class="divDetail" style="width=400px;">';
	echo '<div class="adminHeading2">Member Particulars' . '</div>';
	echo '<form method="POST" action="memReset.php" onsubmit="return ValidateForm(this)">';
		echo '<label for="email" style="width:100px">Email:</label>';
		echo '<input name="email" id="email" type="text" accesskey="1" tabindex="3" style="width:350px" maxlength="100" value="'.$email.'" > <br><p>';
		#
		echo '<label for="captchaimg" style="width:100px">&nbsp;</label>';
		echo '<img id="captchaimg" src="genCaptcha.php" width="200" height="45" style="border: 1px solid #999"; alt="CAPTCHA">'
		?>
		<small><a href="#" class="newuserlink" onclick="document.getElementById('captchaimg').src = 'genCaptcha.php?' + Math.random();
		document.getElementById('captcha').value = ''; return false; ">Regenerate</a></small><br>
		<?php
		#
		echo '<label for="captcha" style="width:100px">Enter Code:</label>';
		echo '<input name="captcha" id="captcha" type="text" accesskey="1" tabindex="3" style="width:200px" maxlength="30" value=""> <br><p>';
		#
		echo '<div class="divactionbtn">';
		echo '<button type="submit" method="post" accesskey="1" title="Register" class="btnupdate" style="height:32px; width:110px;" id="btnreset"  name="btnreset">Reset</button>';
		echo '<button type="submit" method="post" accesskey="2" title="Cancel"   class="btncancel" style="height:32px; width:110px;" id="btncancel" name="btncancel">Cancel</button>';
		echo '</div>';
	echo '</form>';
	echo '</div>';
}
function manReset(){
	if (isset($_SESSION['captcha']) && $_SESSION['captcha']==$_POST['captcha']) {
		if (!sendEmailReset($_POST['email'])) { manInput(); }
	}else{ 
		showError('Code mismatch, please fill required info and try again!'); 
		manInput('add'); 
	}
}
?>