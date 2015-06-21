<?php
/**
 * A class containing custom database functions that are used by the various projects
 * 
 * Version 0.6
 *
 * ------------------------------------------------------------------------------------------------------------
 * 
 * <b>Changes to this version</b><br />
 * Implemented the custom authentication
 * Support of LDAP authentication
 *
 * 
 * @category   Database
 * @package    DatabaseFunctions
 * @author     Kihara Absolomon <soloincc@gmail.com>
 * @version    0.6
 */
class DBase extends Config {
   /**
    * A global variable that will hold the currently opened MySQL link identifier
    *
    * @var MySQL_link   The currently opened MySQL link identifier
    */
   public $dbcon;

   /**
    * @var string    A place holder for the last error that occured. Useful for sending data back to the user
    */
   public $lastError;

   /**
    * @var string    A mysql query to be executed
    */
   public  $query;

   /**
    * @var mixed     A place holder which will hold the last results from an executed query
    */
   public $lastResult;

   public $Config;

   /**
    * @var  array    An array where results of session manipulation will be stored. This is because we cannot return meaningful data from the session operations.
    */
   public $session;

   public function  __construct() {
      //create the GeneralClass
      $this->Config = new Config();
      $this->GeneralTasks = new GeneralTasks();
      $this->session = array();
//      echo '<pre>'.print_r($GLOBALS, true).'</pre>';
   }

   /**
    * Initializes the database connection using the credentials inherited from the config class
    *
    * @return  integer  Returns 0 when all went ok, else returns 1 bt the error message is saved in $this->lastError
    */
   public function InitializeConnection() {
//      echo '<pre>'.print_r($config, true).'wtf1</pre>';
      if(!isset ($config)){   //assume we have some inherited config
         if(!isset($this->config)){
            $this->lastError = 'Database settings are missing. Cannot create a dbase connection.';
            $this->CreateLogEntry('Database settings are missing. Cannot create a dbase connection.', 'fatal');
            return 1;
         }
         $config = $this->config;
      }
      
      $this->dbcon = new mysqli($config['dbloc'], $config['user'], $config['pass'], $config['dbase']);
      if($this->dbcon->connect_error) {
         $this->lastError = 'Cannot connect to the database!';
         $this->CreateLogEntry('Cannot connect to the database. '.$this->dbcon->connect_errno." ".$this->dbcon->connect_error, 'fatal');
         return 1;
      }
      $this->query = "SET NAMES 'utf8'";
      if(!$this->dbcon->query($this->query)){
         $this->lastError = 'Error while setting the character set.';
         $this->CreateLogEntry('Error while setting the character set.', 'error', true);
         return 0;
      }
   }

   /**
    * Fetches a value in a specific row
    * 
    * @param string $table     The name of the table to fetch the data from
    * @param string $toreturn  The column name to return
    * @param mixed $col        The column name(s) to be used in the search criteria -- can either be an array or a string
    * @param mixed $colval     The corresponding value(s) to $col to be used in the search criteria -- can either be an array or a string
    * @param string $operand    The type of comparison to be used to build the query, common operands are =, like
    * @return mixed            Returns the column value on successful completion and -2 on an error
    */
   public function GetSpecificValue($table, $toreturn, $col, $colval, $operand = "="){
      if(is_array($col)){
         $con='';
         for($i=0; $i<count($col);$i++) $con.=($i==0)?$col[$i]."$operand '".$colval[$i]."'":' AND '.$col[$i]."$operand '".$colval[$i]."'";
         $this->query="SELECT $toreturn FROM $table WHERE $con LOCK IN SHARE MODE";
      }
      else $this->query="SELECT $toreturn FROM $table WHERE $col $operand '$colval' LOCK IN SHARE MODE";
      //echo $query.'<br>';
      $result=$this->dbcon->query($this->query);
      if(!$result){
         $this->CreateLogEntry("There was an error while fetching data from the $table table", 'fatal', true);
         $this->lastError = "There was an error while fetching data from the database.";
         return 1;
      }
      $this->lastResult = NULL;
      $row = $result->fetch_array(MYSQLI_NUM);
      $this->lastResult = $row[0];
      return 0;        //this is being returned as a string even if its an integer
   }

   /**
    * Inserts data into a table
    *
    * @param string $table    The tabel to insert data
    * @param array $cols      The columns to update
    * @param array $colvals   The column values to update
    * @return int Returns 0 on success else returns 1. In case of an error an error message is @see $lastError added to $this->lastError
    */
   public function InsertData($table, $cols, $colvals) {
      //lock the table to prevent concurrent reads and updates
      /**
       * Review the importance of this piece of code. It greatly reduces perfomance especially when there are many inserts
       *
      $this->query = "SELECT ".implode(',',$cols)." FROM $table FOR UPDATE";
      $result = $this->dbcon->query($this->query);
      if($result===false) {
         $this->CreateLogEntry("There was an error while inserting data into the $table table", 'fatal', true);
         $this->lastError = "There was an error while inserting data into the database.";
         return 1;
      }
      */

      $col_vals = "'" . implode("', '",$colvals) . "'";
      $this->query = "INSERT INTO $table(".implode(", ",$cols).") VALUES($col_vals)";
      $result = $this->dbcon->query($this->query);
      if($result===false) {
         $this->CreateLogEntry("There was an error while inserting data to the $table table.", 'fatal', true);
         $this->lastError = "There was an error while updating the database.";
         return 1;
      }
      else return 0;
   }
   
