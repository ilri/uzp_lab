<?php

/**
 * This module will have the general functions that appertains to the system
 *
 * @category   Repository
 * @package    Main
 * @author     Kihara Absolomon <a.kihara@cgiar.org>
 * @since      v0.1
 */
class Uzp extends DBase{

   /**
    * @var Object An object with the database functions and properties. Implemented here to avoid having a million & 1 database connections
    */
   public $Dbase;

   /**
    * @var Object An object that is responsible for all security functions eg (authing user, getting modules user has access to)
    */
   private $security;

   public $addinfo;

   public $footerLinks = '';

   /**
    * @var  string   Just a string to show who is logged in
    */
   public $whoisme = '';

   /**
    * @var  string   A place to store any errors that happens before we have a valid connection
    */
   public $errorPage = '';

   /**
    * @var  bool     A flag to indicate whether we have an error or not
    */
   public $error = false;

   public function  __construct() {
      $this->Dbase = new DBase('mysql');

      $this->Dbase->InitializeConnection();
      if(is_null($this->Dbase->dbcon)) {
         ob_start();
         $this->homePage(OPTIONS_MSSG_DB_CON_ERROR);
         $this->errorPage = ob_get_contents();
         ob_end_clean();
         return;
      }
      $this->Dbase->InitializeLogs();
   }

   public function sessionStart() {
//      $this->Dbase->SessionStart();
   }

   /**
    * Controls the program execution
    */
   public function TrafficController(){
      if(OPTIONS_REQUESTED_MODULE != 'login' && !Config::$downloadFile){  //when we are normally browsing, check that we have the right credentials
         //we hope that we have still have the right credentials
         $this->Dbase->ManageSession();
         $this->whoisme = "{$_SESSION['surname']} {$_SESSION['onames']}, {$_SESSION['user_level']}";
      }

      if(OPTIONS_REQUESTED_MODULE == '') $this->homePage();
      elseif(OPTIONS_REQUESTED_MODULE == 'step1'){
         if(OPTIONS_REQUESTED_SUB_MODULE == '') $this->receiveSamples();
         elseif(OPTIONS_REQUESTED_SUB_MODULE == 'save') $this->receiveSamplesSave();
      }
      elseif(OPTIONS_REQUESTED_MODULE == 'step2'){
         if(OPTIONS_REQUESTED_SUB_MODULE == '') $this->brothEnrichmentHome();
         elseif(OPTIONS_REQUESTED_SUB_MODULE == 'save') $this->brothEnrichmentSave();
      }
      elseif(OPTIONS_REQUESTED_MODULE == 'step3'){
         if(OPTIONS_REQUESTED_SUB_MODULE == '') $this->mcConkyPlateHome();
         elseif(OPTIONS_REQUESTED_SUB_MODULE == 'save') $this->mcConkyPlateSave();
      }
      elseif(OPTIONS_REQUESTED_MODULE == 'step4'){
         if(OPTIONS_REQUESTED_SUB_MODULE == '') $this->coloniesHome();
         elseif(OPTIONS_REQUESTED_SUB_MODULE == 'save') $this->coloniesSave();
      }
      elseif(OPTIONS_REQUESTED_MODULE == 'step4.1'){
         if(OPTIONS_REQUESTED_SUB_MODULE == '') $this->mhHome();
         elseif(OPTIONS_REQUESTED_SUB_MODULE == 'save') $this->mhSave();
      }
      elseif(OPTIONS_REQUESTED_MODULE == 'step4.2'){
         if(OPTIONS_REQUESTED_SUB_MODULE == '') $this->mhVialHome();
         elseif(OPTIONS_REQUESTED_SUB_MODULE == 'save') $this->mhVialSave();
      }
      elseif(OPTIONS_REQUESTED_MODULE == 'step5.1'){
         if(OPTIONS_REQUESTED_SUB_MODULE == '') $this->coloniesStorage();
         elseif(OPTIONS_REQUESTED_SUB_MODULE == 'save') $this->coloniesStorageSave();
      }
      elseif(OPTIONS_REQUESTED_MODULE == 'step5'){
         if(OPTIONS_REQUESTED_SUB_MODULE == '') $this->plate2Home();
         elseif(OPTIONS_REQUESTED_SUB_MODULE == 'save') $this->plate2Save();
      }
      elseif(OPTIONS_REQUESTED_MODULE == 'step5.2'){
         if(OPTIONS_REQUESTED_SUB_MODULE == '') $this->plate2ToMHHome();
         elseif(OPTIONS_REQUESTED_SUB_MODULE == 'save') $this->plate2ToMHSave();
      }
      elseif(OPTIONS_REQUESTED_MODULE == 'step6'){
         if(OPTIONS_REQUESTED_SUB_MODULE == '') $this->bioChemTestPrepHome();
         elseif(OPTIONS_REQUESTED_SUB_MODULE == 'save') $this->bioChemTestPrepSave();
      }
      elseif(OPTIONS_REQUESTED_MODULE == 'step7'){
         if(OPTIONS_REQUESTED_SUB_MODULE == '') $this->bioChemTestResultHome();
         elseif(OPTIONS_REQUESTED_SUB_MODULE == 'save') $this->bioChemTestResultSave();
      }
      elseif(OPTIONS_REQUESTED_MODULE == 'step8'){
         if(OPTIONS_REQUESTED_SUB_MODULE == '') $this->plate3Home();
         elseif(OPTIONS_REQUESTED_SUB_MODULE == 'save') $this->plate3Save();
      }
      elseif(OPTIONS_REQUESTED_MODULE == 'step8.1'){
         if(OPTIONS_REQUESTED_SUB_MODULE == '') $this->plate3ToMHHome();
         elseif(OPTIONS_REQUESTED_SUB_MODULE == 'save') $this->plate3ToMHSave();
      }
      elseif(OPTIONS_REQUESTED_MODULE == 'step9'){
         if(OPTIONS_REQUESTED_SUB_MODULE == '') $this->plate3to45Home();
         elseif(OPTIONS_REQUESTED_SUB_MODULE == 'save') $this->plate3to45Save();
      }
      elseif(OPTIONS_REQUESTED_MODULE == 'step10'){
         if(OPTIONS_REQUESTED_SUB_MODULE == '') $this->astResultHome();
         elseif(OPTIONS_REQUESTED_SUB_MODULE == 'save') $this->astResultSave();
      }
      elseif(OPTIONS_REQUESTED_MODULE == 'step11'){
         if(OPTIONS_REQUESTED_SUB_MODULE == '') $this->regrowHome();
         elseif(OPTIONS_REQUESTED_SUB_MODULE == 'save') $this->regrowSave();
      }
      elseif(OPTIONS_REQUESTED_MODULE == 'step11.1'){
         if(OPTIONS_REQUESTED_SUB_MODULE == '') $this->plate6ToMHHome();
         elseif(OPTIONS_REQUESTED_SUB_MODULE == 'save') $this->plate6ToMHSave();
      }
      elseif(OPTIONS_REQUESTED_MODULE == 'step12'){
         if(OPTIONS_REQUESTED_SUB_MODULE == '') $this->plateToEppendorfHome();
         elseif(OPTIONS_REQUESTED_SUB_MODULE == 'save') $this->plateToEppendorfSave();
      }
      elseif(OPTIONS_REQUESTED_MODULE == 'step13'){
         if(OPTIONS_REQUESTED_SUB_MODULE == '') $this->dnaArchivingHome();
         elseif(OPTIONS_REQUESTED_SUB_MODULE == 'save') $this->dnaArchivingSave();
      }
      elseif(OPTIONS_REQUESTED_MODULE == 'campy_step1'){
         if(OPTIONS_REQUESTED_SUB_MODULE == '') $this->campyReceiptHome();
         elseif(OPTIONS_REQUESTED_SUB_MODULE == 'save') $this->campyReceiptSave();
      }
      elseif(OPTIONS_REQUESTED_MODULE == 'campy_step2'){
         if(OPTIONS_REQUESTED_SUB_MODULE == '') $this->campyFalconHome();
         elseif(OPTIONS_REQUESTED_SUB_MODULE == 'save') $this->campyFalconSave();
      }
      elseif(OPTIONS_REQUESTED_MODULE == 'campy_step3'){
         if(OPTIONS_REQUESTED_SUB_MODULE == '') $this->campyMccdaHome();
         elseif(OPTIONS_REQUESTED_SUB_MODULE == 'save') $this->campyMccdaSave();
      }
      elseif(OPTIONS_REQUESTED_MODULE == 'campy_step3.5'){
         if(OPTIONS_REQUESTED_SUB_MODULE == '') $this->campyFalcon2CryoHome();
         elseif(OPTIONS_REQUESTED_SUB_MODULE == 'save') $this->campyFalcon2CryoSave();
      }
      elseif(OPTIONS_REQUESTED_MODULE == 'campy_step4'){
         if(OPTIONS_REQUESTED_SUB_MODULE == '') $this->campyMccdaGrowthHome();
         elseif(OPTIONS_REQUESTED_SUB_MODULE == 'save') $this->campyMccdaGrowthSave();
      }
      elseif(OPTIONS_REQUESTED_MODULE == 'campy_step5'){
         if(OPTIONS_REQUESTED_SUB_MODULE == '') $this->campyMicroaerobicColoniesHome();
         elseif(OPTIONS_REQUESTED_SUB_MODULE == 'save') $this->campyMicroaerobicColoniesSave();
      }
      elseif(OPTIONS_REQUESTED_MODULE == 'logout') {
         $this->LogOutCurrentUser();
      }
      elseif(OPTIONS_REQUESTED_MODULE == 'login') {
         $this->ValidateUser();
      }
      elseif(OPTIONS_REQUESTED_MODULE == 'home') {
         $this->logAccess();
         $this->homePage();
      }
	   elseif(OPTIONS_REQUESTED_MODULE == 'dump') {
         $this->dumpData();
      }
      elseif(OPTIONS_REQUESTED_MODULE == 'send_db') {
         $this->sendDb();
      }
      elseif(OPTIONS_REQUESTED_MODULE == 'receive_db') {
         $this->receiveDb();
      }
      elseif(OPTIONS_REQUESTED_MODULE == 'db_checks') {
         if(OPTIONS_REQUESTED_SUB_MODULE == '' || OPTIONS_REQUESTED_SUB_MODULE == 'home') $this->dbChecks();
         else if(OPTIONS_REQUESTED_SUB_MODULE == 'received_samples') $this->dbCheckReceivedSamples();
         else if(OPTIONS_REQUESTED_SUB_MODULE == 'ecoli2_table1') $this->dbCheckFieldBrothSamples();
         else if(OPTIONS_REQUESTED_SUB_MODULE == 'broth_mcconky') $this->dbCheckBrothMcconkySamples();
         else if(OPTIONS_REQUESTED_SUB_MODULE == 'mcconky_colonies') $this->dbCheckMcconkyColonies();
         else if(OPTIONS_REQUESTED_SUB_MODULE == 'colonies_mh') $this->dbCheckColoniesMH();
         else if(OPTIONS_REQUESTED_SUB_MODULE == 'mh_vials') $this->dbCheckMHVials();
      }
      elseif(OPTIONS_REQUESTED_MODULE == 'view') {
         if(OPTIONS_REQUESTED_SUB_MODULE == '' || OPTIONS_REQUESTED_SUB_MODULE == 'home')$this->viewData();
         else if(OPTIONS_REQUESTED_SUB_MODULE == 'get_data') $this->getLabData();
         else if(OPTIONS_REQUESTED_SUB_MODULE == 'get_excel') $this->donwloadExcelData();
      }
      elseif(OPTIONS_REQUESTED_MODULE == 'update_lab_data'){
         if(OPTIONS_REQUESTED_SUB_MODULE == '') $this->updateLabDBHome();
         else if(OPTIONS_REQUESTED_SUB_MODULE == 'save') $this->saveUploadedBackups();
      }
   }

