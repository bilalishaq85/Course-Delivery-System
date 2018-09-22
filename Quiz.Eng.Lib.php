<?PHP
function dispQuestion1($ans) {
	if ($ans == "NEW") {
		echo "<input id='txtAnswer' type='text' class='quizanstxt'> <p> </p>";
	}else{
		echo "<input id='txtAnswer' type='text' class='quizanstxt' readonly='readOnly' value='".$ans."'> <p> </p>";
	}
}

function dispQuestion2($ans, $radName) {
	if ($ans == "NEW") { $lockSelected=""; $lockEmpty=""; } else { $lockSelected=" checked='checked' value='yes' "; $lockEmpty=" disabled='yes' value='No'";  }
	$i=1; 
	echo "<div class='quizoptmain'>";
	foreach ( $_SESSION['queOption'] as $option ){
		//if ($_SESSION['queShowInline'] == True) {echo "<div class='quizoptrow'>";}else{echo "<div class='quizoptcol'>";}
		if ($_SESSION['queShowInline'] == True) {echo "<div class='quizoptrow'>";}else{ if ($option['optImage'] == ""){echo "<div class='quizoptcoltxt'>";}else{echo "<div class='quizoptcol'>";}} 
		if ($ans == $i) 
			{ echo "<input type='radio' class='quizradio' name='".$radName."' id='".$radName.$i."' value='".$i."' " .$lockSelected. ">"; }
		else 
			{ echo "<input type='radio' class='quizradio' name='".$radName."' id='".$radName.$i."' value='".$i."' " .$lockEmpty.    ">"; }
		//echo "</td><td>";
		if ($_SESSION['queShowInline'] == True) { echo "<br>"; }
		if ($option['optImage'] == "") {
			echo "<label class='quizlabeltext' for='".$radName.$i."'>" . $option['optDetail'] . "</label>";
		}else{
			echo "<img id='Image".$i."' class='quizimage' alt='".$option['optDetail']."' src='".$option['optImage']."'".$option['optImageH']." ".$option['optImageW'].
				 " onclick=\"document.getElementById('".$radName.$i."').click();\" />";		
		}
		$i++; 
		echo "</div>";
	}
	echo "</div>";
}

function dispQuestion3($ans) {
	if ($ans == "NEW") { $newQuestion=TRUE; $lockSelected=""; $lockEmpty=""; } 
	else { $newQuestion=FALSE; $ans = explode(',',$ans); $lockSelected=" checked='checked' value='yes' onclick='return false' "; $lockEmpty=" disabled='yes' value='No'";  }
	$i=1; $matAns="";
	echo "<div class='quizoptmain'>";
	foreach ( $_SESSION['queOption'] as $option ){
		if ($_SESSION['queShowInline'] == True) {echo "<div class='quizoptrow'>";}else{ if ($option['optImage'] == ""){echo "<div class='quizoptcoltxt'>";}else{echo "<div class='quizoptcol'>";}} 
		if  (!$newQuestion && $ans[$i-1] > 0 ) {
			echo "<input type='checkbox' class='quizcheckbox' name='chkList' id='chkList".$i."' value=".$i. $lockSelected.">";
		}else{
			echo "<input type='checkbox' class='quizcheckbox' name='chkList' id='chkList".$i."' value=".$i. $lockEmpty.">";
		}
		if ($_SESSION['queShowInline'] == True) { echo "<br>"; }
		if ($option['optImage'] == "") {
			echo "<label class='quizlabeltext' for='chkList".$i."'>".$option['optDetail']."</label>";
		}else{
			echo "<img id='Image".$i."' class='quizimage' alt='".$option['optDetail']."' src='".$option['optImage']."'".$option['optImageH']." ".$option['optImageW'].
				 " onclick=\"document.getElementById('chkList".$i."').click();\" />";				
		}
		$i++; 
		echo "</div>";
	} 
	echo "</div>";
}