   /**
    * Adds data to the database but incase of an existing entry, it updates the entry
    * 
    * @param   string   The name of the table that we want to add/update
    * @param   array    An array with the column names to be added/updated
    * @param   array    An array with the corresponding column values
    * @return  integer  Returns 0 when everything goes ok, else it returns 1 and store an error description in $this->lastError
    * @since   v0.4
    */
   public function InsertOnDuplicateUpdate($table, $cols, $colvals){
      //lock the table to prevent concurrent reads and updates
      $this->query = "SELECT ".implode(',',$cols)." FROM $table FOR UPDATE";
      $result = $this->dbcon->query($this->query);
      if($result===false){
         $this->lastError = "There was an error while fetching data from the '$table' table.";
         $this->CreateLogEntry($this->lastError, 'fatal', true);
         return 1;
      }

      $col_vals="'".implode("', '",$colvals)."'";
      $colCount = count($cols); $valCount = count($colvals);
      if($colCount != $valCount){
         $this->lastError = 'There is an error in your data. The column count does not match the values count!.';
         $this->CreateLogEntry($this->lastError);
         return 1;
      }
      $onUpdate='';
      for($i=0; $i < $colCount; $i++){
         $onUpdate.=($onUpdate=='')?'':', ';
         $onUpdate.="$cols[$i]='$colvals[$i]'";
      }
      $this->query = "INSERT INTO $table(".implode(", ",$cols).") VALUES($col_vals) ON DUPLICATE KEY UPDATE $onUpdate";
      $result = $this->dbcon->query($this->query);
      if($result === false){
         $this->lastError = "There was an error while adding/replacing data to the '$table' table.";
         $this->CreateLogEntry($this->lastError, 'fatal', true);
         return 1;
      }
      else return 0;
   }

   /**
    * Executes a query
    *
    * @param   string   $fetchMode  (Optional) The type of array that will be fetched. Can be MYSQ_BOTH, MYSQL_ASSOC, MYSQL_NUM. Defaults to MYSQL_BOTH
    * @return  mixed    A multi-dimensioanl array with the results as fetched from the dbase when successful else returns 1
    */
   public function ExecuteQuery($fetchMode = MYSQLI_BOTH){
      $result = $this->dbcon->query($this->query);
      if(!$result){
         $this->CreateLogEntry("There was an error while fetching data from the $table table.", 'fatal', true);
         $this->lastError = "There was an error while fetching data from the database.";
         return 1;
      }
      $results=array();
      while($row = $result->fetch_array($fetchMode)) array_push($results, $row);
      return $results;
   }

   /**
    * Fetches a single column value in a given row
    *
    * @param string $table     The name of the table to fetch the data from
    * @param string $toreturn  The column name to return
    * @param mixed $col        The column name(s) to be used in the search criteria -- can be an array
    * @param mixed $colval     The corresponding value(s) to $col to be used in the search criteria -- can be an array
    * @param mixed $operand    (Optional) The type of comparison to be used to build the query, common operands are =, like
    * @return mixed            Returns the column value on successful completion and -2 on an error
    */
   public function GetSingleRowValue($table, $toreturn, $col, $colval, $operand = "="){
      if(is_array($col)){
         $con='';
         for($i=0; $i<count($col);$i++) $con.=($i==0)?$col[$i]."$operand '".$colval[$i]."'":' AND '.$col[$i]."$operand '".$colval[$i]."'";
         $this->query = "SELECT $toreturn FROM $table WHERE $con LOCK IN SHARE MODE";
      }
      else $this->query = "SELECT $toreturn FROM $table WHERE $col $operand '$colval' LOCK IN SHARE MODE";

//      echo '<pre>'.print_r($this, true).'</pre>'; die();
      $result = $this->dbcon->query($this->query);
      if(!$result){
         $this->CreateLogEntry("There was an error while fetching data from the $table table.", 'fatal', true);
         $this->lastError = "There was an error while fetching data from the database.";
         return -2;
      }
      $row = $result->fetch_array(MYSQLI_NUM);
      return $row[0];        //this is being returned as a string even if its an integer
   }

   /**
    * Get all the values of only one column in a table
    * This function is just like GetAllColumnValues only that it gets values from a single column only n does not support linked tables
    *
    * @param string $table   The table to fetch data from
    * @param string $col     The column to fetch values from
    * @param bool $order     Whether to order the fetch values or not. We shall use the column being selected for ordering
    * @param mixed $criteria (Optional)The criteria to be used when executing the query
    * @return mixed Returns an array with the found values on success and 1 on error
    */
   public function GetSingleColumnValues($table, $col, $order, $criteria = null){
      if($order) $ordering = " order by $col";
      else $ordering='';
      if(is_array($criteria)){
         $columns=$criteria[0]; $vals=$criteria[1];
         $criteria='';
         for($i=0;$i<count($columns);$i++){
            $criteria.=($i==0)?'WHERE ':' AND ';
            $criteria.=$columns[$i]."='".$vals[$i]."'";
         }
      }
      elseif($criteria==null || $criteria==false || $criteria=='') $criteria = '';
      else $criteria = "where $criteria";

      $this->query = "SELECT $col FROM $table $criteria $ordering LOCK IN SHARE MODE";
      $result = $this->dbcon->query($this->query);
      if(!$result){
         $this->CreateLogEntry("There was an error while fetching data from the $table table.", 'fatal', true);
         $this->lastError = "There was an error while fetching data from the database.";
         return 1;
      }
      $results = array();
      while($row = $result->fetch_array(MYSQLI_NUM)) array_push($results, $row[0]);
      return $results;
   }

