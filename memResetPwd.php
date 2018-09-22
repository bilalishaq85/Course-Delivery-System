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
if     (isset($_POST['btnreset']))  { updatePwd(); }
elseif (isset($_POST['btncancel'])) { unset($_SESSION['actionsave']); header('Location: '.$gVar['WebURL']); ob_flush(); exit;}
else   {resetPwd($_GET['key']); } 
#~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~* 
		echo '</article>';
	echo '</div>';
	webPageFooter();
}
webHTMLFooter();
ob_flush();
######################
/*
SELECT recCode, memCode, datediff(Now(), recCreated)
FROM mRecover
Where recKey = 'uDKVjom5adRYah7xSlrgpBH7lMeyN6SylhYA5wnBCzr62DY2VqmRnapzZzuCRSqYAMz7fwM0rNn6hQdo'
And   recActive = 1

SELECT recCode, memCode, TimeStampDiff(MINUTE, recCreated, Now())
FROM mRecover
Where recKey = 'uDKVjom5adRYah7xSlrgpBH7lMeyN6SylhYA5wnBCzr62DY2VqmRnapzZzuCRSqYAMz7fwM0rNn6hQdo'
And   recActive = 1
*/
function resetPwd($key) {
	$recover='<a href="memReset.php" class="newuserlink">Reset Password</a>';
	$qry = " SELECT recCode, memCode, TimeStampDiff(HOUR, recCreated, Now()) as recExpiry";
	$qry.= " FROM mRecover";
	$qry.= " Where recKey = '" . $key . "'";
	$qry.= " And   recType = 'RESET'";
	$qry.= " And   recActive = 1";
	$result = mysql_query($qry) or die('Error: ' . mysql_error());
	$numrows = mysql_num_rows($result);
	if (!($result)) { errHandler($qry); header('Location: '.$_SERVER['HTTP_REFERER']); ob_flush(); exit; }
	if ($numrows > 0){
		global $gVar;
		$row = mysql_fetch_assoc($result);
		if ($row['recExpiry'] < $gVar['MemExpireRecKey'] || $gVar['MemExpireRecKey'] == 0) {
			echo '<div class="divDetail" style="width=400px;">';
			echo '<div class="adminHeading2">Enter New Password' . '</div>';
			echo '<form method="POST" action="memResetPwd.php" onsubmit="return ValidateForm(this)">';
				echo '<input name="memcode" id="memcode" type="hidden" value="'.$row['memCode'].'">';
				echo '<label for="password1" style="width:100px">Password:</label>';
				echo '<input name="password1" id="password1" type="password" accesskey="1" tabindex="3" style="width:200px" maxlength="30" placeholder="Password"> <br>';
				echo '<label for="password2" style="width:100px">Confirm:</label>';
				echo '<input name="password2" id="password2" type="password" accesskey="1" tabindex="3" style="width:200px" maxlength="30" placeholder="Password"> <br><p>';
				#
				echo '<div class="divactionbtn">';
		echo '<button type="submit" method="post" accesskey="1" title="Register" class="btnupdate" style="height:32px; width:110px;" id="btnreset"  name="btnreset">Reset</button>';
		echo '<button type="submit" method="post" accesskey="2" title="Cancel"   class="btncancel" style="height:32px; width:110px;" id="btncancel" name="btncancel">Cancel</button>';
				echo '</div>';
			echo '</form>';
			echo '</div>';
		}else{ showError('Your password reset key is expired, click '.$recover.' to request a new key.'); }
		deleteRecKey($row['memCode']); 
	}else{ showError('Your password reset key is expired or invalid, click '.$recover.' to request a new key.'); }
}
function updatePwd(){
	$qry = " Update mMembers set";
	$qry.= " memPassword = '".$_POST['password1']."',";
	$qry.= " memVerified = 1,";
	$qry.= " memFailedLogin = 0";
	$qry.= " Where memCode =" . $_POST['memcode'];
	$result = mysql_query($qry) or die('Error: ' . mysql_error());
	showMessage('<p><b>Password Reset Complete!</b><br><p>You can now login using your new password.');
	delayedRedirect();
}
function deleteRecKey($memCode){
	$qry = " Delete from mRecover";
	$qry.= " Where memCode =" . $memCode;
	$qry.= " And   recType ='RESET'";
	$result = mysql_query($qry) or die('Error: ' . mysql_error());
}
?>