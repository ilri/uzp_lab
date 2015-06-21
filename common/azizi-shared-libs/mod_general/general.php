<?php
/**
 * Given the necessary params, it creates a drop down box
 *
 * @param <array> $items      The options that will be displayed to the user
 * @param <array> $values     The hidden variable linking to the displayed values
 * @param <string> $defval    Default value to be displayed when nothing is selected
 * @param <string> $name      The combobox name. The string 'id' will be appedned to create its Id
 * @param <integer> $selected The value to select
 * @param <bool> $enabled     To enable it or not
 * @param <string> $changewat A function to call when a value is selected
 * @return <string>  Returns HTML code of the formatted combo
 */
function Populate_Combo($items, $values, $defval, $name, $selected, $enabled, $changewat) {
   //reset($items);reset($values);
   if(!is_array($values)) $values=range(1,count($items));
   ($enabled)?$dis="":$dis="disabled";
   $change=($changewat!='' && isset($changewat))?"onChange='$changewat;'":'';
   $selected=(!isset($selected))?0:$selected;
   for($i=0;$i<count($items)+1;$i++) {
      if($i==0) {
         $combo="<select name='$name' id=\"{$name}id\" $dis $change>\n\t\t\t<option value=\"0\"";
         ($selected==$i)?$combo.=" selected>":$combo.=">";
         $combo.=$defval;
      }else {
         $combo.="<option value='".current($values)."'";
         ($selected==current($values))?$combo.=" selected>":$combo.=">";
         $combo .= current($items);
         next($items);
         next($values);
      }
   }
   $combo.="\n\t\t</select>";
   return $combo;
}
//==============================================================================================================================================

/**
 * Validation function. Given data in input and a key it builds a reg exp which is used on validation
 *
 * @param mixed $input   The data to be validated
 * @param integer $key   The type of data expected
 * @return integer    return 1 on success and 0 when validation fails
 */
