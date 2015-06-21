<?php

/* 
 * This module is responsible for authenticating users.
 * Some of the functionality here was moved from the mod_dbase* files
 */
class Security {
   
   private $dBase;
   
   /**
    * The constructor. Initialize all the things:
    *    - dBase
    */
   public function __construct($dBase) {
      $this->dBase = $dBase;
   }
   
   /**
    * Moved from mod_objectbased_dbase.
    * This function decryps the provided ciphertext using
    * azizi's RSA private key
    * Crypto algorithm used here is RSA
    * 
    * @param String $cipherText  Base-64 encoded string to be decrypted
    * 
    * @return mixed Returns the plain text if able to decrypt the ciphertext or null if not
    */
   public function decryptCypherText($cipherText){
      $privateKey = openssl_pkey_get_private(Config::$rsaPrivKey);
      $result = "";
      if(openssl_private_decrypt(base64_decode($cipherText), $result, $privateKey)) {
         $this->dBase->CreateLogEntry("Provided ciphertext successfully decrypted", "info");
         return $result;
      }
      else {
         $this->dBase->CreateLogEntry("Was unable to decrypt some ciphertext", "fatal");
         return null;
      }
   }
   
   /**
    * This function encrypts the provided plaintext using azizi's RSA public
    * key. Crypto algorithm used is RSA
    * 
    * @param type $plainText  The text to be encrypted
    * @return mixed  Returns the ciphertext 
    */
   public function encryptPlainText($plainText) {
      $publicKey = Config::$rsaPubKeyUnwrapped;
      $result = "";
      if(openssl_public_encrypt($plainText, $result, $publicKey)){
         $this->dBase->CreateLogEntry("Provided plaintext successfully encrypted", "info");
         $this->dBase->CreateLogEntry("Ciphertext = ".base64_encode($result), "debug");
         return base64_encode($result);
      }
      else {
         return null;
      }
   }
   
   /**
    * Moved from mod_objectbased_dbase.
    * This function takes a username and encrypted password
    * and tries to authenticate the user in this order:
    *    1. local azizi database
    *    2. cgiar ldap server
    * 
    * @param type $user
    * @param type $pass
    * 
    * @return int Returns 1 incase of a fatal error, 2 incase of a wrong password, 3 incase the account doesnt exist, 4 in case the a/c is disabled or 0 in case all is ok.
    */
   public function authUser($user, $pass){
      $this->dBase->CreateLogEntry("Authenticating ".$user, "info");
      $_SESSION['username'] = $user;
      $_SESSION['password'] = $pass;
      
      $decryptedPW = $this->decryptCypherText($pass);
      
      $adAuthAllowed = true;//this variable is changed to false if ad auth dissallowed from the local database
      
      //1. check if user exists in local database
      $query = "SELECT id, salt,ldap_authentication, allowed"
              . " FROM ". Config::$config['session_dbase'] . ".users"
              . " WHERE login=:username";
      
      $result = $this->dBase->ExecuteQuery($query, array("username" => $user));
      
      if($result == 1){
         $this->dBase->CreateLogEntry("problem occured while trying to check if user in local database","fatal");
         return 1;
      }
      else {
         if(count($result) == 1){//if there is only one user in the database with the specified username
            
            if($result[0]['ldap_authentication'] == 0 || $result[0]['allowed'] == 0){//if user only allowed to log in using local auth or his/her account has been disabled
               $adAuthAllowed = false;//prevents the user from logging in using ldap
            }
            
            $userID = $result[0]['id'];
            $salt = $result[0]['salt'];
            
            /* hash the password before querying the database
             * hash is sha1(salt + md5(password))
             */
            $hashedPW = $this->hashPassword($decryptedPW, $salt);
            
            //2. try local auth
            $query = "SELECT allowed, sname, onames"
                    . " FROM " . Config::$config['session_dbase'] . ".users"
                    . " WHERE id = :id AND psswd=:hashedPW AND ldap_authentication=0 AND allowed = 1";
            $result = $this->dBase->ExecuteQuery($query, array("id" => $userID, "hashedPW" => $hashedPW));
            if($result == 1){
               $this->dBase->CreateLogEntry("problem occured while trying to access the local database for local auth", "fatal");
               return 1;
            }
            else {
               if(count($result) == 1){//user successfully authenticated in local database
                  //3. check if user account is disabled
                  if($result[0]['allowed'] == 1){
                     $_SESSION['auth_type'] = "local";
                     $this->setUserDetails($user);
                     
                     $this->dBase->CreateLogEntry($user . " successfully authenticated using the local database", "info");
                     return 0;
                  }
                  else {
                     $this->dBase->CreateLogEntry($user." successfully authenticated locally but account is disabled", "fatal");
                     return 4;
                  }
               }
               else {
                  /* user not authenticated in local database because:
                   *    - the passwords don't match hence no result fetched from database
                   *    - more than one user fetched (I do not know how to explain in which situation this would happen)
                   */
                  if(count($result) > 1){
                     $this->dBase->CreateLogEntry("more than one user in the local database with the same username and password", "fatal");
                     return 1;
                  }
                  else {
                     $this->dBase->CreateLogEntry("Unable to authenticate " . $user . " locally. Will now try LDAP", "warning");
                  }
               }
            }
         }
         else if(count($result) > 1){
            $this->dBase->CreateLogEntry("more than one user in the local database with the same username. Using LDAP for auth", "warning");
         }
      }
      
      /*if we have reached this far it might mean:
       *    - user not in local database
       *    - user in local database but not successfully authed
       */
      if($adAuthAllowed == true){
         $ldapAuth = $this->ldapAuth($user, $decryptedPW);
         
         $this->setUserDetails($user);//call this function after ldapAuth. Check setuserDetails function docs
         
         return $ldapAuth;
      }
      else {
         $this->dBase->CreateLogEntry($user . " disallowed for LDAP/Active Directory authentication", "fatal");
         return 2;
      }
   }
   
