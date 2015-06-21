<?php
/**
 * THIS FILE WILL BE USED BY DIFFERENT PROJECTS AS IT CONTAINS GENERAL FUNCTIONS. WHEN EDITING IT PLEASE CONSIDER THE IMPLICATIONS
 * THE CHANGES WILL HAVE ON THE OTHER PROJECTS
 *
 * @global <Connection Id> $dbcon   The current mysql connection id being used, Appears in all functions attempting to connect to e db
 * @global <array> $config          An array containing the login details
 */


//=================================================SESSION FUNCTIONS=============================================================

//set the dbase where the session table is located
if(!isset($config['session_dbase'])) $config['session_dbase']=$config['dbase'];

/**
 * Creates the initial login to a database
 * @global <array> $config   An array containing the login credentials
 */
function open_session($spec_conf='') {
global $dbcon, $config;
//   echo "here we at";
	date_default_timezone_set('Africa/Nairobi');
   if(is_array($spec_conf)) {$use_config=$spec_conf;}
   else {$use_config=$config;}
//   echo '<pre>'.print_r($dbcon, true).'</pre>';

	$dbcon = mysql_connect($use_config["dbloc"], $use_config["user"], $use_config["pass"]);
   if(!$dbcon){
      LogError(); //'Cannot connect to the database.'
      exit(ErrorPage('Cannot connect to the database.'));
   }
	$results=mysql_query("SET NAMES 'utf8'", $dbcon) or exit(ErrorPage('error while setting charset '. mysql_error()));
	$results=mysql_query("SET CHARACTER SET 'utf8'", $dbcon) or exit(ErrorPage('error while setting charset '. mysql_error()));
	if(!mysql_select_db($use_config['dbase'],$dbcon)){
      LogError();
      exit(ErrorPage('Error while selecting the database.'));
   }
//   echo 'niko';
}
//================================================================================================================================

/**
 * An alias of open_session
 *
 * @global <type> $dbcon
 * @global <type> $config
 * @return <type> Returns 0 on success else a string with the error message
 */
function Connect2DB($spec_conf){
global $dbcon, $config;
   date_default_timezone_set('Africa/Nairobi');
   if(isset($spec_conf)) $use_config=$spec_conf;
   else $use_config=$config;

	$dbcon = mysql_connect($use_config["dbloc"], $use_config["user"], $use_config["pass"]);
   if(!$dbcon){
      LogError();
      return 'Cannot connect to the database.';
   }
	//$results=mysql_query("SET NAMES 'utf8'", $dbcon) or exit(ErrorPage('error while setting charset '. mysql_error()));
	if(!mysql_select_db($use_config['dbase'],$dbcon)){
      LogError();
      return 'Error while selecting the database.';
   }
   return 0;
}
//================================================================================================================================

/**
 * Reopens a connection based on the login credentials in $config
 * @global <array> $config An array containing the new connections details
 * @param <string> $type   The type of request asking to reopen a connection. either ajax or normal
 * @return <mixed>         On success, it returns nothing, on error it returns a string with the error message
 */
function ReopenConnection($type){
global $dbcon, $config;
	$errMssg='';
//	echo '<pre>'.print_r($config, true).'</pre>';
	$dbcon = mysql_connect($config["dbloc"], $config["user"], $config["pass"]);
	if(!$dbcon){
      LogError();
      $errMssg='Error! Cannot connect to the database.<br />Please contact your system administrator.';
   }
	else{
		if(!mysql_query("SET NAMES 'utf8'", $dbcon)){
         LogError(); $errMssg='Error while setting charset.<br />Please contact your system administrator.';
      }
		else{
         if(!mysql_select_db($config['dbase'],$dbcon)){
            LogError(); $errMssg='Error while selecting the database.<br />Please contact your system administrator.';
         }
      }
	}

	if($type=='ajax' && $errMssg!='') return $errMssg;
	elseif($type=='normal' && $errMssg!='') exit(ErrorPage($errMssg));
}
//================================================================================================================================

/**
 * Starts a session
 */
function StartingSession(){
	return session_start();
}
//================================================================================================================================

function close_session(){
global $dbcon;
	if($dbcon==NULL) return;
	return mysql_close($dbcon);
}
//================================================================================================================================

