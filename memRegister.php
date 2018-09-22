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
			echo "<div class='adminHeading1'>" . "Member Registration" . "</div>";
			echo "<p>";
			echo "Welcome to ".$gVar['WebPageTitle'] . ", a new learning experience!";
			echo "<p>";
			if (isset($_POST['ResendVemail'])) { 
				sendEmailVerification($_POST['ResendVemail']); 
			}else {
#~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~* 		
echo "Please provide valid email address, a confirmation email will be sent to provided email account upon successful registration. ";
echo "This email will contain link to activate your account.";
echo "<p>";
if     (isset($_POST['btninsert']))  { manInsert(); }
elseif (isset($_POST['btncancel']))  { header('Location: '.$gVar['WebURL']); ob_flush(); exit;} 	// Removed statement: unset($_SESSION['actionsave']);
else   { manAddEdit('add'); }                                  
#~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~* 
			}
		echo '</article>';
	echo '</div>';
	webPageFooter();
}
webHTMLFooter();
ob_flush();
######################
function manAddEdit($option) {
	global $gVar;
	$_SESSION['actionsave']="Y";
	$title=""; $firstname=""; $lastname=""; $password1=""; $email=""; $country="";
	if (isset($_POST['email'])) 
		{ $title=$_POST['title']; $firstname=$_POST['firstname']; $lastname=$_POST['lastname']; $password1=$_POST['password1']; $email=$_POST['email']; $country=$_POST['country'];}
	echo '<div class="divDetail" style="width=400px;">';
	echo '<div class="adminHeading2">Member Particulars' . '</div>';
	echo '<form method="POST" action="memRegister.php" onsubmit="return ValidateForm(this)">';
		
		echo '<label for="title" style="width:100px">Title:</label>';
		echo '<select name="title" id="title" style="width:75px" tabindex="1" title="Select Title">';
			echo '<option value="Mr."  '.manSelect($title,'Mr.').  ' >Mr.</option>';
			echo '<option value="Miss." '.manSelect($title,'Miss.'). ' >Miss.</option>';
			echo '<option value="Mrs." '.manSelect($title,'Mrs.'). ' >Mrs.</option>';
		echo '</select> <br>';
		#
		echo '<label for="firstname" style="width:100px ">First Name:</label>';
		echo '<input name="firstname" id="firstname" type="text" accesskey="1" tabindex="2" style="width:200px" maxlength="50" value="'. $firstname.'" > <br>';
		#
		echo '<label for="lastname" style="width:100px">Last Name:</label>';
		echo '<input name="lastname" id="lastname" type="text" accesskey="1" tabindex="3" style="width:200px" maxlength="50" value="'.$lastname.'" > <br><p>'; 
		#
		echo '<label for="country" style="width:100px">Country:</label>';
		listCountries(4,$country);
		echo ' <br><p>';
		#
		echo '<label for="password1" style="width:100px">Password:</label>';
		echo '<input name="password1" id="password1" type="password" accesskey="1" tabindex="5" style="width:200px" maxlength="30" placeholder="Password"> <br>';
		echo '<label for="password2" style="width:100px">Confirm:</label>';
		echo '<input name="password2" id="password2" type="password" accesskey="1" tabindex="6" style="width:200px" maxlength="30" placeholder="Password"> <br><p>';
		#
		echo '<label for="email" style="width:100px">Email:</label>';
		echo '<input name="email" id="email" type="text" accesskey="1" tabindex="7" style="width:350px" maxlength="100" value="'.$email.'" > <br><p>';
		#
		echo '<label for="captchaimg" style="width:100px">&nbsp;</label>';
		echo '<img id="captchaimg" src="genCaptcha.php" width="200" height="45" style="border: 1px solid #999"; alt="CAPTCHA">'
		?>
		<small><a href="#" class="newuserlink" onclick="document.getElementById('captchaimg').src = 'genCaptcha.php?' + Math.random();
		document.getElementById('captcha').value = ''; return false; ">Regenerate</a></small><br>
		<?php
		#
		echo '<label for="captcha" style="width:100px">Enter Code:</label>';
		echo '<input name="captcha" id="captcha" type="text" accesskey="1" tabindex="8" style="width:200px" maxlength="30" value=""> <br><p>';
		#
		echo '<div class="divactionbtn">';
		echo '<button type="submit" method="post" accesskey="1" title="Register" class="btnupdate" style="height:32px; width:110px;" id="btninsert"   name="btninsert">Register</button>';
		echo '<button type="submit" method="post" accesskey="2" title="Cancel"   class="btncancel" style="height:32px; width:110px;" id="btncancel" name="btncancel">Cancel</button>';
		echo '</div>';
	echo '</form>';
	echo '</div>';
}
function manInsert() {
	global $gVar;
	if (isset($_SESSION['actionsave']) && $_SESSION['actionsave']=="Y") {
		if (isset($_SESSION['captcha']) && $_SESSION['captcha']==$_POST['captcha']) {
			$qry = " Insert into mMembers (memCode, memTitle, memFirstName, memLastName, cntCode, memStudents, memPassword, memEmail, memType, memVerified, memActive) VALUES (";
			$qry.= " NULL , ";
			$qry.= "'" . $_POST['title'] . "',";
			$qry.= "'" . mysql_real_escape_string(trim($_POST['firstname'])) . "',";
			$qry.= "'" . mysql_real_escape_string(trim($_POST['lastname'])) . "',";
			$qry.=$_POST['country'] . ",";
			$qry.=$gVar['MemMaxStudents'] . ",";
			$qry.= "'" . mysql_real_escape_string(trim($_POST['password1'])) . "',";
			$qry.= "'" . mysql_real_escape_string(trim(strtolower($_POST['email']))) . "',";
			$qry.= "40" . ",";
			$qry.= "0"   . ",";
			$qry.= "1";
			$qry.= ")";  	
			$result = mysql_query($qry); 
			if (!($result)) {
				$errorno=mysql_errno();
				if ($errorno == 1062) {
					$email=""; if (isset($_POST['email'])) { $email=$_POST['email']; }
					echo '<form id="resendMail" method="POST" action="memRegister.php">';
					echo '<input name="ResendVemail" id="ResendVemail" type="hidden" value="'.$_POST['email'].'">';
					echo '</form>';
					$verify ='<a href="javascript: document.getElementById(\'resendMail\').submit();" class="newuserlink">Resend Verification Email</a>';
					$recover='<a href="memReset.php" class="newuserlink">Reset Password</a>';
					showError('ERROR: Email ID (' . $_POST['email'] . ') is already registered!');
					$msg ='<b>In order to proceed:</b><br>';
					$msg.='Use different email address and resubmit your registration.<br>';
					$msg.='Lost your verification email? click '.$verify.'<br>';
					$msg.='Forgot your old password? click '.$recover;
					showError($msg);
					manAddEdit('add');
				}else{ errHandler($qry); return; }	
			}else{ unset($_SESSION["captcha"]); unset($_SESSION['actionsave']); sendEmailVerification($_POST['email']); }
		}else{ showError('Code mismatch, please fill required info and try again!'); manAddEdit('add'); }
	}else{ header('Location: '.$gVar['WebURL']); ob_flush(); exit; }
}
?>