function Checks($input, $key='', $pattern=''){
   $input=trim($input);
   $storeArray=array();
   if($key=='' && $pattern!=''){  //we have a pattern that we wanna use
      if($input=='') return true;
      return !eregi($pattern, $input, $storeArray);
   }
   switch($key){
		case 0:	//id format:12345678, pass format:A123456/7
         $span_str='0123456789';
         if(eregi('^A[0-9]{6,7}$',$input) || strspn($input,$span_str)==8)
            return 1;   //atleast a match is found
         return 0;   //no match
      break;

      case 1:   //shld contain only chars,n '
         if(strlen($input)<3 || strlen($input)>15)
            return 1;   //a name mst be at least 3 letters
         if(!eregi('^[a-z][A-Z]+$', $input)) return 1;
         return 0;      //all is ok
      break;

      case 2:   //format: 07 12123456 or 2547 12123456 or 020 1234567 or 254 20 1234567
         if(strlen($input)!=10 && strlen($input)!=12) return 1;   //lengths dont match
         if(eregi('^07[0-9]{8}$',$input)) $subinput=substr($input,4,6);
         elseif(eregi('^2547[0-9]{8}$',$input)) $subinput=substr($input,6,6);
         elseif(eregi('^25420[0-9]{7}$',$input)) $subinput=substr($input,5,7);
         elseif(eregi('^020[0-9]{7}$',$input)) $subinput=substr($input,3,7);
         else
            return 1;     //no match found so its error
      break;

      case 3:         //format:AB12345
         if(!eregi('^[A-Z]{2}[0-9]{5}$',$input)) return 1;
         else return 0;
      break;

      case 4:         //format:AB.12345.DD.MM.YYYY
         if(!eregi('^[A-Z]{2}\\.[0-9]{5}\\.[0-9]{2}\\.[0-9]{2}\\.[0-9]{4}$',$input)) return 1;
         else return 0;
      break;

      case 5:         //shldnt contain 'href', 'script', <, >, *, ;.
         if($input=="") return 1;
         $span_str="<>*;";
         if(strspn($input,$span_str)==1) return 1;   //a name mst be at least 3 letters
         return 0;
      break;

      case 6:         //index shldnt be 0
         if($input==0) return 1;
         return 0;
      break;

      case 7:		//shld contain only chars,n '
         if(strlen($input)==0) return 1;
         $span_str="abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ-_'. ";
         if(strspn($input,$span_str)!=strlen($input)) return 1;   //there are some of bad chars
         else return 0;      //all is ok
      break;

      case 8:      //ints only
      	if($input=='') return 1;// echo 'asfd';
      	return eregi('[^0-9]+', $input);
      break;

      case 9:     //email format: xxxxx@xxxx.xxx         //'^[A-Z]{2}\\.[0-9]{5}\\.[0-9]{2}\\.[0-9]{2}\\.[0-9]{4}$'
         return (!eregi('^[-!#$%&\'*+\\./0-9=?A-Z^_`a-z{|}~]+@[-\'+\\/0-9=?A-Z^_`a-z{|}~]+\.([A-Za-z]+)|([A-Za-z]+\.[A-Za-z]+)$',$input));
         //return (!eregi('^[-!#$%&\'*+\\./0-9=?A-Z^_`a-z{|}~]+@[-\'+\\/0-9=?A-Z^_`a-z{|}~]+\.[A-Za-z]+$',$input));
      break;

      case 11:      //illegal chars to refuse -- allows igbo characters to be present in the input.
      	$span_str="!\"#$%&`()*+/0123456789:;<=>?@[\\]^{|}~";
         if(strspn($input,$span_str)) return 1;		//if we have even one of the above trigger an error
         return 0;
      break;

      case 12:     //check for illegal names
         if(eregi("^((root)|(bin)|(daemon)|(adm)|(lp)|(sync)|(shutdown)|(halt)|(mail)|(news)|(uucp)|(operator)|(games)|(mysql)|
				(httpd)|(nobody)|(dummy)|(www)|(cvs)|(shell)|(ftp)|(irc)|(debian)|(ns)|(download))$", $input)){
					return 1;
			}
			if (eregi("^(anoncvs_)", $input)) return 1;
         return 0;
      break;

		case 13:	//alpha-numeric
			if(strlen($input)==0) return 1;
      	elseif(!eregi('[a-zA-Z0-9_ ]+', $input)) return 1;
         else return 0;
		break;

      case 14: //dates wit the format jan, feb, .. dec
         return (!eregi('^(\d{2})\-(jan|feb|mar|apr|may|jun|jul|aug|sep|oct|nov|dec)\-(\d{4})$', $input));
      break;

      case 15: //tone markings
         return(!eregi('^((high)|(low)|(medium))$', $input));
      break;

      case 16: //titles and names
         return eregi('[0-9\/#\$\|=~!@%\^\*\+\{\}\[\]\:\;\\]+', $input);
      break;

      case 17:         //format:[HH][:.,][MM] OR [HH][:.,][MM][:.,][SS] time in 24hr format with/out the seconds
         if(!eregi('^[0-2]{1}[0-9]{1}[:.,]{1}[0-5]{1}[0-9]{1}$',$input)
               && !eregi('^[0-2]{1}[0-9]{1}[:.,]{1}[0-5]{1}[0-9]{1}[:.,]{1}[0-5]{1}[0-9]{1}$',$input)) return 1;
         else return 0;
      break;

      case 18:      //ints and some $$s only
      	if($input=='') return 1;// echo 'asfd';
      	return eregi('[^0-9\$,]+', $input);
      break;

      case 19:      //only alpha characters
      	if($input=='') return 1;// echo 'asfd';
      	return eregi('[a-zA-Z]+', $input);
      break;

      case 20:      //strictly alpha-numeric characters
      	if($input=='') return 1;
      	return !eregi('[a-zA-Z0-9]+', $input, $storeArray);
      break;

      default:
      break;
  	}
}
//===============================================================================================================================================

/**
 * Logs a user out
 */
function LogOut(){
   if(isset($_COOKIE[session_name()])) setcookie(session_name(), '', time()-42000, '/');
   if(isset($_SESSION['username'])) session_destroy();
}
//===============================================================================================================================================

//creates the login of the site
function HiddenLogin(){
$contents=<<<CONTENTS
<div id='login' class='expanded'>
	<form name="login" action="$pageref?page=login" method="POST">
      <table style='width:200px;'><tr><td colspan='2' style="text-align: center;">Enter your login details</td></tr>
		<tr><td>User Name</td><td><input type="text" name="username" value="" size="15" /></td></tr>
		<tr><td>Password</td><td><input type="password" name="psswd" value="" size="15" /></td></tr>
		<tr><td colspan='2' style="text-align: right;"><input type="submit" value="Log In" name="submit" /><input type="reset" value="Cancel" name="cancel" /></td></tr>
      </table>
	</form>
</div>
CONTENTS;
return $contents;
}
//=============================================================================================================================================