/**
 * Initializes the session. Reads the session data at the start of each session. An automatic function called by the function StartingSession
 * which handles the starting of sessions
 *
 * @global <integer> $timeout This is the time limit a user can be idle before being logged out by the system. set in config file
 * @param <string> $sid       A unique session identifier for the session in question
 * @return <mixed>            Returns the session details on success and an error page on error
 */
function read_session($sid){
global $dbcon, $config, $query, $timeout, $pageref;
//    echo "Reading the session info: $sid <br />";
	//check that the session is not timed out, if it is just die
   //LogError(print_r($config, true));
 	$query = "SELECT data FROM ".$config['session_dbase'].".sessions WHERE session_id='".mysql_real_escape_string($sid)."'";
 	$referrer=basename($_SERVER['SCRIPT_NAME']);
	$result = mysql_query($query, $dbcon);
	if(!$result){
      LogError(); exit(ErrorPage('Cannot fetch data from the database.'));
   }
	if(mysql_num_rows($result) == 1) {
		list($data) = mysql_fetch_array($result);
		//check that the user session time aint expired, if it has log the user out.
		$query = "SELECT updated_at FROM ".$config['session_dbase'].".sessions WHERE session_id='".mysql_real_escape_string($sid)."'";
		$result=mysql_query($query, $dbcon);
      if(!$result) LogError();
		if(mysql_num_rows($result)==1){
			list($olTime) = mysql_fetch_array($result); $time=date('Y-m-d H:i:s');
			$format="%Y-%m-%d-%H-%M-%S";
			//echo strftime($format, strtotime($olTime))." $olTime <br>";
			$olTime=explode('-',strftime($format, strtotime($olTime)));
			$newTime=explode('-',strftime($format, strtotime($time)));
			//print_r($olTime);
			$days=$newTime[2]-$olTime[2]; $hrs=$newTime[3]-$olTime[3]; $mins=$newTime[4]-$olTime[4];
			//echo "$hrs:$mins <--the time you were inactive<br>";
			if($mins<0){$mins=60+$mins; $hrs=$hrs-1;}
         if($hrs<0) $hrs=24+$hrs;
         if($days==0){
				if(($hrs*60+$mins)<$timeout) return $data;
			}
         elseif($days>0) $hrs+=24*$days;
			//if u here then ur time has expired
			if(!isset($_SESSION['username'])) destroy_session($sid);		//delete the dbase entries that might be there
			LogOut();
		 $hrs=($hrs==1)?"$hrs hour":"$hrs hours";
         if($referrer=='seamless.php') die('error$$'."You were inactive for $hrs hours and $mins minutes and you have been logged out. Please login in again.");
         else  exit(ErrorPage("You were inactive for $hrs and $mins minutes and you have been logged out.<br />Please login in again."));
		}
	}
	elseif(mysql_num_rows($result)==0){
		//die('Cant find session data');
		return '';
	}
	else {
		if($referrer=='seamless.php') die('error$$There was an error while fetching the session data. Please contact your system administrator.');
      else exit(ErrorPage('More than one session data in the dbase'));
	}
}
//================================================================================================================================

function write_session($sid, $data){
global $dbcon, $config, $query;
//   echo '<pre>'.print_r($dbcon, true).'</pre>';
	if($data=='' || !isset($data)) return ;
   if(!$dbcon) return;
//   $result=mysql_query("SET AUTOCOMMIT=1", $dbcon);
//	if(!$result){LogError(); die();}
	//LogError(print_r($_SESSION));
 	//also ensure that incase the session_id is the same and bt the incoming data differs frm the data in the dbase--coming from the same computer
   $time=date('Y-m-d H:i:s');
   //$query="SELECT * FROM ".$config['session_dbase'].".sessions WHERE STRCMP(session_id, BINARY '$sid')=0 AND STRCMP(data, BINARY '".mysql_real_escape_string($data)."')<>0";
   //echo "$query<br>";
   //$res=GetQueryValues($query);
//   echo '<pre>'.print_r($dbcon, true).'</pre>';
	$query = "INSERT INTO ".$config['session_dbase'].".sessions(session_id, data, updated_at) VALUES ('".mysql_real_escape_string($sid)."', '".mysql_real_escape_string($data)."','$time')";
	$result = mysql_query($query, $dbcon);
   mysql_query("COMMIT", $dbcon);
   //LogError($query);
//   echo "Writing the session data: $query<br>";
//   echo 'Affected Rows: '.mysql_affected_rows($dbcon).'<br />';
	if(!$result){LogError(); die();}
	return mysql_affected_rows($dbcon);
}
//================================================================================================================================

