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
			echo "<div class='adminHeading1'>" . "Member Email Address Verification" . "</div>";
			echo "<p>";
			echo "Welcome to ".$gVar['WebPageTitle'] . ", a new learning experience!";
			echo "<p>";
#~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~* 		
if     (isset($_GET['key'])) { verifyEmail($_GET['key']); }
elseif (isset($_POST['btnresend'])) { if (!sendEmailVerification($_POST['email'])) { manInput(); }; }
else   { manInput(); }
#~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~* 
		echo '</article>';
	echo '</div>';
	webPageFooter();
}
webHTMLFooter();
ob_flush();
######################
function verifyEmail($key) {
	$recover='<a href="memVerify.php" class="newuserlink">Resend Verification Email</a>';
	$qry = " SELECT recCode, memCode, TimeStampDiff(HOUR, recCreated, Now()) as recExpiry";
	$qry.= " FROM mRecover";
	$qry.= " Where recKey = '" . $key . "'";
	$qry.= " And   recType = 'ACTIV'";
	$qry.= " And   recActive = 1";
	$result = mysql_query($qry) or die('Error: ' . mysql_error());
	$numrows = mysql_num_rows($result);
	if (!($result)) { errHandler($qry); header('Location: '.$_SERVER['HTTP_REFERER']); ob_flush(); exit; }
	if ($numrows > 0){
		global $gVar;
		$row = mysql_fetch_assoc($result);
		if ($row['recExpiry'] < $gVar['MemExpireRecKey'] || $gVar['MemExpireRecKey'] == 0) {
			$qry = " Update mMembers set";
			$qry.= " memVerified = 1";
			$qry.= " Where memCode =" . $row['memCode'];
			$result = mysql_query($qry) or die('Error: ' . mysql_error());
			showMessage('<p><b>Congratulations!</b><br><p>Your email address verification is complete. You can now login using your user-name (email) and password.');
			delayedRedirect();
		}else{ showError('Your account activation key is expired, click '.$recover.' to request a new key.'); }
		deleteRecKey($row['memCode']); 
	}else{ showError('Your account activation key is expired or invalid, click '.$recover.' to request a new key.'); }
}
function deleteRecKey($memCode){
	$qry = " Delete from mRecover";
	$qry.= " Where memCode =" . $memCode;
	$qry.= " And   recType ='ACTIV'";
	$result = mysql_query($qry) or die('Error: ' . mysql_error());
}
function manInput() {
	$email="";	
	if (isset($_POST['email'])) { $email=$_POST['email'];}
	echo "<p> In order to send you a new email address verification key, please fill in below form.";
	echo '<div class="divDetail" style="width=400px;">';
	echo '<div class="adminHeading2">Member Particulars' . '</div>';
	echo '<form method="POST" action="memVerify.php" onsubmit="return ValidateForm(this)">';
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
		echo '<button type="submit" method="post" accesskey="1" title="Resend" class="btnupdate" style="height:32px; width:110px;" id="btnresend"  name="btnresend">Resend</button>';
		echo '<button type="submit" method="post" accesskey="2" title="Cancel" class="btncancel" style="height:32px; width:110px;" id="btncancel" name="btncancel">Cancel</button>';
		echo '</div>';
	echo '</form>';
	echo '</div>';
}
?>