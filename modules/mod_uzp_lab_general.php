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
      $this->Dbase->SessionStart();
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
      elseif(OPTIONS_REQUESTED_MODULE == 'step5.1'){
         if(OPTIONS_REQUESTED_SUB_MODULE == '') $this->coloniesStorage();
         elseif(OPTIONS_REQUESTED_SUB_MODULE == 'save') $this->coloniesStorageSave();
      }
      elseif(OPTIONS_REQUESTED_MODULE == 'step5'){
         if(OPTIONS_REQUESTED_SUB_MODULE == '') $this->plate2Home();
         elseif(OPTIONS_REQUESTED_SUB_MODULE == 'save') $this->plate2Save();
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
         if(OPTIONS_REQUESTED_SUB_MODULE == '') $this->campyFalconFreezingHome();
         elseif(OPTIONS_REQUESTED_SUB_MODULE == 'save') $this->campyFalconFreezingSave();
      }
      elseif(OPTIONS_REQUESTED_MODULE == 'campy_step3'){
         if(OPTIONS_REQUESTED_SUB_MODULE == '') $this->campyMccdaHome();
         elseif(OPTIONS_REQUESTED_SUB_MODULE == 'save') $this->campyMccdaSave();
      }
      elseif(OPTIONS_REQUESTED_MODULE == 'campy_step4'){
         if(OPTIONS_REQUESTED_SUB_MODULE == '') $this->campyMccdaGrowthHome();
         elseif(OPTIONS_REQUESTED_SUB_MODULE == 'save') $this->campyMccdaGrowthSave();
      }
      elseif(OPTIONS_REQUESTED_MODULE == 'campy_step5'){
         if(OPTIONS_REQUESTED_SUB_MODULE == '') $this->campyMicroaerobicColoniesHome();
         elseif(OPTIONS_REQUESTED_SUB_MODULE == 'save') $this->coloniesStorageSave();
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
   }

   private function dumpData() {
        if(!file_exists(Config::$config['rootdir']."\downloads")) mkdir(Config::$config['rootdir']."\downloads");
		$date = new DateTime();
		$filename = Config::$config['rootdir']."\downloads\99hh_".Config::$config['site']."_".$date->format('Y-m-d_H-i-s').'.sql';
		$zipName = $filename.".zip";
		$command = Config::$config['mysqldump']." -u ".Config::$config['user']." -p".Config::$config['pass']." ".Config::$config['dbase'].' > '.$filename;
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
         <li><a href="?page=step4">Colonies (4)</a></li>
         <li><a href="?page=step5.1">Colonies Archival (5.1)</a></li>
         <li><a href="?page=step5">Archival -> Plate 2 (5)</a></li>
         <li><a href="?page=step6">Biochemical Test Prep (6)</a></li>
         <li><a href="?page=step7">Biochemical Test Result (7)</a></li>
         <li><a href="?page=step8">Archival -> Plate 3 (8)</a></li>
         <li><a href="?page=step9">Plate3 -> Plates 4,5 (9)</a></li>
         <li><a href="?page=step10">Plates 4,5 -> AST Result Reading (10)</a></li>
         <li><a href="?page=step11">Archival -> Plate 6 (Regrowing) (11)</a></li>
         <li><a href="?page=step12">Plate 6 -> Eppendorf / DNA Extract (12)</a></li>
         <div><br /><b>Campylobacter Lab Modules</b></div>
         <li><a href="?page=campy_step1">Receive Bootsocks</a></li>
         <li><a href="?page=campy_step2">Bootsocks to Falcon and cryo tubes</a></li>
         <li><a href="?page=campy_step3">Falcon tube to MCCDA plate</a></li>
         <li><a href="?page=campy_step4">MCCDA plate to Aerobic/Microaerobic plate</a></li>
         <li><a href="?page=campy_step5">Microaerobic colonies freezing</a></li>
         <div><br /><b>Miscellaneous</b></div>
         <li><a href="?page=dump">Backup database</a></li>
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
      <div id="colony_format"><label style="float: left;">Colony format: </label>&nbsp;&nbsp;<input type="text" name="colony_format" class="input-small" value="AVAQ70919" /></div>
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
      $checkQuery = 'select id from colonies where colony = :colony';
      $insertQuery = 'insert into plate2(colony_id, plate, user) values(:field_sample_id, :broth_sample, :user)';

      $result = $this->Dbase->ExecuteQuery($checkQuery, array('colony' => $_POST['field_sample']));
      if($result == 1) die(json_encode(array('error' => true, 'mssg' => $this->Dbase->lastError)));
      else if(count($result) == 0) die(json_encode(array('error' => true, 'mssg' => "The field sample '{$_POST['field_sample']}' is not in the database.")));

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

   private function bioChemTestPrepSave() {
      $checkQuery = 'select id from plate2 where plate = :plate';
      $insertQuery = 'insert into biochemical_test(plate2_id, media, user) values(:plate, :media, :user)';

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
      <div id="colony_format"><label style="float: left;">Colony format: </label>&nbsp;&nbsp;<input type="text" name="colony_format" class="input-small" value="AVAQ70919" /></div>
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
      $checkQuery = 'select id from colonies where colony = :colony';
      $insertQuery = 'insert into plate3(colony_id, plate, user) values(:field_sample_id, :broth_sample, :user)';

      $result = $this->Dbase->ExecuteQuery($checkQuery, array('colony' => $_POST['field_sample']));
      if($result == 1) die(json_encode(array('error' => true, 'mssg' => $this->Dbase->lastError)));
      else if(count($result) == 0) die(json_encode(array('error' => true, 'mssg' => "The field sample '{$_POST['field_sample']}' is not in the database.")));

      // now add the association
      $result = $this->Dbase->ExecuteQuery($insertQuery, array('field_sample_id' => $result[0]['id'], 'broth_sample' => $_POST['broth_sample'], 'user' => $_POST['cur_user']));
      if($result == 1) die(json_encode(array('error' => true, 'mssg' => $this->Dbase->lastError)));
      else die(json_encode(array('error' => false, 'mssg' => 'The association has been saved succesfully.')));
   }

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
   <h3 class="center" id="home_title">Plate 3 -> Plate 4 and Plate 5</h3>
   <a href="./?page=" style="float: left; margin-bottom: 10px;">Back</a> <br />
   <div class="scan">
      <div id="plate_format"><label style="float: left;">Plate 3 format: </label>&nbsp;&nbsp;<input type="text" name="plate_format" class="input-small" value="AVAQ70919" /></div>
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

   private function plate3to45Save() {
      $checkQuery = 'select id from plate3 where plate = :plate';
      $insertQuery = 'insert into plate45(plate3_id, plate, number, user) values(:plate3_id, :curr_plate, :number, :user)';

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
      $checkQuery = 'select id from colonies where colony = :colony';
      $insertQuery = 'insert into plate6(colony_id, plate, user) values(:field_sample_id, :broth_sample, :user)';

      $result = $this->Dbase->ExecuteQuery($checkQuery, array('colony' => $_POST['field_sample']));
      if($result == 1) die(json_encode(array('error' => true, 'mssg' => $this->Dbase->lastError)));
      else if(count($result) == 0) die(json_encode(array('error' => true, 'mssg' => "The field sample '{$_POST['field_sample']}' is not in the database.")));

      // now add the association
      $result = $this->Dbase->ExecuteQuery($insertQuery, array('field_sample_id' => $result[0]['id'], 'broth_sample' => $_POST['broth_sample'], 'user' => $_POST['cur_user']));
      if($result == 1) die(json_encode(array('error' => true, 'mssg' => $this->Dbase->lastError)));
      else die(json_encode(array('error' => false, 'mssg' => 'The association has been saved succesfully.')));
   }

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
   <h3 class="center" id="home_title">Plate 6 -> Eppendorf / DNA Extract (12)</h3>
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
      $checkQuery = 'select id from plate6 where plate = :plate';
      $insertQuery = 'insert into dna_eppendorfs(plate6_id, eppendorf, user) values(:plate6_id, :eppendorf, :user)';

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
         $userVals = array('John Kiiru', 'Tom Ouko', 'Hannah Njeri', 'Sam Njoroge', 'Benson Kiiru', 'Purity Karimi');
         $userIds = array('kiiru_john', 'Tom_Ouko', 'Hannah_Njeri', 'Sam_Njoroge', 'Benson_Kiiru', 'Purity_Karimi');
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
      $userVals = array('TSI', 'Urea', 'MIO', 'Citrate');
      $userIds = array('tsi', 'urea', 'mio', 'citrate');
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
      <div id="mcconky_format"><label style="float: left;">McConky Plate format: </label>&nbsp;&nbsp;<input type="text" name="plate_format" class="input-small" value="AVAQ70919" /></div>
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
      <div id="colonies_format"><label style="float: left;">Colonies format: </label>&nbsp;&nbsp;<input type="text" name="colonies_format" class="input-small" value="BDT013939" /></div>
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
            if(isset($samples[$k])) $layout .= "<div class='pos occupied'>$k</div>";
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
      $checkQuery = 'select id, box, position_in_box from colonies where colony = :colony';
      $updateQuery = 'update colonies set box = :box, position_in_box = :pos, pos_saved_by = :user where id = :id';

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
      <div id="sample_format"><label style="float: left;">Bootsock Barcode format: </label>&nbsp;&nbsp;<input type="text" name="sample_format" value="AVAQ63847" /></div>
      <div id="current_user"><label style="float: left;">Current User: </label>&nbsp;&nbsp;<?php echo $userCombo; ?></div> <br />

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
      $query = 'insert into received_bootsocks(sample, user) values(:sample, :user)';
      $vals = array('sample' => $_POST['sample'], 'user' => $_POST['cur_user']);

      $result = $this->Dbase->ExecuteQuery($query, $vals);
      if($result == 1) die(json_encode(array('error' => true, 'mssg' => $this->Dbase->lastError)));
      else die(json_encode(array('error' => false, 'mssg' => 'The sample has been saved succesfully.')));
   }

   /**
    * Creates a home page for saving bootsock to a falcon tube and the cryo vials
    */
   private function campyFalconFreezingHome(){
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
   <h3 class="center" id="home_title">Linking a field bootsock to falcon tubes and cryo vials</h3>
   <a href="./?page=" style="float: left; margin-bottom: 10px;">Back</a> <br />
   <div class="scan">
      <div id="sample_format"><label style="float: left;">Bootsock Sample format: </label>&nbsp;&nbsp;<input type="text" name="sample_format" class="input-small" value="AVAQ70919" /></div>
      <div id="broth_format"><label style="float: left;">Falcon tube/Freezing Samples format: </label>&nbsp;&nbsp;<input type="text" name="broth_format" class="input-small" value="BSR010959" /></div>
      <div id="current_user"><label style="float: left;">Current User: </label>&nbsp;&nbsp;<?php echo $userCombo; ?></div> <br />

      <div class="center">
         <input type="text" name="sample" />
         <div>
            <input style='margin-top: 5px;' type="submit" value="Submit" id='jqxSubmitButton' />
         </div>
      </div>
   </div>
   <div class="received"><div class="saved">Falcon tubes and vials appear here</div></div>
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
   private function campyFalconFreezingSave(){
      /**
       * check whether the bootsoc is in the database
       * if it is in the database, save the association
       */
      $checkQuery = 'select id from received_bootsocks where sample = :sample';
      $insertQuery = 'insert into bootsock_assoc(bootsock_id, daughter_sample, user) values(:bootsock_id, :daughter_sample, :user)';

      $result = $this->Dbase->ExecuteQuery($checkQuery, array('sample' => $_POST['field_sample']));
      if($result == 1) die(json_encode(array('error' => true, 'mssg' => $this->Dbase->lastError)));
      else if(count($result) == 0) die(json_encode(array('error' => true, 'mssg' => "The field sample '{$_POST['field_sample']}' is not in the database.")));

      // now add the association
      $result = $this->Dbase->ExecuteQuery($insertQuery, array('bootsock_id' => $result[0]['id'], 'daughter_sample' => $_POST['broth_sample'], 'user' => $_POST['cur_user']));
      if($result == 1) die(json_encode(array('error' => true, 'mssg' => $this->Dbase->lastError)));
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
   <h3 class="center" id="home_title">Loading broth from falcon tube to a plate (Plate 1)</h3>
   <a href="./?page=" style="float: left; margin-bottom: 10px;">Back</a> <br />
   <div class="scan">
      <div id="colony_format"><label style="float: left;">Falcon tube format: </label>&nbsp;&nbsp;<input type="text" name="colony_format" class="input-small" value="BSR010959" /></div>
      <div id="plate_format"><label style="float: left;">Plate 3 format: </label>&nbsp;&nbsp;<input type="text" name="plate_format" class="input-small" value="AVMS00043" /></div>
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
    * Saves a new association of the falcon tubes and the mccda plates
    */
   private function campyMccdaSave(){
      /**
       * check whether the falcon sample is in the database
       * if it is in the database, save the association of the falcon tube and the plate
       */
      $checkQuery = 'select id from bootsock_assoc where daughter_sample = :sample';
      $insertQuery = 'insert into mccda_assoc(falcon_id, plate1_barcode, user) values(:bootsock_id, :plate1_barcode, :user)';

      $result = $this->Dbase->ExecuteQuery($checkQuery, array('sample' => $_POST['field_sample']));
      if($result == 1) die(json_encode(array('error' => true, 'mssg' => $this->Dbase->lastError)));
      else if(count($result) == 0) die(json_encode(array('error' => true, 'mssg' => "The falcon sample '{$_POST['field_sample']}' is not in the database.")));

      // now add the association
      $result = $this->Dbase->ExecuteQuery($insertQuery, array('bootsock_id' => $result[0]['id'], 'plate1_barcode' => $_POST['broth_sample'], 'user' => $_POST['cur_user']));
      if($result == 1){
         if($this->Dbase->lastErrorCodes[1] == 1062) die(json_encode(array('error' => true, 'mssg' => 'Duplicate entry for the current association')));
         else die(json_encode(array('error' => true, 'mssg' => $this->Dbase->lastError)));
      }
      else die(json_encode(array('error' => false, 'mssg' => 'The association has been saved succesfully.')));
   }

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
      <div id="plate_format"><label style="float: left;">MCCDA Plate format: </label>&nbsp;&nbsp;<input type="text" name="plate_format" class="input-small" value="AVMS00045" /></div>
      <div id="media_format"><label style="float: left;">Aerobic and Micro-aerobic Plates format: </label>&nbsp;&nbsp;<input type="text" name="media_format" class="input-small" value="AVAQ64156" /></div>
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
   $("#jqxSubmitButton").on('click', uzp.saveColonies).jqxButton({ width: '150'});

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

   private function campyMccdaGrowthSave(){
      /**
       * check whether the plate is in the database
       * if it is in the database, save the plate and its associated colonies
       */
      $checkQuery = 'select id from mccda_assoc where plate1_barcode = :plate';
      $insertQuery = 'insert into mccda_growth(mccda_plate_id, am_plate, user) values(:mccda_plate_id, :am_plate, :user)';

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
   <h3 class="center" id="home_title">Logging all microaerobic colonies</h3>
   <a href="./?page=" style="float: left; margin-bottom: 10px;">Back</a> <br />
   <div class="scan">
      <div id="colonies_format"><label style="float: left;">Colonies format: </label>&nbsp;&nbsp;<input type="text" name="colonies_format" class="input-small" value="BDT013939" /></div>
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
}
?>