/**
 * Deletes the session data from the database
 *
 * @param <session_Id> $sid   The session id to destroy
 * @return <integer>          The number of deleted rows
 */
function destroy_session($sid) {
global $dbcon, $config;
   $query = "DELETE FROM ".$config['session_dbase'].".sessions WHERE session_id='".mysql_real_escape_string($sid)."'";
 	//echo $query.'<br>';
	$result = mysql_query($query, $dbcon);
	$_SESSION = array();
//   echo "Destroying the session: $query<br />";
	return mysql_affected_rows($dbcon);
}
//================================================================================================================================

function clean_session($expire) {
	global $dbcon;
 	$query = "DELETE FROM ".$config['session_dbase'].".sessions WHERE DATE_ADD(updated_at, INTERVAL ".(int) $expire." SECOND) < NOW()";
	$result = mysql_query($query, $dbcon);
   echo "Cleaning the session info: $query<br />";
	return mysql_affected_rows($dbcon);
}
//================================================================================================================================

session_set_save_handler('open_session', 'close_session', 'read_session', 'write_session', 'destroy_session', 'clean_session') or exit(ErrorPage('Cannot start sessions.'));
//=================================================END OF SESSION FUNCTIONS=============================================================

/**
 * Given a username and a password, confirms whether the user has priviledges to access the dbase.
 *
 * @global  array    $psswdSettings    Password settings as defined in the fonfiguration file
 * @param   string   $username         The username specified by the user
 * @param   string   $password         The password entered for this page. This password should be encoded before being passed to this function
 * @param   string   $reqType          The type of request the user is submitting, either an ajax request or a conventional request
 * @return  mixed    Return an array with the login details on successfull, else it returns -1
 */
function ConfirmUser($username, $password, $reqType='normal'){
//check if the user is ok and return the rights level, if ok initialize the dbcon and initialise sessions
global $dbcon, $query, $paging, $psswdSettings, $config;
   $username = mysql_real_escape_string($username); $password=mysql_real_escape_string($password);
   if($psswdSettings['useSalt']){
      $query = "SELECT * FROM {$config['session_dbase']}.users WHERE login='$username' AND psswd=sha1(concat(salt,'$password')) AND allowed='1' LOCK IN SHARE MODE";
   }
   else{
      $query = "SELECT * FROM {$config['session_dbase']}.users WHERE login='$username' AND psswd='$password' AND allowed='1' LOCK IN SHARE MODE";
   }
//   echo '<pre>'. print_r($_POST, true) .'</pre>';
//   echo $query.'<br>';
   $result = mysql_query($query, $dbcon);
   if(!$result){
      LogError(); LogOut();
      if($reqType=='normal') exit(ErrorPage("Invalid query."));
      else return -1;
   }
   $message = '<i>Invalid username or password, please try again.<br> '
      .'If your log in details are correct, you may not have sufficient rights to access the system.<br> '
      .'Please contact the System Administrator.</i>';

   if(mysql_num_rows($result) == 0){
      LogError("Login attempt for the user $username failed."); LogOut();
      if($reqType=='normal') exit(ErrorPage($message));
      else return -1;
   }
   $row=mysql_fetch_array($result);
   //echo $paging;
   if($paging=='login' || $paging=='logout' || $paging=='change'){}
   else{
      if($psswdSettings['changeOnLogin']){
         $user=$row;
         //echo $_SESSION['psswd'].' == '.md5($psswdSettings['default']).' || '.md5($_SESSION['psswd']).'=='.md5($psswdSettings['default']);
         if($_SESSION['psswd']==md5($psswdSettings['default']) || md5($_SESSION['psswd'])==md5($psswdSettings['default'])){
            $links=FooterLinks();
            if($reqType=='normal') die (ErrorPage(ChangePassword(''), $links));
            else return 0;

         }
      }
   }
   return $row;
}
//================================================================================================================================