/*USAGE:
INPUT:
OUTPUT:*/
function CreateLink($action, $display, $submit){
global $pageref;
//creates a link
   $sublink=($action=='')?'':"?page=$action";
   $section='';
   //these action details are meant to determine which sections we will be dealing wit. these sections have treeviews
   if($action=='discourse') $section=', 0';
   if($action=='dialects') $section=', 2';
   if($action=='dui') $section=', 3';
   if($submit) $linking="<a href='$pageref{$sublink}' onClick='submitme(form);'>$display</a>";
   else $linking="<a href='$pageref{$sublink}'>$display</a>";
return $linking;
}
//===========================================================================================================================================

/**
 * Formulates the dates between the sdate and edate(inclusive) using the format given start date, end date, date format expected
 * @param <string> $sDate  The starting date of the range
 * @param <string> $eDate  The ending date of the range
 * @param <string> $format The expected format for the dates in the range
 * @return <array>         An array containing all the dates in the range
 */
function DateRange($sDate, $eDate, $format){
   if(strpos($sDate, '-')===false){} //wrong delimiter, try another one
   else{
      $t=explode('-',$sDate); $t1=explode('-',$eDate);
      $sDate=date('d/m/Y',mktime(0,0,0,$t[1],$t[2],$t[0])); $eDate=date('d/m/Y',mktime(0,0,0,$t1[1],$t1[2],$t1[0]));
   }
   $t=explode('/',$sDate); $t1=explode('/',$eDate);
   //echo "$sDate $eDate $j $i $k<br />";
   $allDates=array();
   for($i=$t[2]; $i<=$t1[2]; $i++){
		if($i>$t1[2]) break;
		$j1=(is_null($j))?$t[1]:1;
		for($j=$j1; $j<=12; $j++){
			if($j>$t1[1] && $t1[2]==$i) break;
         $tDate=date_create();
         date_date_set($tDate, 0, $j, $k);
			$cMonDays=date_format($tDate, 't');
			
         $k1=(is_null($k))?$t[0]:1;
			for($k=$k1; $k<=$cMonDays; $k++){
				if($k>$t1[0] && $j==$t1[1] && $t1[2]==$i) break;
            $tDate=date($format, mktime(0,0,0,$j,$k,$i));
				array_push($allDates, $tDate); //[allDates.length]=k+'/'+j+'/'+i;
			}
		}
	}
   //print_r($allDates);
   return $allDates;
}
//===========================================================================================================================================

/**
 * Given a range of dates, a date or a mixture, it validates all dates are ok and returns an array containing all the dates without any range
 * The dates are expected to be in the format dd/mm/yyyy
 * @param <string> $dates  The dates to be validated
 * @return <mixed>         An array when all the dates have passed validation and -1 when an error occurred. if no dates are specified it returns null
 */
function CheckDates($dates){
   $allDates=explode(',',$dates);
   if($dates!=''){
      $gdDates=array();
      foreach($allDates as $t){
         $t=trim($t);
         if(strpos($t, '-')===false){
            $yr=substr($t,6,4); $mon=substr($t,3,2); $day=substr($t,0,2);
            if(checkdate($mon, $day, $yr)==false) return -1;
            else array_push($gdDates, $t);
         }
         else{ //we have a range, so get all the dates in this range
            $td=explode('-',$t); $temp=trim($td[0]); //echo "$t";
            $yr=substr($temp,6,4); $mon=substr($temp,3,2); $day=substr($temp,0,2);
            if(checkdate($mon, $day, $yr)==false) return -1;
            $temp=trim($td[1]);
            $yr=substr($temp,6,4); $mon=substr($temp,3,2); $day=substr($temp,0,2);
            if(checkdate($mon, $day, $yr)==false) return -1;
            $tds=DateRange($td[0], $td[1], 'd/m/Y');
            $gdDates=array_merge($gdDates, $tds);
         }
      }
   }
   return $gdDates;
}
//===========================================================================================================================================

//helps sort a matrix
function ArrayArraySort($a, $b){
   if($a[0]===$b[0]) return 0;
   return ($a[0]<$b[0]) ? -1 : 1;
}
//===========================================================================================================================================

