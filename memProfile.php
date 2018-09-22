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
			echo "<div class='adminHeading1'>" . "Member's Profile" . "</div>";
			echo "<p>";
			echo "Welcome to ".$gVar['WebPageTitle'] . ", a new learning experience!";
			echo "<p>";
			if (isset($_POST['ResendVemail'])) { 
				sendEmailVerification($_POST['ResendVemail']); 
			}else {
#~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~* 		
//echo "Incase of email address change, you will need to re-activate your account. A confirmation email will be sent to youprovided email account upon successful registration. ";
//echo "This email will contain link to activate your account.";
echo "<p>";
if     (isset($_POST['btnupdate']))  { manUpdate(); }
elseif (isset($_POST['btncancel']))  { unset($_SESSION['actionsave']); header('Location: '.$gVar['WebURL']); ob_flush(); exit;}
else   { manAddEdit('edit'); }                                  
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
	$qry = "SELECT memTitle, memFirstName, memLastName, cntCode, memEmail, memPassword from mMembers where memCode = " . $_SESSION["memCode"];
	$result = mysql_query($qry); if (!($result)) {errHandler($qry); return;}
	$numrows = mysql_num_rows($result);
	if ($numrows > 0){ 
		$row = mysql_fetch_assoc($result);
		$title = $row['memTitle'];
		$firstname = $row['memFirstName'];
		$lastname = $row['memLastName'];
		$country = $row['cntCode'];
		$email = $row['memEmail'];
		$password1 = $row['memPassword'];
		$password2 = $row['memPassword'];
	} else {
		header('Location: '.$gVar['WebURL']); ob_flush(); exit;
		return;
	}
	
	if (isset($_POST['email'])) 
		{ $title=$_POST['title']; $firstname=$_POST['firstname']; $lastname=$_POST['lastname']; $password1=$_POST['password1']; $email=$_POST['email'];$country=$_POST['country'];}
	echo '<div class="divDetail" style="width=400px;">';
	echo '<div class="adminHeading2">Edit Member Particulars' . '</div>';
	echo '<form method="POST" action="memProfile.php" onsubmit="return ValidateForm(this)">';
		
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
		echo '<input name="password1" id="password1" type="password" accesskey="1" tabindex="5" style="width:200px" maxlength="30" placeholder="Password" value='.$password1.'> <br>';
		echo '<label for="password2" style="width:100px">Confirm:</label>';
		echo '<input name="password2" id="password2" type="password" accesskey="1" tabindex="6" style="width:200px" maxlength="30" placeholder="Password" value='.$password2.'> <br><p>';
		#
		echo '<label for="email" style="width:100px">Email:</label>';
		echo '<input name="email" id="email" type="text" accesskey="1" tabindex="7" style="width:350px" maxlength="100" value="'.$email.'" disabled > <br><p>';
		#
		echo '<div class="divactionbtn">';
		echo '<button type="submit" method="post" accesskey="1" title="Update" class="btnupdate" style="height:32px; width:110px;" id="btnupdate" name="btnupdate">Update</button>';
		echo '<button type="submit" method="post" accesskey="2" title="Cancel"   class="btncancel" style="height:32px; width:110px;" id="btncancel" name="btncancel">Cancel</button>';
		echo '</div>';
	echo '</form>';
	echo '</div>';
}
function manUpdate() {
	global $gVar;
	if (isset($_SESSION['actionsave']) && $_SESSION['actionsave']=="Y") {
		$qry = " Update mMembers set";
		$qry.= " memTitle='" . $_POST['title'] . "',";
		$qry.= " memFirstName='" . mysql_real_escape_string(trim($_POST['firstname'])) . "',";
		$qry.= " memLastName='" . mysql_real_escape_string(trim($_POST['lastname'])) . "',";
		$qry.= " memPassword='" . mysql_real_escape_string(trim($_POST['password1'])) . "',";
		$qry.= " cntCode=" . $_POST['country'];
		$qry.= " Where memCode=" . $_SESSION['memCode'];  	
		$result = mysql_query($qry); if (!($result)) {errHandler($qry); return;}
		sendEmailProfile($_SESSION['memCode']);
		unset($_SESSION['actionsave']); 
	}else{ header('Location: '.$gVar['WebURL']); ob_flush(); exit; }
}
?>