   /**
    * Fetches specific rows from the table using the search criteria
    *
    * @param string $table     The table to fetch the data from
    * @param mixed $cols       Either an array of the columns that we want returned or the column name that we are interested in
    * @param mixed $criteria   Either a string or array. If its an array it holds an array of columns and their values to be used in the search
    * @param mixed $fetchMode  (Optional)The mode that will be used to fetch data, can be MYSQL_ASSOC, MYSQL_NUM or MYSQL_BOTH. The default is MYSQL_BOTH
    * @return mixed      Returns an multi-dimensions array with the results on sucess, else it returns a string with the error that occured
    */
   public function GetColumnValues($table, $cols, $criteria, $fetchMode=MYSQLI_BOTH, $join = 'AND'){
      if(is_array($criteria)) {
         $columns=$criteria[0];
         $vals=$criteria[1];
         $criteria='';
         for($i=0;$i<count($columns);$i++) {
            $criteria.=($i==0)?'WHERE ':" $join ";
            $criteria.=$columns[$i]."='".$vals[$i]."'";
         }
      }
      if(is_array($cols)) $this->query = "SELECT ".implode(',',$cols)." FROM $table $criteria LOCK IN SHARE MODE";
      else $this->query = "SELECT $cols FROM $table $criteria LOCK IN SHARE MODE";
      $result = $this->dbcon->query($this->query);
      //LogError('Debugging');
      if(!$result) {
         $this->CreateLogEntry("There was an error while fetching data from the $table table.", 'fatal', true);
         $this->lastError = "There was an error while fetching data from the database.";
         return 1;
      }
      if($result->num_rows==0) return array();
      $results = array();
      while($row = $result->fetch_array($fetchMode)) array_push($results, $row);
      return $results;
   }

   /**
    * updates row(s) in a table
    *
    * @param   string   $table         Table to update the data
    * @param   mixed    $cols          The columns to update can be a single column or cols in an array
    * @param   mixed    $colvals       The column values to update can be a single column or cols in an array
    * @param   mixed    $conditioncol  The columns to be used in the where criteria
    * @param   mixed    $conditionval  The column values to be used in the criteria
    * @return  integer  Returns 0 on success or 1 on error
    */
   public function UpdateRecords($table, $cols, $colvals, $conditioncol, $conditionval){
      //create the conditions incase there are multiple conditions
      if(is_array($conditioncol)){
         $condition='';
         for($i=0; $i < count($conditioncol); $i++){
            $condition.=($i==0)?$conditioncol[$i]."='".$conditionval[$i]."'":' AND '.$conditioncol[$i]."='".$conditionval[$i]."'";
         }
      }
      else $condition="$conditioncol = '$conditionval'";

      $col_vals='';
      if(is_array($cols)){
         for($i=0; $i<count($cols); $i++){
            $col_vals.=($i==0)?$cols[$i]."='".$colvals[$i]."'":",".$cols[$i]."='".$colvals[$i]."'";
         }
         //lock the table to prevent concurrent reads and updates
         $this->query = "SELECT ".implode(',',$cols)." FROM $table FOR UPDATE";
      }
      else{
         $col_vals = "$cols = '$colvals'";
         //lock the table to prevent concurrent reads and updates
         $this->query = "SELECT $cols FROM $table FOR UPDATE";
      }
      $result = $this->dbcon->query($this->query);
      if(!$result){
         $this->CreateLogEntry("There was an error while fetching data from the $table table.", 'fatal', true);
         $this->lastError = "There was an error while fetching data from the database.";
         return 1;
      }

      $this->query = "UPDATE $table SET $col_vals WHERE $condition";
      $result = $this->dbcon->query($this->query);
      if(!$result){
         $this->CreateLogEntry("There was an error while fetching data from the $table table.", 'fatal', true);
         $this->lastError = "There was an error while fetching data from the database.";
         return 1;
      }
      return 0;
   }

   /**
    * Deletes data from the database
    *
    * @param string  $table  The name of the table we are deleting from
    * @param mixed   $col     Can be an array or a string. Specifies the columns we want to use for the search criteria
    * @param mixed   $colval  Corresponds to the data type of the col parameter
    * @return mixed      Returns 0 on successfull delete, else returns a string with the error information
    */
   function DeleteData($table, $col, $colval){
      if(is_array($col)){
         $con='';
         for($i=0; $i<count($col);$i++) $con.=($i==0)?$col[$i]."='".$colval[$i]."'":' AND '.$col[$i]."='".$colval[$i]."'";
         $this->dbcon->query = "SELECT ".implode(',',$col)." FROM $table FOR UPDATE";
      }
      else{
         $con="$col='$colval'";
         $this->dbcon->query = "SELECT $col FROM $table FOR UPDATE";
      }
      //echo $query.'<br>';
      //lock the table to prevent concurrent reads and updates
      $result = $this->dbcon->query($this->query);
      if(!$result){
         $this->CreateLogEntry("There was an error while fetching data from the $table.", 'fatal', true);
         $this->lastError = 'There was an error while fetching data from the database.';
         return 1;
      }
      $this->dbcon->query = "DELETE FROM $table WHERE $con";
      $this->CreateLogEntry($this->dbcon->query);
      $result = $this->dbcon->query($this->dbcon->query);
      if(!$result){
         $this->CreateLogEntry("There was an error while deleting from the $table.", 'fatal', true);
         $this->lastError = 'There was an error while updating the database.';
         return 1;
      }
      return 0;
   }