/**
 *  builds a query to select data from the dbase using different tables
 *
 * @param <array> $select  array containing the different tables to select data. the tables are grouped in arrays with each array containing
      the index of the table in array tables and the column to be selected from. this criteria is applied also in where array.
 * @param <array> $joins   an array specifying the join of the nth table and (n+1)nth table. this specifies the cols in the 2 tables to be used in the join
      as well as the type of join itself.
 * @param <array> $where   an array wiht the where statements
 * @param <array> $order   array specifying in an order the ordering to be applied in the query.
 * @param <array> $group
 * @param <array> $tables  an array with tables to select from
 * @return <mixed>         an array with data from executing the query on success, an error string on error.
 */
function GetAllColumnValues($select,$joins,$where,$order,$group,$tables){
//all the values are arrays. check the documentation on how the arrays are arranged
global $dbcon, $query;
	//the select clause
	$sel='';
	for($i=0;$i<count($select);$i++){
		$temp=$select[$i]; $comma=($i==0)?'':',';
		$count=substr($temp,0,strpos($temp,'.')); $ident=str_repeat('A',$count);
		$col=substr($temp,strpos($temp,'.')+1);
		$sel.="$comma $ident.$col";
	}
	$sel="SELECT DISTINCT $sel";
	//from and inner joins
	$from=' FROM ';
	for($i=0;$i<count($tables);$i++){
		if($i==0) $from.=$tables[$i].' AS A';
		else{
			if(!isset($joins[$i-1])) continue;
			$tempjoin=$joins[$i-1];
			$count=substr($tempjoin[0],0,strpos($tempjoin[0],'.'));
			$count1=substr($tempjoin[1],0,strpos($tempjoin[1],'.'));
			$ident=str_repeat('A',$count); $ident1=str_repeat('A',$i+1);
			$col=substr($tempjoin[0],strpos($tempjoin[0],'.')+1);
			$col1=substr($tempjoin[1],strpos($tempjoin[1],'.')+1);
			$from.=" ".$tempjoin[2]." JOIN ".$tables[$i].' AS '.$ident1." ON $ident.$col=$ident1.$col1";
		}
	}
	//where clause
	$whea='';
	for($i=0;$i<count($where);$i++){
  		$ident=str_repeat('A',$i+1);
  		for($j=0;$j<count($where[$i]);$j+=2){
  			$temp=$where[$i];
  			$join=(strpos($temp[$j+1],'%')!==false)?'LIKE':'=';
  			$and=($whea=='')?'':'AND';
  			$whea.=" $and $ident.".$temp[$j]." $join '".$temp[$j+1]."'";
  		}
	}
	$whea=($whea=='')?'':" WHERE $whea";
	//order clause
	$ord='';
	for($i=0;$i<count($order);$i++){
		$temp=$order[$i]; $comma=($i==0)?'':',';
		$count=substr($temp,0,strpos($temp,'.')); $ident=str_repeat('A',$count);
		$col=substr($temp,strpos($temp,'.')+1);
		$ord.="$comma $ident.$col";
	}
	$ord=($ord=='')?'':" ORDER BY $ord";

	//group by clause
	$grp='';
	for($i=0;$i<count($group);$i++){
  		$temp=$order[$i]; $comma=($i==0)?'':',';
		$count=substr($temp,0,strpos($temp,'.')); $ident=str_repeat('A',$count);
		$col=substr($temp,strpos($temp,'.')+1);
		$grp.="$comma $ident.$col";
	}
	$grp=($grp=='')?'':" GROUP BY $grp";
	$query="$sel $from $whea $grp $ord LOCK IN SHARE MODE";

	//LogError($query);
	$result=mysql_query($query, $dbcon);
   if(!$result){
      LogError(); return "There was an error while fetching the values from the tables.";
   }
   $results=array();
   while($row=mysql_fetch_row($result)){
   	//echo implode(' ',$row).'<br>';
    	array_push($results,$row);
   }
   return $results;
}
//================================================================================================================================

