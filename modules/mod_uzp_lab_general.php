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

      if(!Config::$downloadFile && ($this->Dbase->session['error'] || $this->Dbase->session['timeout'])){
         if(OPTIONS_REQUEST_TYPE == 'normal'){
            $this->LoginPage($this->Dbase->session['message'], $_SESSION['username']);
            return;
         }
         elseif(OPTIONS_REQUEST_TYPE == 'ajax') die('-1' . $this->Dbase->session['message']);
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
         <li><a href="?page=step3">McConky Plate (3)</a></li>
         <li><a href="?page=step4">Colonies (4)</a></li>
         <li><a href="?page=step5.1">Colonies Archival (5.1)</a></li>
         <li><a href="?page=step5">Archival -> Plate (5)</a></li>
         <li><a href="?page=step6">Biochemical Test Prep (6)</a></li>
         <li><a href="?page=step7">Biochemical Test Result (7)</a></li>
         <li><a href="?page=step8">Archival -> Plate3 (8)</a></li>
         <li><a href="?page=step9">Plate3 -> Plates 4,5 (9)</a></li>
         <li><a href="?page=step10">Plates 4,5 -> AST Result Reading (10)</a></li>
         <li><a href="?page=step11">Archival -> Plate 6 (Regrowing) (11)</a></li>
         <li><a href="?page=step12">Plate 6 -> Eppendorf / DNA Extract (12)</a></li>
         <li><a href="?page=step13">Eppendorf / DNA Extract -> Archive (13)</a></li>
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
   <h3 class="center" id="home_title">Log samples received from the field</h3>
   <div class="scan">
      <div id="sample_format"><label style="float: left;">Sample format: </label>&nbsp;&nbsp;<input type="text" name="sample_format" value="AVAQ63847" /></div>
      <div id="current_user"><label style="float: left;">Current User: </label>&nbsp;&nbsp;<?php echo $userCombo; ?></div> <br />

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

   private function receiveSamplesSave(){
      // time to save the received sample
      $query = 'insert into received_samples(sample, user) values(:sample, :user)';
      $vals = array('sample' => $_POST['sample'], 'user' => $_POST['cur_user']);

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
      $checkQuery = 'select id from archive where colony = :colony';
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
      $testResultCombo = $this->bioChemicalTestResultCombo();
?>
    <link rel="stylesheet" href="<?php echo OPTIONS_COMMON_FOLDER_PATH; ?>jqwidgets/jqwidgets/styles/jqx.base.css" type="text/css" />
    <script type="text/javascript" src="js/uzp_lab.js"></script>
    <script type="text/javascript" src="<?php echo OPTIONS_COMMON_FOLDER_PATH; ?>jquery/jquery.min.js"></script>
    <script type="text/javascript" src="<?php echo OPTIONS_COMMON_FOLDER_PATH; ?>jqwidgets/jqwidgets/jqxcore.js"></script>
    <script type="text/javascript" src="<?php echo OPTIONS_COMMON_FOLDER_PATH; ?>jqwidgets/jqwidgets/jqxinput.js"></script>
    <script type="text/javascript" src="<?php echo OPTIONS_COMMON_FOLDER_PATH; ?>jqwidgets/jqwidgets/jqxbuttons.js"></script>
    <script type="text/javascript" src="<?php echo OPTIONS_COMMON_FOLDER_PATH; ?>jqwidgets/jqwidgets/jqxnotification.js"></script>

<div id="broth_enrichment">
   <h3 class="center" id="home_title">Biochemical Test Results</h3>
   <div class="scan">
      <div id="current_user"><label style="float: left;">Current User: </label>&nbsp;&nbsp;<?php echo $userCombo; ?></div> <br />

      <div class="center">
         <div id="test_name"><label style="display: initial;">Test done: </label>&nbsp;&nbsp;<?php echo $testCombo; ?></div> <br />
         <div id="test_result"><label style="display: initial;">Result: </label>&nbsp;&nbsp;<?php echo $testResultCombo; ?></div> <br />
         <input type="text" name="sample" />
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
   private function bioChemTestResultSave(){
      /**
       * check whether the parent sample is in the database
       * if it is in the database, save the association
       */
      //{cur_user: cur_user, sample: sample, test_name: test_name, test_result: test_result}
      $checkQuery = 'select id from biochemical_test where media = :media';
      $insertQuery = 'insert into biochemical_test_results(media_id, test, result, user) values(:media_id, :test, :result, :user)';

      $result = $this->Dbase->ExecuteQuery($checkQuery, array('media' => $_POST['sample']));
      if($result == 1) die(json_encode(array('error' => true, 'mssg' => $this->Dbase->lastError)));
      else if(count($result) == 0) die(json_encode(array('error' => true, 'mssg' => "The sample '{$_POST['sample']}' is not in the database.")));

      // now add the association
      $result = $this->Dbase->ExecuteQuery($insertQuery, array('media_id' => $result[0]['id'], 'test' => $_POST['test_name'], 'result' => $_POST['test_result'], 'user' => $_POST['cur_user']));
      if($result == 1) die(json_encode(array('error' => true, 'mssg' => $this->Dbase->lastError)));
      else die(json_encode(array('error' => false, 'mssg' => 'Test has been saved succesfully.')));
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
      $checkQuery = 'select id from archive where colony = :colony';
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
      $drugNameCombo = $this->drugNameCombo();
?>
    <link rel="stylesheet" href="<?php echo OPTIONS_COMMON_FOLDER_PATH; ?>jqwidgets/jqwidgets/styles/jqx.base.css" type="text/css" />
    <script type="text/javascript" src="js/uzp_lab.js"></script>
    <script type="text/javascript" src="<?php echo OPTIONS_COMMON_FOLDER_PATH; ?>jquery/jquery.min.js"></script>
    <script type="text/javascript" src="<?php echo OPTIONS_COMMON_FOLDER_PATH; ?>jqwidgets/jqwidgets/jqxcore.js"></script>
    <script type="text/javascript" src="<?php echo OPTIONS_COMMON_FOLDER_PATH; ?>jqwidgets/jqwidgets/jqxinput.js"></script>
    <script type="text/javascript" src="<?php echo OPTIONS_COMMON_FOLDER_PATH; ?>jqwidgets/jqwidgets/jqxbuttons.js"></script>
    <script type="text/javascript" src="<?php echo OPTIONS_COMMON_FOLDER_PATH; ?>jqwidgets/jqwidgets/jqxnotification.js"></script>

<div id="broth_enrichment">
   <h3 class="center" id="home_title">Plates 4,5 -> AST Result Reading</h3>
   <div class="scan">
      <div id="drug_name"><label style="float: left;">Drug name: </label>&nbsp;&nbsp;<?php echo $drugNameCombo; ?></div>
      <div id="drug_value"><label style="float: left;">Drug value: </label>&nbsp;&nbsp;<input type="text" name="drug_value" class="input-small"/></div>
      <div id="current_user"><label style="float: left;">Current User: </label>&nbsp;&nbsp;<?php echo $userCombo; ?></div>
      <div class="center">
         <input type="text" name="sample" />
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
   $(document).keypress(uzp.receiveSampleKeypress);
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
      $result = $this->Dbase->ExecuteQuery($insertQuery, array('plate45_id' => $result[0]['id'], 'drug' => $_POST['drug'], 'value' => $_POST['drug_value'], 'user' => $_POST['cur_user']));
      if($result == 1) die(json_encode(array('error' => true, 'mssg' => $this->Dbase->lastError)));
      else die(json_encode(array('error' => false, 'mssg' => 'Test has been saved succesfully.')));
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
      $checkQuery = 'select id from archive where colony = :colony';
      $insertQuery = 'insert into plate6(colony_id, plate, user) values(:field_sample_id, :broth_sample, :user)';

      $result = $this->Dbase->ExecuteQuery($checkQuery, array('colony' => $_POST['field_sample']));
      if($result == 1) die(json_encode(array('error' => true, 'mssg' => $this->Dbase->lastError)));
      else if(count($result) == 0) die(json_encode(array('error' => true, 'mssg' => "The field sample '{$_POST['field_sample']}' is not in the database.")));

      // now add the association
      $result = $this->Dbase->ExecuteQuery($insertQuery, array('field_sample_id' => $result[0]['id'], 'broth_sample' => $_POST['broth_sample'], 'user' => $_POST['cur_user']));
      if($result == 1) die(json_encode(array('error' => true, 'mssg' => $this->Dbase->lastError)));
      else die(json_encode(array('error' => false, 'mssg' => 'The association has been saved succesfully.')));
   }
   private function usersCombo(){
      $userVals = array('John Kiiru');
      $userIds = array('kiiru_john');
      $settings = array('items' => $userVals, 'values' => $userIds, 'firstValue' => 'Select One', 'name' => 'users', 'id' => 'usersId', 'class' => 'input-medium');
      $userCombo = GeneralTasks::PopulateCombo($settings);

      return $userCombo;
   }
   
   private function bioChemicalTestCombo(){
      $userVals = array('Biochemical test1', 'Biochemical test2');
      $userIds = array('test1', 'test2');
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
}
?>