   /**
    * Checks if a box is defined in the database. If it is not, it adds it with the passed parameters. If its there it is updated
    *
    * @param   string   $name             The name of the box
    * @param   string   $size             The size of the box in the format '[A-Z]:[0-9]+.[A-Z]:[0-9]+'
    * @param   string   $type             The box type. Its mostly a box
    * @param   integer  $keeper           An id of the person responsible for this box
    * @param   string   $features         (Optional) A description of the box
    * @param   integer  $location         (Optional) The id of the location where the box is stored
    * @param   string   $rack             (Optional) The rack name where the box is located
    * @param   integer  $rack_position    (Optional) The position of the box in the rack
    * @return  mixed    Returns the added or updated box id if all goes ok, or a string with an error message if something fails
    */
   public function AddNewTray($dbase, $name, $size, $type, $keeper, $box_features = NULL, $location = NULL, $rack = NULL, $rack_position = NULL) {
      if($name=='' || !isset($name)) return 'Cannot add an empty box name!!';

      //fromat the cols and col vals
      $cols = array('box_name', 'size', 'box_type', 'keeper');
      $colvals = array($name, $size, $type, $keeper);
      $addons = array('box_features', 'location', 'rack', 'rack_position');
      foreach($addons as $t) {
         if(isset($$t)) {
            $cols[] = $t;  //add the column name
            $colvals[] = $$t; //add the value passed with this column name
         }
      }

      $res = $this->GetSingleRowValue("$dbase.boxes_def", 'box_id', 'box_name', $name);
      if($res == -2) return $this->lastError;
      elseif(isset($res)) { //the box is already defined, so just update the data
         $boxId = $res+0;  //convert it to an integer
         $res = $this->UpdateRecords("$dbase.boxes_def", $cols, $colvals, 'box_id', $boxId);
         if($res) return $this->lastError;
         //all is ok, return the last inserted id
         return $boxId;
      }
      elseif(is_null($res)) {  //we dont have this box so add it
         $res = $this->InsertData("$dbase.boxes_def", $cols, $colvals);
         if($res) return $this->lastError;
         //all is ok, return the last inserted id
         return $this->dbcon->insert_id;
      }
      else return 'unknown option';
   }
   
   /**
    * Adds a new sample type to the labcollector's database.
    *
    * @param string $dbase       The name of the database that we are going to add the samples to
    * @param string $sampleName  The name of the sample name that we are going to add
    * @return mixed  Return a string with the error message in case an error occurs, else it returns 0, meaning the sample type has been successfully added
    * @since v0.3
    */
   public function AddSampleType($dbase, $sampleName) {
      if($sampleName=='' || !isset($sampleName)) return 'Cannot add an empty sample name!!';
      //check if the sample type is already defined
      $curSample = $this->GetSingleRowValue("$dbase.sample_types_def", 'count', 'sample_type_name', $sampleName);
      if($curSample == -2) return $this->lastError;
      elseif(is_string($curSample)) return 0;  //the sample is already added to the database
      
      $res = $this->InsertData("$dbase.sample_types_def", array('sample_type_name'), array($sampleName));
      if($res == 1) return 1;
      return 0;
   }

   /**
    * Fetches all the defined sample type from labcollector's database
    *
    * @param   string   $dbase The name of the database that we will fetch data from
    * @return  mixed    Returns a string with an error message in case an error occurs, else it returns an array with all the sample types
    * @since v0.3
    */
   public function GetAllSampleTypes($dbase) {
      $this->query = "select * from $dbase.sample_types_def";
      $res = $this->ExecuteQuery(MYSQLI_ASSOC);
      if($res==1) return 1;
      $allSampleTypes = array();
      foreach($res as $t) $allSampleTypes[$t['sample_type_name']] = $t['count'];
      return $allSampleTypes;
   }

   /**
    * Links a sample with its parent the LabCollector's way
    *
    * @param string $dbase       The name of the database where the changes are going
    * @param integer $parentId   The id of the parent sample
    * @param integer $childId    The id of the child sample
    * @param strung $parentType  The module to which the parent belongs: can be Sample, Primer
    * @param string $childType   The module to which the child belongs to: can be Sample, Primer
    * @return integer   Returns 0 in case all went ok, else it returns 1
    * @since v0.3
    */
   public function LinkParentWithChildren($dbase, $parentId, $childId, $parentType, $childType){
      $moduleTypes = array(
         'Sample' => 'SP',
         'Primer' => 'PR'
      );
      $cols = array('module_from', 'id_from', 'module_to', 'id_to');
      $colvals = array($moduleTypes[$parentType], $parentId, $moduleTypes[$childType], $childId);
      $res = $this->InsertData("$dbase.modules_relation", $cols, $colvals);
      if($res) return 1;
      return 0;
   }

   /**
    * Validates and adds opttional data to an array of data to be added to the database
    * 
    * Not all columns in a table must be set. Some of these columns are optional and from the user input we must determine whether to add them or not.
    * This function allows us to determine if a value is set and if it is and it is not empty, it is escaped and added to a list of values to be
    * inserted to the database.
    * 
    * Brief example of use:
    * 
    * <code>
    * //create the array with the data to be added
    * $optionalData = array(
    *     'other_type' => array('column_name' =>'sample_type'),
    *     'other_source' => array('column_name' =>'sample_source'),
    *     'contact_person' => array('column_name' =>'contact_person'),
    *     'sample_location' => array('column_name' =>'storage_location'),
    *     'box_name' => array('column_name' =>'box_name'),
    *     'box_pos' => array('column_name' =>'position')
    * );
    * MapOptionalDatabaseData($optionalData);
    * </code>
    * 
    * @param   array    $data    A multi-dimensional array with the data. The keys are the html field names, the value is an array having the column names.
    * @return  array    Returns a multi dimensional array with the column names and values to be added to the database.
    * @since v0.4
    */
   public function MapOptionalDatabaseData($data){
      //loop thru the input array and add all this data
      $additionalCols = array(); $additionalColVals = array();
      foreach($data as $key => $value){
         if(isset($_POST[$key]) && $_POST[$key] != ''){
            $additionalCols[] = $value['column_name'];
            if(is_string($_POST[$key])) $additionalColVals[] = $this->dbcon->real_escape_string($_POST[$key]);
            else $additionalColVals[] = $_POST[$key];
         }
      }
      return array('cols' => $additionalCols, 'colvals' => $additionalColVals);
   }