/**
 * Get all the values of only one column in a table
 * This function is just like GetAllColumnValues only that it gets values from a single column only n dont support linked tables
 *
 * @param <string> $table   The table to fetch data from
 * @param <string> $col     The column to fetch values from
 * @param <mixed> $order    The ordering criteria to be used to order the results
 * @param <mixed> $criteria The criteria to be used when executing the query
 * @return <mixed> Returns an array with the found values on success and an error string on error
 */
function GetSingleColumnValues($table, $col, $order, $criteria=null){
global $dbcon, $query;
   if($order===true|| $order===false || $order===null) $ordering=($order)?" ORDER BY $col":'';
   else $ordering=" ORDER BY $order";
   if(is_array($criteria)){
		$columns=$criteria[0]; $vals=$criteria[1];
		$criteria='';
		for($i=0;$i<count($columns);$i++){
			$criteria.=($i==0)?'WHERE ':' AND ';
         $criteria.=$columns[$i]."='".$vals[$i]."'";
		}
	}
   elseif($criteria==null || $criteria==false) $criteria='';

   $query="SELECT $col FROM $table $criteria $ordering LOCK IN SHARE MODE";
   //echo $query.'<br>';
   $result=mysql_query($query, $dbcon);
   if(!$result){
      LogError(); return "There was an error while fetching the values from the $table table.";
   }
   $results=array();
   while($row=mysql_fetch_array($result)) array_push($results,$row[0]);
   return $results;
}
//================================================================================================================================

/**
 * Selects specific rows from the table using the search criteria
 *
 * @param <string> $table     The table to fetch the data from
 * @param <array> $cols       An array of the columns that we want returned
 * @param <mixed> $criteria   Either a string or array. If its an array it holds an array of columns and their values to be used in the search
 * @return <mixed>      Returns an multi-dimensions array with the results on sucess, else it returns a string with the error that occured
 */
function GetColumnValues($table, $cols, $criteria, $fetchMode=MYSQL_BOTH){
global $dbcon, $query;
	if(is_array($criteria)){
		$columns=$criteria[0]; $vals=$criteria[1];
		$criteria='';
		for($i=0;$i<count($columns);$i++){
			$criteria.=($i==0)?'WHERE ':' AND ';
         $criteria.=$columns[$i]."='".$vals[$i]."'";
		}
	}
	$query="SELECT ".implode(',',$cols)." FROM $table $criteria LOCK IN SHARE MODE";
   $result=mysql_query($query, $dbcon);
   //LogError('Debugging');
   if(!$result){
      LogError(); return "There was an error while fetching the values from the $table table.";
   }
   if(mysql_num_rows($result)==0) return array();
	$results=array();
   while($row=mysql_fetch_array($result, $fetchMode)) array_push($results,$row);
   return $results;
}
//================================================================================================================================

/**
 * Selects specific rows from the table using the search criteria
 * This is the replica of GetColumnValues which will be deprecated due to the wrong name
 *
 * @see GetColumnValues
 */
function GetRowValues($table, $cols, $criteria, $fetchMode=MYSQL_BOTH){
global $dbcon, $query;
	if(is_array($criteria)){
		$columns=$criteria[0]; $vals=$criteria[1];
		$criteria='';
		for($i=0;$i<count($columns);$i++){
			$criteria.=($i==0)?'WHERE ':' AND ';
         $criteria.=$columns[$i]."='".$vals[$i]."'";
		}
	}
	$query="SELECT ".implode(',',$cols)." FROM $table $criteria LOCK IN SHARE MODE";
   $result=mysql_query($query, $dbcon);
   //LogError('Debugging');
   if(!$result){
      LogError(); return "There was an error while fetching the values from the $table table.";
   }
   if(mysql_num_rows($result)==0) return array();
	$results=array();
   while($row=mysql_fetch_array($result, $fetchMode)) array_push($results,$row);
   return $results;
}
//================================================================================================================================

/**
 * Executes a query
 *
 * @param <string> $queryed   The query to execute
 * @param <string> $resType   (Optional) The type of array that will be fetched. Can be MYSQ_BOTH, MYSQL_ASSOC, MYSQL_NUM. Defaults to MYSQL_BOTH
 * @return <mixed>      A multi-dimensioanl array with the results as fetched from the dbase when successful else a string with the error which occured
 */