   /**
    * Moved from mod_objectbased_dbase
    * This function tries to authenticate using CGIAR's Active Directory
    * Authentication is done by trying to bind as the user. If binding fails
    * then the user is not authenticated
    * 
    * @param type $user Username to be authenticated
    * @param type $pass Unencrypted password
    * 
    * @return 1 if an error occured, 2 if user not authed and 0 if everything is fine. Return values should matche those from authUser($user, $pass)
    */
   public function ldapAuth($user, $pass){
      $ldapConnection = ldap_connect(Config::$config['ldapHost'], Config::$config['ldapPort']);
      if(!$ldapConnection){
          $this->dBase->CreateLogEntry('Could not connect to the AD server', 'fatal');
          return 1;
       }
       else {
          ldap_set_option($ldapConnection, LDAP_OPT_REFERRALS, 0);
          ldap_set_option($ldapConnection, LDAP_OPT_PROTOCOL_VERSION, 3);
          $ldapBind = ldap_bind($ldapConnection, "$user@ilri.cgiarad.org", $pass);
          if ($ldapBind) {
             $ldapSr = ldap_search($ldapConnection, Config::$config['ldapSSpace'], "(sAMAccountName=$user)", array('sn', 'givenName', 'title'));
             
             /*********************/
             $entries = ldap_get_entries($ldapConnection, $ldapSr);
             $this->dBase->CreateLogEntry(print_r($entries, true), "debug");
             /*********************/
             
             if (!$ldapSr) {
                $this->dBase->CreateLogEntry('Connected successfully to the AD server, but cannot search as the user', 'fatal');
                return 1;
             }
             
             $entry1 = ldap_first_entry($ldapConnection, $ldapSr);
             if (!$entry1) {
                $this->dBase->CreateLogEntry('Connected successfully to the AD server as user. However searching user in AD did not come up with any hits', 'fatal');
                return 1;
             }
             
             $ldapAttributes = ldap_get_attributes($ldapConnection, $entry1);
             $this->dBase->CreateLogEntry(print_r($ldapAttributes, true), "debug");
             $_SESSION['surname'] = $ldapAttributes['sn'][0];
             $_SESSION['onames'] = $ldapAttributes['givenName'][0];
             if(!isset($_SESSION['user_type'])){
                $_SESSION['user_type'] = array();
             }
             array_push($_SESSION['user_type'], $ldapAttributes['title'][0]);
             
             $this->dBase->CreateLogEntry($user." successfully authenticated using Active Directory", 'info');
             $_SESSION['auth_type'] = "ldap";
             $_SESSION['unhashedPW'] = $pass;//potential security hole
             return 0;
          }
          else {
             $this->dBase->CreateLogEntry("There was an error while binding user '$user' to the AD server", 'fatal');
             return 2;
          }
       }
    }
    