   /**
    * Creates a new log entry into the logs
    *
    * There can be different log levels as defined in settings.<br/>
    * If the message is empty and $logMysqlError is set to true, it logs the last MySQL error encountered or the last sql statement executed as a debug log
    * If only the message is set, then an entry is added to the info logs
    *
    * @param   string   $message       (Optional) The message that we want to log
    * @param   string   $level         (Optional) The level of the message. if not defined, it defaults to an info log
    * @param   boolean  $logMysqlError (Optional) Determines whether to log the last MySQL error encountered and the last sql statement executed
    * @return  integer  Returns a custom 0 as there is nothing more to do
    * @todo    Add a log rotation whenever the log size limit is reached
    */
   public function CreateLogEntry($message, $level='', $logMysqlError=''){
      if(!isset($this->logSettings) || !$this->logSettings['logErrors']){  //if not set, it means that no logs are required so just return
         return 0;
      }
      $curLogFile = '';
      if($this->logSettings['combined'])   $curLogFile = 'all_logs.log';    //combine all the errors in one
      else{
         if($level=='' && $logMysqlError) $level = 'debug';
         elseif($level=='') $level = 'info';   //if level is not specified and we are not logging mysql errors, all logs are considered as info
         $curLogFile = $this->logLevels[$level][1];
      }
      $curLogFile = "{$this->logSettings['logFileDir']}/$curLogFile";
      
      //we are all set to log
//      echo "<pre>{$this->query}</pre>";
//      echo "<pre>Level: $level, File: $curLogFile, Message: $message</pre>";
//      echo "<pre>".print_r($this->logLevels[$level], true)."</pre>";
      if(!$fd = fopen($curLogFile, 'a')) return 0;
      $messageString=date('Y-m-d H:i:s: ');
      //if we dont have any message and $logMysqlError is set to true, then log the last mysql error
      if($message=='' && $logMysqlError) $err = "({$this->dbcon->errno}) {$this->dbcon->error}\nLast Query: {$this->query}";
      elseif($logMysqlError) $err = "$message\nMySQL Error and String: ({$this->dbcon->errno}) {$this->dbcon->error}\nLast Query: {$this->query}";
      else $err = $message;

      if($this->logLevels[$level][0] == '') fputs($fd, "$err\n");
      else fputs($fd, "{$messageString}{$this->logLevels[$level][0]} $err\n\n");
      fclose($fd);
      return  0;
   }

   /**
    * Truncates or initializes log files
    *
    * @global  array    $logSettings   The log settings as defined in the config file
    * @global  object   $GeneralTasks  An object with the general tasks. Goes hand in hand with the dbase module
    * @global  array    $logLevels     An array defining the various levels of logs
    * @param   array    $logs          (Optional) An array with the log file names that we are to work on, defaults to $this->logLevels
    * @param   bool     $truncate      (Optional) Whether to truncate the files or just initialize them, defaults to false
    * @return  int      Returns 0 on an error, I dont know what it returns when all is ok.
    */
   public function InitializeLogs($logs = NULL, $truncate = false){
      if(!isset($this->logSettings) || !$this->logSettings['logErrors']){  //if not set, it means that no logs are required so just return
         return 0;
      }
      if(!isset($this->logSettings['logFileDir']) || $this->logSettings['logFileDir'] ==''){
         unset($this->logSettings); //disable logging
         return 0;   //the logs file dir must be set, else no logs
      }
      if(!is_dir($filename)){    //the dir doesnt exist, create one
         $res = $this->GeneralTasks->CreateDirIfNotExists($this->logSettings['logFileDir']);
         if(!$res) return 0;     //there was an error in creating the directory, hence no logs will be saved
      }

      if(!isset($logs)){
         $logs = array();
         foreach($this->logLevels as $t) $logs[] = $t[1];
      }
      foreach($logs as $curLogFile){
         $curLogFile = "{$this->logSettings['logFileDir']}/$curLogFile";
         //check whether the file exists and is writable. if it doest exists create it, if its not writable, make it writable
         if(!file_exists($curLogFile)) {
            $fd=fopen($curLogFile, "wt");
            if(!$fd) {
               return 0; //cant be able to create the file
            }
            else fclose($fd);
            if(!is_writable($curLogFile)) {
               if(!chmod($curLogFile, '644')) return 0;
            }
         }
         if($truncate) $this->GeneralTasks->TruncateFile($curLogFile);
      }
   }

