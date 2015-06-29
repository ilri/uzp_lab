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
      elseif(OPTIONS_REQUESTED_MODULE == 'step3'){
         if(OPTIONS_REQUESTED_SUB_MODULE == '') $this->mcConkyPlateHome();
         elseif(OPTIONS_REQUESTED_SUB_MODULE == 'save') $this->mcConkyPlateSave();
      }
      elseif(OPTIONS_REQUESTED_MODULE == 'step4'){
         if(OPTIONS_REQUESTED_SUB_MODULE == '') $this->coloniesHome();
         elseif(OPTIONS_REQUESTED_SUB_MODULE == 'save') $this->coloniesSave();
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

   /**
    * Create the user dropdown boxes which will be consistent in most of the modules
    *
    * @return  string   Returns a HTML string which creates the user dropdown
    */
   private function usersCombo(){
      $userVals = array('John Kiiru');
      $userIds = array('kiiru_john');
      $settings = array('items' => $userVals, 'values' => $userIds, 'firstValue' => 'Select One', 'name' => 'users', 'id' => 'usersId', 'class' => 'input-medium');
      $userCombo = GeneralTasks::PopulateCombo($settings);

      return $userCombo;
   }

   private function mcConkyPlateHome(){
      $userCombo = $this->usersCombo();

      $mediaVals = array('Sample Media');
      $mediaIds = array('sample_media');
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
   <h3 class="center" id="home_title">Loading the broth samples on the McConky plate</h3>
   <div class="scan">
      <div id="broth_format"><label style="float: left;">Broth format: </label>&nbsp;&nbsp;<input type="text" name="broth_format" class="input-small" value="BSR010959" /></div>
      <div id="mcconky_format"><label style="float: left;">McConky Plate format: </label>&nbsp;&nbsp;<input type="text" name="plate_format" class="input-small" value="AVAQ70919" /></div>
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
   <h3 class="center" id="home_title">Creating colonies for archival from the McConky plate</h3>
   <div class="scan">
      <div id="mcconky_format"><label style="float: left;">McConky Plate format: </label>&nbsp;&nbsp;<input type="text" name="plate_format" class="input-small" value="AVAQ70919" /></div>
      <div id="colonies_format"><label style="float: left;">Colonies format: </label>&nbsp;&nbsp;<input type="text" name="colonies_format" class="input-small" value="BSR010959" /></div>
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
   $("#jqxSubmitButton").on('click', uzp.saveColonies).jqxButton({ width: '150'});

   uzp.prevSample = undefined;
   uzp.curSample = undefined;
   uzp.curSampleType = undefined;
   uzp.prevSampleType = undefined;
   $(document).keypress(uzp.receiveSampleKeypress);
</script>
<?php

   }
}
?>