   /**
    * This function is responsible for sending the database backup to ILRI servers
    */
   private function sendDb() {
      if(!file_exists(Config::$config['rootdir'].DIRECTORY_SEPARATOR."downloads")) mkdir(Config::$config['rootdir'].DIRECTORY_SEPARATOR."downloads");
		$date = new DateTime();
      $name = "99hh_".Config::$config['site']."_".$date->format('Y-m-d_H-i-s').'.sql';
		$filename = Config::$config['rootdir'].DIRECTORY_SEPARATOR."downloads".DIRECTORY_SEPARATOR.$name;
      $command = Config::$config['mysqldump']." -u ".Config::$config['user']." -p".Config::$config['pass']." ".Config::$config['dbase'].' > '.$filename;
		shell_exec($command);
      $dbPath = realpath($filename);//TODO: get the real path for the file
      echo $dbPath;
      $ch = curl_init(Config::$config['remote_server']."?page=receive_db");
      $cFile = new CURLFile($dbPath, 'text/plain', $name);
      $postData = array(
         "db_file" => $cFile
      );
//      curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
      curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);
      curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, FALSE);
      curl_setopt($ch, CURLOPT_POST, 1);
      curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
      curl_close($ch);
   }

   /**
    * This function is responsible for receiving the database from the labs
    */
   private function receiveDb() {
      if(!empty($_FILES['file'])){
         $this->Dbase->CreateLogEntry("trying to get file from client", "debug");
         if($_FILES['file']['error'] > 0){
            $this->Dbase->CreateLogEntry("File error thrown while tying to download file from client. Error is ".$_FILES['file']['error'], "fatal");
         }
         else{
            $fileLoc = "uploads/".$_FILES['file']['name'];
            $uploadStatus = move_uploaded_file($_FILES['file']['tmp_name'], $fileLoc);
            if($uploadStatus == true) {
               $this->Dbase->CreateLogEntry("moved file from client to ".$fileLoc, "debug");
               $command = "mysql -u ".Config::$config['user']." -p".Config::$config['pass']." -h ".Config::$config['dbloc']." ".Config::$config['dbase']." < ".realpath($fileLoc);
               $this->Dbase->CreateLogEntry("About to execute '$command'", "fatal");
               //shell_exec($command);
            }
            else {
               $this->Dbase->CreateLogEntry("Unable to upload".$fileLoc, "fatal");
            }
         }
      }
      else {//the received file is empty
         $this->Dbase->CreateLogEntry("The received file ".  print_r($_FILES['file'], true)." is empty", "fatal");
      }
   }

   private function dumpData() {
		$date = new DateTime();
		$filename = "99hh_".Config::$config['site']."_".$date->format('Y-m-d_H-i-s').'.sql';
		$zipName = $filename.".zip";
		$command = Config::$config['mysqldump']." -u ".Config::$superConfig['user']." -p'".Config::$superConfig['pass']."' -h ".Config::$config['dbloc']." ".Config::$config['dbase'].' > '.$filename;
      $this->Dbase->CreateLogEntry($command, 'info');
      shell_exec($command);
		$zip = new ZipArchive();
		$zip->open($zipName, ZipArchive::CREATE);
		$zip->addFile($filename, basename($filename));
		$zip->close();
		header('Content-Description: File Transfer');
		header('Content-Type: application/zip');
		header("Content-Disposition: attachment; filename=".basename($zipName));
		//header('Content-Transfer-Encoding: binary');
		//header('Pragma: public');
		header('Content-Length: '.filesize($zipName));
		header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
		ob_clean();
		flush();
		readfile($zipName);
		return;
   }

   /**
    * This function exports the data as an excel file
    */
   private function donwloadExcelData() {
      include_once OPTIONS_COMMON_FOLDER_PATH.'PHPExcel/Classes/PHPExcel.php';
      $foreignKeys = array(
         "ast_result" => array("column" => "plate45_id", "parent" => "plate45", "parent_column" => "plate", "parent_id" => "id"),
         "biochemical_test" => array("column" => "mh2_id", "parent" => "mh2_assoc", "parent_column" => "mh", "parent_id" => "id"),
         "biochemical_test_results" => array("column" => "media_id", "parent" => "biochemical_test", "parent_column" => "media", "parent_id" => "id"),
         "broth_assoc" => array("column" => "field_sample_id", "parent" => "received_samples", "parent_column" => "sample", "parent_id" => "id"),
         "campy_bootsock_assoc" => array("column" => "bootsock_id", "parent" => "campy_received_bootsocks", "parent_column" => "sample", "parent_id" => "id"),
         "campy_colonies" => array("column" => "colony", "parent" => "campy_mccda_growth", "parent_column" => "am_plate", "parent_id" => "am_plate"),
         "campy_cryovials" => array("column" => "falcon_id", "parent" => "campy_bootsock_assoc", "parent_column" => "broth_sample", "parent_id" => "id"),
         "campy_mccda_assoc" => array("column" => "falcon_id", "parent" => "campy_bootsock_assoc", "parent_column" => "broth_sample", "parent_id" => "id"),
         "campy_mccda_growth" => array("column" => "mccda_plate_id", "parent" => "campy_mccda_assoc", "parent_column" => "plate1_barcode", "parent_id" => "id"),
         "colonies" => array("column" => "mcconky_plate_id", "parent" => "mcconky_assoc", "parent_column" => "plate1_barcode", "parent_id" => "id"),
         "dna_eppendorfs" => array("column" => "mh6_id", "parent" => "mh6_assoc", "parent_column" => "mh", "parent_id" => "id"),
         "mcconky_assoc" => array("column" => "broth_sample_id", "parent" => "broth_assoc", "parent_column" => "broth_sample", "parent_id" => "id"),
         "mh2_assoc" => array("column" => "plate2_id", "parent" => "plate2", "parent_column" => "plate", "parent_id" => "id"),
         "mh3_assoc" => array("column" => "plate3_id", "parent" => "plate3", "parent_column" => "plate3_id", "parent_id" => "id"),
         "mh6_assoc" => array("column" => "plate6_id", "parent" => "plate6", "parent_column" => "plate", "parent_id" => "id"),
         "mh_assoc" => array("column" => "colony_id", "parent" => "colonies", "parent_column" => "colony", "parent_id" => "id"),
         "mh_vial" => array("column" => "mh_id", "parent" => "mh_assoc", "parent_column" => "mh", "parent_id" => "id"),
         "plate2" => array("column" => "mh_vial_id", "parent" => "mh_vial", "parent_column" => "mh_vial", "parent_id" => "id"),
         "plate3" => array("column" => "mh_vial_id", "parent" => "mh_vial", "parent_column" => "mh_vial", "parent_id" => "id"),
         "plate6" => array("column" => "mh_vial_id", "parent" => "mh_vial", "parent_column" => "mh_vial", "parent_id" => "id"),
         "plate45" => array("column" => "mh3_id", "parent" => "mh3_assoc", "parent_column" => "mh", "parent_id" => "id")
      );
      $replaceHeadings = array(
         "broth_assoc" => "broth",
         "mcconky_assoc" => "primary_plate",
         "colonies" => "primary_plate_colonies",
         "mh_assoc" => "muller_hinton",
         "mh_vial" => "archived_vial",
         "mh3_assoc" => "plate3_muller_hinton",
         "mh2_assoc" => "plate2_muller_hinton",
         "mh6_assoc" => "plate6_muller_hinton",
         "campy_bootsock_assoc" => "campy_falcon_tube"
      );
      $children = array("biochemical_test_results", "ast_result", "dna_eppendorfs", "campy_colonies", "campy_cryovials");
      $date = new DateTime();
      $filename = "99HH Database ".$date->format('Y-m-d H-i-s');
      $excelObject = new PHPExcel();
      $excelObject->getProperties()->setCreator("Azizi Biorepository");
      $excelObject->getProperties()->setLastModifiedBy("Azizi Biorepository");
      $excelObject->getProperties()->setTitle($filename);
      $excelObject->getProperties()->setSubject("Generated using the 99HH database system");
      $excelObject->getProperties()->setDescription("This Excel file has been generated using the 99HH database system that utilizes the PHPExcel library on PHP");
      $sheetIndex = 0;
      foreach($children as $currRootChild) {
         $queryDetails = $this->getCascadingQuery("", "", $currRootChild, $foreignKeys);
         $select = $queryDetails['select'];
         $from = $queryDetails['from'];
         $query = "select ".$select." from ".$from;
         //$this->Dbase->CreateLogEntry($query, "fatal");
         $result = $this->Dbase->ExecuteQuery($query);
         if(is_array($result) && count($result) > 0) {
            $tableColumns = array_reverse(array_keys($result[0]));
            if($sheetIndex > 0) {
               $excelObject->createSheet();
            }
            $excelObject->setActiveSheetIndex($sheetIndex);
            $sheetIndex++;
            $excelObject->getActiveSheet()->setTitle($currRootChild);
            $lastColumn = explode("-", $tableColumns[0]);
            $lastColumn = $lastColumn[0];
            $mainColumnCount = 0;
            for($columnIndex = 0; $columnIndex < count($tableColumns); $columnIndex++) {
               $explodedHeading = explode("-", $tableColumns[$columnIndex]);
               $currColumn = $explodedHeading[0];
               if($currColumn != $lastColumn) {
                  $mainColumnCount++;
                  $lastColumn = $currColumn;
               }
               if(($mainColumnCount % 2) == 0) {//even
                  $bgColour = "EDF1C1";
               }
               else {
                  $bgColour = "DCAF86";
               }
               $readableHeading = $tableColumns[$columnIndex];
               $this->Dbase->CreateLogEntry($explodedHeading[0],"fatal");
               if(isset($replaceHeadings[$explodedHeading[0]]) == true) {
                  $readableHeading = $replaceHeadings[$explodedHeading[0]]."-".$explodedHeading[1];
               }
               $headingCell = PHPExcel_Cell::stringFromColumnIndex($columnIndex)."1";
               $excelObject->getActiveSheet()->setCellValue($headingCell, $readableHeading);
               $excelObject->getActiveSheet()->getColumnDimension(PHPExcel_Cell::stringFromColumnIndex($columnIndex))->setAutoSize(true);
               $excelObject->getActiveSheet()->getStyle($headingCell)->getFont()->setBold(TRUE);
               $excelObject->getActiveSheet()->getStyle($headingCell)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setARGB($bgColour);
               for($rowIndex = 0; $rowIndex < count($result); $rowIndex++) {
                  $rowName = $rowIndex + 2;
                  $dataCell = PHPExcel_Cell::stringFromColumnIndex($columnIndex).$rowName;
                  $excelObject->getActiveSheet()->setCellValue($dataCell, $result[$rowIndex][$tableColumns[$columnIndex]]);
                  $excelObject->getActiveSheet()->getStyle($dataCell)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setARGB($bgColour);
               }
            }
         }
         else {
            $this->Dbase->CreateLogEntry("Could not execute query", "fatal");
            $this->Dbase->CreateLogEntry($this->Dbase->lastError, "fatal");
         }
      }
      $excelObject->setActiveSheetIndex(0);
      $objWriter = new PHPExcel_Writer_Excel2007($excelObject);
      if(!file_exists(Config::$config['rootdir'].DIRECTORY_SEPARATOR."downloads")) mkdir(Config::$config['rootdir'].DIRECTORY_SEPARATOR."downloads");
      $objWriter->save(Config::$config['rootdir'].DIRECTORY_SEPARATOR."downloads".DIRECTORY_SEPARATOR.$filename.'.xlsx');

      header('Content-Description: File Transfer');
		header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
		header("Content-Disposition: attachment; filename=".basename($filename.'.xlsx'));
		//header('Content-Transfer-Encoding: binary');
		//header('Pragma: public');
		header('Content-Length: '.filesize(Config::$config['rootdir'].DIRECTORY_SEPARATOR."downloads".DIRECTORY_SEPARATOR.$filename.'.xlsx'));
		header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
		ob_clean();
		flush();
		readfile(Config::$config['rootdir'].DIRECTORY_SEPARATOR."downloads".DIRECTORY_SEPARATOR.$filename.'.xlsx');
		return;
   }

   /**
    * This recursive function constructs the select and from string of a query given the last cascading
    * child and an array showing the cascading parents of all the tables
    *
    * @param type $select     A string containing the already constructed select part of the query
    * @param type $from       A string containing the already constructed from part of the query
    * @param type $currTable  The table which we are going to append details from
    * @param type $tableAssoc An associative array showing parent child associations between the tables with the child table name as the key
    * @param type $child      The name of the child table for $currTable. Is optional
    *
    * @return type   An associative array containing 'select' and 'from' as its keys
    */
   private function getCascadingQuery($select, $from, $currTable, $tableAssoc, $child = null) {
      $query = "desc $currTable";
      $result = array_reverse($this->Dbase->ExecuteQuery($query));
      $columns = "";
      foreach($result as $currResult) {
         if($currResult['Field'] != 'id' && !$this->endsWith($currResult['Field'], "id")) {
            if(strlen($columns) == 0) $columns = $currTable.".".$currResult['Field']." as `".$currTable."-".$currResult['Field']."`";
            else $columns .= ", ".$currTable.".".$currResult['Field']." as `".$currTable."-".$currResult['Field']."`";
         }
      }
      if(strlen($select) == 0)$select = $columns;
      else $select .= ", ".$columns;
      if(strlen($from) == 0) $from = $currTable;
      else if($child != null) $from .= " right join ".$currTable." on ".$child.".".$tableAssoc[$child]['column']." = ".$currTable.".".$tableAssoc[$child]['parent_id'];
      if(isset($tableAssoc[$currTable])) {
         $parentData = $this->getCascadingQuery($select, $from, $tableAssoc[$currTable]['parent'], $tableAssoc, $currTable);
         $select = $parentData['select'];
         $from = $parentData['from'];
      }
      return array("select" => $select, "from" => $from);
   }
   private function endsWith($haystack, $needle) {
      return $needle === "" || (($temp = strlen($haystack) - strlen($needle)) >= 0 && strpos($haystack, $needle, $temp) !== FALSE);
   }


   /**
    * This function renders the view data page
    */
   private function viewData() {
?>
<script type="text/javascript" src="js/view_lab.js"></script>
<link rel="stylesheet" href="<?php echo OPTIONS_COMMON_FOLDER_PATH?>jqwidgets/jqwidgets/styles/jqx.base.css" type="text/css" />
<script type="text/javascript" src="<?php echo OPTIONS_COMMON_FOLDER_PATH?>jqwidgets/jqwidgets/jqxcore.js"></script>
<script type="text/javascript" src="<?php echo OPTIONS_COMMON_FOLDER_PATH?>jqwidgets/jqwidgets/jqxdata.js"></script>
<script type="text/javascript" src="<?php echo OPTIONS_COMMON_FOLDER_PATH?>jqwidgets/jqwidgets/jqxbuttons.js"></script>
<script type="text/javascript" src="<?php echo OPTIONS_COMMON_FOLDER_PATH?>jqwidgets/jqwidgets/jqxscrollbar.js"></script>
<script type="text/javascript" src="<?php echo OPTIONS_COMMON_FOLDER_PATH?>jqwidgets/jqwidgets/jqxmenu.js"></script>
<script type="text/javascript" src="<?php echo OPTIONS_COMMON_FOLDER_PATH?>jqwidgets/jqwidgets/jqxcheckbox.js"></script>
<script type="text/javascript" src="<?php echo OPTIONS_COMMON_FOLDER_PATH?>jqwidgets/jqwidgets/jqxlistbox.js"></script>
<script type="text/javascript" src="<?php echo OPTIONS_COMMON_FOLDER_PATH?>jqwidgets/jqwidgets/jqxdropdownlist.js"></script>
<script type="text/javascript" src="<?php echo OPTIONS_COMMON_FOLDER_PATH?>jqwidgets/jqwidgets/jqxgrid.js"></script>
<script type="text/javascript" src="<?php echo OPTIONS_COMMON_FOLDER_PATH?>jqwidgets/jqwidgets/jqxgrid.sort.js"></script>
<script type="text/javascript" src="<?php echo OPTIONS_COMMON_FOLDER_PATH?>jqwidgets/jqwidgets/jqxgrid.pager.js"></script>
<script type="text/javascript" src="<?php echo OPTIONS_COMMON_FOLDER_PATH?>jqwidgets/jqwidgets/jqxgrid.selection.js"></script>
<script type="text/javascript" src="<?php echo OPTIONS_COMMON_FOLDER_PATH?>jqwidgets/jqwidgets/jqxgrid.filter.js"></script>
<script type="text/javascript" src="<?php echo OPTIONS_COMMON_FOLDER_PATH?>jqwidgets/jqwidgets/jqxnotification.js"></script>
<script type="text/javascript" src="<?php echo OPTIONS_COMMON_FOLDER_PATH?>jqwidgets/jqwidgets/jqxgrid.export.js"></script>
<script type="text/javascript" src="<?php echo OPTIONS_COMMON_FOLDER_PATH?>jqwidgets/jqwidgets/jqxdata.export.js"></script>
<div id="lab_view">
   <a href="./?page=" style="float: left; margin-bottom: 10px;">Back</a> <br />
   <select id="table_to_show">
      <option value="ecoli_table1">Biochemical Results</option>
      <option value="ecoli2_table1">AST Results</option>
      <option value="ecoli3_table1">DNA Sequencing</option>
      <option value="campy_table1">Campy Cryovials</option>
      <option value="campy2_table1">Campy MCCDA</option>
   </select>
   <div id="ecoli_table1_hd"><h3>Biochemical tests</h3></div>
   <div id="ecoli_table1">&nbsp;</div>
   <div id="ecoli2_table1_hd"><h3>AST Results</h3></div>
   <div id="ecoli2_table1">&nbsp;</div>
   <div id="ecoli3_table1_hd"><h3>DNA Sequencing</h3></div>
   <div id="ecoli3_table1">&nbsp;</div>
   <div id="campy_table1_hd"><h3>Campy Cryovials</h3></div>
   <div id="campy_table1">&nbsp;</div>
   <div id="campy2_table1_hd"><h3>Campy MCCDA</h3></div>
   <div id="campy2_table1">&nbsp;</div>
</div>
<script type="text/javascript">
   $(document).ready(function(){
      var lv = new LabView();
   });
</script>
   <?php
   }

   /**
    * This function returns lab data requested for by a GET request
    */
   private function getLabData() {
      $dataType = $_REQUEST['type'];
      if($dataType == "table1") {
         $query = "select a.id received_samples_id, a.for_sequencing received_samples_for_sequencing, a.sample received_samples_sample, a.user received_samples_user, a.datetime_received received_samples_datetime_received,"
               . "b.broth_sample broth_assoc_broth_sample, b.datetime_added broth_assoc_datetime_added, b.field_sample_id broth_assoc_field_sample_id, b.user broth_assoc_user, b.id broth_assoc_id,"
               . "c.id mcconky_assoc_id, c.datetime_added mcconky_assoc_datetime_added, c.media_used mcconky_assoc_media_used, c.no_qtr_colonies mcconky_assoc_no_qtr_colonies, c.plate1_barcode mcconky_assoc_plate1_barcode"
               . " from received_samples as a"
               . " left join broth_assoc as b on a.id = b.field_sample_id"
               . " left join mcconky_assoc as c on b.id = c.broth_sample_id";
         $result = $this->Dbase->ExecuteQuery($query);
      }
      else if($dataType == 'table2') {
         $id = $_REQUEST['id'];
         $query = "select b.id colonies_id, b.datetime_saved colonies_datetime_saved, b.colony colonies_colony, b.user colonies_user,"
               . "c.datetime_added mh_assoc_datetime_added, c.mh mh_assoc_mh, c.user mh_assoc_user,"
               . "d.id mh_vial_id, d.datetime_saved mh_vial_datetime_saved, d.box mh_vial_box, d.mh_vial mh_vial_mh_vial, d.position_in_box mh_vial_position_in_box, d.pos_saved_by mh_vial_pos_saved_by, d.user mh_vial_user"
               . " from colonies as b"
               . " left join mh_assoc as c on b.id = c.colony_id"
               . " left join mh_vial as d on c.id = d.mh_id"
               . " where b.mcconky_plate_id = :id";
         $result = $this->Dbase->ExecuteQuery($query, array("id" => $id));
      }
      else if($dataType == 'table3') {
         $id = $_REQUEST['id'];
         $query = "select a.id plate2_id, a.datetime_added plate2_datetime_added, a.plate plate2_plate, a.user plate2_user,"
               . "b.datetime_added mh2_assoc_datetime_added, b.mh mh2_assoc_mh, b.user mh2_assoc_user, b.id mh2_assoc_id"
               . " from plate2 as a"
               . " left join mh2_assoc as b on a.id = b.plate2_id"
               . " where a.mh_vial_id = :id";
         $result = $this->Dbase->ExecuteQuery($query, array("id" => $id));
      }
      else if($dataType == 'table4') {
         $id = $_REQUEST['id'];
         $query = "select a.datetime_added biochemical_test_datetime_added ,  a.media biochemical_test_media ,  a.user biochemical_test_user ,  a.id biochemical_test_id"
               . " from biochemical_test as a"
               . " where a.mh2_id = :id";
         $result = $this->Dbase->ExecuteQuery($query, array("id" => $id));
      }
      else if($dataType == 'table5') {
         $id = $_REQUEST['id'];
         $query = "select a.id biochemical_test_results_id,  a.datetime_added biochemical_test_results_datetime_added,  a.observ_type biochemical_test_results_observ_type, a.observ_value biochemical_test_results_observ_value, a.test biochemical_test_results_test, a.user biochemical_test_results_user"
               . " from biochemical_test_results as a"
               . " where a.media_id = :id";
         $result = $this->Dbase->ExecuteQuery($query, array("id" => $id));
      }
      else if($dataType == 'table2-3') {
         $id = $_REQUEST['id'];
         $query = "select a.id plate3_id, a.datetime_added plate3_datetime_added, a.plate plate3_plate, a.user plate3_user,"
               . "b.datetime_added mh3_assoc_datetime_added, b.mh mh3_assoc_mh, b.user mh3_assoc_user, b.id mh3_assoc_id"
               . " from plate3 as a"
               . " left join mh3_assoc as b on a.id = b.plate3_id"
               . " where a.mh_vial_id = :id";
         $result = $this->Dbase->ExecuteQuery($query, array("id" => $id));
      }
      else if($dataType == 'table3-3') {
         $id = $_REQUEST['id'];
         $query = "select a.id plate6_id, a.datetime_added plate6_datetime_added, a.plate plate6_plate, a.user plate6_user,"
               . "b.datetime_added mh6_assoc_datetime_added, b.mh mh6_assoc_mh, b.user mh6_assoc_user, b.id mh6_assoc_id,"
               . "c.eppendorf as dna_eppendorfs_eppendorf, c.user as dna_eppendorfs_user"
               . " from plate6 as a"
               . " left join mh6_assoc as b on a.id = b.plate6_id"
               . " left join dna_eppendorfs as c on b.id = c.mh6_id"
               . " where a.mh_vial_id = :id";
         $result = $this->Dbase->ExecuteQuery($query, array("id" => $id));
      }
      else if($dataType == 'table2-4') {
         $id = $_REQUEST['id'];
         $query = "select a.id plate45_id, a.datetime_added plate45_datetime_added, a.number plate45_number, a.plate plate45_plate, a.user plate45_user"
               . " from plate45 as a"
               . " where a.mh3_id = :id";
         $result = $this->Dbase->ExecuteQuery($query, array("id" => $id));
      }
      else if($dataType == 'table2-5') {
         $id = $_REQUEST['id'];
         $query = "select a.id ast_result_id,  a.datetime_added ast_result_datetime_added,  a.drug ast_result_drug, a.user ast_result_user, a.value ast_result_value"
               . " from ast_result as a"
               . " where a.plate45_id = :id";
         $result = $this->Dbase->ExecuteQuery($query, array("id" => $id));
      }
      else if($dataType == 'campy1') {
         $query = "select a.id campy_received_bootsocks_id, a.datetime_received campy_received_bootsocks_datetime_received, a.for_sequencing campy_received_bootsocks_for_sequencing, a.sample campy_received_bootsocks_sample, a.user campy_received_bootsocks_user,"
               . "b.id campy_bootsock_assoc_id, b.daughter_sample campy_bootsock_assoc_daughter_sample, b.datetime_added campy_bootsock_assoc_datetime_added, b.user campy_bootsock_assoc_user,"
               . "c.cryovial campy_cryovials_cryovial, c.datetime_saved campy_cryovials_datetime_saved, c.id campy_cryovials_id, c.position_in_box campy_cryovials_position_in_box, c.user campy_cryovials_user, c.box campy_cryovials_box"
               . " from campy_received_bootsocks as a"
               . " left join campy_bootsock_assoc as b on a.id = b.bootsock_id"
               . " left join campy_cryovials as c on b.id = c.falcon_id";
         $result = $this->Dbase->ExecuteQuery($query);
      }
      else if($dataType == 'campy2-1') {
         $query = "select a.id campy_received_bootsocks_id, a.datetime_received campy_received_bootsocks_datetime_received, a.for_sequencing campy_received_bootsocks_for_sequencing, a.sample campy_received_bootsocks_sample, a.user campy_received_bootsocks_user,"
               . "b.id campy_bootsock_assoc_id, b.daughter_sample campy_bootsock_assoc_daughter_sample, b.datetime_added campy_bootsock_assoc_datetime_added, b.user campy_bootsock_assoc_user,"
               . "c.datetime_added campy_mccda_assoc_datetime_added, c.plate1_barcode campy_mccda_assoc_plate1_barcode, c.user campy_mccda_assoc_user, c.id campy_mccda_assoc_id"
               . " from campy_received_bootsocks as a"
               . " left join campy_bootsock_assoc as b on a.id = b.bootsock_id"
               . " left join campy_mccda_assoc as c on b.id = c.falcon_id";
         $result = $this->Dbase->ExecuteQuery($query);
      }
      else if($dataType == 'campy2-2') {
         $id = $_REQUEST['id'];
         $query = "select a.am_plate campy_mccda_growth_am_plate, a.datetime_saved campy_mccda_growth_datetime_saved, a.user campy_mccda_growth_user,"
               . "b.colony campy_colonies_colony, b.box campy_colonies_box, b.position_in_box campy_colonies_position_in_box, b.user campy_colonies_user"
               . " from campy_mccda_growth as a"
               . " left join campy_colonies as b on a.am_plate = b.colony"
               . " where a.mccda_plate_id = :id";
         $result = $this->Dbase->ExecuteQuery($query, array("id" => $id));
      }
      if(is_array($result)) {
         die(json_encode($result));
      }
      else {
         die(json_encode(array()));
      }
   }

   /**
    * Creates the home page of the lab system
    * @param type $error
    */
   private function homePage($addInfo = NULL){
      $addInfo = ($addInfo != '') ? "<div id='addinfo'>$addInfo</div>" : '';
      ?>
<div id='home'>
   <?php echo $addInfo; ?>
   <h3 class="center" id="home_title">UZP - 99H - Lab modules</h3>
   <div class="user_options">
      <ul>
         <li><a href="?page=step1">Receive field samples (1)</a></li>
         <li><a href="?page=step2">Broth Enrichment (2)</a></li>
         <li><a href="?page=step3">Primary Plating (3)</a></li>
         <li><a href="?page=step4">Get Colonies from Primary Plate (4)</a></li>
         <li><a href="?page=step4.1">Colonies to MH Plate(4.1)</a></li>
         <li><a href="?page=step4.2">MH Plate to Vial(4.2)</a></li>
         <li><a href="?page=step5.1">MH Vial to Archive(5.1)</a></li>
         <li><a href="?page=step5">Archival to Plate 2 (5)</a></li>
         <li><a href="?page=step5.2">Plate 2 to MH Plate (5.2)</a></li>
         <li><a href="?page=step6">Biochemical Test Prep (6)</a></li>
         <li><a href="?page=step7">Biochemical Test Result (7)</a></li>
         <li><a href="?page=step8">Archival to Plate 3 (8)</a></li>
         <li><a href="?page=step8.1">Plate 3 to MH Plate(8.1)</a></li>
         <li><a href="?page=step9">MH Plate to Plates 4,5 (9)</a></li>
         <li><a href="?page=step10">Plates 4,5 to AST Result Reading (10)</a></li>
         <li><a href="?page=step11">Archival to Plate 6 (11)</a></li>
         <li><a href="?page=step11.1">Plate 6 to MH Plate(11.1)</a></li>
         <li><a href="?page=step12">MH Plate to Eppendorf / DNA Extract (12)</a></li>
         <div><br /><b>Campylobacter Lab Modules</b></div>
         <li><a href="?page=campy_step1">Receive Bootsocks/Faeces/Meat Pots</a></li>
         <li><a href="?page=campy_step2">Bootsocks/Pots to Falcon tubes</a></li>
         <li><a href="?page=campy_step3">Falcon tube to MCCDA plate</a></li>
         <li><a href="?page=campy_step3.5">Falcon tube to cryo vials</a></li>
         <li><a href="?page=campy_step4">MCCDA plate to Aerobic/Microaerobic plate</a></li>
         <li><a href="?page=campy_step5">Microaerobic colonies freezing</a></li>
         <div><br /><b>Miscellaneous</b></div>
         <li><a href="?page=dump">Backup database</a></li>
         <li><a href="?page=view">View database data</a></li>
         <li><a href="?page=db_checks">Run DB Checks</a></li>
         <li><a href="?page=update_lab_data">Update Lab Databases</a></li>
         <!--li><a href="?page=step13">Eppendorf / DNA Extract -> Archive (13)</a></li-->
      </ul>
   </div>
</div>
<script>
   $('#whoisme .back').html('<a href=\'?page=home\'>Back</a>');//back link
</script>
<?php
   }

   /**
    * Create a page for receiving samples
    */
   private function receiveSamples(){
      $addInfo = ($addInfo != '') ? "<div id='addinfo'>$addInfo</div>" : '';
      $userCombo = $this->usersCombo();
      $sequencingCombo = $this->sequencingCombo();
?>
    <link rel="stylesheet" href="<?php echo OPTIONS_COMMON_FOLDER_PATH; ?>jqwidgets/jqwidgets/styles/jqx.base.css" type="text/css" />
    <script type="text/javascript" src="js/uzp_lab.js"></script>
    <script type="text/javascript" src="<?php echo OPTIONS_COMMON_FOLDER_PATH; ?>jquery/jquery.min.js"></script>
    <script type="text/javascript" src="<?php echo OPTIONS_COMMON_FOLDER_PATH; ?>jqwidgets/jqwidgets/jqxcore.js"></script>
    <script type="text/javascript" src="<?php echo OPTIONS_COMMON_FOLDER_PATH; ?>jqwidgets/jqwidgets/jqxinput.js"></script>
    <script type="text/javascript" src="<?php echo OPTIONS_COMMON_FOLDER_PATH; ?>jqwidgets/jqwidgets/jqxbuttons.js"></script>
    <script type="text/javascript" src="<?php echo OPTIONS_COMMON_FOLDER_PATH; ?>jqwidgets/jqwidgets/jqxnotification.js"></script>

<div id="receive_samples">
   <h3 class="center" id="home_title">Log samples received from the field</h3>
   <a href="./?page=" style="float: left; margin-bottom: 10px;">Back</a> <br />
   <div class="scan">
      <div id="sample_format"><label style="float: left;">Sample format: </label>&nbsp;&nbsp;<input type="text" name="sample_format" value="AVAQ63847" /></div>
      <div id="current_user"><label style="float: left;">Current User: </label>&nbsp;&nbsp;<?php echo $userCombo; ?></div>
      <div id="for_sequencing"><label style="float: left;">For genome sequencing: </label>&nbsp;&nbsp;<?php echo $sequencingCombo; ?></div> <br />

      <input type="text" name="sample" />
      <div>
         <input style='margin-top: 5px;' type="submit" value="Submit" id='jqxSubmitButton' />
      </div>
   </div>
   <div class="received"><div class="saved">Received samples appear here</div></div>
</div>
<div id="notification_box"><div id="msg"></div></div>
<script>
   var uzp = new Uzp();

   $('#whoisme .back').html('<a href=\'?page=home\'>Back</a>');
   $("[name=sample]").focus().jqxInput({placeHolder: "Scan a sample", width: 200, minLength: 1 });
   $("#jqxSubmitButton").on('click', uzp.saveReceivedSample).jqxButton({ width: '150'});

   $(document).keypress(uzp.receiveSampleKeypress);
</script>
<?php
   }

   /**
    * Save the received sample
    */
   private function receiveSamplesSave(){
      // time to save the received sample
      $query = 'insert into received_samples(sample, user, for_sequencing) values(:sample, :user, :for_sequencing)';
      $vals = array('sample' => $_POST['sample'], 'user' => $_POST['cur_user'], 'for_sequencing' => $_POST['for_sequencing']);

      $result = $this->Dbase->ExecuteQuery($query, $vals);
      if($result == 1) die(json_encode(array('error' => true, 'mssg' => $this->Dbase->lastError)));
      else die(json_encode(array('error' => false, 'mssg' => 'The sample has been saved succesfully.')));
   }

   private function brothEnrichmentHome(){
      $userCombo = $this->usersCombo();
?>
    <link rel="stylesheet" href="<?php echo OPTIONS_COMMON_FOLDER_PATH; ?>jqwidgets/jqwidgets/styles/jqx.base.css" type="text/css" />
    <script type="text/javascript" src="js/uzp_lab.js"></script>
    <script type="text/javascript" src="<?php echo OPTIONS_COMMON_FOLDER_PATH; ?>jquery/jquery.min.js"></script>
    <script type="text/javascript" src="<?php echo OPTIONS_COMMON_FOLDER_PATH; ?>jqwidgets/jqwidgets/jqxcore.js"></script>
    <script type="text/javascript" src="<?php echo OPTIONS_COMMON_FOLDER_PATH; ?>jqwidgets/jqwidgets/jqxinput.js"></script>
    <script type="text/javascript" src="<?php echo OPTIONS_COMMON_FOLDER_PATH; ?>jqwidgets/jqwidgets/jqxbuttons.js"></script>
    <script type="text/javascript" src="<?php echo OPTIONS_COMMON_FOLDER_PATH; ?>jqwidgets/jqwidgets/jqxnotification.js"></script>

<div id="broth_enrichment">
   <h3 class="center" id="home_title">Linking field and broth samples</h3>
   <a href="./?page=" style="float: left; margin-bottom: 10px;">Back</a> <br />
   <div class="scan">
      <div id="sample_format"><label style="float: left;">Field Sample format: </label>&nbsp;&nbsp;<input type="text" name="sample_format" class="input-small" value="AVAQ70919" /></div>
      <div id="broth_format"><label style="float: left;">Broth Sample format: </label>&nbsp;&nbsp;<input type="text" name="broth_format" class="input-small" value="BSR010959" /></div>
      <div id="current_user"><label style="float: left;">Current User: </label>&nbsp;&nbsp;<?php echo $userCombo; ?></div> <br />

      <div class="center">
         <input type="text" name="sample" />
         <div>
            <input style='margin-top: 5px;' type="submit" value="Submit" id='jqxSubmitButton' />
         </div>
      </div>
   </div>
   <div class="received"><div class="saved">Linked samples appear here</div></div>
</div>
<div id="notification_box"><div id="msg"></div></div>
<script>
   var uzp = new Uzp();

   $('#whoisme .back').html('<a href=\'?page=home\'>Back</a>');
   $("[name=sample]").focus().jqxInput({placeHolder: "Scan a sample", width: 200, minLength: 1 });
   $("#jqxSubmitButton").on('click', uzp.saveBrothSample).jqxButton({ width: '150'});

   uzp.prevSample = undefined;
   uzp.curSample = undefined;
   uzp.curSampleType = undefined;
   uzp.prevSampleType = undefined;
   $(document).keypress(uzp.receiveSampleKeypress);
</script>
<?php
   }

   /**
    * Saves a new association of the broth enrichment
    */
   private function brothEnrichmentSave(){
      /**
       * check whether the parent sample is in the database
       * if it is in the database, save the association
       */
      $checkQuery = 'select id from received_samples where sample = :sample';
      $insertQuery = 'insert into broth_assoc(field_sample_id, broth_sample, user) values(:field_sample_id, :broth_sample, :user)';

      $result = $this->Dbase->ExecuteQuery($checkQuery, array('sample' => $_POST['field_sample']));
      if($result == 1) die(json_encode(array('error' => true, 'mssg' => $this->Dbase->lastError)));
      else if(count($result) == 0) die(json_encode(array('error' => true, 'mssg' => "The field sample '{$_POST['field_sample']}' is not in the database.")));

      // now add the association
      $result = $this->Dbase->ExecuteQuery($insertQuery, array('field_sample_id' => $result[0]['id'], 'broth_sample' => $_POST['broth_sample'], 'user' => $_POST['cur_user']));
      if($result == 1) die(json_encode(array('error' => true, 'mssg' => $this->Dbase->lastError)));
      else die(json_encode(array('error' => false, 'mssg' => 'The association has been saved succesfully.')));
   }

   private function plate2Home(){
      $userCombo = $this->usersCombo();
?>
    <link rel="stylesheet" href="<?php echo OPTIONS_COMMON_FOLDER_PATH; ?>jqwidgets/jqwidgets/styles/jqx.base.css" type="text/css" />
    <script type="text/javascript" src="js/uzp_lab.js"></script>
    <script type="text/javascript" src="<?php echo OPTIONS_COMMON_FOLDER_PATH; ?>jquery/jquery.min.js"></script>
    <script type="text/javascript" src="<?php echo OPTIONS_COMMON_FOLDER_PATH; ?>jqwidgets/jqwidgets/jqxcore.js"></script>
    <script type="text/javascript" src="<?php echo OPTIONS_COMMON_FOLDER_PATH; ?>jqwidgets/jqwidgets/jqxinput.js"></script>
    <script type="text/javascript" src="<?php echo OPTIONS_COMMON_FOLDER_PATH; ?>jqwidgets/jqwidgets/jqxbuttons.js"></script>
    <script type="text/javascript" src="<?php echo OPTIONS_COMMON_FOLDER_PATH; ?>jqwidgets/jqwidgets/jqxnotification.js"></script>

<div id="broth_enrichment">
   <h3 class="center" id="home_title">Second Plating</h3>
   <a href="./?page=" style="float: left; margin-bottom: 10px;">Back</a> <br />
   <div class="scan">
      <div id="colony_format"><label style="float: left;">Archived vial format: </label>&nbsp;&nbsp;<input type="text" name="colony_format" class="input-small" value="AVAQ70919" /></div>
      <div id="plate_format"><label style="float: left;">Plate 2 format: </label>&nbsp;&nbsp;<input type="text" name="plate_format" class="input-small" value="BSR010959" /></div>
      <div id="current_user"><label style="float: left;">Current User: </label>&nbsp;&nbsp;<?php echo $userCombo; ?></div> <br />

      <div class="center">
         <input type="text" name="sample" />
         <div>
            <input style='margin-top: 5px;' type="submit" value="Submit" id='jqxSubmitButton' />
         </div>
      </div>
   </div>
   <div class="received"><div class="saved">Linked samples appear here</div></div>
</div>
<div id="notification_box"><div id="msg"></div></div>
<script>
   var uzp = new Uzp();

   $('#whoisme .back').html('<a href=\'?page=home\'>Back</a>');
   $("[name=sample]").focus().jqxInput({placeHolder: "Scan a sample", width: 200, minLength: 1 });
   $("#jqxSubmitButton").on('click', uzp.saveBioChemSample).jqxButton({ width: '150'});

   uzp.prevSample = undefined;
   uzp.curSample = undefined;
   uzp.curSampleType = undefined;
   uzp.prevSampleType = undefined;
   $(document).keypress(uzp.receiveSampleKeypress);
</script>
<?php
   }

   /**
    * Saves a new association of the broth enrichment
    */
   private function plate2Save(){
      /**
       * check whether the parent sample is in the database
       * if it is in the database, save the association
       */
      $checkQuery = 'select id from mh_vial where mh_vial = :colony';
      $insertQuery = 'insert into plate2(mh_vial_id, plate, user) values(:field_sample_id, :broth_sample, :user)';

      $result = $this->Dbase->ExecuteQuery($checkQuery, array('colony' => $_POST['field_sample']));
      if($result == 1) die(json_encode(array('error' => true, 'mssg' => $this->Dbase->lastError)));
      else if(count($result) == 0) die(json_encode(array('error' => true, 'mssg' => "The field sample '{$_POST['field_sample']}' is not in the database.")));

      // now add the association
      $result = $this->Dbase->ExecuteQuery($insertQuery, array('field_sample_id' => $result[0]['id'], 'broth_sample' => $_POST['broth_sample'], 'user' => $_POST['cur_user']));
      if($result == 1) die(json_encode(array('error' => true, 'mssg' => $this->Dbase->lastError)));
      else die(json_encode(array('error' => false, 'mssg' => 'The association has been saved succesfully.')));
   }

   private function plate2ToMHHome() {
      $userCombo = $this->usersCombo();
?>
    <link rel="stylesheet" href="<?php echo OPTIONS_COMMON_FOLDER_PATH; ?>jqwidgets/jqwidgets/styles/jqx.base.css" type="text/css" />
    <script type="text/javascript" src="js/uzp_lab.js"></script>
    <script type="text/javascript" src="<?php echo OPTIONS_COMMON_FOLDER_PATH; ?>jquery/jquery.min.js"></script>
    <script type="text/javascript" src="<?php echo OPTIONS_COMMON_FOLDER_PATH; ?>jqwidgets/jqwidgets/jqxcore.js"></script>
    <script type="text/javascript" src="<?php echo OPTIONS_COMMON_FOLDER_PATH; ?>jqwidgets/jqwidgets/jqxinput.js"></script>
    <script type="text/javascript" src="<?php echo OPTIONS_COMMON_FOLDER_PATH; ?>jqwidgets/jqwidgets/jqxbuttons.js"></script>
    <script type="text/javascript" src="<?php echo OPTIONS_COMMON_FOLDER_PATH; ?>jqwidgets/jqwidgets/jqxnotification.js"></script>

<div id="broth_enrichment">
   <h3 class="center" id="home_title">Plate 2 to MH Plate</h3>
   <a href="./?page=" style="float: left; margin-bottom: 10px;">Back</a> <br />
   <div class="scan">
      <div id="colony_format"><label style="float: left;">Plate 2 format: </label>&nbsp;&nbsp;<input type="text" name="colony_format" class="input-small" value="AVAQ70919" /></div>
      <div id="plate_format"><label style="float: left;">MH Plate format: </label>&nbsp;&nbsp;<input type="text" name="plate_format" class="input-small" value="BSR010959" /></div>
      <div id="current_user"><label style="float: left;">Current User: </label>&nbsp;&nbsp;<?php echo $userCombo; ?></div> <br />

      <div class="center">
         <input type="text" name="sample" />
         <div>
            <input style='margin-top: 5px;' type="submit" value="Submit" id='jqxSubmitButton' />
         </div>
      </div>
   </div>
   <div class="received"><div class="saved">Linked samples appear here</div></div>
</div>
<div id="notification_box"><div id="msg"></div></div>
<script>
   var uzp = new Uzp();

   $('#whoisme .back').html('<a href=\'?page=home\'>Back</a>');
   $("[name=sample]").focus().jqxInput({placeHolder: "Scan a sample", width: 200, minLength: 1 });
   $("#jqxSubmitButton").on('click', uzp.saveMh2).jqxButton({ width: '150'});

   uzp.prevSample = undefined;
   uzp.curSample = undefined;
   uzp.curSampleType = undefined;
   uzp.prevSampleType = undefined;
   $(document).keypress(uzp.receiveSampleKeypress);
</script>
<?php
   }

   /**
    * Renders the Plate2 to Muller Hinton page
    */
   private function plate2ToMHSave() {
      $checkQuery = 'select id from plate2 where plate = :colony';
      $insertQuery = 'insert into mh2_assoc(plate2_id, mh, user) values(:field_sample_id, :broth_sample, :user)';

      $result = $this->Dbase->ExecuteQuery($checkQuery, array('colony' => $_POST['field_sample']));
      if($result == 1) die(json_encode(array('error' => true, 'mssg' => $this->Dbase->lastError)));
      else if(count($result) == 0) die(json_encode(array('error' => true, 'mssg' => "Plate '{$_POST['field_sample']}' is not in the database.")));

      // now add the association
      $result = $this->Dbase->ExecuteQuery($insertQuery, array('field_sample_id' => $result[0]['id'], 'broth_sample' => $_POST['broth_sample'], 'user' => $_POST['cur_user']));
      if($result == 1) die(json_encode(array('error' => true, 'mssg' => $this->Dbase->lastError)));
      else die(json_encode(array('error' => false, 'mssg' => 'The association has been saved succesfully.')));
   }

   private function bioChemTestPrepHome(){
      $userCombo = $this->usersCombo();
?>
    <link rel="stylesheet" href="<?php echo OPTIONS_COMMON_FOLDER_PATH; ?>jqwidgets/jqwidgets/styles/jqx.base.css" type="text/css" />
    <script type="text/javascript" src="js/uzp_lab.js"></script>
    <script type="text/javascript" src="<?php echo OPTIONS_COMMON_FOLDER_PATH; ?>jquery/jquery.min.js"></script>
    <script type="text/javascript" src="<?php echo OPTIONS_COMMON_FOLDER_PATH; ?>jqwidgets/jqwidgets/jqxcore.js"></script>
    <script type="text/javascript" src="<?php echo OPTIONS_COMMON_FOLDER_PATH; ?>jqwidgets/jqwidgets/jqxinput.js"></script>
    <script type="text/javascript" src="<?php echo OPTIONS_COMMON_FOLDER_PATH; ?>jqwidgets/jqwidgets/jqxbuttons.js"></script>
    <script type="text/javascript" src="<?php echo OPTIONS_COMMON_FOLDER_PATH; ?>jqwidgets/jqwidgets/jqxnotification.js"></script>

<div id="colonies">
   <h3 class="center" id="home_title">Biochemical test</h3>
   <a href="./?page=" style="float: left; margin-bottom: 10px;">Back</a> <br />
   <div class="scan">
      <div id="plate_format"><label style="float: left;">Plate format: </label>&nbsp;&nbsp;<input type="text" name="plate_format" class="input-small" value="AVAQ70919" /></div>
      <div id="media_format"><label style="float: left;">Media format: </label>&nbsp;&nbsp;<input type="text" name="media_format" class="input-small" value="BSR010959" /></div>
      <div id="current_user"><label style="float: left;">Current User: </label>&nbsp;&nbsp;<?php echo $userCombo; ?></div> <br />
      <div class="center">
         <input type="text" name="sample" />
         <label>Scanned colonies</label><div id="scanned_colonies" class="center"></div>
         <div>
            <input style='margin-top: 5px;' type="submit" value="Submit" id='jqxSubmitButton' />
         </div>
      </div>
   </div>
   <div class="received"><div class="saved">Saved colonies appear here</div></div>
</div>
<div id="notification_box"><div id="msg"></div></div>
<script>
   var uzp = new Uzp();

   $('#whoisme .back').html('<a href=\'?page=home\'>Back</a>');
   $("[name=sample]").focus().jqxInput({placeHolder: "Scan a sample", width: 200, minLength: 1 });
   $("#jqxSubmitButton").on('click', uzp.saveBioChemPrep).jqxButton({ width: '150'});

   uzp.prevSample = undefined;
   uzp.curSample = undefined;
   uzp.curSampleType = undefined;
   uzp.prevSampleType = undefined;
   uzp.mediumBarcodeList = [];
   uzp.plateSample = undefined;
   $(document).keypress(uzp.receiveSampleKeypress);
</script>
<?php
   }

   /**
    * Renders the Biochemical test preperation page
    */
   private function bioChemTestPrepSave() {
      $checkQuery = 'select id from mh2_assoc where mh = :plate';
      $insertQuery = 'insert into biochemical_test(mh2_id, media, user) values(:plate, :media, :user)';

      $result = $this->Dbase->ExecuteQuery($checkQuery, array('plate' => $_POST['plate']));
      if($result == 1) die(json_encode(array('error' => true, 'mssg' => $this->Dbase->lastError)));
      else if(count($result) == 0) die(json_encode(array('error' => true, 'mssg' => "The plate '{$_POST['plate']}' is not in the database.")));

      // now add the association
      $this->Dbase->StartTrans();
      foreach($_POST['colonies'] as $colony){
         $res = $this->Dbase->ExecuteQuery($insertQuery, array('plate' => $result[0]['id'], 'media' => $colony, 'user' => $_POST['cur_user']));
         if($res == 1){
            $this->Dbase->RollBackTrans();
            if($this->Dbase->lastErrorCodes[1] == 1062) die(json_encode(array('error' => true, 'mssg' => 'Duplicate entry for the current association')));
            else die(json_encode(array('error' => true, 'mssg' => $this->Dbase->lastError)));
         }
      }
      $this->Dbase->CommitTrans();
      die(json_encode(array('error' => false, 'mssg' => 'The association has been saved succesfully.')));

   }

   /**
    * Renders the biochemical test result page
    */
   private function bioChemTestResultHome(){
      $userCombo = $this->usersCombo();
      $testCombo = $this->bioChemicalTestCombo();
?>
    <link rel="stylesheet" href="<?php echo OPTIONS_COMMON_FOLDER_PATH; ?>jqwidgets/jqwidgets/styles/jqx.base.css" type="text/css" />
    <script type="text/javascript" src="js/uzp_lab.js"></script>
    <script type="text/javascript" src="<?php echo OPTIONS_COMMON_FOLDER_PATH; ?>jquery/jquery.min.js"></script>
    <script type="text/javascript" src="<?php echo OPTIONS_COMMON_FOLDER_PATH; ?>jqwidgets/jqwidgets/jqxcore.js"></script>
    <script type="text/javascript" src="<?php echo OPTIONS_COMMON_FOLDER_PATH; ?>jqwidgets/jqwidgets/jqxinput.js"></script>
    <script type="text/javascript" src="<?php echo OPTIONS_COMMON_FOLDER_PATH; ?>jqwidgets/jqwidgets/jqxbuttons.js"></script>
    <script type="text/javascript" src="<?php echo OPTIONS_COMMON_FOLDER_PATH; ?>jqwidgets/jqwidgets/jqxnotification.js"></script>

<div id="biochem_test">
   <h3 class="center" id="home_title">Biochemical Test Results</h3>
   <a href="./?page=" style="float: left; margin-bottom: 10px;">Back</a> <br />
   <div class="scan">
      <div id="current_user"><label style="float: left;">Current User: </label>&nbsp;&nbsp;<?php echo $userCombo; ?></div> <br />
      <div id="sample_div"><label style="float: left;">Sample barcode: </label>&nbsp;&nbsp;<input type="text" name="sample" /></div> <br />
      <div class="center">
         <div id="test_name"><label style="display: initial;">Test done: </label>&nbsp;&nbsp;<?php echo $testCombo; ?></div> <br />
         <div id="res1" style='display: table; margin-left: 160px;'><label id="res1_label" style="display: inline-block;width: 150px;"></label></div> <br />
         <div id="res2" style='display: table; margin-left: 160px;'><label id="res2_label" style="display: inline-block;width: 150px;"></label></div> <br />
         <div id="res3" style='display: table; margin-left: 160px;'><label id="res3_label" style="display: inline-block;width: 150px;"></label></div> <br />
         <div id="res4" style='display: table; margin-left: 160px;'><label id="res4_label" style="display: inline-block;width: 150px;"></label></div> <br />

         <div>
            <input style='margin-top: 5px;' type="submit" value="Submit" id='jqxSubmitButton' />
         </div>
      </div>
   </div>
   <div class="received"><div class="saved">Recorded tests appear here</div></div>
</div>
<div id="notification_box"><div id="msg"></div></div>
<script>
   var uzp = new Uzp();

   $('#whoisme .back').html('<a href=\'?page=home\'>Back</a>');
   $("[name=sample]").focus().jqxInput({placeHolder: "Scan a sample", width: 200, minLength: 1 });
   $("#jqxSubmitButton").on('click', uzp.saveBioChemResult).jqxButton({ width: '150'});
   $("#testId").change(uzp.biochemTestLogic);
   uzp.prevSample = undefined;
   uzp.curSample = undefined;
   uzp.curSampleType = undefined;
   uzp.prevSampleType = undefined;
</script>
<?php
   }

   /**
    * Saves a new association of the broth enrichment
    */
   private function bioChemTestResultSave(){
      /**
       * check whether the parent sample is in the database
       * if it is in the database, save the association
       */
      //{cur_user: cur_user, sample: sample, test_name: test_name, test_result: test_result}
      $checkQuery = 'select id from biochemical_test where media = :media';
      $insertQuery = 'insert into biochemical_test_results(media_id, test, observ_type, observ_value, user) values(:media_id, :test, :observ_type, :observ_value, :user)';

      $result = $this->Dbase->ExecuteQuery($checkQuery, array('media' => $_POST['sample']));
      if($result == 1) die(json_encode(array('error' => true, 'mssg' => $this->Dbase->lastError)));
      else if(count($result) == 0) die(json_encode(array('error' => true, 'mssg' => "The sample '{$_POST['sample']}' is not in the database.")));

      // now add the association
      foreach($_POST['observations'] as $currTest){
         $res = $this->Dbase->ExecuteQuery($insertQuery, array('media_id' => $result[0]['id'], 'test' => $_POST['test'], 'observ_type' => $currTest['name'], 'observ_value' => $currTest['result'], 'user' => $_POST['cur_user']));
         if($res == 1) die(json_encode(array('error' => true, 'mssg' => $this->Dbase->lastError)));
      }
      die(json_encode(array('error' => false, 'mssg' => 'Test has been saved succesfully.')));
   }

   private function plate3Home(){
      $userCombo = $this->usersCombo();
?>
    <link rel="stylesheet" href="<?php echo OPTIONS_COMMON_FOLDER_PATH; ?>jqwidgets/jqwidgets/styles/jqx.base.css" type="text/css" />
    <script type="text/javascript" src="js/uzp_lab.js"></script>
    <script type="text/javascript" src="<?php echo OPTIONS_COMMON_FOLDER_PATH; ?>jquery/jquery.min.js"></script>
    <script type="text/javascript" src="<?php echo OPTIONS_COMMON_FOLDER_PATH; ?>jqwidgets/jqwidgets/jqxcore.js"></script>
    <script type="text/javascript" src="<?php echo OPTIONS_COMMON_FOLDER_PATH; ?>jqwidgets/jqwidgets/jqxinput.js"></script>
    <script type="text/javascript" src="<?php echo OPTIONS_COMMON_FOLDER_PATH; ?>jqwidgets/jqwidgets/jqxbuttons.js"></script>
    <script type="text/javascript" src="<?php echo OPTIONS_COMMON_FOLDER_PATH; ?>jqwidgets/jqwidgets/jqxnotification.js"></script>

<div id="broth_enrichment">
   <h3 class="center" id="home_title">Third Plating (Archival -> Plate 3)</h3>
   <a href="./?page=" style="float: left; margin-bottom: 10px;">Back</a> <br />
   <div class="scan">
      <div id="colony_format"><label style="float: left;">Archived vial format: </label>&nbsp;&nbsp;<input type="text" name="colony_format" class="input-small" value="AVAQ70919" /></div>
      <div id="plate_format"><label style="float: left;">Plate 3 format: </label>&nbsp;&nbsp;<input type="text" name="plate_format" class="input-small" value="BSR010959" /></div>
      <div id="current_user"><label style="float: left;">Current User: </label>&nbsp;&nbsp;<?php echo $userCombo; ?></div> <br />

      <div class="center">
         <input type="text" name="sample" />
         <div>
            <input style='margin-top: 5px;' type="submit" value="Submit" id='jqxSubmitButton' />
         </div>
      </div>
   </div>
   <div class="received"><div class="saved">Linked samples appear here</div></div>
</div>
<div id="notification_box"><div id="msg"></div></div>
<script>
   var uzp = new Uzp();

   $('#whoisme .back').html('<a href=\'?page=home\'>Back</a>');
   $("[name=sample]").focus().jqxInput({placeHolder: "Scan a sample", width: 200, minLength: 1 });
   $("#jqxSubmitButton").on('click', uzp.savePlate3).jqxButton({ width: '150'});

   uzp.prevSample = undefined;
   uzp.curSample = undefined;
   uzp.curSampleType = undefined;
   uzp.prevSampleType = undefined;
   $(document).keypress(uzp.receiveSampleKeypress);
</script>
<?php
   }

   /**
    * Saves a new association of the broth enrichment
    */
   private function plate3Save(){
      /**
       * check whether the parent sample is in the database
       * if it is in the database, save the association
       */
      $checkQuery = 'select id from mh_vial where mh_vial = :colony';
      $insertQuery = 'insert into plate3(mh_vial_id, plate, user) values(:field_sample_id, :broth_sample, :user)';

      $result = $this->Dbase->ExecuteQuery($checkQuery, array('colony' => $_POST['field_sample']));
      if($result == 1) die(json_encode(array('error' => true, 'mssg' => $this->Dbase->lastError)));
      else if(count($result) == 0) die(json_encode(array('error' => true, 'mssg' => "The field sample '{$_POST['field_sample']}' is not in the database.")));

      // now add the association
      $result = $this->Dbase->ExecuteQuery($insertQuery, array('field_sample_id' => $result[0]['id'], 'broth_sample' => $_POST['broth_sample'], 'user' => $_POST['cur_user']));
      if($result == 1) die(json_encode(array('error' => true, 'mssg' => $this->Dbase->lastError)));
      else die(json_encode(array('error' => false, 'mssg' => 'The association has been saved succesfully.')));
   }

   private function plate3ToMHHome() {
      $userCombo = $this->usersCombo();
?>
    <link rel="stylesheet" href="<?php echo OPTIONS_COMMON_FOLDER_PATH; ?>jqwidgets/jqwidgets/styles/jqx.base.css" type="text/css" />
    <script type="text/javascript" src="js/uzp_lab.js"></script>
    <script type="text/javascript" src="<?php echo OPTIONS_COMMON_FOLDER_PATH; ?>jquery/jquery.min.js"></script>
    <script type="text/javascript" src="<?php echo OPTIONS_COMMON_FOLDER_PATH; ?>jqwidgets/jqwidgets/jqxcore.js"></script>
    <script type="text/javascript" src="<?php echo OPTIONS_COMMON_FOLDER_PATH; ?>jqwidgets/jqwidgets/jqxinput.js"></script>
    <script type="text/javascript" src="<?php echo OPTIONS_COMMON_FOLDER_PATH; ?>jqwidgets/jqwidgets/jqxbuttons.js"></script>
    <script type="text/javascript" src="<?php echo OPTIONS_COMMON_FOLDER_PATH; ?>jqwidgets/jqwidgets/jqxnotification.js"></script>

<div id="broth_enrichment">
   <h3 class="center" id="home_title">Plate 3 to MH Plate</h3>
   <a href="./?page=" style="float: left; margin-bottom: 10px;">Back</a> <br />
   <div class="scan">
      <div id="colony_format"><label style="float: left;">Plate 3 format: </label>&nbsp;&nbsp;<input type="text" name="colony_format" class="input-small" value="AVAQ70919" /></div>
      <div id="plate_format"><label style="float: left;">MH Plate format: </label>&nbsp;&nbsp;<input type="text" name="plate_format" class="input-small" value="BSR010959" /></div>
      <div id="current_user"><label style="float: left;">Current User: </label>&nbsp;&nbsp;<?php echo $userCombo; ?></div> <br />

      <div class="center">
         <input type="text" name="sample" />
         <div>
            <input style='margin-top: 5px;' type="submit" value="Submit" id='jqxSubmitButton' />
         </div>
      </div>
   </div>
   <div class="received"><div class="saved">Linked samples appear here</div></div>
</div>
<div id="notification_box"><div id="msg"></div></div>
<script>
   var uzp = new Uzp();

   $('#whoisme .back').html('<a href=\'?page=home\'>Back</a>');
   $("[name=sample]").focus().jqxInput({placeHolder: "Scan a sample", width: 200, minLength: 1 });
   $("#jqxSubmitButton").on('click', uzp.saveMh3).jqxButton({ width: '150'});

   uzp.prevSample = undefined;
   uzp.curSample = undefined;
   uzp.curSampleType = undefined;
   uzp.prevSampleType = undefined;
   $(document).keypress(uzp.receiveSampleKeypress);
</script>
<?php
   }

   /**
    * Renders the plate 3 to muller hinton to page
    */
   private function plate3ToMHSave() {
      $checkQuery = 'select id from plate3 where plate = :colony';
      $insertQuery = 'insert into mh3_assoc(plate3_id, mh, user) values(:field_sample_id, :broth_sample, :user)';

      $result = $this->Dbase->ExecuteQuery($checkQuery, array('colony' => $_POST['field_sample']));
      if($result == 1) die(json_encode(array('error' => true, 'mssg' => $this->Dbase->lastError)));
      else if(count($result) == 0) die(json_encode(array('error' => true, 'mssg' => "Plate '{$_POST['field_sample']}' is not in the database.")));

      // now add the association
      $result = $this->Dbase->ExecuteQuery($insertQuery, array('field_sample_id' => $result[0]['id'], 'broth_sample' => $_POST['broth_sample'], 'user' => $_POST['cur_user']));
      if($result == 1) die(json_encode(array('error' => true, 'mssg' => $this->Dbase->lastError)));
      else die(json_encode(array('error' => false, 'mssg' => 'The association has been saved succesfully.')));
   }

   private function plate6ToMHHome() {
      $userCombo = $this->usersCombo();
?>
    <link rel="stylesheet" href="<?php echo OPTIONS_COMMON_FOLDER_PATH; ?>jqwidgets/jqwidgets/styles/jqx.base.css" type="text/css" />
    <script type="text/javascript" src="js/uzp_lab.js"></script>
    <script type="text/javascript" src="<?php echo OPTIONS_COMMON_FOLDER_PATH; ?>jquery/jquery.min.js"></script>
    <script type="text/javascript" src="<?php echo OPTIONS_COMMON_FOLDER_PATH; ?>jqwidgets/jqwidgets/jqxcore.js"></script>
    <script type="text/javascript" src="<?php echo OPTIONS_COMMON_FOLDER_PATH; ?>jqwidgets/jqwidgets/jqxinput.js"></script>
    <script type="text/javascript" src="<?php echo OPTIONS_COMMON_FOLDER_PATH; ?>jqwidgets/jqwidgets/jqxbuttons.js"></script>
    <script type="text/javascript" src="<?php echo OPTIONS_COMMON_FOLDER_PATH; ?>jqwidgets/jqwidgets/jqxnotification.js"></script>

<div id="broth_enrichment">
   <h3 class="center" id="home_title">Plate 6 to MH Plate</h3>
   <a href="./?page=" style="float: left; margin-bottom: 10px;">Back</a> <br />
   <div class="scan">
      <div id="colony_format"><label style="float: left;">Plate 6 format: </label>&nbsp;&nbsp;<input type="text" name="colony_format" class="input-small" value="AVAQ70919" /></div>
      <div id="plate_format"><label style="float: left;">MH Plate format: </label>&nbsp;&nbsp;<input type="text" name="plate_format" class="input-small" value="BSR010959" /></div>
      <div id="current_user"><label style="float: left;">Current User: </label>&nbsp;&nbsp;<?php echo $userCombo; ?></div> <br />

      <div class="center">
         <input type="text" name="sample" />
         <div>
            <input style='margin-top: 5px;' type="submit" value="Submit" id='jqxSubmitButton' />
         </div>
      </div>
   </div>
   <div class="received"><div class="saved">Linked samples appear here</div></div>
</div>
<div id="notification_box"><div id="msg"></div></div>
<script>
   var uzp = new Uzp();

   $('#whoisme .back').html('<a href=\'?page=home\'>Back</a>');
   $("[name=sample]").focus().jqxInput({placeHolder: "Scan a sample", width: 200, minLength: 1 });
   $("#jqxSubmitButton").on('click', uzp.saveMh6).jqxButton({ width: '150'});

   uzp.prevSample = undefined;
   uzp.curSample = undefined;
   uzp.curSampleType = undefined;
   uzp.prevSampleType = undefined;
   $(document).keypress(uzp.receiveSampleKeypress);
</script>
<?php
   }

   /**
    * Renders the plate 6 to muller hinton page
    */
   private function plate6ToMHSave() {
      $checkQuery = 'select id from plate6 where plate = :colony';
      $insertQuery = 'insert into mh6_assoc(plate6_id, mh, user) values(:field_sample_id, :broth_sample, :user)';

      $result = $this->Dbase->ExecuteQuery($checkQuery, array('colony' => $_POST['field_sample']));
      if($result == 1) die(json_encode(array('error' => true, 'mssg' => $this->Dbase->lastError)));
      else if(count($result) == 0) die(json_encode(array('error' => true, 'mssg' => "Plate '{$_POST['field_sample']}' is not in the database.")));

      // now add the association
      $result = $this->Dbase->ExecuteQuery($insertQuery, array('field_sample_id' => $result[0]['id'], 'broth_sample' => $_POST['broth_sample'], 'user' => $_POST['cur_user']));
      if($result == 1) die(json_encode(array('error' => true, 'mssg' => $this->Dbase->lastError)));
      else die(json_encode(array('error' => false, 'mssg' => 'The association has been saved succesfully.')));
   }

   /**
    * Renders the plate 3 to plate 4 and 5 page
    */
   private function plate3to45Home(){
      $userCombo = $this->usersCombo();
?>
    <link rel="stylesheet" href="<?php echo OPTIONS_COMMON_FOLDER_PATH; ?>jqwidgets/jqwidgets/styles/jqx.base.css" type="text/css" />
    <script type="text/javascript" src="js/uzp_lab.js"></script>
    <script type="text/javascript" src="<?php echo OPTIONS_COMMON_FOLDER_PATH; ?>jquery/jquery.min.js"></script>
    <script type="text/javascript" src="<?php echo OPTIONS_COMMON_FOLDER_PATH; ?>jqwidgets/jqwidgets/jqxcore.js"></script>
    <script type="text/javascript" src="<?php echo OPTIONS_COMMON_FOLDER_PATH; ?>jqwidgets/jqwidgets/jqxinput.js"></script>
    <script type="text/javascript" src="<?php echo OPTIONS_COMMON_FOLDER_PATH; ?>jqwidgets/jqwidgets/jqxbuttons.js"></script>
    <script type="text/javascript" src="<?php echo OPTIONS_COMMON_FOLDER_PATH; ?>jqwidgets/jqwidgets/jqxnotification.js"></script>

<div id="colonies">
   <h3 class="center" id="home_title">MH Plate -> Plate 4 and Plate 5</h3>
   <a href="./?page=" style="float: left; margin-bottom: 10px;">Back</a> <br />
   <div class="scan">
      <div id="plate_format"><label style="float: left;">MH Plate format: </label>&nbsp;&nbsp;<input type="text" name="plate_format" class="input-small" value="AVAQ70919" /></div>
      <div id="media_format"><label style="float: left;">Plate 4,5 format: </label>&nbsp;&nbsp;<input type="text" name="media_format" class="input-small" value="BSR010959" /></div>
      <div id="current_user"><label style="float: left;">Current User: </label>&nbsp;&nbsp;<?php echo $userCombo; ?></div> <br />

      <div class="center">
         <input type="text" name="sample" />
         <label>Scanned colonies</label><div id="scanned_colonies" class="center"></div>
         <div>
            <input style='margin-top: 5px;' type="submit" value="Submit" id='jqxSubmitButton' />
         </div>
      </div>
   </div>
   <div class="received"><div class="saved">Saved colonies appear here</div></div>
</div>
<div id="notification_box"><div id="msg"></div></div>
<script>
   var uzp = new Uzp();

   $('#whoisme .back').html('<a href=\'?page=home\'>Back</a>');
   $("[name=sample]").focus().jqxInput({placeHolder: "Scan a sample", width: 200, minLength: 1 });
   $("#jqxSubmitButton").on('click', uzp.savePlate3to45).jqxButton({ width: '150'});

   uzp.prevSample = undefined;
   uzp.curSample = undefined;
   uzp.curSampleType = undefined;
   uzp.prevSampleType = undefined;
   uzp.mediumBarcodeList = [];
   uzp.plateSample = undefined;
   $(document).keypress(uzp.receiveSampleKeypress);
</script>
<?php
   }

   /**
    * Saves request data from the plate 3 to plate 4 and 5 page
    */
   private function plate3to45Save() {
      $checkQuery = 'select id from mh3_assoc where mh = :plate';
      $insertQuery = 'insert into plate45(mh3_id, plate, number, user) values(:plate3_id, :curr_plate, :number, :user)';

      $result = $this->Dbase->ExecuteQuery($checkQuery, array('plate' => $_POST['plate']));
      if($result == 1) die(json_encode(array('error' => true, 'mssg' => $this->Dbase->lastError)));
      else if(count($result) == 0) die(json_encode(array('error' => true, 'mssg' => "The plate '{$_POST['plate']}' is not in the database.")));

      // now add the association
      $this->Dbase->StartTrans();
      $number = 4;
      foreach($_POST['colonies'] as $colony){
         $res = $this->Dbase->ExecuteQuery($insertQuery, array('plate3_id' => $result[0]['id'], 'curr_plate' => $colony, 'number' => $number, 'user' => $_POST['cur_user']));
         if($res == 1){
            $this->Dbase->RollBackTrans();
            if($this->Dbase->lastErrorCodes[1] == 1062) die(json_encode(array('error' => true, 'mssg' => 'Duplicate entry for the current association')));
            else die(json_encode(array('error' => true, 'mssg' => $this->Dbase->lastError)));
         }
         $number++;
      }
      $this->Dbase->CommitTrans();
      die(json_encode(array('error' => false, 'mssg' => 'The association has been saved succesfully.')));
   }

   /**
    * Renders the AST results page
    */
   private function astResultHome(){
      $userCombo = $this->usersCombo();
      $drugNameTable = $this->drugNameTable();
?>
    <link rel="stylesheet" href="<?php echo OPTIONS_COMMON_FOLDER_PATH; ?>jqwidgets/jqwidgets/styles/jqx.base.css" type="text/css" />
    <script type="text/javascript" src="js/uzp_lab.js"></script>
    <script type="text/javascript" src="<?php echo OPTIONS_COMMON_FOLDER_PATH; ?>jquery/jquery.min.js"></script>
    <script type="text/javascript" src="<?php echo OPTIONS_COMMON_FOLDER_PATH; ?>jqwidgets/jqwidgets/jqxcore.js"></script>
    <script type="text/javascript" src="<?php echo OPTIONS_COMMON_FOLDER_PATH; ?>jqwidgets/jqwidgets/jqxinput.js"></script>
    <script type="text/javascript" src="<?php echo OPTIONS_COMMON_FOLDER_PATH; ?>jqwidgets/jqwidgets/jqxbuttons.js"></script>
    <script type="text/javascript" src="<?php echo OPTIONS_COMMON_FOLDER_PATH; ?>jqwidgets/jqwidgets/jqxnotification.js"></script>

<div id="ast_result">
   <h3 class="center" id="home_title">Plates 4,5 -> AST Result Reading</h3>
   <a href="./?page=" style="float: left; margin-bottom: 10px;">Back</a> <br />
   <div class="scan">
      <div id="current_user"><label style="float: left;">Current User: </label>&nbsp;&nbsp;<?php echo $userCombo; ?></div>
      <div id="sample_div"><label style="float: left;">Sample barcode: </label>&nbsp;&nbsp;<input type="text" name="sample" /></div> <br />
      <div class="center">
         <?php echo $drugNameTable;?>
         <div>
            <input style='margin-top: 5px;' type="submit" value="Submit" id='jqxSubmitButton' />
         </div>
      </div>
   </div>
   <div class="received"><div class="saved">Recorded tests appear here</div></div>
</div>
<div id="notification_box"><div id="msg"></div></div>
<script>
   var uzp = new Uzp();

   $('#whoisme .back').html('<a href=\'?page=home\'>Back</a>');
   $("[name=sample]").focus().jqxInput({placeHolder: "Scan a sample", width: 200, minLength: 1 });
   $("#jqxSubmitButton").on('click', uzp.saveAstResult).jqxButton({ width: '150'});

   uzp.prevSample = undefined;
   uzp.curSample = undefined;
   uzp.curSampleType = undefined;
   uzp.prevSampleType = undefined;
</script>
<?php
   }

   /**
    * Saves a new association of the broth enrichment
    */
   private function astResultSave(){
      /**
       * check whether the parent sample is in the database
       * if it is in the database, save the association
       */
      //{cur_user: cur_user, sample: sample, test_name: test_name, test_result: test_result}
      $checkQuery = 'select id from plate45 where plate = :plate';
      $insertQuery = 'insert into ast_result(plate45_id, drug, value, user) values(:plate45_id, :drug, :value, :user)';

      $result = $this->Dbase->ExecuteQuery($checkQuery, array('plate' => $_POST['sample']));
      if($result == 1) die(json_encode(array('error' => true, 'mssg' => $this->Dbase->lastError)));
      else if(count($result) == 0) die(json_encode(array('error' => true, 'mssg' => "The sample '{$_POST['sample']}' is not in the database.")));

      // now add the association
      foreach($_POST['drugs'] as $currDrug) {
         $res = $this->Dbase->ExecuteQuery($insertQuery, array('plate45_id' => $result[0]['id'], 'drug' => $currDrug['name'], 'value' => $currDrug['value'], 'user' => $_POST['cur_user']));
         if($res == 1) die(json_encode(array('error' => true, 'mssg' => $this->Dbase->lastError)));
      }
      die(json_encode(array('error' => false, 'mssg' => 'Tests have been saved succesfully.')));
   }

   private function regrowHome(){
      $userCombo = $this->usersCombo();
?>
    <link rel="stylesheet" href="<?php echo OPTIONS_COMMON_FOLDER_PATH; ?>jqwidgets/jqwidgets/styles/jqx.base.css" type="text/css" />
    <script type="text/javascript" src="js/uzp_lab.js"></script>
    <script type="text/javascript" src="<?php echo OPTIONS_COMMON_FOLDER_PATH; ?>jquery/jquery.min.js"></script>
    <script type="text/javascript" src="<?php echo OPTIONS_COMMON_FOLDER_PATH; ?>jqwidgets/jqwidgets/jqxcore.js"></script>
    <script type="text/javascript" src="<?php echo OPTIONS_COMMON_FOLDER_PATH; ?>jqwidgets/jqwidgets/jqxinput.js"></script>
    <script type="text/javascript" src="<?php echo OPTIONS_COMMON_FOLDER_PATH; ?>jqwidgets/jqwidgets/jqxbuttons.js"></script>
    <script type="text/javascript" src="<?php echo OPTIONS_COMMON_FOLDER_PATH; ?>jqwidgets/jqwidgets/jqxnotification.js"></script>

<div id="broth_enrichment">
   <h3 class="center" id="home_title">Sixth Plating (Archival -> Plate 6)</h3>
   <a href="./?page=" style="float: left; margin-bottom: 10px;">Back</a> <br />
   <div class="scan">
      <div id="colony_format"><label style="float: left;">Colony format: </label>&nbsp;&nbsp;<input type="text" name="colony_format" class="input-small" value="AVAQ70919" /></div>
      <div id="plate_format"><label style="float: left;">Plate 6 format: </label>&nbsp;&nbsp;<input type="text" name="plate_format" class="input-small" value="BSR010959" /></div>
      <div id="current_user"><label style="float: left;">Current User: </label>&nbsp;&nbsp;<?php echo $userCombo; ?></div> <br />

      <div class="center">
         <input type="text" name="sample" />
         <div>
            <input style='margin-top: 5px;' type="submit" value="Submit" id='jqxSubmitButton' />
         </div>
      </div>
   </div>
   <div class="received"><div class="saved">Linked samples appear here</div></div>
</div>
<div id="notification_box"><div id="msg"></div></div>
<script>
   var uzp = new Uzp();

   $('#whoisme .back').html('<a href=\'?page=home\'>Back</a>');
   $("[name=sample]").focus().jqxInput({placeHolder: "Scan a sample", width: 200, minLength: 1 });
   $("#jqxSubmitButton").on('click', uzp.saveRegrow).jqxButton({ width: '150'});

   uzp.prevSample = undefined;
   uzp.curSample = undefined;
   uzp.curSampleType = undefined;
   uzp.prevSampleType = undefined;
   $(document).keypress(uzp.receiveSampleKeypress);
</script>
<?php
   }

   /**
    * Saves a new association of the broth enrichment
    */
   private function regrowSave(){
      /**
       * check whether the parent sample is in the database
       * if it is in the database, save the association
       */
      $checkQuery = 'select id from mh_vial where mh_vial = :colony';
      $insertQuery = 'insert into plate6(mh_vial_id, plate, user) values(:field_sample_id, :broth_sample, :user)';

      $result = $this->Dbase->ExecuteQuery($checkQuery, array('colony' => $_POST['field_sample']));
      if($result == 1) die(json_encode(array('error' => true, 'mssg' => $this->Dbase->lastError)));
      else if(count($result) == 0) die(json_encode(array('error' => true, 'mssg' => "The field sample '{$_POST['field_sample']}' is not in the database.")));

      // now add the association
      $result = $this->Dbase->ExecuteQuery($insertQuery, array('field_sample_id' => $result[0]['id'], 'broth_sample' => $_POST['broth_sample'], 'user' => $_POST['cur_user']));
      if($result == 1) die(json_encode(array('error' => true, 'mssg' => $this->Dbase->lastError)));
      else die(json_encode(array('error' => false, 'mssg' => 'The association has been saved succesfully.')));
   }

   /**
    * Renders the muller hinton to eppendorf page
    */
   private function plateToEppendorfHome(){
      $userCombo = $this->usersCombo();
?>
    <link rel="stylesheet" href="<?php echo OPTIONS_COMMON_FOLDER_PATH; ?>jqwidgets/jqwidgets/styles/jqx.base.css" type="text/css" />
    <script type="text/javascript" src="js/uzp_lab.js"></script>
    <script type="text/javascript" src="<?php echo OPTIONS_COMMON_FOLDER_PATH; ?>jquery/jquery.min.js"></script>
    <script type="text/javascript" src="<?php echo OPTIONS_COMMON_FOLDER_PATH; ?>jqwidgets/jqwidgets/jqxcore.js"></script>
    <script type="text/javascript" src="<?php echo OPTIONS_COMMON_FOLDER_PATH; ?>jqwidgets/jqwidgets/jqxinput.js"></script>
    <script type="text/javascript" src="<?php echo OPTIONS_COMMON_FOLDER_PATH; ?>jqwidgets/jqwidgets/jqxbuttons.js"></script>
    <script type="text/javascript" src="<?php echo OPTIONS_COMMON_FOLDER_PATH; ?>jqwidgets/jqwidgets/jqxnotification.js"></script>

<div id="broth_enrichment">
   <h3 class="center" id="home_title">MH Plate -> Eppendorf / DNA Extract (12)</h3>
   <a href="./?page=" style="float: left; margin-bottom: 10px;">Back</a> <br />
   <div class="scan">
      <div id="current_user"><label style="float: left;">Current User: </label>&nbsp;&nbsp;<?php echo $userCombo; ?></div>
      <div class="center">
         <div id="eppendorf_label"></div><br/>
         <input type="text" name="sample" />
         <div>
            <input style='margin-top: 5px;' type="submit" value="Submit" id='jqxSubmitButton' />
         </div>
      </div>
   </div>
   <div class="received"><div class="saved">Recorded plates here</div></div>
</div>
<div id="notification_box"><div id="msg"></div></div>
<script>
   var uzp = new Uzp();

   $('#whoisme .back').html('<a href=\'?page=home\'>Back</a>');
   $("[name=sample]").focus().jqxInput({placeHolder: "Scan a sample", width: 200, minLength: 1 });
   $("#jqxSubmitButton").on('click', uzp.savePlateToEppendorfs).jqxButton({ width: '150'});

   uzp.prevSample = undefined;
   uzp.curSample = undefined;
   uzp.curSampleType = undefined;
   uzp.prevSampleType = undefined;
   $(document).keypress(uzp.receiveSampleKeypress);
</script>
<?php
   }

   /**
    * Saves a new association of the broth enrichment
    */
   private function plateToEppendorfSave(){
      /**
       * check whether the parent sample is in the database
       * if it is in the database, save the association
       */
      //{cur_user: cur_user, sample: sample, test_name: test_name, test_result: test_result}
      $checkQuery = 'select id from mh6_assoc where mh = :plate';
      $insertQuery = 'insert into dna_eppendorfs(mh6_id, eppendorf, user) values(:plate6_id, :eppendorf, :user)';

      $result = $this->Dbase->ExecuteQuery($checkQuery, array('plate' => $_POST['sample']));
      if($result == 1) die(json_encode(array('error' => true, 'mssg' => $this->Dbase->lastError)));
      else if(count($result) == 0) die(json_encode(array('error' => true, 'mssg' => "The sample '{$_POST['sample']}' is not in the database.")));

      // now add the association
      $plateId = $result[0]['id'];
      $query = "select count(id) as number from dna_eppendorfs";
      $result = $this->Dbase->ExecuteQuery($query);

      //get the number of eppendorfs in the database
      $noEppendorfs = $result[0]['number'];
      $uniqueLabelFound = false;
      $eppendorfLabel = "";
      while ($uniqueLabelFound == false) {
         $eppendorfLabel = $this->getRandomEppendorfLabel($noEppendorfs);
         $query = "select id from dna_eppendorfs where eppendorf=:eppendorf";
         $res = $this->Dbase->ExecuteQuery($query, array("eppendorf" => $eppendorfLabel));
         if($res == 1) die(json_encode(array('error' => true, 'mssg' => $this->Dbase->lastError)));
         else if(count($res) == 0) {
            $uniqueLabelFound = true;
         }
      }
      $res = $this->Dbase->ExecuteQuery($insertQuery, array("plate6_id" => $plateId, "eppendorf" => $eppendorfLabel, "user" => $_POST['cur_user']));
      if($res == 1) die(json_encode(array('error' => true, 'mssg' => $this->Dbase->lastError)));
      else die(json_encode(array('error' => false, 'mssg' => 'Eppendorf saved succesfully.', 'eppendorf' => $eppendorfLabel)));
   }

   /**
    * Generates a random eppendorf label
    *
    * @param Number $noEppendorfs  The number of already generated eppendorf labels
    * @return String The random eppendorf label
    */
   private function getRandomEppendorfLabel($noEppendorfs) {
      //use the number to get the range e.g if number is < 1000 then we are in the first range. We have 6 ranges with 4 elements each
      //ranges are: 0-1000,1001-2000..5001-6000
      $range = floor($noEppendorfs/1000);
      //get the four indexes to be considered given the range. e.g if our range is 0 then the four possible values are 0,1,2,3
      $possibleIndexs = array();
      for($index = $range; $index < ($range + 4); $index++) {
         $possibleIndexs[] = $index;
      }
      //select the lucky index from the list of four possible
      $luckyIndex = $possibleIndexs[rand(0, 3)];
      $alphabet = 'abcdefghijklmnopqrstuvwxyz';
      $digits = '0123456789';
      $firstCharacter = $alphabet[$luckyIndex];
      $secondCharacter = $alphabet[rand(0, 23)];
      $thirdCharacter = $digits[rand(0,9)];
      return strtoupper($firstCharacter.$secondCharacter.$thirdCharacter);
   }

   /**
    * Renders the DNA archiving page
    */
   private function dnaArchivingHome(){
      $userCombo = $this->usersCombo();
?>
    <link rel="stylesheet" href="<?php echo OPTIONS_COMMON_FOLDER_PATH; ?>jqwidgets/jqwidgets/styles/jqx.base.css" type="text/css" />
    <script type="text/javascript" src="js/uzp_lab.js"></script>
    <script type="text/javascript" src="<?php echo OPTIONS_COMMON_FOLDER_PATH; ?>jquery/jquery.min.js"></script>
    <script type="text/javascript" src="<?php echo OPTIONS_COMMON_FOLDER_PATH; ?>jqwidgets/jqwidgets/jqxcore.js"></script>
    <script type="text/javascript" src="<?php echo OPTIONS_COMMON_FOLDER_PATH; ?>jqwidgets/jqwidgets/jqxinput.js"></script>
    <script type="text/javascript" src="<?php echo OPTIONS_COMMON_FOLDER_PATH; ?>jqwidgets/jqwidgets/jqxbuttons.js"></script>
    <script type="text/javascript" src="<?php echo OPTIONS_COMMON_FOLDER_PATH; ?>jqwidgets/jqwidgets/jqxnotification.js"></script>

<div id="broth_enrichment">
   <h3 class="center" id="home_title">Eppendorf / DNA Extract -> Archive</h3>
   <a href="./?page=" style="float: left; margin-bottom: 10px;">Back</a> <br />
   <div class="scan">
      <div id="colony_format"><label style="float: left;">Eppendorf format: </label>&nbsp;&nbsp;<input type="text" name="colony_format" class="input-small" value="AVAQ70919" /></div>
      <div id="plate_format"><label style="float: left;">DNA barcode format: </label>&nbsp;&nbsp;<input type="text" name="plate_format" class="input-small" value="BSR010959" /></div>
      <div id="current_user"><label style="float: left;">Current User: </label>&nbsp;&nbsp;<?php echo $userCombo; ?></div> <br />

      <div class="center">
         <input type="text" name="sample" />
         <div>
            <input style='margin-top: 5px;' type="submit" value="Submit" id='jqxSubmitButton' />
         </div>
      </div>
   </div>
   <div class="received"><div class="saved">Linked samples appear here</div></div>
</div>
<div id="notification_box"><div id="msg"></div></div>
<script>
   var uzp = new Uzp();

   $('#whoisme .back').html('<a href=\'?page=home\'>Back</a>');
   $("[name=sample]").focus().jqxInput({placeHolder: "Scan a sample", width: 200, minLength: 1 });
   $("#jqxSubmitButton").on('click', uzp.saveDnaArchiving).jqxButton({ width: '150'});

   uzp.prevSample = undefined;
   uzp.curSample = undefined;
   uzp.curSampleType = undefined;
   uzp.prevSampleType = undefined;
   $(document).keypress(uzp.receiveSampleKeypress);
</script>
<?php
   }

   /**
    * Saves a new association of the broth enrichment
    */
   private function dnaArchivingSave(){
      /**
       * check whether the parent sample is in the database
       * if it is in the database, save the association
       */
      $insertQuery = 'update dna_eppendorfs set dna = :dna, user = :user where eppendorf = :eppendorf';
      // now add the association
      $result = $this->Dbase->ExecuteQuery($insertQuery, array('dna' => $_POST['dna'], 'eppendorf' => $_POST['eppendorf'], 'user' => $_POST['cur_user']));
      if($result == 1) die(json_encode(array('error' => true, 'mssg' => $this->Dbase->lastError)));
      else die(json_encode(array('error' => false, 'mssg' => 'The association saved succesfully.')));
   }
      /**
    * Create the user dropdown boxes which will be consistent in most of the modules
    *
    * @return  string   Returns a HTML string which creates the user dropdown
    */
   private function usersCombo(){
      if(Config::$config['site'] == "KEMRI") {
         $userVals = array('John Kiiru', 'Tom Ouko', 'Hannah Njeri', 'Sam Njoroge', 'Benson Kiiru', 'Purity Karimi', 'Hannah Waruguru', 'Edna Kerubo');
         $userIds = array('kiiru_john', 'Tom_Ouko', 'Hannah_Njeri', 'Sam_Njoroge', 'Benson_Kiiru', 'Purity_Karimi', 'Hannah_Waruguru', 'Edna_Kerubo');
      }
      else if(Config::$config['site'] == 'UoN') {
         $userVals = array('John Kiiru', 'Johnstone Masinde', 'Lucy Gitonga', 'Beatrice Wandia', 'Caroline Kimunye');
         $userIds = array('kiiru_john', 'Johnstone Masinde', 'Lucy Gitonga', 'Beatrice Wandia', 'Caroline Kimunye');
      }
      $settings = array('items' => $userVals, 'values' => $userIds, 'firstValue' => 'Select One', 'name' => 'users', 'id' => 'usersId', 'class' => 'input-medium');
      $userCombo = GeneralTasks::PopulateCombo($settings);

      return $userCombo;
   }

   private function sequencingCombo(){
      $userVals = array('Yes', 'No');
      $userIds = array('yes', 'no');
      $settings = array('items' => $userVals, 'values' => $userIds, 'firstValue' => 'Select One', 'name' => 'users', 'id' => 'sequencingId', 'class' => 'input-medium');
      $userCombo = GeneralTasks::PopulateCombo($settings);

      return $userCombo;
   }

   private function bioChemicalTestCombo(){
      $userVals = array('TSI', 'Urea', 'MIL', 'Citrate');
      $userIds = array('tsi', 'urea', 'mil', 'citrate');
      $settings = array('items' => $userVals, 'values' => $userIds, 'firstValue' => 'Select One', 'name' => 'tests', 'id' => 'testId', 'class' => 'input-medium');
      $userCombo = GeneralTasks::PopulateCombo($settings);

      return $userCombo;
   }

   private function bioChemicalTestResultCombo(){
      $userVals = array('Positive', 'Negative');
      $userIds = array('positive', 'negative');
      $settings = array('items' => $userVals, 'values' => $userIds, 'firstValue' => 'Select One', 'name' => 'testResults', 'id' => 'testResultId', 'class' => 'input-medium');
      $userCombo = GeneralTasks::PopulateCombo($settings);

      return $userCombo;
   }

   private function drugNameCombo(){
      $userVals = array('AMP10', 'AMC30', 'CAZ30', 'CRO30', 'AZT30', 'CTX30', 'FOX30', 'C30', 'CIP5', 'CN10', 'NA30', 'S10', 'FEP30', 'CPD10', 'CTX30', 'TRIM5', 'SUL25', 'TET30');
      $userIds = array('AMP10', 'AMC30', 'CAZ30', 'CRO30', 'AZT30', 'CTX30', 'FOX30', 'C30', 'CIP5', 'CN10', 'NA30', 'S10', 'FEP30', 'CPD10', 'CTX30', 'TRIM5', 'SUL25', 'TET30');
      $settings = array('items' => $userVals, 'values' => $userIds, 'firstValue' => 'Select One', 'name' => 'drugName', 'id' => 'drugNameId', 'class' => 'input-medium');
      $userCombo = GeneralTasks::PopulateCombo($settings);
      return $userCombo;
   }

   private function drugNameTable(){
      $userVals = array('AMP10', 'AMC30', 'CAZ30', 'CRO30', 'AZT30', 'CTX30', 'FOX30', 'C30', 'CIP5', 'CN10', 'NA30', 'S10', 'FEP30', 'CPD10', 'CTX30', 'TRIM5', 'SUL25', 'TET30');
      $userIds = array('AMP10', 'AMC30', 'CAZ30', 'CRO30', 'AZT30', 'CTX30', 'FOX30', 'C30', 'CIP5', 'CN10', 'NA30', 'S10', 'FEP30', 'CPD10', 'CTX30', 'TRIM5', 'SUL25', 'TET30');
      $html = "<div>";
      if(count($userVals) == count($userIds)) {
         $bgColor = "rgb(230, 180, 127)";
         for($index = 0; $index < count($userIds); $index++) {
            if($index == ceil(count($userIds)/2)) {
               $html .= "</table>";
               $bgColor = "rgb(230, 180, 127)";
            }
            if($index == 0 || $index == ceil(count($userIds)/2)) $html .= "<table style='margin-right: 15px;display: inline-block; background-color: ".$bgColor."; border: 15px solid ".$bgColor.";'><tr><th>Drug Name</th><th>Value 1</th><th>Value 2</th></tr>";

            $html .= "<tr><td>".$userVals[$index]."</td><td><input type='text' class='input-small' name='drug_".$userIds[$index]."_val1' id='drug_".$userIds[$index]."_val1' style='height:30px;' /></td><td><input type='text' class='input-small' name='drug_".$userIds[$index]."_val2' id='drug_".$userIds[$index]."_val2' style='height:30px;' /></td></tr>";

            if($index == (count($userIds) - 1)) $html .= "</table>";
         }
      }
      $html .= "</div>";
      return $html;
   }

   /**
    * Renders the Broth to primary plate page
    */
   private function mcConkyPlateHome(){
      $userCombo = $this->usersCombo();

      $mediaVals = array('McConky', 'EMBA');
      $mediaIds = array('McConky', 'EMBA');
      $settings = array('items' => $mediaVals, 'values' => $mediaIds, 'firstValue' => 'Select One', 'name' => 'media', 'id' => 'mediaId', 'class' => 'input-medium');
      $mediaCombo = GeneralTasks::PopulateCombo($settings);
?>
    <link rel="stylesheet" href="<?php echo OPTIONS_COMMON_FOLDER_PATH; ?>jqwidgets/jqwidgets/styles/jqx.base.css" type="text/css" />
    <script type="text/javascript" src="js/uzp_lab.js"></script>
    <script type="text/javascript" src="<?php echo OPTIONS_COMMON_FOLDER_PATH; ?>jquery/jquery.min.js"></script>
    <script type="text/javascript" src="<?php echo OPTIONS_COMMON_FOLDER_PATH; ?>jqwidgets/jqwidgets/jqxcore.js"></script>
    <script type="text/javascript" src="<?php echo OPTIONS_COMMON_FOLDER_PATH; ?>jqwidgets/jqwidgets/jqxinput.js"></script>
    <script type="text/javascript" src="<?php echo OPTIONS_COMMON_FOLDER_PATH; ?>jqwidgets/jqwidgets/jqxbuttons.js"></script>
    <script type="text/javascript" src="<?php echo OPTIONS_COMMON_FOLDER_PATH; ?>jqwidgets/jqwidgets/jqxnotification.js"></script>

<div id="mcconky_plate">
   <h3 class="center" id="home_title">Loading the broth samples on the primary plate</h3>
   <a href="./?page=" style="float: left; margin-bottom: 10px;">Back</a> <br />
   <div class="scan">
      <div id="broth_format"><label style="float: left;">Broth format: </label>&nbsp;&nbsp;<input type="text" name="broth_format" class="input-small" value="BSR010959" /></div>
      <div id="mcconky_format"><label style="float: left;">Plate format: </label>&nbsp;&nbsp;<input type="text" name="plate_format" class="input-small" value="AVAQ70919" /></div>
      <div id="media_used"><label style="float: left;">Media Used: </label>&nbsp;&nbsp;<?php echo $mediaCombo; ?></div>
      <div id="current_user"><label style="float: left;">Current User: </label>&nbsp;&nbsp;<?php echo $userCombo; ?></div> <br />

      <div class="center">
         <input type="text" name="sample" />
         <div>
            <input style='margin-top: 5px;' type="submit" value="Submit" id='jqxSubmitButton' />
         </div>
      </div>
   </div>
   <div class="received"><div class="saved">Linked plates appear here</div></div>
</div>
<div id="notification_box"><div id="msg"></div></div>
<script>
   var uzp = new Uzp();

   $('#whoisme .back').html('<a href=\'?page=home\'>Back</a>');
   $("[name=sample]").focus().jqxInput({placeHolder: "Scan a sample", width: 200, minLength: 1 });
   $("#jqxSubmitButton").on('click', uzp.saveMcconkyPlate).jqxButton({ width: '150'});

   uzp.prevSample = undefined;
   uzp.curSample = undefined;
   uzp.curSampleType = undefined;
   uzp.prevSampleType = undefined;
   $(document).keypress(uzp.receiveSampleKeypress);
</script>
<?php
   }

   private function mcConkyPlateSave(){
      /**
       * check whether the broth sample is in the database
       * if it is in the database, save the association of the broth and plate
       */
      $checkQuery = 'select id from broth_assoc where broth_sample = :sample';
      $insertQuery = 'insert into mcconky_assoc(broth_sample_id, plate1_barcode, user, media_used) values(:broth_sample_id, :plate1_barcode, :user, :media_used)';

      $result = $this->Dbase->ExecuteQuery($checkQuery, array('sample' => $_POST['broth_sample']));
      if($result == 1) die(json_encode(array('error' => true, 'mssg' => $this->Dbase->lastError)));
      else if(count($result) == 0) die(json_encode(array('error' => true, 'mssg' => "The broth sample '{$_POST['broth_sample']}' is not in the database.")));

      // now add the association
      $result = $this->Dbase->ExecuteQuery($insertQuery, array('broth_sample_id' => $result[0]['id'], 'plate1_barcode' => $_POST['plate_barcode'], 'user' => $_POST['cur_user'], 'media_used' => $_POST['media_used']));
      if($result == 1){
         if($this->Dbase->lastErrorCodes[1] == 1062) die(json_encode(array('error' => true, 'mssg' => 'Duplicate entry for the current association')));
         else die(json_encode(array('error' => true, 'mssg' => $this->Dbase->lastError)));
      }
      else die(json_encode(array('error' => false, 'mssg' => 'The association has been saved succesfully.')));
   }

   /*
    * Creates a home page for the colonies and plate association
    */
   private function coloniesHome(){
      $userCombo = $this->usersCombo();
?>
    <link rel="stylesheet" href="<?php echo OPTIONS_COMMON_FOLDER_PATH; ?>jqwidgets/jqwidgets/styles/jqx.base.css" type="text/css" />
    <script type="text/javascript" src="js/uzp_lab.js"></script>
    <script type="text/javascript" src="<?php echo OPTIONS_COMMON_FOLDER_PATH; ?>jquery/jquery.min.js"></script>
    <script type="text/javascript" src="<?php echo OPTIONS_COMMON_FOLDER_PATH; ?>jqwidgets/jqwidgets/jqxcore.js"></script>
    <script type="text/javascript" src="<?php echo OPTIONS_COMMON_FOLDER_PATH; ?>jqwidgets/jqwidgets/jqxinput.js"></script>
    <script type="text/javascript" src="<?php echo OPTIONS_COMMON_FOLDER_PATH; ?>jqwidgets/jqwidgets/jqxbuttons.js"></script>
    <script type="text/javascript" src="<?php echo OPTIONS_COMMON_FOLDER_PATH; ?>jqwidgets/jqwidgets/jqxnotification.js"></script>

<div id="colonies">
   <h3 class="center" id="home_title">Creating colonies for archival from the plate</h3>
   <a href="./?page=" style="float: left; margin-bottom: 10px;">Back</a> <br />
   <div class="scan">
      <div id="mcconky_format"><label style="float: left;">Plate format: </label>&nbsp;&nbsp;<input type="text" name="plate_format" class="input-small" value="AVAQ70919" /></div>
      <div id="colonies_format"><label style="float: left;">Colonies format: </label>&nbsp;&nbsp;<input type="text" name="colonies_format" class="input-small" value="BDT013939" /></div>
      <div id="no_qtr_colonies"><label style="float: left;">No. Colonies in quarter: </label>&nbsp;&nbsp;<input type="number" name="no_qtr_colonies" class="input-small" style="height: 30px;" /></div>
      <div id="current_user"><label style="float: left;">Current User: </label>&nbsp;&nbsp;<?php echo $userCombo; ?></div> <br />

      <div class="center">
         <input type="text" name="sample" />
         <label id="label_scanned_colonies">Scanned colonies</label><div id="scanned_colonies" class="center"></div>
         <div>
            <input style='margin-top: 5px;' type="submit" value="Submit" id='jqxSubmitButton' />
         </div>
      </div>
   </div>
   <div class="received"><div class="saved">Saved colonies appear here</div></div>
</div>
<div id="notification_box"><div id="msg"></div></div>
<script>
   var uzp = new Uzp();

   $('#whoisme .back').html('<a href=\'?page=home\'>Back</a>');
   $("[name=sample]").focus().jqxInput({placeHolder: "Scan a sample", width: 200, minLength: 1 });
   $("#jqxSubmitButton").on('click', uzp.saveColonies).jqxButton({ width: '150'});

   uzp.prevSample = undefined;
   uzp.curSample = undefined;
   uzp.curSampleType = undefined;
   uzp.prevSampleType = undefined;
   $(document).keypress(uzp.receiveSampleKeypress);
</script>
<?php
   }

   /**
    * Saves a plate and the colonies derived from that plate
    */
   private function coloniesSave(){
      /**
       * check whether the plate is in the database
       * if it is in the database, save the plate and its associated colonies
       */
      $checkQuery = 'select id from mcconky_assoc where plate1_barcode = :plate';
      $insertQuery = 'insert into colonies(mcconky_plate_id, colony, user) values(:mcconky_plate_id, :colony, :user)';

      $result = $this->Dbase->ExecuteQuery($checkQuery, array('plate' => $_POST['plate']));
      if($result == 1) die(json_encode(array('error' => true, 'mssg' => $this->Dbase->lastError)));
      else if(count($result) == 0) die(json_encode(array('error' => true, 'mssg' => "The plate '{$_POST['plate']}' is not in the database.")));

      // now add the association
      $this->Dbase->StartTrans();
      foreach($_POST['colonies'] as $colony){
         $this->Dbase->ExecuteQuery("update mcconky_assoc set no_qtr_colonies = :no_qtr_colonies where id = :id", array("no_qtr_colonies" => $_POST['no_qtr_colonies'], "id" => $result[0]['id']));
         $res = $this->Dbase->ExecuteQuery($insertQuery, array('mcconky_plate_id' => $result[0]['id'], 'colony' => $colony, 'user' => $_POST['cur_user']));
         if($res == 1){
            $this->Dbase->RollBackTrans();
            if($this->Dbase->lastErrorCodes[1] == 1062) die(json_encode(array('error' => true, 'mssg' => 'Duplicate entry for the current association')));
            else die(json_encode(array('error' => true, 'mssg' => $this->Dbase->lastError)));
         }
      }
      $this->Dbase->CommitTrans();
      die(json_encode(array('error' => false, 'mssg' => 'The association has been saved succesfully.')));
   }

   private function mhHome(){
      $userCombo = $this->usersCombo();
?>
    <link rel="stylesheet" href="<?php echo OPTIONS_COMMON_FOLDER_PATH; ?>jqwidgets/jqwidgets/styles/jqx.base.css" type="text/css" />
    <script type="text/javascript" src="js/uzp_lab.js"></script>
    <script type="text/javascript" src="<?php echo OPTIONS_COMMON_FOLDER_PATH; ?>jquery/jquery.min.js"></script>
    <script type="text/javascript" src="<?php echo OPTIONS_COMMON_FOLDER_PATH; ?>jqwidgets/jqwidgets/jqxcore.js"></script>
    <script type="text/javascript" src="<?php echo OPTIONS_COMMON_FOLDER_PATH; ?>jqwidgets/jqwidgets/jqxinput.js"></script>
    <script type="text/javascript" src="<?php echo OPTIONS_COMMON_FOLDER_PATH; ?>jqwidgets/jqwidgets/jqxbuttons.js"></script>
    <script type="text/javascript" src="<?php echo OPTIONS_COMMON_FOLDER_PATH; ?>jqwidgets/jqwidgets/jqxnotification.js"></script>

<div id="broth_enrichment">
   <h3 class="center" id="home_title">Colonies to MH Plate</h3>
   <a href="./?page=" style="float: left; margin-bottom: 10px;">Back</a> <br />
   <div class="scan">
      <div id="colony_format"><label style="float: left;">Colony format: </label>&nbsp;&nbsp;<input type="text" name="colony_format" class="input-small" value="AVAQ70919" /></div>
      <div id="plate_format"><label style="float: left;">Broth Sample format: </label>&nbsp;&nbsp;<input type="text" name="plate_format" class="input-small" value="BSR010959" /></div>
      <div id="current_user"><label style="float: left;">Current User: </label>&nbsp;&nbsp;<?php echo $userCombo; ?></div> <br />

      <div class="center">
         <input type="text" name="sample" />
         <div>
            <input style='margin-top: 5px;' type="submit" value="Submit" id='jqxSubmitButton' />
         </div>
      </div>
   </div>
   <div class="received"><div class="saved">Linked samples appear here</div></div>
</div>
<div id="notification_box"><div id="msg"></div></div>
<script>
   var uzp = new Uzp();

   $('#whoisme .back').html('<a href=\'?page=home\'>Back</a>');
   $("[name=sample]").focus().jqxInput({placeHolder: "Scan a sample", width: 200, minLength: 1 });
   $("#jqxSubmitButton").on('click', uzp.saveMh).jqxButton({ width: '150'});

   uzp.prevSample = undefined;
   uzp.curSample = undefined;
   uzp.curSampleType = undefined;
   uzp.prevSampleType = undefined;
   $(document).keypress(uzp.receiveSampleKeypress);
</script>
<?php
   }

   private function mhSave() {
      $checkQuery = 'select id from colonies where colony = :colony';
      $insertQuery = 'insert into mh_assoc(colony_id, mh, user) values(:colony_id, :mh, :user)';

      $result = $this->Dbase->ExecuteQuery($checkQuery, array('colony' => $_POST['colony_sample']));
      if($result == 1) die(json_encode(array('error' => true, 'mssg' => $this->Dbase->lastError)));
      else if(count($result) == 0) die(json_encode(array('error' => true, 'mssg' => "The colony '{$_POST['colony_sample']}' is not in the database.")));

      // now add the association
      $result = $this->Dbase->ExecuteQuery($insertQuery, array('colony_id' => $result[0]['id'], 'mh' => $_POST['mh_sample'], 'user' => $_POST['cur_user']));
      if($result == 1) die(json_encode(array('error' => true, 'mssg' => $this->Dbase->lastError)));
      else die(json_encode(array('error' => false, 'mssg' => 'The association has been saved succesfully.')));
   }

   private function mhVialHome(){
      $userCombo = $this->usersCombo();
?>
    <link rel="stylesheet" href="<?php echo OPTIONS_COMMON_FOLDER_PATH; ?>jqwidgets/jqwidgets/styles/jqx.base.css" type="text/css" />
    <script type="text/javascript" src="js/uzp_lab.js"></script>
    <script type="text/javascript" src="<?php echo OPTIONS_COMMON_FOLDER_PATH; ?>jquery/jquery.min.js"></script>
    <script type="text/javascript" src="<?php echo OPTIONS_COMMON_FOLDER_PATH; ?>jqwidgets/jqwidgets/jqxcore.js"></script>
    <script type="text/javascript" src="<?php echo OPTIONS_COMMON_FOLDER_PATH; ?>jqwidgets/jqwidgets/jqxinput.js"></script>
    <script type="text/javascript" src="<?php echo OPTIONS_COMMON_FOLDER_PATH; ?>jqwidgets/jqwidgets/jqxbuttons.js"></script>
    <script type="text/javascript" src="<?php echo OPTIONS_COMMON_FOLDER_PATH; ?>jqwidgets/jqwidgets/jqxnotification.js"></script>

<div id="broth_enrichment">
   <h3 class="center" id="home_title">MH Plate to Vials</h3>
   <a href="./?page=" style="float: left; margin-bottom: 10px;">Back</a> <br />
   <div class="scan">
      <div id="colony_format"><label style="float: left;">Plate format: </label>&nbsp;&nbsp;<input type="text" name="colony_format" class="input-small" value="AVAQ70919" /></div>
      <div id="plate_format"><label style="float: left;">Vial format: </label>&nbsp;&nbsp;<input type="text" name="plate_format" class="input-small" value="BSR010959" /></div>
      <div id="current_user"><label style="float: left;">Current User: </label>&nbsp;&nbsp;<?php echo $userCombo; ?></div> <br />

      <div class="center">
         <input type="text" name="sample" />
         <div>
            <input style='margin-top: 5px;' type="submit" value="Submit" id='jqxSubmitButton' />
         </div>
      </div>
   </div>
   <div class="received"><div class="saved">Linked samples appear here</div></div>
</div>
<div id="notification_box"><div id="msg"></div></div>
<script>
   var uzp = new Uzp();

   $('#whoisme .back').html('<a href=\'?page=home\'>Back</a>');
   $("[name=sample]").focus().jqxInput({placeHolder: "Scan a sample", width: 200, minLength: 1 });
   $("#jqxSubmitButton").on('click', uzp.saveMh).jqxButton({ width: '150'});

   uzp.prevSample = undefined;
   uzp.curSample = undefined;
   uzp.curSampleType = undefined;
   uzp.prevSampleType = undefined;
   $(document).keypress(uzp.receiveSampleKeypress);
</script>
<?php
   }

   private function mhVialSave() {
      $checkQuery = 'select id from mh_assoc where mh = :colony';
      $insertQuery = 'insert into mh_vial(mh_id, mh_vial, user) values(:colony_id, :mh, :user)';

      $result = $this->Dbase->ExecuteQuery($checkQuery, array('colony' => $_POST['colony_sample']));
      if($result == 1) die(json_encode(array('error' => true, 'mssg' => $this->Dbase->lastError)));
      else if(count($result) == 0) die(json_encode(array('error' => true, 'mssg' => "The MH Plate '{$_POST['colony_sample']}' is not in the database.")));

      // now add the association
      $result = $this->Dbase->ExecuteQuery($insertQuery, array('colony_id' => $result[0]['id'], 'mh' => $_POST['mh_sample'], 'user' => $_POST['cur_user']));
      if($result == 1) die(json_encode(array('error' => true, 'mssg' => $this->Dbase->lastError)));
      else die(json_encode(array('error' => false, 'mssg' => 'The association has been saved succesfully.')));
   }

   /**
    * Create the home page for saving colonies in boxes
    */
   private function coloniesStorage(){
      $userCombo = $this->usersCombo();
      $layout = $this->storageBoxLayout(10, 10);
?>
    <link rel="stylesheet" href="<?php echo OPTIONS_COMMON_FOLDER_PATH; ?>jqwidgets/jqwidgets/styles/jqx.base.css" type="text/css" />
    <script type="text/javascript" src="js/uzp_lab.js"></script>
    <script type="text/javascript" src="<?php echo OPTIONS_COMMON_FOLDER_PATH; ?>jquery/jquery.min.js"></script>
    <script type="text/javascript" src="<?php echo OPTIONS_COMMON_FOLDER_PATH; ?>jqwidgets/jqwidgets/jqxcore.js"></script>
    <script type="text/javascript" src="<?php echo OPTIONS_COMMON_FOLDER_PATH; ?>jqwidgets/jqwidgets/jqxinput.js"></script>
    <script type="text/javascript" src="<?php echo OPTIONS_COMMON_FOLDER_PATH; ?>jqwidgets/jqwidgets/jqxbuttons.js"></script>
    <script type="text/javascript" src="<?php echo OPTIONS_COMMON_FOLDER_PATH; ?>jqwidgets/jqwidgets/jqxnotification.js"></script>

<div id="colonies_storage">
   <h3 class="center" id="home_title">Logging all created colonies</h3>
   <a href="./?page=" style="float: left; margin-bottom: 10px;">Back</a> <br />
   <div class="scan">
      <div id="colonies_format"><label style="float: left;">MH Vial format: </label>&nbsp;&nbsp;<input type="text" name="colonies_format" class="input-small" value="BDT013939" /></div>
      <div id="plate_format"><label style="float: left;">Storage Box: </label>&nbsp;&nbsp;<input type="text" name="storage_box" class="input-small" value="AVMS00050" /></div>
      <div id="colony_pos"><label style="float: left;">Position: </label>&nbsp;&nbsp;<input type="text" name="colony_pos" class="input-small" value="1" /></div>
      <div id="current_user"><label style="float: left;">Current User: </label>&nbsp;&nbsp;<?php echo $userCombo; ?></div> <br />
   </div>
   <div class="left">
      <input type="text" name="sample" />
      <div>
         <input style='margin-top: 5px;' type="submit" value="Submit" id='jqxSubmitButton' />
      </div>
   </div>
   <div id="plate_layout"><?php echo $layout; ?></div>
</div>
<div id="notification_box"><div id="msg"></div></div>
<script>
   var uzp = new Uzp();

   $('#whoisme .back').html('<a href=\'?page=home\'>Back</a>');
   $("[name=sample]").focus().jqxInput({placeHolder: "Scan a sample", width: 200, minLength: 1 });
   $("#jqxSubmitButton").on('click', uzp.saveColonies).jqxButton({ width: '150'});

   $(document).keypress(uzp.receiveSampleKeypress);
</script>
<?php
   }

   /**
    * Creates a layout for a box of size $sizeL x $sizeH
    *
    * @param   integer  $sizeL   The number of positions along the box length
    * @param   integer  $sizeH   The number of positions on the width
    */
   private function storageBoxLayout($sizeL, $sizeH, $samples){
      $k = 1;
      $layout = '';
      for($i = 0; $i < $sizeL; $i++){
         $layout .= "<div class='row'>";
         for($j = 0; $j < $sizeH; $j++, $k++){
            // create a div for this box
            if(isset($samples[$k])) $layout .= "<div class='pos occupied'>{$samples[$k]} ($k)</div>";
            else $layout .= "<div class='pos empty pos_$k'>$k</div>";
         }
         $layout .= "</div>";
      }

      return $layout;
   }

   /**
    * Save a colony in the specified box
    */
   private function coloniesStorageSave(){
      /**
       * Check whether the colony exists in the database and save it in the defined box and position
       */
      $checkQuery = 'select id, box, position_in_box from mh_vial where mh_vial = :colony';
      $updateQuery = 'update mh_vial set box = :box, position_in_box = :pos, pos_saved_by = :user where id = :id';

      $result = $this->Dbase->ExecuteQuery($checkQuery, array('colony' => $_POST['colony']));
      if($result == 1) die(json_encode(array('error' => true, 'mssg' => $this->Dbase->lastError)));
      else if(count($result) == 0) die(json_encode(array('error' => true, 'mssg' => "The colony '{$_POST['colony']}' is not in the database.")));
      else if($result[0]['box'] != NULL) die(json_encode(array('error' => true, 'mssg' => "The colony '{$_POST['colony']}' has already been saved before in <b>{$result[0]['box']}</b> pos <b>{$result[0]['position_in_box']}</b>.")));

      $res = $this->Dbase->ExecuteQuery($updateQuery, array('box' => $_POST['box'], 'pos' => $_POST['cur_pos'], 'user' => $_POST['cur_user'], 'id' => $result[0]['id']));
      if($res == 1){
         if($this->Dbase->lastErrorCodes[1] == 1062) die(json_encode(array('error' => true, 'mssg' => 'Duplicate entry for the current position')));
         else die(json_encode(array('error' => true, 'mssg' => $this->Dbase->lastError)));
      }
      else die(json_encode(array('error' => false, 'mssg' => 'The colony storage has been saved succesfully.')));
   }

   /**
    * Create a home page for receiving the bootsock
    */
   private function campyReceiptHome(){
      $addInfo = ($addInfo != '') ? "<div id='addinfo'>$addInfo</div>" : '';
      $userCombo = $this->usersCombo();
	  $sequencingCombo = $this->sequencingCombo();
?>
    <link rel="stylesheet" href="<?php echo OPTIONS_COMMON_FOLDER_PATH; ?>jqwidgets/jqwidgets/styles/jqx.base.css" type="text/css" />
    <script type="text/javascript" src="js/uzp_lab.js"></script>
    <script type="text/javascript" src="<?php echo OPTIONS_COMMON_FOLDER_PATH; ?>jquery/jquery.min.js"></script>
    <script type="text/javascript" src="<?php echo OPTIONS_COMMON_FOLDER_PATH; ?>jqwidgets/jqwidgets/jqxcore.js"></script>
    <script type="text/javascript" src="<?php echo OPTIONS_COMMON_FOLDER_PATH; ?>jqwidgets/jqwidgets/jqxinput.js"></script>
    <script type="text/javascript" src="<?php echo OPTIONS_COMMON_FOLDER_PATH; ?>jqwidgets/jqwidgets/jqxbuttons.js"></script>
    <script type="text/javascript" src="<?php echo OPTIONS_COMMON_FOLDER_PATH; ?>jqwidgets/jqwidgets/jqxnotification.js"></script>

<div id="receive_samples">
   <h3 class="center" id="home_title">Log the bootsock received from the field</h3>
   <a href="./?page=" style="float: left; margin-bottom: 10px;">Back</a> <br />
   <div class="scan">
      <div id="sample_format"><label style="float: left;">Bootsock/Pot Barcode format: </label>&nbsp;&nbsp;<input type="text" name="sample_format" value="AVAQ63847" /></div>
      <div id="current_user"><label style="float: left;">Current User: </label>&nbsp;&nbsp;<?php echo $userCombo; ?></div>
	  <div id="for_sequencing"><label style="float: left;">For genome sequencing: </label>&nbsp;&nbsp;<?php echo $sequencingCombo; ?></div><br />

      <input type="text" name="sample" />
      <div>
         <input style='margin-top: 5px;' type="submit" value="Submit" id='jqxSubmitButton' />
      </div>
   </div>
   <div class="received"><div class="saved">Received bootsocks appear here</div></div>
</div>
<div id="notification_box"><div id="msg"></div></div>
<script>
   var uzp = new Uzp();

   $('#whoisme .back').html('<a href=\'?page=home\'>Back</a>');
   $("[name=sample]").focus().jqxInput({placeHolder: "Scan a sample", width: 200, minLength: 1 });
   $("#jqxSubmitButton").on('click', uzp.saveReceivedSample).jqxButton({ width: '150'});

   $(document).keypress(uzp.receiveSampleKeypress);
</script>
<?php
   }

   /**
    * Saves the received sample in a bootsock
    */
   private function campyReceiptSave(){
      // time to save the received sample
      $query = 'insert into campy_received_bootsocks(sample, user, for_sequencing) values(:sample, :user, :for_sequencing)';
      $vals = array('sample' => $_POST['sample'], 'user' => $_POST['cur_user'], 'for_sequencing' => $_POST['for_sequencing']);

      $result = $this->Dbase->ExecuteQuery($query, $vals);
      if($result == 1) die(json_encode(array('error' => true, 'mssg' => $this->Dbase->lastError)));
      else die(json_encode(array('error' => false, 'mssg' => 'The sample has been saved succesfully.')));
   }

   /**
    * Creates a home page for saving bootsock to a falcon tube and the cryo vials
    */
   private function campyFalconHome(){
      $userCombo = $this->usersCombo();
?>
    <link rel="stylesheet" href="<?php echo OPTIONS_COMMON_FOLDER_PATH; ?>jqwidgets/jqwidgets/styles/jqx.base.css" type="text/css" />
    <script type="text/javascript" src="js/uzp_lab.js"></script>
    <script type="text/javascript" src="<?php echo OPTIONS_COMMON_FOLDER_PATH; ?>jquery/jquery.min.js"></script>
    <script type="text/javascript" src="<?php echo OPTIONS_COMMON_FOLDER_PATH; ?>jqwidgets/jqwidgets/jqxcore.js"></script>
    <script type="text/javascript" src="<?php echo OPTIONS_COMMON_FOLDER_PATH; ?>jqwidgets/jqwidgets/jqxinput.js"></script>
    <script type="text/javascript" src="<?php echo OPTIONS_COMMON_FOLDER_PATH; ?>jqwidgets/jqwidgets/jqxbuttons.js"></script>
    <script type="text/javascript" src="<?php echo OPTIONS_COMMON_FOLDER_PATH; ?>jqwidgets/jqwidgets/jqxnotification.js"></script>

<!-- So yes its a different module, but the functionality is just the same. I will not bother changing the HTML placeholders -->
<div id="broth_enrichment">
   <h3 class="center" id="home_title">Linking a bootsock to falcon tubes</h3>
   <a href="./?page=" style="float: left; margin-bottom: 10px;">Back</a> <br />
   <div class="scan">
      <div id="sample_format"><label style="float: left;">Bootsock/Pot format: </label>&nbsp;&nbsp;<input type="text" name="sample_format" class="input-small" value="AVAQ70919" /></div>
      <div id="broth_format"><label style="float: left;">Falcon tube format: </label>&nbsp;&nbsp;<input type="text" name="broth_format" class="input-small" value="BSR010959" /></div>
      <div id="current_user"><label style="float: left;">Current User: </label>&nbsp;&nbsp;<?php echo $userCombo; ?></div> <br />

      <div class="center">
         <input type="text" name="sample" />
         <div>
            <input style='margin-top: 5px;' type="submit" value="Submit" id='jqxSubmitButton' />
         </div>
      </div>
   </div>
   <div class="received"><div class="saved">Falcon tubes appear here</div></div>
</div>
<div id="notification_box"><div id="msg"></div></div>
<script>
   var uzp = new Uzp();

   $('#whoisme .back').html('<a href=\'?page=home\'>Back</a>');
   $("[name=sample]").focus().jqxInput({placeHolder: "Scan a sample", width: 200, minLength: 1 });
   $("#jqxSubmitButton").on('click', uzp.saveBrothSample).jqxButton({ width: '150'});

   uzp.prevSample = undefined;
   uzp.curSample = undefined;
   uzp.curSampleType = undefined;
   uzp.prevSampleType = undefined;
   $(document).keypress(uzp.receiveSampleKeypress);
</script>
<?php
   }

   /**
    * Saves a new association of the bootsoc and the falcon tubes/cryo vials
    */
   private function campyFalconSave(){
      /**
       * check whether the bootsoc is in the database
       * if it is in the database, save the association
       */
      $checkQuery = 'select id from campy_received_bootsocks where sample = :sample';
      $insertQuery = 'insert into campy_bootsock_assoc(bootsock_id, daughter_sample, user) values(:bootsock_id, :daughter_sample, :user)';

      $result = $this->Dbase->ExecuteQuery($checkQuery, array('sample' => $_POST['field_sample']));
      if($result == 1) die(json_encode(array('error' => true, 'mssg' => $this->Dbase->lastError)));
      else if(count($result) == 0) die(json_encode(array('error' => true, 'mssg' => "The field sample '{$_POST['field_sample']}' is not in the database.")));

      // now add the association
      $result = $this->Dbase->ExecuteQuery($insertQuery, array('bootsock_id' => $result[0]['id'], 'daughter_sample' => $_POST['broth_sample'], 'user' => $_POST['cur_user']));
      if($result == 1) die(json_encode(array('error' => true, 'mssg' => $this->Dbase->lastError)));
      else die(json_encode(array('error' => false, 'mssg' => 'The association has been saved succesfully.')));
   }

   /**
    * Create a home page for saving the cryo vials from the falcon tube
    */
   private function campyFalcon2CryoHome(){
      $userCombo = $this->usersCombo();
      $boxQuery = 'select position_in_box, cryovial from campy_cryovials order by position_in_box';
      $res = $this->Dbase->ExecuteQuery($boxQuery, NULL, PDO::FETCH_KEY_PAIR);
      if($res == 1){
         $this->homePage($this->Dbase->lastError);
         return;
      }
      // get all the samples currently saved in the box
      $layout = $this->storageBoxLayout(10, 10, $res);
      $lastCount = count($res) + 1;
?>
    <link rel="stylesheet" href="<?php echo OPTIONS_COMMON_FOLDER_PATH; ?>jqwidgets/jqwidgets/styles/jqx.base.css" type="text/css" />
    <script type="text/javascript" src="js/uzp_lab.js"></script>
    <script type="text/javascript" src="<?php echo OPTIONS_COMMON_FOLDER_PATH; ?>jquery/jquery.min.js"></script>
    <script type="text/javascript" src="<?php echo OPTIONS_COMMON_FOLDER_PATH; ?>jqwidgets/jqwidgets/jqxcore.js"></script>
    <script type="text/javascript" src="<?php echo OPTIONS_COMMON_FOLDER_PATH; ?>jqwidgets/jqwidgets/jqxinput.js"></script>
    <script type="text/javascript" src="<?php echo OPTIONS_COMMON_FOLDER_PATH; ?>jqwidgets/jqwidgets/jqxbuttons.js"></script>
    <script type="text/javascript" src="<?php echo OPTIONS_COMMON_FOLDER_PATH; ?>jqwidgets/jqwidgets/jqxnotification.js"></script>

<div id="colonies_storage">
   <h3 class="center" id="home_title">Logging all created cryo vials from the falcon tubes</h3>
   <a href="./?page=" style="float: left; margin-bottom: 10px;">Back</a> <br />
   <div class="scan">
      <div id="falcon_format"><label style="float: left;">Falcon format: </label>&nbsp;&nbsp;<input type="text" name="falcon_format" class="input-small" value="BSR013939" /></div>
      <div id="cryovial_format"><label style="float: left;">Vials format: </label>&nbsp;&nbsp;<input type="text" name="cryo_format" class="input-small" value="AVAQ01965" /></div>
      <div id="plate_format"><label style="float: left;">Storage Box: </label>&nbsp;&nbsp;<input type="text" name="storage_box" class="input-small" value="BREP0050" /></div>
      <div id="colony_pos"><label style="float: left;">Position: </label>&nbsp;&nbsp;<input type="text" name="vial_pos" class="input-small" value="<?php echo $lastCount; ?>" size="25px" /></div>
      <div id="current_user"><label style="float: left;">Current User: </label>&nbsp;&nbsp;<?php echo $userCombo; ?></div> <br />
   </div>
   <div id="cryo_storage_left" class="left">
      <input type="text" name="sample" />
      <div>
         <input style='margin-top: 5px;' type="submit" value="Submit" id='jqxSubmitButton' />
      </div>
      <div class="received"><div class="saved">Associated falcon tubes and cryo vials appear here</div></div>
   </div>
   <div id="plate_layout"><?php echo $layout; ?></div>
</div>
<div id="notification_box"><div id="msg"></div></div>
<script>
   var uzp = new Uzp();

   $('#whoisme .back').html('<a href=\'?page=home\'>Back</a>');
   $("[name=sample]").focus().jqxInput({placeHolder: "Scan a sample", width: 200, minLength: 1 });
   $("#jqxSubmitButton").on('click', uzp.saveFalconVials).jqxButton({ width: '150'});

   $(document).keypress(uzp.receiveSampleKeypress);
</script>
<?php
   }

   /**
    * Saves a falcon tube -- cryo vial association. In addition it saves the cryo vial in its required position
    */
   private function campyFalcon2CryoSave(){
      $checkQuery = 'select id from campy_bootsock_assoc where daughter_sample = :sample';
      $insertQuery = 'insert into campy_cryovials(falcon_id, cryovial, user, box, position_in_box) values(:falcon_id, :cryovial, :user, :box, :position_in_box)';

      $result = $this->Dbase->ExecuteQuery($checkQuery, array('sample' => $_POST['falcon_tube']));
      if($result == 1) die(json_encode(array('error' => true, 'mssg' => $this->Dbase->lastError)));
      else if(count($result) == 0){
         $this->Dbase->CreateLogEntry("The falcon sample '{$_POST['falcon_tube']}' is not in the database.", 'fatal');
         die(json_encode(array('error' => true, 'mssg' => "The falcon sample '{$_POST['falcon_tube']}' is not in the database.")));
      }

      // now add the association
      $result = $this->Dbase->ExecuteQuery($insertQuery, array('falcon_id' => $result[0]['id'], 'cryovial' => $_POST['cryo_vial'], 'user' => $_POST['cur_user'], 'box' => $_POST['box'], 'position_in_box' => $_POST['pos']));
      if($result == 1){
         if($this->Dbase->lastErrorCodes[1] == 1062) die(json_encode(array('error' => true, 'mssg' => 'Duplicate entry for the current association')));
         else die(json_encode(array('error' => true, 'mssg' => $this->Dbase->lastError)));
      }
      else die(json_encode(array('error' => false, 'mssg' => 'The association has been saved succesfully.')));
   }

   /**
    * mccda plate
    */
   private function campyMccdaHome(){
      $userCombo = $this->usersCombo();
?>
    <link rel="stylesheet" href="<?php echo OPTIONS_COMMON_FOLDER_PATH; ?>jqwidgets/jqwidgets/styles/jqx.base.css" type="text/css" />
    <script type="text/javascript" src="js/uzp_lab.js"></script>
    <script type="text/javascript" src="<?php echo OPTIONS_COMMON_FOLDER_PATH; ?>jquery/jquery.min.js"></script>
    <script type="text/javascript" src="<?php echo OPTIONS_COMMON_FOLDER_PATH; ?>jqwidgets/jqwidgets/jqxcore.js"></script>
    <script type="text/javascript" src="<?php echo OPTIONS_COMMON_FOLDER_PATH; ?>jqwidgets/jqwidgets/jqxinput.js"></script>
    <script type="text/javascript" src="<?php echo OPTIONS_COMMON_FOLDER_PATH; ?>jqwidgets/jqwidgets/jqxbuttons.js"></script>
    <script type="text/javascript" src="<?php echo OPTIONS_COMMON_FOLDER_PATH; ?>jqwidgets/jqwidgets/jqxnotification.js"></script>

<!-- We are re-using the broth template. So no changing things a lot -->
<div id="broth_enrichment">
   <h3 class="center" id="home_title">Loading broth from falcon tube to MCCDA plates</h3>
   <a href="./?page=" style="float: left; margin-bottom: 10px;">Back</a> <br />
   <div class="scan">
      <div id="colony_format"><label style="float: left;">Falcon tube format: </label>&nbsp;&nbsp;<input type="text" name="plate_format" class="input-small" value="BSR010959" /></div>
      <div id="plate_format"><label style="float: left;">MCCDA format: </label>&nbsp;&nbsp;<input type="text" name="media_format" class="input-small" value="AVMS00043" /></div>
      <div id="current_user"><label style="float: left;">Current User: </label>&nbsp;&nbsp;<?php echo $userCombo; ?></div> <br />

      <div class="center">
         <input type="text" name="sample" />
         <label>Scanned plates</label><div id="scanned_colonies" class="center"></div>
         <div>
            <input style='margin-top: 5px;' type="submit" value="Submit" id='jqxSubmitButton' />
         </div>
      </div>
   </div>
   <div class="received"><div class="saved">Linked plates appear here</div></div>
</div>
<div id="notification_box"><div id="msg"></div></div>
<script>
   var uzp = new Uzp();

   $('#whoisme .back').html('<a href=\'?page=home\'>Back</a>');
   $("[name=sample]").focus().jqxInput({placeHolder: "Scan a sample", width: 200, minLength: 1 });
   $("#jqxSubmitButton").on('click', uzp.saveBioChemPrep).jqxButton({ width: '150'});

   uzp.prevSample = undefined;
   uzp.curSample = undefined;
   uzp.curSampleType = undefined;
   uzp.prevSampleType = undefined;
   $(document).keypress(uzp.receiveSampleKeypress);
</script>
<?php
   }

   /**
    * Saves a new association of the falcon tubes and the mccda plates
    */
   private function campyMccdaSave(){
      /**
       * check whether the falcon sample is in the database
       * if it is in the database, save the association of the falcon tube and the plate
       */
      $checkQuery = 'select id from campy_bootsock_assoc where daughter_sample = :sample';
      $insertQuery = 'insert into campy_mccda_assoc(falcon_id, plate1_barcode, user) values(:bootsock_id, :plate1_barcode, :user)';

      $result = $this->Dbase->ExecuteQuery($checkQuery, array('sample' => $_POST['plate']));
      if($result == 1) die(json_encode(array('error' => true, 'mssg' => $this->Dbase->lastError)));
      else if(count($result) == 0){
         $this->Dbase->CreateLogEntry("The falcon sample '{$_POST['plate']}' is not in the database.", 'fatal');
         die(json_encode(array('error' => true, 'mssg' => "The falcon sample '{$_POST['plate']}' is not in the database.")));
      }

      // now add the association(s)
      $this->Dbase->StartTrans();
      foreach($_POST['colonies'] as $colony){
         $res = $this->Dbase->ExecuteQuery($insertQuery, array('bootsock_id' => $result[0]['id'], 'plate1_barcode' => $colony, 'user' => $_POST['cur_user']));
         if($res == 1){
            if($this->Dbase->lastErrorCodes[1] == 1062) die(json_encode(array('error' => true, 'mssg' => 'Duplicate entry for the current association')));
            else{
               $this->Dbase->RollBackTrans();
               die(json_encode(array('error' => true, 'mssg' => $this->Dbase->lastError)));
            }
         }
      }
      $this->Dbase->CommitTrans();
      die(json_encode(array('error' => false, 'mssg' => 'The association has been saved succesfully.')));
   }

   /**
    * Create a home page for loading the aerobic/micro-aerobic plates with the broth from the MCCDA plate
    */
   private function campyMccdaGrowthHome(){
      $userCombo = $this->usersCombo();
?>
    <link rel="stylesheet" href="<?php echo OPTIONS_COMMON_FOLDER_PATH; ?>jqwidgets/jqwidgets/styles/jqx.base.css" type="text/css" />
    <script type="text/javascript" src="js/uzp_lab.js"></script>
    <script type="text/javascript" src="<?php echo OPTIONS_COMMON_FOLDER_PATH; ?>jquery/jquery.min.js"></script>
    <script type="text/javascript" src="<?php echo OPTIONS_COMMON_FOLDER_PATH; ?>jqwidgets/jqwidgets/jqxcore.js"></script>
    <script type="text/javascript" src="<?php echo OPTIONS_COMMON_FOLDER_PATH; ?>jqwidgets/jqwidgets/jqxinput.js"></script>
    <script type="text/javascript" src="<?php echo OPTIONS_COMMON_FOLDER_PATH; ?>jqwidgets/jqwidgets/jqxbuttons.js"></script>
    <script type="text/javascript" src="<?php echo OPTIONS_COMMON_FOLDER_PATH; ?>jqwidgets/jqwidgets/jqxnotification.js"></script>

    <!-- Using the colonies page for this stage -->
<div id="colonies">
   <h3 class="center" id="home_title">MCCDA Plate -> Aerobic and Micro-aerobic Plates</h3>
   <a href="./?page=" style="float: left; margin-bottom: 10px;">Back</a> <br />
   <div class="scan">
      <div id="plate_format"><label style="float: left;">MCCDA Plate: </label>&nbsp;&nbsp;<input type="text" name="plate_format" class="input-small" value="AVMS00045" /></div>
      <div id="media_format"><label style="float: left;">Aerobic Plates format: </label>&nbsp;&nbsp;<input type="text" name="media_format" class="input-small" value="AVAQ64156" /></div>
      <div id="current_user"><label style="float: left;">Current User: </label>&nbsp;&nbsp;<?php echo $userCombo; ?></div> <br />

      <div class="center">
         <input type="text" name="sample" />
         <label>Scanned colonies</label><div id="scanned_colonies" class="center"></div>
         <div>
            <input style='margin-top: 5px;' type="submit" value="Submit" id='jqxSubmitButton' />
         </div>
      </div>
   </div>
   <div class="received"><div class="saved">Aerobic and Micro-aerobic plates appear here</div></div>
</div>
<div id="notification_box"><div id="msg"></div></div>
<script>
   var uzp = new Uzp();

   $('#whoisme .back').html('<a href=\'?page=home\'>Back</a>');
   $("[name=sample]").focus().jqxInput({placeHolder: "Scan a sample", width: 200, minLength: 1 });
   $("#jqxSubmitButton").on('click', uzp.savePlate3to45).jqxButton({ width: '150'});

   uzp.prevSample = undefined;
   uzp.curSample = undefined;
   uzp.curSampleType = undefined;
   uzp.prevSampleType = undefined;
   uzp.mediumBarcodeList = [];
   uzp.plateSample = undefined;
   $(document).keypress(uzp.receiveSampleKeypress);
</script>
<?php
   }

   /**
    * Saves the association between the colonies which grew from the mccda plate and the aerobi/microbic plate
    */
   private function campyMccdaGrowthSave(){
      /**
       * check whether the plate is in the database
       * if it is in the database, save the plate and its associated colonies
       */
      $checkQuery = 'select id from campy_mccda_assoc where plate1_barcode = :plate';
      $insertQuery = 'insert into campy_mccda_growth(mccda_plate_id, am_plate, user) values(:mccda_plate_id, :am_plate, :user)';

      $result = $this->Dbase->ExecuteQuery($checkQuery, array('plate' => $_POST['plate']));
      if($result == 1) die(json_encode(array('error' => true, 'mssg' => $this->Dbase->lastError)));
      else if(count($result) == 0) die(json_encode(array('error' => true, 'mssg' => "The plate '{$_POST['plate']}' is not in the database.")));

      // now add the association
      $this->Dbase->StartTrans();
      foreach($_POST['colonies'] as $colony){
         $res = $this->Dbase->ExecuteQuery($insertQuery, array('mccda_plate_id' => $result[0]['id'], 'am_plate' => $colony, 'user' => $_POST['cur_user']));
         if($res == 1){
            $this->Dbase->RollBackTrans();
            if($this->Dbase->lastErrorCodes[1] == 1062) die(json_encode(array('error' => true, 'mssg' => 'Duplicate entry for the current association')));
            else die(json_encode(array('error' => true, 'mssg' => $this->Dbase->lastError)));
         }
      }
      $this->Dbase->CommitTrans();
      die(json_encode(array('error' => false, 'mssg' => 'The association has been saved succesfully.')));
   }

   /**
    * Create the home page for saving colonies in boxes
    */
   private function campyMicroaerobicColoniesHome(){
      $userCombo = $this->usersCombo();
      $boxQuery = 'select position_in_box, colony from campy_colonies order by position_in_box';
      $colonies = $this->Dbase->ExecuteQuery($boxQuery, NULL, PDO::FETCH_KEY_PAIR);
      if($colonies == 1){
         $this->homePage($this->Dbase->lastError);
         return;
      }
      // get all the samples currently saved in the box
      $layout = $this->storageBoxLayout(10, 10, $colonies);
?>
    <link rel="stylesheet" href="<?php echo OPTIONS_COMMON_FOLDER_PATH; ?>jqwidgets/jqwidgets/styles/jqx.base.css" type="text/css" />
    <script type="text/javascript" src="js/uzp_lab.js"></script>
    <script type="text/javascript" src="<?php echo OPTIONS_COMMON_FOLDER_PATH; ?>jquery/jquery.min.js"></script>
    <script type="text/javascript" src="<?php echo OPTIONS_COMMON_FOLDER_PATH; ?>jqwidgets/jqwidgets/jqxcore.js"></script>
    <script type="text/javascript" src="<?php echo OPTIONS_COMMON_FOLDER_PATH; ?>jqwidgets/jqwidgets/jqxinput.js"></script>
    <script type="text/javascript" src="<?php echo OPTIONS_COMMON_FOLDER_PATH; ?>jqwidgets/jqwidgets/jqxbuttons.js"></script>
    <script type="text/javascript" src="<?php echo OPTIONS_COMMON_FOLDER_PATH; ?>jqwidgets/jqwidgets/jqxnotification.js"></script>

<div id="colonies_storage">
   <h3 class="center" id="home_title">Logging all microaerobic colonies</h3>
   <a href="./?page=" style="float: left; margin-bottom: 10px;">Back</a> <br />
   <div class="scan">
      <div id="colonies_format"><label style="float: left;">Colonies format: </label>&nbsp;&nbsp;<input type="text" name="colonies_format" class="input-small" value="AVAQ13939" /></div>
      <div id="plate_format"><label style="float: left;">Storage Box: </label>&nbsp;&nbsp;<input type="text" name="storage_box" class="input-small" value="AVMS00050" /></div>
      <div id="colony_pos"><label style="float: left;">Position: </label>&nbsp;&nbsp;<input type="text" name="colony_pos" class="input-small" value="1" /></div>
      <div id="current_user"><label style="float: left;">Current User: </label>&nbsp;&nbsp;<?php echo $userCombo; ?></div> <br />
   </div>
   <div class="left">
      <input type="text" name="sample" />
      <div>
         <input style='margin-top: 5px;' type="submit" value="Submit" id='jqxSubmitButton' />
      </div>
   </div>
   <div id="plate_layout"><?php echo $layout; ?></div>
</div>
<div id="notification_box"><div id="msg"></div></div>
<script>
   var uzp = new Uzp();

   $('#whoisme .back').html('<a href=\'?page=home\'>Back</a>');
   $("[name=sample]").focus().jqxInput({placeHolder: "Scan a sample", width: 200, minLength: 1 });
   $("#jqxSubmitButton").on('click', uzp.saveCampyColonies).jqxButton({ width: '150'});

   $(document).keypress(uzp.receiveSampleKeypress);
</script>
<?php
   }

   /**
    * Save the colonies which have grown in microaerobic conditions
    */
   private function campyMicroaerobicColoniesSave(){
      /**
       * Check whether the colony exists in the database and save it in the defined box and position
       */
      $checkQuery = 'select id, box, position_in_box from campy_colonies where colony = :colony';
      $insertQuery = 'insert into campy_colonies(colony, user, box, position_in_box) values(:colony, :user, :box, :position_in_box)';

      $result = $this->Dbase->ExecuteQuery($checkQuery, array('colony' => $_POST['colony']));
      if($result == 1) die(json_encode(array('error' => true, 'mssg' => $this->Dbase->lastError)));
      else if($result[0]['box'] != NULL) die(json_encode(array('error' => true, 'mssg' => "The colony '{$_POST['colony']}' has already been saved before in <b>{$result[0]['box']}</b> pos <b>{$result[0]['position_in_box']}</b>.")));

      $result = $this->Dbase->ExecuteQuery($insertQuery, array('colony' => $_POST['colony'], 'user' => $_POST['cur_user'], 'box' => $_POST['box'], 'position_in_box' => $_POST['pos']));
      if($result == 1){
         if($this->Dbase->lastErrorCodes[1] == 1062) die(json_encode(array('error' => true, 'mssg' => 'Duplicate entry for the current position')));
         else die(json_encode(array('error' => true, 'mssg' => $this->Dbase->lastError)));
      }
      else die(json_encode(array('error' => false, 'mssg' => 'The colony storage has been saved succesfully.')));
   }

   /**
    * This function renders the view data page
    */
   private function dbChecks() {
?>
<script type="text/javascript" src="js/view_lab.js"></script>
<link rel="stylesheet" href="<?php echo OPTIONS_COMMON_FOLDER_PATH?>jqwidgets/jqwidgets/styles/jqx.base.css" type="text/css" />
<script type="text/javascript" src="<?php echo OPTIONS_COMMON_FOLDER_PATH?>jqwidgets/jqwidgets/jqxcore.js"></script>
<script type="text/javascript" src="<?php echo OPTIONS_COMMON_FOLDER_PATH?>jqwidgets/jqwidgets/jqxdata.js"></script>
<script type="text/javascript" src="<?php echo OPTIONS_COMMON_FOLDER_PATH?>jqwidgets/jqwidgets/jqxbuttons.js"></script>
<script type="text/javascript" src="<?php echo OPTIONS_COMMON_FOLDER_PATH?>jqwidgets/jqwidgets/jqxscrollbar.js"></script>
<script type="text/javascript" src="<?php echo OPTIONS_COMMON_FOLDER_PATH?>jqwidgets/jqwidgets/jqxmenu.js"></script>
<script type="text/javascript" src="<?php echo OPTIONS_COMMON_FOLDER_PATH?>jqwidgets/jqwidgets/jqxcheckbox.js"></script>
<script type="text/javascript" src="<?php echo OPTIONS_COMMON_FOLDER_PATH?>jqwidgets/jqwidgets/jqxlistbox.js"></script>
<script type="text/javascript" src="<?php echo OPTIONS_COMMON_FOLDER_PATH?>jqwidgets/jqwidgets/jqxdropdownlist.js"></script>
<script type="text/javascript" src="<?php echo OPTIONS_COMMON_FOLDER_PATH?>jqwidgets/jqwidgets/jqxgrid.js"></script>
<script type="text/javascript" src="<?php echo OPTIONS_COMMON_FOLDER_PATH?>jqwidgets/jqwidgets/jqxgrid.sort.js"></script>
<script type="text/javascript" src="<?php echo OPTIONS_COMMON_FOLDER_PATH?>jqwidgets/jqwidgets/jqxgrid.pager.js"></script>
<script type="text/javascript" src="<?php echo OPTIONS_COMMON_FOLDER_PATH?>jqwidgets/jqwidgets/jqxgrid.selection.js"></script>
<script type="text/javascript" src="<?php echo OPTIONS_COMMON_FOLDER_PATH?>jqwidgets/jqwidgets/jqxgrid.filter.js"></script>
<script type="text/javascript" src="<?php echo OPTIONS_COMMON_FOLDER_PATH?>jqwidgets/jqwidgets/jqxnotification.js"></script>
<script type="text/javascript" src="<?php echo OPTIONS_COMMON_FOLDER_PATH?>jqwidgets/jqwidgets/jqxgrid.export.js"></script>
<script type="text/javascript" src="<?php echo OPTIONS_COMMON_FOLDER_PATH?>jqwidgets/jqwidgets/jqxgrid.columnsresize.js"></script>
<script type="text/javascript" src="<?php echo OPTIONS_COMMON_FOLDER_PATH?>jqwidgets/jqwidgets/jqxdata.export.js"></script>
<script type="text/javascript" src="<?php echo OPTIONS_COMMON_FOLDER_PATH;?>jqwidgets/jqwidgets/jqxcalendar.js"></script>
<script type="text/javascript" src="<?php echo OPTIONS_COMMON_FOLDER_PATH;?>jqwidgets/jqwidgets/jqxtooltip.js"></script>
<script type="text/javascript" src="<?php echo OPTIONS_COMMON_FOLDER_PATH;?>jqwidgets/jqwidgets/jqxdatetimeinput.js"></script>
<div id="lab_view">
   <a href="./?page=" style="float: left; margin-bottom: 10px;">Back</a> <br />
   <div id="top">
      <div>
         <select id="table_to_show" onChange="dbChecks.initiateChecksGrid()">
            <option value="select_one" selected="true">Select One</option>
            <option value="received_samples">Received Samples</option>
            <option value="ecoli2_table1">Received -> Broth</option>
            <option value="broth_mcconky">Broth -> McConky</option>
            <option value="mcconky_colonies">McConky -> Colonies</option>
            <option value="colonies_mh">Colonies -> MH</option>
            <option value="mh_vials">MH -> Vials</option>
         </select>
      </div>
      <div id='range'></div>
   </div>
   <div id="grid"></div>
</div>
<div id="notification_box"></div>
<script type="text/javascript">
   $(document).ready(function(){
      var dbChecks = new DBChecks();
      var uzp = new Uzp();
   });
</script>
   <?php
   }

   /**
    * Get a summary of the received samples
    */
   private function dbCheckReceivedSamples(){
      // get the summary of the received samples
      $receivedSamplesQ = 'select "KEMRI" as lab, date(datetime_received) as date_received, count(*) as count from '.Config::$kemri_db_name.'.received_samples group by date(datetime_received) '
            . 'union '
            . 'select "UoN" as lab, date(datetime_received) as date_received, count(*) as count from '.Config::$uon_db_name.'.received_samples group by date(datetime_received) '
            . 'order by date_received desc';
      $receivedSamples = $this->Dbase->ExecuteQuery($receivedSamplesQ);
      if($receivedSamples == 1) die(json_encode(array('error' => true, 'mssg' => $this->Dbase->lastError)));
      $samplesCount = count($receivedSamples);
      for($i = 0; $i < $samplesCount; $i++){
         if($receivedSamples[$i]['lab'] == 'UoN') $lab = Config::$uon_db_name;
         else if($receivedSamples[$i]['lab'] == 'KEMRI') $lab = Config::$kemri_db_name;

         $seqSamplesQ = "select count(*) as count from $lab.received_samples where for_sequencing='yes' and datetime_received like :date_r";
         $brothSamplesQ = "select count(*) as count from $lab.broth_assoc where datetime_added like :date_r";
         $mcconkyAssocQ = "select count(*) as count from $lab.mcconky_assoc where datetime_added like :date_r";
         $coloniesAssocQ = "select count(*) as count from $lab.colonies where datetime_saved like :date_r";
         $mhAssocQ = "select count(*) as count from $lab.mh_assoc where datetime_added like :date_r";
         $mhVialQ = "select count(*) as count from $lab.mh_vial where datetime_saved like :date_r";

         $seqSamples = $this->Dbase->ExecuteQuery($seqSamplesQ, array('date_r' => "{$receivedSamples[$i]['date_received']}%"));
         $brothSamples = $this->Dbase->ExecuteQuery($brothSamplesQ, array('date_r' => "{$receivedSamples[$i]['date_received']}%"));
         $mcconkyAssoc = $this->Dbase->ExecuteQuery($mcconkyAssocQ, array('date_r' => "{$receivedSamples[$i]['date_received']}%"));
         $coloniesAssoc = $this->Dbase->ExecuteQuery($coloniesAssocQ, array('date_r' => "{$receivedSamples[$i]['date_received']}%"));
         $mhAssoc = $this->Dbase->ExecuteQuery($mhAssocQ, array('date_r' => "{$receivedSamples[$i]['date_received']}%"));
         $mhVial = $this->Dbase->ExecuteQuery($mhVialQ, array('date_r' => "{$receivedSamples[$i]['date_received']}%"));

         if($seqSamples == 1) die(json_encode(array('error' => true, 'mssg' => $this->Dbase->lastError)));
         if($brothSamples == 1) die(json_encode(array('error' => true, 'mssg' => $this->Dbase->lastError)));
         if($mcconkyAssoc == 1) die(json_encode(array('error' => true, 'mssg' => $this->Dbase->lastError)));
         if($coloniesAssoc == 1) die(json_encode(array('error' => true, 'mssg' => $this->Dbase->lastError)));
         if($mhAssoc == 1) die(json_encode(array('error' => true, 'mssg' => $this->Dbase->lastError)));
         if($mhVial == 1) die(json_encode(array('error' => true, 'mssg' => $this->Dbase->lastError)));

         $receivedSamples[$i]['for_seq'] = $seqSamples[0]['count'];
         $receivedSamples[$i]['broth_samples'] = $brothSamples[0]['count'];
         $receivedSamples[$i]['mcconky'] = $mcconkyAssoc[0]['count'];
         $receivedSamples[$i]['colonies'] = $coloniesAssoc[0]['count'];
         $receivedSamples[$i]['mh'] = $mhAssoc[0]['count'];
         $receivedSamples[$i]['vials'] = $mhVial[0]['count'];
      }

      die(json_encode(array('error' => false, 'data' => $receivedSamples)));
   }

   /**
    * Run summaries for field and broth samples
    */
   private function dbCheckFieldBrothSamples(){
      $samplesQ = 'select "Samples w/o Broth" issue, "KEMRI" lab, a.sample, a.datetime_received, a.user rec_user, a.for_sequencing for_seq, b.field_sample_id, b.broth_sample, b.datetime_added, b.user br_user '
            . 'from '.Config::$kemri_db_name.'.received_samples a left join '.Config::$kemri_db_name.'.broth_assoc b on a.id=b.field_sample_id where b.id is null '
            . 'union '
            . 'select "Samples w/o Broth" issue, "UoN" lab, a.sample, a.datetime_received, a.user rec_user, a.for_sequencing for_seq, b.field_sample_id, b.broth_sample, b.datetime_added, b.user br_user '
            . 'from '.Config::$uon_db_name.'.received_samples a left join '.Config::$uon_db_name.'.broth_assoc b on a.id=b.field_sample_id where b.id is null '
            . 'union '
            . 'select "Multiple Broth Samples" issue, "KEMRI" lab, a.sample, a.datetime_received, a.user rec_user, a.for_sequencing for_seq, b.field_sample_id, b.broth_sample, b.datetime_added, b.user br_user '
            . 'from '.Config::$kemri_db_name.'.broth_assoc as b inner join '.Config::$kemri_db_name.'.received_samples as a on b.field_sample_id=a.id where field_sample_id in (select field_sample_id from '.Config::$uon_db_name.'.broth_assoc group by field_sample_id having count(*) > 1)'
            . 'union '
            . 'select "Multiple Broth Samples" issue, "UoN" lab, a.sample, a.datetime_received, a.user rec_user, a.for_sequencing for_seq, b.field_sample_id, b.broth_sample, b.datetime_added, b.user br_user '
            . 'from '.Config::$uon_db_name.'.broth_assoc as b inner join '.Config::$uon_db_name.'.received_samples as a on b.field_sample_id=a.id where field_sample_id in (select field_sample_id from '.Config::$uon_db_name.'.broth_assoc group by field_sample_id having count(*) > 1)';
      $samples = $this->Dbase->ExecuteQuery($samplesQ);
      if($samples == 1) die(json_encode(array('error' => true, 'mssg' => $this->Dbase->lastError)));

      die(json_encode(array('error' => false, 'data' => $samples)));
   }

   /**
    * Run summaries for broth and mcconky samples
    */
   private function dbCheckBrothMcconkySamples(){
      $samplesQ = 'select "Broth w/o McConky" issue, "KEMRI" lab, a.plate1_barcode, a.datetime_added plate1_datetime, a.media_used, a.user plate1_user, a.no_qtr_colonies, b.broth_sample, b.datetime_added broth_datetime, b.user broth_user '
            . 'FROM '.Config::$kemri_db_name.'.mcconky_assoc as a left join '.Config::$kemri_db_name.'.broth_assoc as b on a.broth_sample_id=b.id where b.id is null '
            . 'union '
            . 'select "Multiple McConky" issue, "KEMRI" lab, a.plate1_barcode, a.datetime_added plate1_datetime, a.media_used, a.user plate1_user, a.no_qtr_colonies, b.broth_sample, b.datetime_added broth_datetime, b.user broth_user '
            . 'from '.Config::$kemri_db_name.'.mcconky_assoc as a inner join '.Config::$kemri_db_name.'.broth_assoc as b on a.broth_sample_id=b.id where field_sample_id in (SELECT broth_sample_id FROM `mcconky_assoc` group by broth_sample_id having count(*) > 1) '
            . 'union '
            . 'select "Broth w/o McConky" issue, "UoN" lab, a.plate1_barcode, a.datetime_added plate1_datetime, a.media_used, a.user plate1_user, a.no_qtr_colonies, b.broth_sample, b.datetime_added broth_datetime, b.user broth_user '
            . 'FROM '.Config::$uon_db_name.'.mcconky_assoc as a left join '.Config::$uon_db_name.'.broth_assoc as b on a.broth_sample_id=b.id where b.id is null '
            . 'union '
            . 'select "Multiple McConky" issue, "UoN" lab, a.plate1_barcode, a.datetime_added plate1_datetime, a.media_used, a.user plate1_user, a.no_qtr_colonies, b.broth_sample, b.datetime_added broth_datetime, b.user broth_user '
            . 'from '.Config::$uon_db_name.'.mcconky_assoc as a inner join '.Config::$uon_db_name.'.broth_assoc as b on a.broth_sample_id=b.id where field_sample_id in (SELECT broth_sample_id FROM `mcconky_assoc` group by broth_sample_id having count(*) > 1)'
            . ' order by plate1_datetime desc';
      $samples = $this->Dbase->ExecuteQuery($samplesQ);
      if($samples == 1) die(json_encode(array('error' => true, 'mssg' => $this->Dbase->lastError)));

      die(json_encode(array('error' => false, 'data' => $samples)));
   }

   /**
    * Run checks for mcconky plate to colonies
    */
   private function dbCheckMcconkyColonies(){
      $samplesQ = 'SELECT "Colonies Count" issue, "UoN" lab, b.plate1_barcode, date(b.datetime_added) plate1_datetime, b.user plate1_user, b.no_qtr_colonies, count(*) as colonies_count, date(a.datetime_saved) as colony_datetime, a.user colony_user, "N/A" as colony '
            . 'FROM '.Config::$uon_db_name.'.colonies as a inner join '.Config::$uon_db_name.'.mcconky_assoc as b on a.mcconky_plate_id=b.id group by a.mcconky_plate_id '
            . 'union '
            . 'SELECT "Colonies Count" issue, "KEMRI" lab, b.plate1_barcode, date(b.datetime_added) plate1_datetime, b.user plate1_user, b.no_qtr_colonies, count(*) as colonies_count, date(a.datetime_saved) as colony_datetime, a.user colony_user, "N/A" as colony '
            . 'FROM '.Config::$kemri_db_name.'.colonies as a inner join '.Config::$uon_db_name.'.mcconky_assoc as b on a.mcconky_plate_id=b.id group by a.mcconky_plate_id '
            . 'union '
            . 'SELECT "Missing Colonies" issue, "UoN" lab, b.plate1_barcode, date(b.datetime_added) plate1_datetime, b.user plate1_user, b.no_qtr_colonies, "N/A" as colonies_count, "N/A" colony_datetime, "N/A" colony_user, "N/A" as colony '
            . 'FROM '.Config::$uon_db_name.'.mcconky_assoc as b left join '.Config::$uon_db_name.'.colonies as a on b.id=a.mcconky_plate_id where a.id is null '
            . 'union '
            . 'SELECT "Missing Colonies" issue, "KEMRI" lab, b.plate1_barcode, date(b.datetime_added) plate1_datetime, b.user plate1_user, b.no_qtr_colonies, "N/A" as colonies_count, "N/A" colony_datetime, "N/A" colony_user, "N/A" as colony '
            . 'FROM '.Config::$kemri_db_name.'.mcconky_assoc as b left join '.Config::$uon_db_name.'.colonies as a on b.id=a.mcconky_plate_id where a.id is null '
            . 'order by plate1_datetime desc';

      $samples = $this->Dbase->ExecuteQuery($samplesQ);
      if($samples == 1) die(json_encode(array('error' => true, 'mssg' => $this->Dbase->lastError)));
      die(json_encode(array('error' => false, 'data' => $samples)));
   }

   /**
    * Run checks for colonies to MH plates
    */
   private function dbCheckColoniesMH(){
      $samplesQ = 'select "Multiple MHs" issue, "UoN" lab, b.colony, date(b.datetime_saved) colony_datetime, a.mh, a.datetime_added mh_datetime, a.user mh_user '
            . 'from '.Config::$uon_db_name.'.mh_assoc as a inner join '.Config::$uon_db_name.'.colonies as b on a.colony_id=b.id '
            . 'where colony_id in (SELECT colony_id FROM '.Config::$uon_db_name.'.mh_assoc group by colony_id having count(*) > 1) '
            . 'union '
            . 'select "Multiple MHs" issue, "KEMRI" lab, b.colony, date(b.datetime_saved) colony_datetime, a.mh, a.datetime_added mh_datetime, a.user mh_user '
            . 'from '.Config::$kemri_db_name.'.mh_assoc as a inner join '.Config::$kemri_db_name.'.colonies as b on a.colony_id=b.id '
            . 'where colony_id in (SELECT colony_id FROM '.Config::$kemri_db_name.'.mh_assoc group by colony_id having count(*) > 1) '
            . 'order by colony, mh_datetime';

      $samples = $this->Dbase->ExecuteQuery($samplesQ);
      if($samples == 1) die(json_encode(array('error' => true, 'mssg' => $this->Dbase->lastError)));
      die(json_encode(array('error' => false, 'data' => $samples)));
   }

   /**
    * Checks for MH -> vials
    */
   private function dbCheckMHVials(){
      $samplesQ = 'SELECT "MH w/o Vials" issue, "UoN" lab, b.mh, date(b.datetime_added) mh_datetime, "NULL" as mh_vial, "NULL" as vial_date, "NULL" as box, "NULL" as pos '
            . 'from '.Config::$uon_db_name.'.mh_vial as a right join '.Config::$uon_db_name.'.mh_assoc as b on b.id=a.mh_id where a.id is null '
            . 'union '
            . 'SELECT "Vials w/o Pos" issue, "UoN" lab, b.mh, date(b.datetime_added) mh_datetime, "NULL" as mh_vial, date(datetime_saved) as vial_date, "NULL" as box, "NULL" as pos '
            . 'from '.Config::$uon_db_name.'.mh_vial as a inner join '.Config::$uon_db_name.'.mh_assoc as b on b.id=a.mh_id where a.box is null '
            . 'union '
            . 'select "Multiple Vials/MH" issue, "UoN" lab, b.mh, date(b.datetime_added) mh_datetime, mh_vial, date(datetime_saved) vial_date, box, position_in_box as pos '
            . 'from '.Config::$uon_db_name.'.mh_vial as a inner join '.Config::$uon_db_name.'.mh_assoc as b on a.mh_id=b.id where a.mh_id in (select mh_id from '.Config::$uon_db_name.'.mh_vial group by mh_id having count(*) > 1) '
            . 'union '
            . 'SELECT "MH w/o Vials" issue, "KEMRI" lab, b.mh, date(b.datetime_added) mh_datetime, "NULL" as mh_vial, "NULL" as vial_date, "NULL" as box, "NULL" as pos '
            . 'from '.Config::$kemri_db_name.'.mh_vial as a right join '.Config::$kemri_db_name.'.mh_assoc as b on b.id=a.mh_id where a.id is null '
            . 'union '
            . 'SELECT "Vials w/o Pos" issue, "KEMRI" lab, b.mh, date(b.datetime_added) mh_datetime, "NULL" as mh_vial, date(datetime_saved) as vial_date, "NULL" as box, "NULL" as pos '
            . 'from '.Config::$kemri_db_name.'.mh_vial as a inner join '.Config::$kemri_db_name.'.mh_assoc as b on b.id=a.mh_id where a.box is null '
            . 'union '
            . 'select "Multiple Vials/MH" issue, "KEMRI" lab, b.mh, date(b.datetime_added) mh_datetime, mh_vial, date(datetime_saved) vial_date, box, position_in_box as pos '
            . 'from '.Config::$kemri_db_name.'.mh_vial as a inner join '.Config::$kemri_db_name.'.mh_assoc as b on a.mh_id=b.id where a.mh_id in (select mh_id from '.Config::$kemri_db_name.'.mh_vial group by mh_id having count(*) > 1) '
            . 'order by mh_datetime, mh';

      $samples = $this->Dbase->ExecuteQuery($samplesQ);
      if($samples == 1) die(json_encode(array('error' => true, 'mssg' => $this->Dbase->lastError)));
      die(json_encode(array('error' => false, 'data' => $samples)));
   }

   /**
    * Creates a home page for updating the lab databases
    */
   private function updateLabDBHome(){
      $allDatabases = array(
         array('id' => 'kemri', 'name' => 'KEMRI Lab Database'),
         array('id' => 'uon', 'name' => 'UoN Lab Database'),
         array('id' => 'postmoterm', 'name' => 'Postmoterm Lab Database'),
         array('id' => 'pm-serum', 'name' => 'PM-Serum Lab Database'),
         array('id' => 'pm-edta', 'name' => 'PM-EDTA Lab Database')
      );
?>
<script type="text/javascript" src="js/uzp_lab.js"></script>
<link rel="stylesheet" href="<?php echo OPTIONS_COMMON_FOLDER_PATH?>azizi-shared-libs/customMessageBox/mssg_box.css" />
<link rel="stylesheet" href="<?php echo OPTIONS_COMMON_FOLDER_PATH?>jqwidgets/jqwidgets/styles/jqx.base.css" type="text/css" />
<script type="text/javascript" src="<?php echo OPTIONS_COMMON_FOLDER_PATH?>jqwidgets/jqwidgets/jqxcore.js"></script>
<script type="text/javascript" src="<?php echo OPTIONS_COMMON_FOLDER_PATH?>jqwidgets/jqwidgets/jqxdata.js"></script>
<script type="text/javascript" src="<?php echo OPTIONS_COMMON_FOLDER_PATH?>jqwidgets/jqwidgets/jqxnotification.js"></script>
<script type="text/javascript" src="<?php echo OPTIONS_COMMON_FOLDER_PATH?>jqwidgets/jqwidgets/jqxbuttons.js"></script>
<script type="text/javascript" src="<?php echo OPTIONS_COMMON_FOLDER_PATH?>jqwidgets/jqwidgets/jqxfileupload.js"></script>

<div id="batch_upload">
   <h3 class="center" id="home_title">Database Backup to the AZIZI database</h3>
   <a href="./?page=" style="float: left; margin-bottom: 10px;">Back</a> <br />
   <div id="info">
      Use the placeholder below to upload a backup of a lab database
      <ol>
         <li><b>CAUTION: TAKE CARE WHEN UPLOADING BACKUP DATABASES.</b></li>
         <li><b>SELECT THE CORRECT DATABASE TO IMPORT THE BACKUP FILE. THIS WILL DELETE THE PREVIOUS DATABASES.</b></li>
      </ol>
   </div>
   <div id="upload"></div>
   <div id="details">
      <div class='control-group'>
         <label class='control-label' for='database_id'>Database</label>
         <div id="database_pl"></div>
      </div>
   </div>
</div>
<div id="notification_box"><div></div></div>

<script type="text/javascript">
   $('#whoisme .back').html('<a href=\'?page=farm_animals\'>Back</a>');       //back link
   var uzp = new Uzp();
   uzp.allDatabases = <?php echo json_encode($allDatabases, true); ?>;
   uzp.initiateBackupUpload();
</script>
<?php
   }

   /**
    * Restore uploaded backups
    */
   private function saveUploadedBackups(){
      $this->Dbase->CreateLogEntry('Processing a database backup...', 'info');
      // if we dont have the event and person who did it reject it
      if($_POST['database'] === 0) die(json_encode(array('error' => true, 'mssg' => 'Please specify the person who carried out this event. The update has been cancelled')));
      $databases = array(
         'postmoterm' => Config::$pm_db_name,
         'uon' => Config::$uon_db_name,
         'kemri' => Config::$kemri_db_name,
         'pm-edta' => Config::$pm_edta_db_name,
         'pm-serum' => Config::$pm_serum_db_name
      );

      // save the file and process it
      $uploaded = GeneralTasks::CustomSaveUploads('tmp/', 'file_2_upload', array('application/octet-stream'), true);
      if(!is_array($uploaded)){
         $this->Dbase->CreateLogEntry($uploaded, 'debug');
         if(is_string($uploaded)) die(json_encode(array('error' => true, 'mssg' => $uploaded)));
         else die(json_encode(array('error' => true, 'mssg' => 'No files were selected for upload.')));
      }
      $uploadedFile = $uploaded[0];
      $currentDB = $databases[$_POST['database']];

      // create a backup of the current database
      $filename = 'current_backup_'.date('Ymd_H:i:s').'.sql';
		$command = Config::$config['mysqldump']." -u ".Config::$superConfig['user']." -p'".Config::$superConfig['pass']."' -h ".Config::$config['dbloc']." ".$currentDB.' > '.$filename;
      $this->Dbase->CreateLogEntry("Backup Database using: $command", 'info');
      $out = shell_exec($command);
      $this->Dbase->CreateLogEntry("Command output: $out", 'info');

      // delete the current database and create a fresh one
      $command = Config::$config['mysql']." -u ".Config::$superConfig['user']." -p'".Config::$superConfig['pass']."' -h ".Config::$config['dbloc']." -e \"drop database $currentDB\"";
      $this->Dbase->CreateLogEntry("Delete database using: $command", 'info');
      $out = shell_exec($command);
      $this->Dbase->CreateLogEntry("Command output: $out", 'info');

      $command = Config::$config['mysql']." -u ".Config::$superConfig['user']." -p'".Config::$superConfig['pass']."' -h ".Config::$config['dbloc']." -e \"create database $currentDB\"";
      $this->Dbase->CreateLogEntry("Create database using: $command", 'info');
      $out = shell_exec($command);
      $this->Dbase->CreateLogEntry("Command output: $out", 'info');

      // try and import the backed up database
      $restoreCommand = Config::$config['mysql']." -u ".Config::$superConfig['user']." -p'".Config::$superConfig['pass']."' -h ".Config::$config['dbloc']." ".$currentDB.' < '.$uploadedFile;
      $this->Dbase->CreateLogEntry("Import backed up database using: $restoreCommand", 'info');
      $out = shell_exec($restoreCommand);
      $this->Dbase->CreateLogEntry("Command output: $out", 'info');

      // delete the backup of the current database
      unlink($filename);

      die(json_encode(array('error' => false, 'mssg' => 'Successfully ran the upload command. Log in to ensure that the data is well backed up.')));
   }
}
?>