function GetQueryValues($queryed, $resType=MYSQL_BOTH){
global $dbcon, $query, $Dbg;
   $query = $queryed;
//   echo '<pre>'.print_r($dbcon, true).'</pre>';
//   echo '<pre>'.$query.'</pre>';
   $result=mysql_query($query, $dbcon);
   if(!$result){LogError(); return "There was an error while fetching the values from the database.";}
   $results=array();
   while($row=mysql_fetch_array($result, $resType)) array_push($results,$row);
   return $results;
}
//================================================================================================================================

/**
 * updates row(s) in a table
 *
 * @param string $table        Table to update the data
 * @param array $cols          The columns to update
 * @param array $colvals       The column values to update
 * @param mixed $conditioncol  The columns to be used in the where criteria
 * @param mixed $conditionval  The column values to be used in the criteria
 * @return mixed               Returns 0 on success a string on error
 */
function UpdateTable($table, $cols, $colvals, $conditioncol, $conditionval){
//this function is used to update a table. cols are cols to be update while colvals are the values for the cols
//condition col and conditionval are used to determine which condition to use
global $dbcon, $query;
	//create the conditions incase there are multiple conditions
	if(is_array($conditioncol)){
		$condition='';
		for($i=0; $i<count($conditioncol);$i++){
			$condition.=($i==0)?$conditioncol[$i]."='".$conditionval[$i]."'":' AND '.$conditioncol[$i]."='".$conditionval[$i]."'";
		}
	}else $condition="$conditioncol = '$conditionval'";

	$cols_vals='';
   for($i=0;$i<count($cols);$i++){
   	$cols_vals.=($i==0)?$cols[$i]."='".$colvals[$i]."'":",".$cols[$i]."='".$colvals[$i]."'";
   }
   //lock the table to prevent concurrent reads and updates
   $query="SELECT ".implode(',',$cols)." FROM $table FOR UPDATE";
   $result=mysql_query($query, $dbcon);
   if(!$result){LogError(); return "There was an error while fetching data from the database."; }

   $query="UPDATE $table SET $cols_vals WHERE $condition";
   $result=mysql_query($query, $dbcon);
//	LogError('Debugging:');
   if(!$result){
      LogError(); return "There was an error while updating the database.";
   }
   return 0;
}
//================================================================================================================================

/**
 * Inserts data into a table
 *
 * @param <type> $table    The tabel to insert data
 * @param <array> $cols    The columns to update
 * @param <array> $colvals The column values to update
 * @return <mixed>         Returns 0 on success a string on error
 */
function InsertValues($table, $cols, $colvals){
global $dbcon, $query;
	//lock the table to prevent concurrent reads and updates
   $query="SELECT ".implode(',',$cols)." FROM $table FOR UPDATE";
   $result=mysql_query($query, $dbcon);
   if($result===false){
      LogError(); return 'There was an error while fetching data from the database.';
   }

	$col_vals="'".implode("', '",$colvals)."'";
   $query="INSERT INTO $table(".implode(", ",$cols).") VALUES($col_vals)";
   //LogError('Debugging:');
   $result=mysql_query($query, $dbcon);
   if($result===false){
      LogError(); return "There was an error while updating the database.";
   }
   else return 0;
}
//================================================================================================================================

/*Utility function
 * Attempts to insert a new row in a table bt if it finds a duplicate row it updates it
 */
function InsertOnDuplicateUpdate($table, $cols, $colvals){
global $dbcon, $query;
	//lock the table to prevent concurrent reads and updates
   $query="SELECT ".implode(',',$cols)." FROM $table FOR UPDATE";
   $result=mysql_query($query, $dbcon);
   if($result===false){
      LogError(); return 'There was an error while fetching data from the database.';
   }

	$col_vals="'".implode("', '",$colvals)."'";
   if(count($cols)!=count($colvals)){
      LogError('There is an error in your data. The column count for columns and values to be inserted is not the same.');
      return "There is an error in your data. The column count for columns and values to be inserted is not the same.";
   }
   $onUpdate='';
   for($i=0; $i<count($cols); $i++){
      $onUpdate.=($onUpdate=='')?'':', ';
      $onUpdate.="$cols[$i]='$colvals[$i]'";
   }
   $query="INSERT INTO $table(".implode(", ",$cols).") VALUES($col_vals) ON DUPLICATE KEY UPDATE $onUpdate";
   //LogError('Debugging');
   //echo $query.'<br>';
   $result=mysql_query($query, $dbcon);
   if($result===false){
      LogError(); return "There was an error while updating the database.";
   }
   else return 0;
}
//================================================================================================================================