/*Checks the user input data and checks for errors. On no errors it connects to the database and checks the authencity of the user. It calls the
 * module DetermineHomePage when a user has access.
 */
function UserControl(){
global $config, $psswdSettings;
   $errors=array();
	if(Checks($_POST['username'],10)) array_push($errors,'username');
   $pass=$_POST['psswd'];
   if(strlen($pass)<4 || strlen($pass)>25) array_push($errors,'password');
   if(count($errors)){	//we have some errors, cant let u thru
		exit(ErrorPage('There are errors in ur input data. cant let u thru.'));
	}
	else{	//all is ok, nw check if the details are ok
		$user=ConfirmUser($_POST['username'], md5($_POST['psswd']));
		if(is_string($user)) exit(ErrorPage($user));
		else{	//we gud so do wat u love best
			//echo 'we gud';
			$utype=GetSingleRowValue('user_type','aka','id',$user['utype']);
			if($utype==-2) exit(ErrorPage('There was an error while fetching the data from the dbase.'.mysql_error()));
			$_SESSION['utype']=$utype; $_SESSION['username']=$_POST['username']; $_SESSION['psswd']=md5($_POST['psswd']);
         $config["user"]=$_SESSION['utype'];
         if($psswdSettings['changeOnLogin']){
            if(md5($_POST['psswd'])==md5($psswdSettings['default'])){
               return ChangePassword();
            }
         }
			return DetermineHomePage('');
		}
	}
}
//===========================================================================================================================================

/**
 * Converts a date to and from dates format btwn YYYY-MM-DD and DD/MM/YYYY
 * @param <string> $date   Date to be converted
 * @param <string> $format The format of the date to be converted
 * @return <string>        The converted date
 */
function ConvertDate($date, $format){
   $format=strtolower($format);
   switch($format){
      case 'yyyy-mm-dd':
         $date=trim($date);
         $t=substr($date,8,2)."/".substr($date,5,2)."/".substr($date,0,4);
      break;
      case 'dd/mm/yyyy':
         $date=trim($date);
         $t=substr($date,6,4)."-".substr($date,3,2)."-".substr($date,0,2);
      break;
   }
   return $t;
}
//===========================================================================================================================================

/**
 * Finds the difference in days btwn the begin_date and end date
 *
 * @param <string> $begin_date   The first date
 * @param <string> $end_date     The end date
 * @return <integer>       Returns the number of days between the begin_date and end_date
 */
function SubtractDates($begin_date, $end_date){
   return round(((strtotime($end_date) - strtotime($begin_date)) / 86400));
}
//===========================================================================================================================================

/**
 * A custom upload function that allows multiple uploads of files and saves them in the specified location with their original names. This function assumes that
 * we are uploading multiple files.
 *
 * @param <array> $uploadedFiles    It contains the location setting where the files are to be saved and the max size allowed to upload a file. The location should
 *                                  be set relative to this file, general.php, since it will attempt to create a file using this path, else use an absolute path
 * @param <string> $html_name       The file name used in the HTML form
 * @param <array> $allowedTypes     An array of allowed file types
 * @param <array> $filename         An array with the destination file names, can be ommitted
 * @return <type>    Returns an array with the path to the uploaded files on success and a string with an error message on error
 */