    /**
     * This function gets user details from local database. Details include:
     *   - The groups the user is in ($_SESSION['user_type'])
     *   - Surname ($_SESSION['surname'])
     *   - Other names ($_SESSION['onames'])
     *   - User ID ($_SESSION['user_id'])
     *   - Username ($_SESSION['username'])
     * 
     * Note that the groups, surname and other names might have already been set
     * in ldapAuth. Any extra groups found in the database will be appended to the
     * User's LDAP group. Names will be overwritten
     * 
     * @param type $user
     */
    private function setUserDetails($user) {
       $this->dBase->CreateLogEntry("adding user details to session", "info");
       
       $query = "SELECT id, sname, onames"
               . " FROM " . Config::$config['session_dbase'] . ".users"
               . " WHERE login = :username";
       
       $result = $this->dBase->ExecuteQuery($query, array("username" => $user));
       
       if($result == 1){
          $this->dBase->CreateLogEntry("Problem occured while trying to get user details from the database", 'fatal');
       }
       else if(count($result) == 1) {
          $_SESSION['username'] = $user;
          $_SESSION['user_id'] = $result[0]['id'];
          $_SESSION['surname'] = $result[0]['sname'];
          $_SESSION['onames'] = $result[0]['onames'];
          
          //$this->dBase->CreateLogEntry("session is = ".print_r($_SESSION, true), "info");
          
          //get the extra groups the user is in
          $query = "SELECT b.name"
                  . " FROM user_groups AS a"
                  . " INNER JOIN groups AS b ON a.group_id = b.id"
                  . " WHERE a.user_id = :id";
          $result = $this->dBase->ExecuteQuery($query, array("id" => $_SESSION['user_id']));
          
          if(!isset($_SESSION['user_type'])){
             $_SESSION['user_type'] = array();
          }
          
          if($result == 1){
             $this->dBase->CreateLogEntry("An error occured while trying to fetch user's groups from the local database", 'fatal');
          }
          else {
             foreach ($result as $currGroup){
                array_push($_SESSION['user_type'], $currGroup['name']);
             }
          }
       }
       else if(count($result) > 1){
          $this->dBase->CreateLogEntry("More than one user in the database with the username ".$user.". Unable to set user details", 'fatal');
       }
       else {
          $this->dBase->CreateLogEntry("Unable to get user details for ".$user." from the local database", 'warning');
       }
    }
    
