<?php
/**
 * Database class containing all the custom database functions
 *
 * This is an improvement of the previous functions that were used in <a href="../common/dbase_functions.php">Old_Functions</a> @link Old_Functions
 * @author Kihara Absolomon <soloincc@movert.co.ke>
 */

require_once 'mod_general_v0.1.php';

/**
 * A Class with all the custom dbase functions
 *
 * @property DBase $Dbase
 */
class DBase extends GeneralTasks {
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
    *
    * @var string    A mysql query to be executed
    */
   public  $query;

   /**
    *
    * @var mixed     A place holder which will hold the last results from an executed query
    */
   public $lastResult;

   public $entry;
   public $exit;

   private $logSettings;

   private $logLevels;

   public $GeneralTasks;

   public function  __construct($logSettings, $logLevels) {
      $this->logSettings = $logSettings;
      $this->logLevels = $logLevels;
      $this->GeneralTasks = new GeneralTasks();
   }

   public function InitializeConnection($config) {
//      echo '<pre>'.print_r($config, true).'wtf1</pre>';
      if(!isset ($config)){
         $this->CreateLogEntry('Database settings are missing. Cannot create a dbase connection.', 'fatal');
         return 1;
      }
      
      $this->dbcon = new mysqli($config['dbloc'], $config['user'], $config['pass'], $config['dbase']);
      if($this->dbcon->connect_error) {
         $this->CreateLogEntry('Cannot connect to the database. '.$this->dbcon->connect_errno." ".$this->dbcon->connect_error, 'fatal');
         return 1;
      }
      $this->query = "SET NAMES 'utf8'";
      if(!$this->dbcon->query($this->query)){
         $this->CreateLogEntry('Error while setting the character set.', 'error', true);
         return 0;
      }
//      $this->CreateLogEntry('Successfully created a dbase connection and changed the connection\' character set to utf8.');
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
    * Executes a query
    *
    * @param string $fetchMode   (Optional) The type of array that will be fetched. Can be MYSQ_BOTH, MYSQL_ASSOC, MYSQL_NUM. Defaults to MYSQL_BOTH
    * @return mixed      A multi-dimensioanl array with the results as fetched from the dbase when successful else returns 1
    */
   public function ExecuteQuery($fetchMode = MYSQLI_BOTH){
      $result = $this->dbcon->query($this->query);
      if(!$result){
         $this->CreateLogEntry("There was an error while fetching data from the $table table.", 'fatal', true);
         $this->lastError = "There was an error while fetching data from the database.";
         return 1;
      }
      $results=array();
//      var_dump($result);
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
    * @param mixed $operand    The type of comparison to be used to build the query, common operands are =, like
    * @return mixed            Returns the column value on successful completion and -2 on an error
    */
   public function GetSingleRowValue($table, $toreturn, $col, $colval, $operand = "="){
      if(is_array($col)){
         $con='';
         for($i=0; $i<count($col);$i++) $con.=($i==0)?$col[$i]."$operand '".$colval[$i]."'":' AND '.$col[$i]."$operand '".$colval[$i]."'";
         $this->query = "SELECT $toreturn FROM $table WHERE $con LOCK IN SHARE MODE";
      }
      else $this->query = "SELECT $toreturn FROM $table WHERE $col $operand '$colval' LOCK IN SHARE MODE";

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
    * @param string $table        Table to update the data
    * @param mixed $cols          The columns to update can be a single column or cols in an array
    * @param mixed $colvals       The column values to update can be a single column or cols in an array
    * @param mixed $conditioncol  The columns to be used in the where criteria
    * @param mixed $conditionval  The column values to be used in the criteria
    * @return mixed               Returns 0 on success or a string on error
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
    * @param string $name           The name of the box
    * @param string $size           The size of the box in the format '[A-Z]:[0-9]+.[A-Z]:[0-9]+'
    * @param string $type           The box type. Its mostly a box
    * @param integer $keeper        An id of the person responsible for this box
    * @param string $features       (Optional) A description of the box
    * @param integer $location      (Optional) The id of the location where the box is stored
    * @param string $rack           (Optional) The rack name where the box is located
    * @param integer $rack_position (Optional) The position of the box in the rack
    * @return mixed Returns the added or updated box id if all goes ok, or a string with an error message if something fails
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
    * @param string $dbase The name of the database that we will fetch data from
    * @return mixed Returns a string with an error message in case an error occurs, else it returns an array with all the sample types
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
    * Creates a new log entry into the logs
    *
    * There can be different log levels as defined in settings.<br/>
    * If the message is empty and $logMysqlError is set to true, it logs the last MySQL error encountered or the last sql statement executed as a debug log
    * If only the message is set, then an entry is added to the info logs
    *
    * @param string $message  (Optional) The message that we want to log
    * @param string $level    (Optional) The level of the message. if not defined, it defaults to an info log
    * @param boolean $logMysqlError (Optional) Determines whether to log the last MySQL error encountered and the last sql statement executed
    * @return 0   Returns a custom 0 as there is nothing more to do
    * @todo Add a log rotation whenever the log size limit is reached
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
      if(!$fd = fopen($curLogFile, 'a')) return 0;
      $messageString=date('m.d.y H:i:s: ');
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
    * @global array $logSettings    The log settings as defined in the config file
    * @global object $GeneralTasks  An object with the general tasks. Goes hand in hand with the dbase module
    * @global array $logLevels      An array defining the various levels of logs
    * @param array $logs            An array with the log file names that we are to work on
    * @param bool $truncate         Whether to truncate the files or just initialize them
    * @return int    Returns 0 on an error, I dont know what it returns when all is ok.
    */
   public function InitializeLogs($logs, $truncate){
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
}
?>