function CustomSaveUploads($uploadedFiles, $html_name, $allowedTypes, $filenames=null){
   $err_occ=0;
   //LogError(print_r($_FILES, true));
   if(count($_FILES)==0) return 'There are no files selected for upload.';
   //save the samples file
   $err_msg='';
   $realUploadedFiles=array();
   for($i=0;$i<count($_FILES[$html_name]['name']);$i++){
      $err_code=$_FILES[$html_name]['error'][$i];
      //LogError("Error Code: $err_code".count($_FILES[$html_name]));
      if($err_code==4) continue;        //no file selected
      //check for the errors that might have occurred
      if($err_code!=0 && $err_code!=4){
      	if($err_code<3){
            $err_msg.=addslashes($_FILES[$html_name]['name'][$i])." exceeded the maximum allowed upload size.<br>";
         }
         if($err_code==3){
            $err_msg.=addslashes($_FILES[$html_name]['name'][$i])." was partially uploaded.<br>";
         }
         $err_occ=1; continue;
      }

      //only allow xml files to be uploaded
      //LogError($_FILES[$html_name]['type'][$i]);
      if(!in_array($_FILES[$html_name]['type'][$i], $allowedTypes)){
      	$err_msg.=addslashes($_FILES[$html_name]['name'][$i])." is not an allowed file type.<br>";
       	$err_occ=1; continue;
      }

      //Dont allow importation of files larger than 10Mb
      if($_FILES[$html_name]['size'][$i] > $uploadedFiles['max_size']){
      	$err_msg.=addslashes($_FILES[$html_name]['name'][$i])." is bigger than 10Mb. You are only allowed to import files less than 10Mb.<br>";
         $err_occ=1;  continue;
      }
      //check if the destination folder exists, and if it doesnt try and create it
      $uploadDir=$uploadedFiles['location'];
      if(is_dir($uploadDir)){        //the dir exists//check if its writable; if not make it writable
         if(!is_writable($uploadDir)) chmod($uploadDir,0766); //echo 'made it writable';
      }
      else{
         if(!mkdir($uploadDir,0766)){//create the destination folder name
            return 'There was an error while creating the destination folder for uploaded files.';
         }
      }
      //create the destination folder name
      if(isset($filenames)) $destfile=$uploadDir.$filenames[$i];
      else $destfile=$uploadDir.basename($_FILES[$html_name]['name'][$i]);
      //move the uploaded file to the final destination
      if(!move_uploaded_file($_FILES[$html_name]['tmp_name'][$i],$destfile)){
         $err_msg.=addslashes($_FILES[$html_name]['name'][$i]).". There was an error while saving this uploaded file.";
         $err_occ=1;  continue;
      }
      else $realUploadedFiles[]=$destfile;
   }
   if($err_occ) return $err_msg;
   elseif(count($realUploadedFiles)==0) return 'There are no saved files. Possibly there were no files selected for upload.';
   else return $realUploadedFiles;
}
//===========================================================================================================================================
/**
 * Attempts to change a password or calls the change password interface for changing the password
 *
 * @global <string> $data Placeholder for holding the change password interface
 */
function ChangePassword(){
global  $config, $psswdSettings, $dbcon;
   if($_POST['login']==NULL && $_POST['pass']==NULL){
      $login=$_SESSION['login']; $psswd=$_SESSION['psswd'];
   }
   else{
      $login=$_POST['login']; $psswd=$_POST['pass']; $psswd1=$_POST['pass1'];
   }
   $data='';
   if($psswd!=$psswd1) $data=ChangePasswordInterface('Error! The new passwords must match.<br />Your login details have not been changed.');
   if(Checks($login, 16)) $data=ChangePasswordInterface('Error! The entered username is invalid.<br />Your login details have not been changed.');
   if($login=='') $data=ChangePasswordInterface('Error! The username must have atleast 3 characters.<br />Your login details have not been changed.');
   if($psswd=='' || strlen($psswd)<4) $data=ChangePasswordInterface('Error! The new password must have atleast 4 characters.<br />Your login details have not been changed.');
   if($psswd==$psswdSettings['default']) $data=ChangePasswordInterface('The password must be different from the default password.<br />Your login details have not been changed.');
   if($psswd==$login) $data=ChangePasswordInterface('Error! Your password cannot be the same as your username.<br />Your login details have not been changed.');

   if($data=='') {
      $psswd=md5($psswd); $login=addslashes($login);
      if($psswd==md5($psswdSettings['default'])) $data=ChangePasswordInterface('The password must be different from the default password.<br />Your login details have not been changed.');
      else { //change the password in peace
      //         //get the salt
      //         $salt=GetSingleRowValue($config['session_dbase'].'.users', 'salt', 'login', $login);
      //         if($salt==-2) {
      //            $data=ChangePasswordInterface('There was an error while fetching data from the database. Contact the system administrator.');
      //            return $data;
      //         }
         StartTrans();
         if($psswdSettings['useSalt']) {
            $query="UPDATE misc_db.users SET login='$login',psswd=sha1(concat('$login',salt,'$psswd')) WHERE login = '".$_SESSION['username']."'";
            $result=mysql_query($query, $dbcon);
         }
         else {
            $cols=array('login','psswd'); $colvals=array($login, $psswd);
            $result=UpdateTable('users', $cols, $colvals, 'login', $_SESSION['username']);
         }
         if(!$result) {
            if(mysql_errno()==1062) $data=ChangePasswordInterface('The username you have selected is in use. Please select another username.');
            else $data=ChangePasswordInterface('There was an error while saving your details. Contact the system administrator.');
            RollBackTrans();
         }
         else {
            CommitTrans();
            $query="select b.name from ".$config['session_dbase'].".users as a inner join ".$config['session_dbase']
                .".user_levels as b on a.user_level=b.id where a.login='$login'";
            $user_level=GetQueryValues($query, MYSQL_ASSOC);
            if(is_string($user_level)) {
               LogOut(); $data=MainPage('Please log in to access the system resources.');
            }
            else {
               $_SESSION['username']=$login; $_SESSION['psswd']=$psswd; //$_SESSION['user_level']=$user_level['name'];
               $data=HomePage('Your login details have been successfully changed.');
            }
         }
      }
   }
   return $data;
}
//=========================================================================================================================================