    /**
     * This function gets all the closed access modules (modules that require user to be logged in)
     * the current user has access to.
     * It creates an associative array of modules where key is the uri of a module and value the name of the module
     * 
     * @param $all Boolean If set to true, will fetch all closed access modules, even the ones not supposed to be in the main menu
     * 
     * @return mixed Returns an associative array of modules if everything is fine or null if something goes wrong
     */
    public function getClosedAccessModules($all){
       $groups = $_SESSION['user_type'];
       $modules = array();
       if(is_array($groups)){
          //get modules accessible to all groups
          $query = "SELECT name, uri"
                  . " FROM ".Config::$config['session_dbase'].".modules"
                  . " WHERE access_level = 'closed' AND group_access = 'all'";
          
          if($all == false){
             $query .= " AND in_menu = 1";
          }
          
          $result = $this->dBase->ExecuteQuery($query);
          if($result == 1){
             $this->dBase->CreateLogEntry("Something went wrong while trying to get closed access (but accessible by all groups) modules", "fatal");
             return array();
          }
          else {
             //only add a module if not in modules array
             foreach ($result as $currRow){
                if(!isset($modules[$currRow['uri']])){
                   $modules[$currRow['uri']] = $currRow['name'];
                }
             }
          }
          
          //get modules only accessible to some groups
          foreach($groups as $currGroup){
             $query = "SELECT d.name, d.uri"
                     . " FROM ".Config::$config['session_dbase'].".group_actions AS a"
                     . " INNER JOIN ".Config::$config['session_dbase'].".sm_actions AS b ON a.sm_action_id = b.id"
                     . " INNER JOIN ".Config::$config['session_dbase'].".sub_modules AS c ON b.sub_module_id = c.id"
                     . " INNER JOIN ".Config::$config['session_dbase'].".modules AS d ON c.module_id = d.id"
                     . " INNER JOIN ".Config::$config['session_dbase'].".groups AS e ON a.group_id = e.id"
                     . " WHERE e.name = :group";
             
             /*$query = "SELECT b.name, b.uri"
                     . " FROM ".Config::$config['session_dbase'].".group_modules AS a"
                     . " INNER JOIN ".Config::$config['session_dbase'].".modules AS b ON b.id = a.module_id"
                     . " INNER JOIN ".Config::$config['session_dbase'].".groups AS c ON c.id = a.group_id"
                     . " WHERE b.access_level = 'closed' AND b.group_access = 'specific' AND c.name = :group";*/
             
             $result = $this->dBase->ExecuteQuery($query, array("group" => $currGroup));
             if($result == 1){
                $this->dBase->CreateLogEntry("Something went wrong while trying to get closed access (group specific) modules for the group ".$currGroup, "fatal");
                return array();
             }
             else {
                //only add the module to the modules array if it doesnt exist there
                foreach($result as $currRow){
                   if(!isset($modules[$currRow['uri']])){
                      $modules[$currRow['uri']] = $currRow['name'];
                   }
                }
             }
          }
       }
       else {
          $this->dBase->CreateLogEntry("The user_type session variable is mulformed");
          return array();
       }
       
       return $modules;
    }
    
    /**
     * This function checkes if a user is allowed to access the given submodule and action
     * @param string $module The uri for the module
     * @param string $subModule The uri for the sub_module
     * @param string $action The uri for the action
     * 
     * @return int O if user has access, 1 if an error occurres and 2 if user does not have access
     */
    public function isUserAllowed($module, $subModule = '', $action = '') {
       
       //first check if all groups are allowed into module;
       $query = "SELECT id"
               . " FROM ".Config::$config['session_dbase'].".modules"
               . " WHERE group_access = 'all' AND uri = :module";
       
       $result = $this->dBase->ExecuteQuery($query, array("module" => $module));
       
       if($result == 1){
          return 1;
       }
       else if(count($result) > 0){//the module is accessible to all groups
          return 0;
       }
       
       if($subModule == null) $subModule = "";
       if($action == null) $action = "";
       
       $groups = $_SESSION['user_type'];
       foreach ($groups as $currGroup){//check if there is at least one group with access to the requested module/submodule/action
          
          $query = "SELECT d.name, d.uri"
                     . " FROM ".Config::$config['session_dbase'].".group_actions AS a"
                     . " INNER JOIN ".Config::$config['session_dbase'].".sm_actions AS b ON a.sm_action_id = b.id"
                     . " INNER JOIN ".Config::$config['session_dbase'].".sub_modules AS c ON b.sub_module_id = c.id"
                     . " INNER JOIN ".Config::$config['session_dbase'].".modules AS d ON c.module_id = d.id"
                     . " INNER JOIN ".Config::$config['session_dbase'].".groups AS e ON a.group_id = e.id";
          
          $data = array();
          if(strlen($subModule) == 0){//we only need to know if the user has access to the module
             $query .= " WHERE e.name = :group AND d.uri = :module";
             $data = array("group" => $currGroup, "module" => $module);
          }
          else if(strlen($subModule) > 0 && strlen($action) == 0){//we only want to know if user has access to a submodule
             $query .= " WHERE e.name = :group AND d.uri = :module AND c.uri = :submodule";
             $data = array("group" => $currGroup, "module" => $module, "submodule" => $subModule);
          }
          else if(strlen($subModule) > 0 && strlen($action) > 0){//we want to know if a user has access to an action in a submodule
             $query .= " WHERE e.name = :group AND d.uri = :module AND c.uri = :submodule AND (b.uri = :action OR b.uri = '')";//last section caters for specifying all actions by eaving action uri empty
             $data = array("group" => $currGroup, "module" => $module, "submodule" => $subModule, "action" => $action);
          }
          $result = $this->dBase->ExecuteQuery($query, $data);
          
          if($result == 1){
             $this->dBase->CreateLogEntry("An error occurred while trying to check if user has access to ".$module, "fatal");
             return 1;
          }
          else if(count($result) > 0){
             return 0;
          }
          //continue checking through the rest of the groups
       }
       
       return 2;//if we have reached this far, the user does not have access to the module/submodule/action
    }
    
