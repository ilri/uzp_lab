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
}
?>