/**
 * Creates the change password interface
 *
 * @param <string> $addinfo Any additional info that might need to be displayed
 * @return <string>  Returns the formatted contents of change password interface
 */
function ChangePasswordInterface($addinfo=''){
global $pageref, $server_name;
   if($addinfo=='') $addinfo="You are using the default username and password. <br />You must change your username and password to continue using SOCS.";

$contents=<<<CONTENTS
<div id='page_header'>Login Information</div>
<div id='addinfo'>$addinfo</div>
<div id='main' style='width: 40%; margin-left: 30%;'><form name="save" action="/avid/?page=change_password" method="POST">
	<div style='margin-left: 5%;'>
      <div style='padding: 2px'>
         <span>New User Name</span><input type="text" name="login" id="loginId" value="" width="20" style='margin-left: 18px;' /></div>
      <div style='padding: 2px'>
         <span>New Password</span><input type="password" name="pass" id="passId" value="" width="20" style='margin-left: 26px;' /></div>
      <div style='padding: 2px'>
         <span>Re-Type Password</span><input type="password" name="pass1" id="pass1Id" value="" width="20" style='margin-left: 5px;' />
      </div>
   </div>
   <div style='text-align: center;'>
      <input type="submit" value="Save Changes" name="save" /> <input type="reset" value="Cancel" name="save" />
   </div>
<input type="hidden" name="flag" id="flagId" value="" />
</form></div>
CONTENTS;

return $contents;
}
//=========================================================================================================================================

/**
 * Converts an array to an associative array with the key values for each entry are stored in the first array
 *
 * @param array $array2Convert   The array to convert
 * @return mixed Returns a string with the error message in case of an error, else it returns the converted array
 */
function ConvertGeneralArrayToAssociativeArray($array2Convert){
   if(!is_array($array2Convert)) return  "Error! The passed data is not an array!";

   $convertedArray = array();
   $rowCount = count($array2Convert);
   for($i=2; $i <= $rowCount; $i++){
      $colCount = count($array2Convert[$i]);
      $tempArray=array();
      for($j=1; $j <= $colCount; $j++){
         $temp = $array2Convert[$i][$j];
         $tempArray[$array2Convert[1][$j]] = $temp;
      }
      $convertedArray[] = $tempArray;
   }
   return $convertedArray;
}
//=========================================================================================================================================

/**
 * Checks whether the currently logged in user is a valid user
 *
 * @global <type> $allowed_user
 * @return <type>
 */
function ValidUser(){
global $allowed_user;
	if($allowed_user) return 'isValid';
	//echo 'validating user<br>';
	if(!isset($_SESSION['username']) || !isset($_SESSION['pass'])) return 'not valid';
	$query="SELECT id FROM users WHERE login='".$_SESSION['username']."' and psswd='".$_SESSION['pass']."' and allowed=1";
   $results=GetQueryValues($query);
   if(is_string($results)) return 'error';
   if(count($results)==0) return 'not valid';
   $id=$results[0];
   //echo $id[0].'<br>';
   $results=LastRowValues('users','id',array('id'));
   if(is_string($results)) return 'error';
   $lastid=$results[0];
   //echo $id.'<br>';
   if($id[0]>0 && $id[0]<($lastid+1)){ $allowed_user=true; return 'isValid';}
   else return 'not valid';
}
//=========================================================================================================================================
?>