    /**
     * This function checks whether the specified module is under open access ie
     *   users can access it without logging in.
     * Please do not confuse open access with group access. Modules that have group access set 
     * to 'all' are accessible to all groups but require authentication before users can access
     * them
     * 
     * @param type $module
     * 
     * @return int 0 if module is open, 1 if an error occurres and 2 if module is not under open access
     */
    public function isModuleOpenAccess($module){
       $query = "SELECT id"
               . " FROM ".Config::$config['session_dbase'].".modules"
               . " WHERE access_level = 'open' AND uri = :module";
       $result = $this->dBase->ExecuteQuery($query, array("module" => $module));
       
       if($result == 1){
          $this->dBase->CreateLogEntry("An error occurred while trying to check if ".$module." lies under open access", "fatal");
          return 1;
       }
       else if(count($result) == 0){
          return 2;
       }
       else if(count($result) > 0) {
          return 0;
       }
       else {
          return 1;
       }
    }
    
    /**
     * This function creates a user
     * 
     * @param string $login The username
     * @param string $encryptedPW Encrypted Password
     * @param string $surname User's given surname
     * @param string $onames User's given other names
     * @param string $project Project user is in
     * @param array $groups Array of group IDs user associated with
     * @param int $ldap 0 If user logs in using local auth and 1 if user logs in using ldap
     * 
     * @return int 1 If error occurres, generated password or null (if user uses LDAP for auth) if user successfully added, 2 if password does not meet min requirements
     */
    public function createUser($login, $surname, $onames, $project, $groups ,$ldap, $email){
       $password = null;
       if($ldap == 0){//user going to use local auth
          $password = $this->generateRandomPassword();

         $randomSalt = $this->generateSalt();
         $hashedPW = $this->hashPassword($password, $randomSalt);

         $query = "INSERT INTO ".Config::$config['session_dbase'].".users(login, psswd, salt, sname, onames, project, allowed, ldap_authentication, email)"
                 . " VALUES(:user, :psswd, :salt, :sname, :onames, :project, :allowed, :ldap, :email)";

         $result = $this->dBase->ExecuteQuery($query, array("user" => $login, "psswd" => $hashedPW, "salt" => $randomSalt, "sname" => $surname, "onames" => $onames, "project" => $project, "allowed" => "1", "ldap" => $ldap, "email" => $email));
       }
       else {//user going to use ldap auth, do not set password and salt
          $query = "INSERT INTO ".Config::$config['session_dbase'].".users(login, sname, onames, project, allowed, ldap_authentication, email)"
                 . " VALUES(:user, :sname, :onames, :project, :allowed, :ldap, :email)";

         $result = $this->dBase->ExecuteQuery($query, array("user" => $login, "sname" => $surname, "onames" => $onames, "project" => $project, "allowed" => "1", "ldap" => $ldap, "email" => $email));
       }
       
       
       //stop here if unable to add user
       if(!is_array($result)) {//returns an empty array if successfull
          $this->dBase->CreateLogEntry("An error occurred while trying to add user to database", "fatal");
          return 1;
       }
       
       $query = "SELECT id"
               . " FROM ".Config::$config['session_dbase'].".users"
               . " WHERE login = :login";
       
       $result = $this->dBase->ExecuteQuery($query, array("login" => $login));
       
       if(is_array($result) && count($result) == 1){
          $id = $result[0]['id'];
          
            //add groups to user_groups table
          foreach($groups AS $currGroup){
             $query = "INSERT INTO user_groups(user_id, group_id)"
                     . " VALUES(:userID, :groupID)";
             $this->dBase->ExecuteQuery($query, array("userID" => $id, "groupID" => $currGroup));
          }
          $this->dBase->CreateLogEntry("Successfully created account for ".$login, "info");
          return $password;
       }
       else {
          $this->dBase->CreateLogEntry("An error occurred while trying to get user id for ".$login, "fatal");
          return 1;
       }
       
    }
    