/*USAGE: Inserts multiple rows in a table at an instance
 * colvals is an array containing arrays of the row values
 */
function InsertMultipleValues($table, $cols, $colvals){
global $dbcon, $query;
   //lock the table to prevent concurrent reads and updates
   $query="SELECT ".implode(',',$cols)." FROM $table FOR UPDATE";
   $result=mysql_query($query, $dbcon);
   if(!$result){
      LogError(); return 'There was an error while fetching data from the database.';
   }

   $vals='';
   //print_r($colvals);
   foreach($colvals as $row){
      $vals.=($vals=='')?'':', ';
      $vals.="('".implode("', '", $row)."')";
   }
   $query="INSERT INTO $table(".implode(", ",$cols).") VALUES $vals";
   //echo $query.'<br>';
   $result=mysql_query($query, $dbcon);
   if(!$result){
      LogError(); return "There was an error while updating the database.";}
   else return 0;
}
//================================================================================================================================

//USAGE: inserts a value to e tables n if successful returns e returncols of e inserted row
/*INPUT: table--table in qst, cols-column names wen inserting, colvals--resp column values,
			ordercol--col to use wen pickin vals to return, rteurncols--cols to return*/
//OUTPUT: on error returns a string wit error details else returns an array wit intended values
function InsertReturnLastValues($table, $cols, $colvals, $ordercol, $returncols){
	$results=InsertValues($table, $cols, $colvals);
	if(is_string($results)) return $results;
	$results=LastRowValues($table,$ordercol,$returncols);
	if(is_string($results)){
		//try n delete the last added value
		$result=DeleteLastEntry($table,'id');
		if($result==0) return "There was an error while adding data into the $table table.";	//delete successful
		else return $result;
	}
	else return $results;
}
//================================================================================================================================

//USAGE:
//INPUT:
//OUTPUT:
function LastRowValues($table,$ordercol,$cols){
//this function is used to select the last row in a given table when ordered ascendingly by ordercol. cols are the values to be
//returned and they will be returned in an array
global $dbcon, $query;
   $query="SELECT ".implode(',',$cols)." FROM $table ORDER BY $ordercol DESC LOCK IN SHARE MODE";
   //echo $query.'<br>';
   $result=mysql_query($query, $dbcon);
   if(!$result){
      LogError(); return "There was an error while updating the database.";
   }
   while($row=mysql_fetch_array($result)) break;
   if(!is_array($row))return "There is no data in the $table table.";
   return $row;
}

/**
 * Deletes data from the database
 *
 * @param <string> $table  The table we are deleting from
 * @param <mixed> $col     Can be an array or a string. Specifies the columns we want to use for the search criteria
 * @param <mixed> $colval  Corresponds to the data type of the col parameter
 * @return <mixed>      Returns 0 on successfull delete, else returns a string with the error information
 */
function DeleteItem($table, $col, $colval){
global $dbcon, $query;
	if(is_array($col)){
		$con='';
		for($i=0; $i<count($col);$i++) $con.=($i==0)?$col[$i]."='".$colval[$i]."'":' AND '.$col[$i]."='".$colval[$i]."'";
		$query="SELECT ".implode(',',$col)." FROM $table FOR UPDATE";
	}
	else{
		$con="$col='$colval'";
		$query="SELECT $col FROM $table FOR UPDATE";
	}
	//echo $query.'<br>';
	//lock the table to prevent concurrent reads and updates
   $result=mysql_query($query, $dbcon);
   if(!$result){
      LogError(); return 'There was an error while fetching data from the database.';
   }
   $query="DELETE FROM $table WHERE $con";
   //echo $query.'<br>';
   $result=mysql_query($query, $dbcon);
	//LogError('Debugging: ');
   if(!$result){
      LogError(); return "There was an error while updating the database.";
   }
   else return 0;
}
//================================================================================================================================