   /**
    * Given a username and a password, confirms whether the user has privileges to access the system or not
    * 
    * Prior to v0.6, Dbase.ConfirmUser confirmed whether a user had privileges to access the system and fetched the necessary data from the database.
    * Since different systems have different dbase structures and they want to fetch different sets of data, the roles of this function are split
    * into two, leaving the confirming of whether a user has privileges or not to this function.
    *
    * @param   string   $username         The username specified by the user
    * @param   string   $password         The password(md5'd) entered for this page.
    * @return  mixed    Returns 1 incase of a fatal error, 2 incase the user has no privileges or 0 in case all is ok.
    *                   The necessary data is saved in the session array
    * @since   v0.5
    */
   public function ConfirmUser($username, $password){
      $username = $this->dbcon->real_escape_string($username);
      $query = "select a.id from {$this->config['session_dbase']}.users as a 
               inner join {$this->config['session_dbase']}.user_levels as b on a.user_level=b.id";

      if(isset($this->psswdSettings['useSalt']) && $this->psswdSettings['useSalt'])
         $this->query = "$query WHERE a.login='$username' AND psswd=sha1(concat('$username',a.salt,'$password')) AND a.allowed=1";
      else  $this->query = "$query WHERE a.login='$username' AND psswd='$password' AND a.allowed=1";
//      echo '<pre>'.$this->query.'</pre>';

      $result = $this->ExecuteQuery(MYSQL_ASSOC);
      if($result == 1){
         $this->CreateLogEntry("There was an error while fetching data from the database.", 'fatal', true);
         $this->lastError = "There was an error while fetching data from the session database.<br />Please try again later.";
         return 1;
      }

      if(count($result) == 0) return 2;  //we have no user with the supplied credentials
      return 0;
   }

   /**
    * Checks if the username is allowed to log in via the AD
    *
    * @param   string   $username   The user we interested in
    * @return  mixed    Returns a string with the error message in case an error occured, else it returns 0 if allowed, 1 if not allowed if nothing to do
    * @since   v0.5
    */
   public function InWhiteList($username){
      $this->query = "select id from {$this->config['session_dbase']}.users where login='$username' and ldap_authentication=1 and allowed=1";
      $res = $this->ExecuteQuery(MYSQLI_ASSOC);
//      echo '<br />Count -- '.count($res).'<br />';
      if($res == 1) return "There was an error while fetching data from the database!";
      elseif(count($res) == 0) return 1;
      elseif(count($res) == 1){
         $_SESSION['project'] = $res[0]['project'];
         return 0;
      }
      else return -2;
   }

   /**
    * Given a username and password, it performs the AD authentication for the user
    *
    * @param   string   $username   The username to use to authenticate
    * @param   string   $password   The password
    * @return  mixed    Returns a string with an error message in case of an error, 1 if the user credentials are bad or 0 if all is ok
    * @since   v0.5
    */
   private function ADAuthenticate($username, $password){
//      echo '<pre>'.print_r($this->Config, true).'</pre>';
      $this->ldapConnection = ldap_connect($this->Config->ldapHost, $this->Config->ldapPort);
      if (!$this->ldapConnection) {
         $this->CreateLogEntry('Could not connect to the AD server!', 'fatal');
         return "There was an error while connecting to the the AD server for authentication!<br />" . $this->Config->contact;
      } else {
         $this->ldapConnection = ldap_connect($this->ldapHost, $this->ldapPort);
         if (!$this->ldapConnection)
            return "Could not connect to the LDAP host";
         else {
            if (ldap_bind($this->ldapConnection, "$username@ilri.cgiarad.org", $password)) {
               ldap_set_option($this->ldapConnection, LDAP_OPT_REFERRALS, 0);
               ldap_set_option($this->ldapConnection, LDAP_OPT_PROTOCOL_VERSION, 3);
               $ldapSr = ldap_search($this->ldapConnection, 'ou=ILRI Kenya,dc=ilri,dc=cgiarad,dc=org', "(sAMAccountName=$username)", array('sn', 'givenName', 'title'));
               if (!$ldapSr) {
                  $this->CreateLogEntry('Connected successfully to the AD server, but cannot perform the search!', 'fatal');
                  return "There was an error while searching the AD server for you!<br />" . $this->Config->contact;
               }
               $entry1 = ldap_first_entry($this->ldapConnection, $ldapSr);
               if (!$entry1) {
                  $this->CreateLogEntry('Connected successfully to the AD server, but there was an error while searching the AD!', 'fatal');
                  return "Invalid username or password(AD)!<br />If your credentials are correct, maybe you do not have sufficient privileges to access the system.<br />" . $this->Config->contact;
               }
               $ldapAttributes = ldap_get_attributes($this->ldapConnection, $entry1);
               $_SESSION['username'] = $username; $_SESSION['surname'] = $ldapAttributes['sn'][0]; $_SESSION['onames'] = $ldapAttributes['givenName'][0];
               $_SESSION['user_level'] = $ldapAttributes['title'][0];
               return 0;
            }
            else {
               $this->CreateLogEntry("There was an error while binding user '$username' to the AD server!", 'fatal');
               return 1;
            }
         }
      }
   }

   /**
    * Adds a user to the whitelist, ie allows a user to login via AD
    *
    * @param   array    $userAttributes   An array with the user attributes to add to the database
    * @return  integer  Returns 1 in case of an error and the error is added to Dbase->lastError, else it returns 0 if all is ok
    * @since   v0.5
    */
   public function Add2WhiteList($userAttributes){
      $cols = array('login', 'sname', 'onames', 'salt', 'user_level', 'ldap_authentication', 'allowed');
      $colvals = array($userAttributes['username'], $userAttributes['sn'], $userAttributes['givenName'], $userAttributes['title'],
          $userAttributes['level'], 1, $userAttributes['allowed']);
      $res = $this->Dbase->InsertData($this->config['session_dbase'].'.users', $cols, $colvals);
      if($res) return $this->Dbase->lastError;
      else return 0;
   }