function dispQuestion4($ans) {
	if ($ans == "NEW") { $newQuestion=TRUE; $txtName="txtList"; } 
	else { $newQuestion=FALSE;  $ans = explode(',',$ans); $txtName="txtListE"; }
	$i=1; $matAns="";
	$txtListWidth=1; if (sizeof($ans) > 10)  { $txtListWidth = 2; }; if (sizeof($ans) > 100) { $txtListWidth = 3; }
	echo "<div class='quizoptmain'>";
	foreach ( $_SESSION['queOption'] as $option ){
		if ($_SESSION['queShowInline'] == True) {echo "<div class='quizoptrow'>";}else{ if ($option['optImage'] == ""){echo "<div class='quizoptcoltxt'>";}else{echo "<div class='quizoptcol'>";}} 
		if  (!$newQuestion) {$matAns=$ans[$i-1];}  
		//echo "<input type='text' name='txtList' id='txtList" . $i ."' size='" . $txtListWidth . "' maxlength='" . $txtListWidth . "' readOnly='readOnly' > ";
		echo "<input type='text' class='quizautotxt' name='".$txtName."' id='".$txtName.$i."' size='".$txtListWidth."' maxlength='".$txtListWidth.
			 "' value='".$matAns."' readOnly='readOnly'>";
		//echo "</td><td>";
		if ($_SESSION['queShowInline'] == True) { echo "<br>"; }
		if ($option['optImage'] == "") {
			echo "<label class='quizlabeltext' name='lblList' id='lblList" . $i ."' for='txtList" . $i ."'>" . $option['optDetail'] . "</label><br />";
		}else{
			echo "<img id='Image".$i."' class='quizimage' alt='".$option['optDetail']."' src='".$option['optImage']."'".$option['optImageH']." ".$option['optImageW'].
				 " onclick=\"document.getElementById('".$txtName.$i."').click();\" />";				
		}
		$i++;
		echo "</div>"; 
	} 
	echo "</div>";
}

function dispQuestion5($ans) {
	if ($ans == "NEW") { $newQuestion=TRUE; $txtName="txtList"; } 
	else { $newQuestion=FALSE;  $ans = explode(',',$ans); $txtName="txtListE"; }
	$i=1; $matAns="";
	echo "<table class='quiztable'>";
	if (isset($_SESSION['optTitle']) && $_SESSION['optTitle'] != "" ) 
		{ echo "<tr><th class='quiztableth'>".$_SESSION['optTitle']."</th><th class='quiztableth'>".$_SESSION['matTitle'] . "</th></tr>";}
	$txtListWidth=1; if (sizeof($ans) > 10)  { $txtListWidth = 2; }; if (sizeof($ans) > 100) { $txtListWidth = 3; }
	foreach ( $_SESSION['queOption'] as $option ){
		if  (!$newQuestion) {$matAns=$ans[$i-1];}  
		echo "<tr><td class='quiztabletd'>";
		echo "<input type='text' class='quizautotxt' name='txtListOE' id='txtListOE".$i."' size='".$txtListWidth."' maxlength='".$txtListWidth.
			 "' value='".$i."' readOnly='readOnly'>";
		if ($option['optImage'] == "") {
		    //echo "<label for='txtListOE" . $i ."'>" . $option['optDetail'] . "</label><br />";
			echo "<label class='quizlabeltext' name='lblList' id='lblList" . $i ."' for='txtListOE" . $i ."'>" . $option['optDetail'] . "</label><br />";
		}else{
			echo "<img id='Image".$i."' class='quizimage' alt='".$option['optDetail']."' src='".$option['optImage']."'".$option['optImageH']." ".$option['optImageW'].
				 " />";
		}
		echo "</td><td class='quiztabletd'>";
		echo "<input type='text' class='quizautotxt' name='".$txtName."' id='".$txtName.$i."' size='".$txtListWidth."' maxlength='".$txtListWidth.
			 "' value='".$matAns."'readOnly='readOnly'>";
		
		if ($_SESSION['queMatch'][$i-1]['matImage'] == "") {
  		    //echo "<label name='lblList' id='lblList" . $i ."' for='".$txtName.$i ."'>" . $_SESSION['queMatch'][$i-1]['matDetail'] . "</label><br />";
			echo "<label class='quizlabeltext' name='lblList' id='lblList" . $i ."' for='".$txtName.$i ."'>" . $_SESSION['queMatch'][$i-1]['matDetail'] . "</label><br />";
		}else{
			echo "<img id='Image".$i."' class='quizimage' alt='".$_SESSION['queMatch'][$i-1]['matDetail']."' src='".$_SESSION['queMatch'][$i-1]['matImage'].
			    "'".$_SESSION['queMatch'][$i-1]['matImageH']." ".$_SESSION['queMatch'][$i-1]['matImageW'].
				 " onclick=\"document.getElementById('".$txtName.$i."').click();\" />";
		}		
		echo "</td></tr>";
		$i++; 
	} 
	echo "</table>";
}
?>