/**
 * Fetches a single column value in a given row
 * @global <type> $dbcon
 * @global <type> $query
 * @param <string> $table     The name of the table to fetch the data from
 * @param <string> $toreturn  The column name to return
 * @param <mixed> $col        The column name(s) to be used in the search criteria -- can be an array
 * @param <mixed> $colval     The corresponding value(s) to $col to be used in the search criteria -- can be an array
 * @param <mixed> $operand    The type of comparison to be used to build the query, common operands are =, like
 * @return <mixed>            Returns the column value on successful completion and -2 on an error
 */
function GetSingleRowValue($table, $toreturn, $col, $colval, $operand="="){
global $dbcon, $query;
   if($operand=='') $operand='=';
	if(is_array($col)){
		$con='';
		for($i=0; $i<count($col);$i++) $con.=($i==0)?$col[$i]."$operand '".$colval[$i]."'":' AND '.$col[$i]."$operand '".$colval[$i]."'";
		$query="SELECT $toreturn FROM $table WHERE $con LOCK IN SHARE MODE";
	}
	else $query="SELECT $toreturn FROM $table WHERE $col $operand '$colval' LOCK IN SHARE MODE";
   //echo $query.'<br>';
   $result=mysql_query($query, $dbcon);
   if(!$result){
      LogError(); return -2;
   }
   $row=mysql_fetch_array($result);
   return $row[0];        //this is being returned as a string even if its an integer
}
//================================================================================================================================

//starts a transaction
function StartTrans(){
global $dbcon;
   $result=mysql_query("SET AUTOCOMMIT=0", $dbcon);
   $result=mysql_query("START TRANSACTION", $dbcon);
}
//================================================================================================================================

//commits a transaction after successfull completion of a task
function CommitTrans(){
global $dbcon;
	$result=mysql_query("COMMIT", $dbcon);
   $result=mysql_query("SET AUTOCOMMIT=1", $dbcon);
}
//================================================================================================================================

//rolls back a transaction
function RollBackTrans(){
global $dbcon;
	$result=mysql_query("ROLLBACK", $dbcon);
   $result=mysql_query("SET AUTOCOMMIT=1", $dbcon);
}
//================================================================================================================================

/**
 * Error Handling: Logs the most recent db error or a debugging message/variable
 *
 * @see #GetSingleRowValue($table, $toreturn, $col, $colval, $operand="=")
 * @global <array> $errHandle Contains the debugging settings as set in the config file
 * @global <string> $query    The last successfull completed query
 * @param <string> $error     If supplied and !='Debugging', it contains the error message/data to be logged
 *                            If =='Debugging', means just log the last dbase query that was executed successfully
 */
function LogError($error='', $file=''){
global $errHandle, $query;

   if(is_array($file)) $errHandle=$file;
//   echo "Error: $error<br />";

/*   if($errHandle['logErrors']==false) return;
   $dir=dirname($errHandle['logFile']);

   if(is_dir($dir)){        //the dir exists//check if its writable; if not make it writable
 		if(!is_writable($dir)) {chmod($dir,0766); /*echo 'made it writable';}*/
/*   }
   else{
   	if(!mkdir("$dir/",0766)){
   		$err_occ=1;
   		die("There was an error while creating the folder $dir for saving the error logs.$contact");
   	}
   }*/

   //check whether the file exists and can be written to. if it doest exists create it, if you cant write to it, make it writable
   if(!file_exists($errHandle['logFile'])){
      $fd=fopen($errHandle['logFile'], "wt");
      if(!$fd){
         return -1; //cant be able to create the file
      }
      else fclose($fd);
      if(!is_writable($errHandle['logFile'])){
         if(!chmod($errHandle['logFile'], '777')) return -1;
      }
   }

   $fd=fopen($errHandle['logFile'], 'a');
   if(!$fd) return -1;
   $errorString=date('m.d.y H:i:s: ');
   $err=($error=='')?mysql_errno()." ".mysql_error():$error;

   if(preg_match('/Debugging/',$error)) fputs($fd, "{$errorString}['Debug'] $query\n");
   elseif($error=='') fputs($fd, "{$errorString}['Error'] $err $query\n");
   else fputs($fd, "{$errorString}[Info] $err\n");
   //fputs($fd, "$query;\n");
   fclose($fd);
}
//================================================================================================================================
?>