   /**
    * Given the username and password does th eauthentication of the user to the system. First checks the custom authentication module and then the AD
    *
    * @param   string   $username   The username of the user
    * @param   string   $password   The password of the user
    * @return  mixed    Returns a string with an error message in case there is an error, 1 if the user is not allowed to log in and 0 if the user credentials are ok
    * @since   v0.5
    * @todo    Fix the LDAP authentication! Dropped dead over the weekend n am bila clue wats happening
    */
   public function Authenticate($username, $password){
      //lets start by custom authentication
//      echo '<pre>'.print_r($this, true).'</pre>';
//      echo "<pre>$username -- $password</pre>";
      $res = $this->ConfirmUser($username, md5($password));
      if($res == 0) return 0;    //all is ok and the user has been correctly been id'd
      elseif($res == 1) return $this->lastError;   //we have some error
      elseif($res == 2 && $this->config['ldap_authenticate']){
         //the user could not be logged in using the custom login method, lets try the AD way
         $res = $this->InWhiteList($username);
         if(is_string($res)) return $res;
         elseif($res == 1) return 1;   //the user is not allowed to log in via AD and is not authenticated via the custom method
         elseif($res == 0){   //can log in via AD, so lets try and log in
            //THIS IS NOT WORKING FOR NOW...WE MOVE ON SWIFTLY
//            return 1;
            $res = $this->ADAuthenticate($username, $password);
            if(is_string($res)) return $res;
            elseif($res == 1) return 1;   //the user credentials are not ok, and he cant log in via the AD
            elseif($res == 0) return 0;   //all is ok, he has the damn tight credentials
         }
      }
      else return 1;    //cannot authenticate
   }

   /**
    * Binds the various session functions to specific functions in this class
    *
    * @since   v0.5
    */
   public function SessionStart(){
      $res = session_set_save_handler(array(&$this, 'OpenSession'), array(&$this, 'CloseSession'), array(&$this, 'ReadSession'),
              array(&$this, 'WriteSession'), array(&$this, 'DestroySession'), array(&$this, 'CleanSession'));
      if(!$res){
         $this->CreateLogEntry("Cannot set the session handlers!", 'fatal');
         return 1;
      }
      session_start();
   }

   /**
    * Does nothing, but it is required by session_set_save_handler
    *
    * @param   array    The path where the session will be saved. In this case it is the table name in the database
    * @param   string   The name of the session that we are dealing with
    * @return  boolean  It just returns true
    * @since   v0.5
    */
   public function OpenSession($sessionSavePath, $sessionName) {
//      echo "<pre>session.open: ".print_r($this, true).'</pre>';
      return true;
   }

   /**
    * Closes a session and in the processes releases the mysql dbase connection that was created
    *
    * @return  mixed    Incase the dbase connection was not opened it returns true as expected by the session_set_save_handler,
    *                   else returns it returns the results of mysqli_close
    * @since   v0.5
    */
   public function CloseSession() {
//      echo "<pre>session.close: ".print_r($Session, true).'</pre>';
      if($this->dbcon == NULL) return true;
      return $this->dbcon->mysqli_close();
   }

   /**
    * Looks for session details in the database
    *
    * @param   string   $sessionId  The session id that we are interested in
    * @return  string   Returns an empty string if there is nothing to read, else returns the data that we are looking for
    * @since   v0.5
    */
   public function ReadSession($sessionId) {
      $this->Dbase->session = array();
      //check that the session is not timed out
      $this->Dbase->query = "SELECT updated_at, data FROM {$this->Dbase->config['session_dbase']}.sessions WHERE session_id='$sessionId'";
      $result = $this->Dbase->ExecuteQuery(MYSQLI_ASSOC);
      if($result == 1){      //we have an error which is already logged. jst return a string as required
         $this->Dbase->session['error'] = true;
         $this->Dbase->session['message'] = "There was an error while fetching data from the database. Please try again later.";
         return '';
      }

      if(count($result) == 0){   //we dont have an initialized session
         $this->Dbase->session['no_session'] = true;
         return '';
      }
      else{
         //check that the user session time aint expired, if it has log the user out.
         $result = $result[0];
         $now = strtotime(date('Y-m-d H:i:s'));
         $updatedAt = strtotime($result['updated_at']);
         $secsDiff = $now - $updatedAt;
         $elapsed['days'] = floor($secsDiff/86400);
         $elapsed['hours'] = floor(($secsDiff - ($elapsed['days']*86400))/3600);
         $elapsed['minutes'] = floor(($secsDiff - (($elapsed['days']*86400) + ($elapsed['hours']*3600)))/60);
         $elapsed['seconds'] = floor(($secsDiff - (($elapsed['days']*86400) + ($elapsed['hours']*3600) + ($elapsed['minutes']*60))));
         $this->Dbase->session['elapsed'] = $elapsed;

         if(floor($secsDiff/60) > $this->timeout){
            $this->Dbase->session['timeout'] = true;
            $timeIn = '';
            foreach($elapsed as $key => $value){
               if($value != 0){
                  if($timeIn != '') $timeIn .= ', ';
                  $timeIn .= "$value $key";
               }
            }
            //we have a session which has already expire. Try and remove junk from the database and then return an error message
            $this->CleanSession($this->timeout);
            $this->Dbase->session['message'] = "You have been logged in for $timeIn and you were automatically logged out.<br />Please log in again.";
            return '';
         }
         return $result['data'];
      }
      return '';
   }