    /**
     * This function creates a user
     * 
     * @param int $id The user's id
     * @param string $login The username
     * @param string $encryptedPW Encrypted Password
     * @param string $surname User's given surname
     * @param string $onames User's given other names
     * @param string $project Project user is in
     * @param array $groups Array of group IDs user associated with
     * @param int $ldap 0 if user logs in using local auth and 1 if user logs in using ldap
     * @param int $allowed 1 if user is allowed to log in and 0 if not
     * 
     * @return int 1 If error occurres, 0 if user successfully added, 2 if password does not meet min requirements
     */
    public function updateUser($id, $login, $encryptedPW, $surname, $onames, $project, $email, $groups, $ldap, $allowed){
       if(strlen($encryptedPW) > 0){
          $password = $this->decryptCypherText($encryptedPW);

         //check if password meets minimum requirements
         $pwSettings = Config::$psswdSettings;

         if(strlen($password) < $pwSettings['minLength']){
            return 2;
         }
         else if($pwSettings['alphaChars'] == true){//test for alphabetical characters
            if(preg_match("/[a-zA-Z]/", $password) == 0){//password does not match pattern
               return 2;
            }
         }
         else if($pwSettings['numericChars'] == true){//test for numeric characters
            if(preg_match("/[0-9]/", $password) == 0){//password does not match pattern
               return 2;
            }
         }
         else if($pwSettings['specialChars'] == true){//test for non-alphanumeric characters
            if(preg_match("/[^a-zA-Z0-9]/", $password) == 0){//password does not match pattern
               return 2;
            }
         }

         $randomSalt = $this->generateSalt();
         $hashedPW = $this->hashPassword($password, $randomSalt);
       }
       else {
          $hashedPW = "";
       }
       
       if(strlen($hashedPW) > 0){
          $query = "UPDATE ".Config::$config['session_dbase'].".users"
                  . " SET login = :user, psswd = :psswd, salt = :salt, sname = :sname, onames = :onames, email = :email, project = :project, allowed = :allowed, ldap_authentication = :ldap"
                  . " WHERE id = :id";

          $result = $this->dBase->ExecuteQuery($query, array("user" => $login, "psswd" => $hashedPW, "salt" => $randomSalt, "sname" => $surname, "onames" => $onames, "email" => $email, "project" => $project, "allowed" => $allowed, "ldap" => $ldap, "id" => $id));
       }
       else {//we dont want to change the password
          $query = "UPDATE ".Config::$config['session_dbase'].".users"
                  . " SET login = :user, sname = :sname, onames = :onames, project = :project, allowed = :allowed, ldap_authentication = :ldap, email = :email"
                  . " WHERE id = :id";

          $result = $this->dBase->ExecuteQuery($query, array("user" => $login, "sname" => $surname, "onames" => $onames, "project" => $project, "allowed" => $allowed, "ldap" => $ldap, "id" => $id, "email" => $email));
       }
       
       //stop here if unable to add user
       if(!is_array($result)) {//returns an empty array if successfull
          return 1;
       }
       
       //delete existing groups
       $query = "DELETE FROM ".Config::$config['session_dbase'].".user_groups"
               . " WHERE user_id = :id";
       $this->dBase->ExecuteQuery($query, array("id" => $id));
       
       foreach($groups AS $currGroup){
         $query = "INSERT INTO user_groups(user_id, group_id)"
                 . " VALUES(:userID, :groupID)";
         $this->dBase->ExecuteQuery($query, array("userID" => $id, "groupID" => $currGroup));
       }
       
       return 0;
    }
    
    /**
     * This function hashes a password using the following algorithm
     * 
     *      sha1(salt + md5(password))
     *      
     * @param type $unhashedPW
     */
    public function hashPassword($unhashedPW, $salt){
       return sha1($salt.md5($unhashedPW));
    }
    
    /**
     * This function generates random text to be used as salt
     */
    public function generateSalt(){
       $length = 20;
      $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
      $randomString = '';
      for ($i = 0; $i < $length; $i++) {
          $randomString .= $characters[rand(0, strlen($characters) - 1)];
      }
      return $randomString;
    }
    
    /**
     * this function creates a user group
     * 
     * @param type $name The name of the new group
     * @param type $groupActions IDs of the sub module actions the group has access to
     * 
     * @return int 0 if group successfully added and 1 otherwise
     */
    public function createUserGroup($name, $groupActions){
       $query = "INSERT INTO groups(name)"
               . " VALUES(:name)";
       $result = $this->dBase->ExecuteQuery($query, array("name" => $name));
       
       if($result == 1){
          $this->dBase->CreateLogEntry("An error occurred while trying to create the group ".$name, "fatal");
          return 1;
       }
       
       $query = "SELECT id"
               . " FROM groups"
               . " WHERE name = :name";
       $ids = $this->dBase->ExecuteQuery($query, array("name" => $name));
       if($ids == 1){
          $this->dBase->CreateLogEntry("An error occurred while trying to get the id for the group ".$name, "fatal");
          return 1;
       }
       else if(count($ids) == 1){//set action access if one group found
          foreach($groupActions as $currAction){
             $query = "INSERT INTO group_actions(group_id, sm_action_id)"
                     . " VALUES(:groupID, :actionID)";
             $this->dBase->ExecuteQuery($query, array("groupID" => $ids[0]['id'], "actionID" => $currAction));
          }
       }
       
       return 0;
    }
    
    /**
     * This function modifies a user group
     * 
     * @param type $id
     * @param type $name
     * @param type $groupActions
     */
    public function editUserGroup($id, $name, $groupActions){
       $query = "UPDATE groups"
               . " SET name=:name"
               . " WHERE id=:id";
       $result = $this->dBase->ExecuteQuery($query, array("name" => $name, "id" => $id));
       
       if($result == 1){
          $this->dBase->CreateLogEntry("Error occurred while trying to update the group ".$name, "fatal");
          return 1;
       }
       
       $query = "DELETE FROM group_actions"
               . " WHERE group_id = :id";
       $this->dBase->ExecuteQuery($query, array("id" => $id));
       
       foreach($groupActions as $currAction){
          $query = "INSERT INTO group_actions(group_id, sm_action_id)"
                  . " VALUES(:groupID, :actionID)";
          $this->dBase->ExecuteQuery($query, array("groupID" => $id, "actionID" => $currAction));
       }
       return 0;
    }
    