   /**
    * Updates the session data in the database, if the session data is already in the database, else it inserts a new record
    *
    * @param   string   $sessionId  The id of the session that we interested in
    * @param   string   $data       The data that we are adding
    * @return  boolean  Returns true when the data has been successfully been added/updated, else returns false
    * @since   v0.5
    */
   public function WriteSession($sessionId, $data) {
//      var_dump($this->Dbase->dbcon->real_escape_string($data));
//      echo "<pre>session.write: $sessionId ".print_r($data, true).'</pre>';
      if($data == '' || !isset($data)) return '';
      //also ensure that incase the session_id is the same and bt the incoming data differs frm the data in the dbase--coming from the same computer
      $time = date('Y-m-d H:i:s');
      $this->Dbase->query = "REPLACE INTO ".$this->config['session_dbase'].".sessions(session_id, data, updated_at) VALUES ('".
      $this->Dbase->dbcon->real_escape_string($sessionId)."', '".$this->Dbase->dbcon->real_escape_string($data)."','$time')";
//      echo "<pre>session.write: $sessionId ".$this->Dbase->query.'</pre>';
      $result = $this->Dbase->dbcon->query($this->Dbase->query);
      if(!$result){     //there is nothing to do at this stage
         $this->Dbase->CreateLogEntry('There was an error while inserting data to the session database', 'fatal', true);
         return false;
      }
      return true;
   }

   /**
    * Deletes the session id data from the database
    *
    * @param   string   $sessionId  The session id that we want deleted from the database
    * @return  boolean  Returns true when all is ok, or false when there was an error as instructed by the manuals!
    * @since   v0.5
    */
   public function DestroySession($sessionId) {
//      echo "<pre>destroy, sessionId = $sessionId: ".print_r($this, true).'</pre>';
      $this->Dbase->query = "DELETE FROM {$this->Dbase->config['session_dbase']}.sessions WHERE session_id='$sessionId'";
      $result = $this->Dbase->dbcon->query($this->Dbase->query);
      $_SESSION = array();
      if(!$result){  //there is nothing we can do apart from logging this error
         $this->Dbase->CreateLogEntry('There was an error while inserting data to the session database', 'fatal', true);
         return false;
      }
      return true;
   }

   /**
    * Clears the session table of garbage, which is generated when a session expires and the system doesnt have the chance to clear the data
    *
    * @param   integer  $expiryInterval   The time in minutes, of which sessions which are older than this will be deleted!
    * @return  bool     Returns true when all is ok, or false when there was an error as instructed by the manuals!
    * @since   v0.5
    */
   public function CleanSession($expiryInterval) {
//      echo "<pre>clean, sessions older than = $expiryInterval: ".print_r($this, true).'</pre>';
      $this->Dbase->query = "DELETE FROM {$this->Dbase->config['session_dbase']}.sessions WHERE DATE_ADD(updated_at, INTERVAL "
         .(int) $expiryInterval." SECOND) < NOW()";
      $result = $this->Dbase->dbcon->query($this->Dbase->query);
      if(!$result){  //there is nothing we can do apart from logging this error
         $this->Dbase->CreateLogEntry('There was an error while inserting data to the session database', 'fatal', true);
         return false;
      }
      return true;
   }

   /**
    * Manage session data...either logs out a user if the time has expired, or restarts a session in case there is need
    */
   public function ManageSession(){
//      echo "<pre>manage session: ".print_r($this->session, true).'</pre>';
      $this->session['restart'] = false;
      if(isset($this->session['timeout']) && $this->session['timeout']){
         $this->LogOut();
         $this->session['restart'] = true;
      }
      if(isset($this->session['no_session']) && $this->session['no_session']) $this->session['restart'] = true;
      if(isset($this->session['error']) && $this->session['error']) $this->session['restart'] = true;
   }

   /**
    * Logs a user out of the system
    */
   public function LogOut(){
      //delete the data from the database
      if(isset($_COOKIE[session_name()])) setcookie(session_name(), '', time()-42000, '/');     //expire the cookie
      if(isset($_SESSION['username'])) session_destroy();   //destroy the session
   }

   /**
    * Start a database transaction. This can only be applied to innodb databases
    *
    * @return  bool  Returns true in case all went ok, else returns false
    * @since   v0.6
    */
   public function StartTrans(){
      $result = $this->Dbase->dbcon->query("SET AUTOCOMMIT=0");
      if(!$result){
         $this->Dbase->CreateLogEntry('There was an error while starting a database session.', 'fatal', true);
         return false;
      }
      $result = $this->Dbase->dbcon->query("START TRANSACTION");
      if(!$result){
         $this->Dbase->CreateLogEntry('There was an error while starting the session.', 'fatal', true);
         return false;
      }
      return true;
   }

   /**
    * Commits an already started transaction. Applies only to innodb databases
    *
    * @return  bool  Returns true in case all went ok, else returns false
    * @since   v0.6
    */
   public function CommitTrans(){
      $result = $this->Dbase->dbcon->query("COMMIT");
      if(!$result){
         $this->Dbase->CreateLogEntry('There was an error while starting a database session.', 'fatal', true);
         return false;
      }
      $result = $this->Dbase->dbcon->query("SET AUTOCOMMIT=1");
      if(!$result){
         $this->Dbase->CreateLogEntry('There was an error while restoring autocommit option.', 'fatal', true);
         return false;
      }
      return true;
   }

   /**
    * Rolls back a db transaction. Applies to only db databases
    *
    * @return  bool  Returns true in case all went ok, else returns false
    * @since   v0.6
    */
   public function RollBackTrans(){
      $result = $this->Dbase->dbcon->query("ROLLBACK");
      if(!$result){
         $this->Dbase->CreateLogEntry('There was an error while starting a database session.', 'fatal', true);
         return false;
      }
      $result = $this->Dbase->dbcon->query("SET AUTOCOMMIT=1");
      if(!$result){
         $this->Dbase->CreateLogEntry('There was an error while restoring autocommit option.', 'fatal', true);
         return false;
      }
      return true;
   }
}
?>