    /**
     * This function is meant to be called by a user who is not necessarily super
     * The function allows for updating of account details
     * 
     * @param type $username           
     * @param type $encryptedOPassword
     * @param type $encryptedNPassword
     * @return int
     */
    public function updateOwnAccount($sname, $onames, $username, $email, $encryptedOPassword, $encryptedNPassword, $ldap){
         if($ldap == 0){
            //auth the user
            if($this->authUser($username, $encryptedOPassword) == 0){
               if(strlen($encryptedNPassword) > 0 && strlen($encryptedOPassword) > 0){//local user changing password
                  $this->dBase->CreateLogEntry("Will be changing password for ".$username." among other things", "info");
                  
                  $oldPassword = $this->decryptCypherText($encryptedOPassword);
                  $newPassword = $this->decryptCypherText($encryptedNPassword);

                  //check if password meets minimum requirements
                 $pwSettings = Config::$psswdSettings;

                 if(strlen($newPassword) < $pwSettings['minLength']){
                    return 2;
                 }
                 else if($pwSettings['alphaChars'] == true){//test for alphabetical characters
                    if(preg_match("/[a-zA-Z]/", $newPassword) == 0){//password does not match pattern
                       return 2;
                    }
                 }
                 else if($pwSettings['numericChars'] == true){//test for numeric characters
                    if(preg_match("/[0-9]/", $newPassword) == 0){//password does not match pattern
                       return 2;
                    }
                 }
                 else if($pwSettings['specialChars'] == true){//test for non-alphanumeric characters
                    if(preg_match("/[^a-zA-Z0-9]/", $newPassword) == 0){//password does not match pattern
                       return 2;
                    }
                 }

                  $query = "SELECT id"
                          . " FROM ". Config::$config['session_dbase'] . ".users"
                          . " WHERE login=:username AND ldap_authentication = 0 AND allowed = 1";
                  $result = $this->dBase->ExecuteQuery($query, array("username" => $username));


                  if(is_array($result) && count($result) == 1){
                     $id = $result[0]['id'];

                     $newSalt = $this->generateSalt();
                     $hashedNewPassword = $this->hashPassword($newPassword, $newSalt);

                     $query = "UPDATE ". Config::$config['session_dbase'] . ".users"
                             . " SET salt = :newSalt, psswd = :newPassword, sname = :sname, onames = :onames, email = :email"
                             . " WHERE id = :id";
                     $result = $this->dBase->ExecuteQuery($query, array("newSalt" => $newSalt, "newPassword" => $hashedNewPassword, "id" => $id, "sname" => $sname, "onames" => $onames, "email" => $email));

                     if(is_array($result)){
                        return 0;
                     }
                     else {
                        $this->dBase->CreateLogEntry("A problem occurred while trying to update account details for ".$username, "fatal");
                        return 1;
                     }
                  }
                  else {
                     return 1;
                  }
               }
               else if(strlen($encryptedNPassword) == 0){//local user changing other things apart from password
                  $this->dBase->CreateLogEntry("Will be changing account information for ".$username." but not the password", "info");
                  
                  $query = "SELECT id"
                          . " FROM ". Config::$config['session_dbase'] . ".users"
                          . " WHERE login=:username AND ldap_authentication = 0 AND allowed = 1";
                  $result = $this->dBase->ExecuteQuery($query, array("username" => $username));


                  if(is_array($result) && count($result) == 1){
                     $id = $result[0]['id'];
                     $salt = $result[0]['salt'];

                     $query = "UPDATE ". Config::$config['session_dbase'] . ".users"
                             . " SET sname = :sname, onames = :onames, email = :email"
                             . " WHERE id = :id";
                     $result = $this->dBase->ExecuteQuery($query, array("id" => $id, "sname" => $sname, "onames" => $onames, "email" => $email));
                     
                     if(is_array($result)){
                        return 0;
                     }
                     else {
                        $this->dBase->CreateLogEntry("An error occurred while trying to update account details for ".$username, "fatal");
                        return 1;
                     }
                  }
                  else {
                     $this->dBase->CreateLogEntry("Unable to find local user with username = ".$username." while trying to modify account","fatal");
                     return 1;
                  }
               }
               else {
                  $this->dBase->CreateLogEntry("Local user ".$username." did not provide current password while modifying account. Ignoring","fatal");
                  return 1;
               }
            }
            else {
               $this->dBase->CreateLogEntry("Unable auth user trying to modify account","fatal");
               return 1;
            }
         }
         else if($ldap == 1){//user auths using ldap
            $query = "SELECT id"
                       . " FROM ". Config::$config['session_dbase'] . ".users"
                       . " WHERE login=:username AND ldap_authentication = 1 AND allowed = 1";
            $result = $this->dBase->ExecuteQuery($query, array("username" => $username));
            if(is_array($result) && count($result) == 1){
               $id = $result[0]['id'];
               
               $query = "UPDATE ". Config::$config['session_dbase'] . ".users"
                        . " SET sname = :sname, onames = :onames, email = :email"
                        . " WHERE id = :id";
               $result = $this->dBase->ExecuteQuery($query, array("id" => $id, "sname" => $sname, "onames" => $onames, "email" => $email));
               $this->dBase->CreateLogEntry("User ".$username." just modified their account", "info");
               
               if(is_array($result)){
                  return 0;
               }
               else {
                  $this->dBase->CreateLogEntry("An error occurred while trying to update account details for ".$username, "fatal");
                  return 1;
               }
            }
            else if($result == 1){
               return 1;
            }
            else {//user auths over ldap and is not in the database. Do nothing
               $this->dBase->CreateLogEntry("User ".$username." uses LDAP for auth but is not in Database. Therefore unable to save account changes made", "fatal");
               return 0;
            }
         }
         else {
            return 1;
         }
    }
    
    public function generateRandomPassword(){
      $length = 8;
      $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
      $randomString = '';
      
      for ($i = 0; $i < $length; $i++) {
          $randomString .= $characters[rand(0, strlen($characters) - 1)];
      }
      
      return $randomString;
    }
}
?>
