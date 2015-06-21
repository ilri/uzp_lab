<?php
/**
 * ChangeLog
 * 2011-08-02  Kihara Absolomon <soloincc@gmail.com>
 * Version 0.4
 * - Re-structred @see GeneralTasks#CreateDbSnapshot so that if a db name is not specified, it uses the db in the config
 * 
 * 2011-02-03  Kihara Absolomon <soloincc@gmail.com>
 * Version 0.3
 * Added @see GeneralTasks#PopulateCombo as an improved version of @see GeneralTasks#Populate_Combo
 * - Requires a single nested array with the settings
 * - Supports creation of multiple drop downs
 * - Added @see GeneralTasks#CreateThumbnail
 * - Added a function @see GeneralTasks#CreateDbSnapshot to create the snapshot of the db
 */

/**
 * A class that has generic general functions that can be used by any application
 *
 * @package    GeneralTasks
 * @author     Kihara Absolomon <soloincc@gmail.com>
 * @todo       Remove GeneralTasks#Populate_Combo in the future versions
 */
class GeneralTasks{

   /**
    * @var     array    A list of the common file types that we expect the user to upload
    * @since   v0.3
    */
   protected $fileTypes = array(
      'images' => array('image/jpeg', 'image/gif', 'image/bmp', 'image/jpg')
   );
   /**
    * Creates a directory if it does not exists and makes it writable. It is capable of creating recursive directories
    *
    * @param string $dir   The path to the folder to be created
    * @return boolean   Returns true if the path is created or exists else returns false in case the directory is not created
    */
   public function CreateDirIfNotExists($dir){
      if($dir == '') return false;
      if(!file_exists($dir)){ //its not there, lets try and create it
         return mkdir($dir, 0755, true);
      }
      return true;
   }

   /**
    * Get a web file (HTML, XHTML, XML, image, etc.) from a URL.  Returns an array containing the header fields and content.
    */
   public function GetWebPage($url, $method) {
      $options = array( 'http' => array(
                      'method' => $method,
                      'user_agent'    => 'spider',    // who am i
                      'max_redirects' => 10,          // stop after 10 redirects
                      'timeout'       => 120,         // timeout on response
              ) );
      $context = stream_context_create( $options );
      $page    = @file_get_contents( $url, false, $context );
//      var_dump($page);
//      echo $url;

      $result  = array( );
      if ( $page != false )
         $result['content'] = $page;
      else if ( !isset( $http_response_header ) )
         return null;    // Bad url, timeout

      // Save the header
      $result['header'] = $http_response_header;

      // Get the *last* HTTP status code
      $nLines = count( $http_response_header );
      for ( $i = $nLines-1; $i >= 0; $i-- ) {
         $line = $http_response_header[$i];
         if ( strncasecmp( "HTTP", $line, 4 ) == 0 ) {
            $response = explode( ' ', $line );
            $result['http_code'] = $response[1];
            break;
         }
      }

      return $result;
   }
   
   /**
    * Generates an alpha numeric code.
    * 
    * The generated codes are unique to the 40,000 and then there are possibilities of repetition. Its advisable that you create a checkin mechanism
    * 
    * @param   int      $seed_length   The length of the code to be generated
    * @return  string   Returns the generated code 
    */
   public function GenAlphaNumericCode($seed_length=8) {
       $seed = "ABCDEFGHJKLMNPQRSTUVWXYZ234567892345678923456789";
       $str = '';
       srand((double)microtime()*1000000);
       for ($i=0;$i<$seed_length;$i++) {
           $str .= substr ($seed, rand() % 48, 1);
       }
       return $str;
   }

   /**
    * Truncates a file
    * 
    * @param   string   $filename   The file to truncate. The file should be writable
    * @return  mixed    Returns 0 on successfull truncation, else it returns a string with the error message
    */
   public function TruncateFile($filename) {
      $fd=fopen($filename, "wt");
      if(!$fd) {
         return 'There was an error while opening the file for trucating!'; //cant be able to create the file
      }
      ftruncate($fd, 0);
      fclose($fd);
      return 0;
   }

   public function Compress($filenames){
      
   }

   /**
    * Given the necessary params, it creates a drop down box
    *
    * @param   array    $items      The options that will be displayed to the user
    * @param   array    $values     The hidden variable linking to the displayed values
    * @param   string   $defval     Default value to be displayed when nothing is selected
    * @param   string   $name       The combobox name. The string 'id' will be appedned to create its Id
    * @param   integer  $selected   The value to select
    * @param   bool     $enabled    To enable it or not
    * @param   string   $changewat  A function to call when a value is selected
    * @param   string   $tclass     (Optional) The class name to appy for this drop down
    * @return  string   Returns HTML code of the formatted combo
    */
   public function Populate_Combo($items, $values, $defval, $name, $selected, $enabled, $changewat = '', $tclass = NULL) {
      //reset($items);reset($values);
      if(!is_array($values)) $values=range(1,count($items));
      ($enabled)?$dis="":$dis="disabled";
      $change=($changewat!='' && isset($changewat))?"onChange='$changewat;'":'';
      $selected=(!isset($selected))?0:$selected;
      $class = (isset($tclass))?"class='$tclass'":'';
      for($i=0; $i<count($items)+1; $i++) {
         if($i==0) {
            $combo="<select name='$name' id=\"{$name}id\" $class $dis $change>\n\t\t\t<option value=\"0\"";
//            if($selected === $i) echo "$selected >> $i -- none selected<br />";
            $combo.=($selected === $i)?" selected>":">";
            $combo.=$defval;
         }
         else {
            $combo.="<option value='".current($values)."'";
            $combo.=($selected===current($values))?" selected>":">";
            $combo .= current($items);
            next($items);
            next($values);
         }
      }
      $combo.="\n\t\t</select>";
      return $combo;
   }

   /**
    * Creates a drop down based on the passed parameters. An improved version of GeneralTasks#Populate_Combo
    *
    * Example of use
    *
    * <code>
    * $settings = array(
    *    'items' => array('Zero', 'One', 'Two'),                  //The options that will be displayed to the user
    *    'values' => array(0, 1, 2),                              //(Optional) The hidden variable linking to the displayed values
    *    'firstValue' => 'Select One',                            //(Optional) The value that will be displayed when nothing is selected. In case of a multip drop down, its not required
    *    'name' => 'numbers',                                     //The HTML name of the drop box
    *    'id' => 'numbersId',                                     //(Optional) The HTML id of the drop box
    *    'selected' => '1',                                       //(Optional) The selected value. Defaults to 0.
    *    'enabled' => true,                                       //(Optional) Whether the drop down will be enabled or not. Defaults to true
    *    'onChange' => 'functionToCallWhenChanged("changed")',    //(Optional) The function to call when the drop down is changed. Defaults to nothing
    *    'class' => 'myClass',                                    //(Optional) The class of this drop down. Defaults to nothing
    *    'multipleDropDown' => false,                             //(Optional) Whether to create a multiple drop down. Defaults to false
    *    'size' => 10                                             //(Optional) The size of the drop down. Useful when creating a multiple drop down. Defaults to 0
    *    'width' => 100                                           //(Optional) The width of the drop down. Especially useful when the drop down is empty
    * );
    * 
    * </code>
    *
    * @param   array    $settings   An array with the drop down settings that are to be used. Check the example above
    * @return  string   Returns HTML code of the formatted combo
    * @since   v0.3
    */
   public function PopulateCombo($settings) {
      //autogenerate numericals for hidden values in case we dont have them defined
      if(!is_array($settings['values'])) $settings['values']=range(1, count($settings['items']));
      $onChange = (isset($settings['onChange']) && $settings['onChange'] != '') ? "onChange='{$settings['onChange']};'" : '';
      $selected = (isset($settings['selected'])) ? $settings['selected'] : 0;
      $class = (isset($settings['class'])) ? "class='{$settings['class']}'" : '';
      $multipleDropDown = (!isset($settings['multipleDropDown']) || $settings['multipleDropDown'] == false) ? '' : "multiple='multiple'";
      $enabled = (!isset($settings['enabled']) || $settings['enabled']) ? '' : 'disbaled';
      $dropDownId = (isset($settings['id'])) ? "id ='{$settings['id']}'": '';
      $itemCount = count($settings['items'])+1;
      //generate the style
      $style = 'style="';
      if(isset($settings['width'])) $style .= "width: {$settings['width']}px;";
      $style .= '"';

      //down to work
//      $combo = '';
      $combo = "\n\t\t<select name='{$settings['name']}' $dropDownId $class $multipleDropDown $enabled $onChange $style>";
      for($i=0; $i < $itemCount; $i++) {
         if($settings['values'][$i] == '') continue;
         if($i == 0) {
            //the first value for a multi combo is different from the first value of a normal drop down
            if(!isset($settings['multipleDropDown']) || $settings['multipleDropDown'] == false){
               $combo .= "\n\t\t\t<option value='0'";
               $combo .= ($selected === $i)?" selected>":">";
               $combo .= $settings['firstValue'];
            }
            //display the 1st value
            if(isset($settings['items'][$i]) && $settings['items'][$i] != ''){
               $combo .= "\n\t\t\t<option value='{$settings['values'][$i]}'";
               $combo .= ($selected === $settings['values'][$i]) ? " selected>" : ">";
               $combo .= $settings['items'][$i];
            }
         }
         else {
            $combo .= "\n\t\t\t<option value='{$settings['values'][$i]}'";
            $combo .= ($selected === $settings['values'][$i]) ? " selected>" : ">";
            $combo .= $settings['items'][$i];
         }
      }
      $combo .= "\n\t\t</select>";
      return $combo;
   }

/**
    * A custom upload function that allows multiple uploads of files and saves them in the specified location with their original names. This function assumes that
    * we are uploading multiple files.
    *
    * @param   string   $uploadDir     The location where the files are to be saved. It should either be relative to the script which was called by the browser, or an absolute path
    * @param   string   $html_name     The file name used in the HTML form
    * @param	array    $allowedTypes  An array of allowed file types. Common file types include {'text/plain', 'application/zip', 'application/vnd.ms-excel'}
    * @param	array    $max_size      (Optional) The max size allowed for the uploaded files. It defaults to 10MB
    * @param	array    $filenames     (Optional) An array with the destination file names, can be ommitted
    * @return  mixed    Returns an array with the path to the uploaded files on success or a string with an error message on error or 0 in case no files were selected for upload
    */
   public function CustomSaveUploads($uploadDir, $html_name, $allowedTypes, $max_size = 10485760, $filenames=null){
      $err_occ=0;
      //LogError(print_r($_FILES, true));
      if(count($_FILES)==0) return 'There are no files selected for upload.';
      //save the samples file
      $err_msg='';
      $realUploadedFiles=array();
      $uploadedFilesCount = count($_FILES[$html_name]['name']);
      for($i=0; $i < $uploadedFilesCount; $i++){
         $err_code=$_FILES[$html_name]['error'][$i];
         if($err_code==4) continue;        //no file selected
         //check for the errors that might have occurred
         if($err_code!=0 && $err_code!=4){
            if($err_code<3){
               $err_msg.=addslashes($_FILES[$html_name]['name'][$i])." exceeded the maximum allowed upload size.<br>";
            }
            if($err_code==3){
               $err_msg.=addslashes($_FILES[$html_name]['name'][$i])." was partially uploaded.<br>";
            }
            $err_occ = 1; continue;
         }

         //only allow xml files to be uploaded
         //LogError($_FILES[$html_name]['type'][$i]);
         if(!in_array($_FILES[$html_name]['type'][$i], $allowedTypes)){
//            echo '<pre>'.print_r($_FILES[$html_name], true).'</pre>';
            $err_msg.=addslashes($_FILES[$html_name]['name'][$i])." is not an allowed file type.<br>";
            $err_occ = 1; continue;
         }

         //Dont allow importation of files larger than max_size
         $userMaxSize = number_format($max_size / (1024*1024), 2);
         if($_FILES[$html_name]['size'][$i] > $max_size){
            $err_msg.=addslashes($_FILES[$html_name]['name'][$i])." is bigger than {$userMaxSize}MB. You are only allowed to import files less than {$userMaxSize}MB.<br>";
            $err_occ = 1;  continue;
         }
         //check if the destination folder exists, and if it doesnt try and create it
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
            $err_occ = 1;  continue;
         }
         else $realUploadedFiles[]=$destfile;
      }
      if($err_occ) return $err_msg;
      elseif(count($realUploadedFiles)==0) return 0;
      else return $realUploadedFiles;
   }

   /**
    * Converts a numeric position to a position that can be used by LabCollector
    *
    * @param integer $position   The numeric position that we want to convert
    * @param integer $rack_size  The size of the tray in question. Expecting a 10 incase the box is 100 welled box or 9 in case it is 81 welled
    * @return string    Returns the converted position that LC is comfortable with
    */
   public function NumericPosition2LCPosition($position, $rack_size){
      if($position%$rack_size==0) $box_detail = chr(64+floor($position/$rack_size)).$rack_size;
      else $box_detail = chr(65+floor($position/$rack_size)).$position%$rack_size;
      return $box_detail;
   }

   /**
    * Copies/moves a file from one place to another
    * 
    * @param   mixed    $files2Move       The absolute/relative paths of the files that we wanna move. Careful when using the relative paths.
    *                                     Can either be an array of files to move or just the path of one file as a string
    * @param   string   $destination      The absoloute path of the destination folder
    * @param   bool     $deleteOriginal   (Optional) Whether to delete the original or not. Defaults to true
    * @return  mixed    Returns a string with the error message in case there was an error while moving/copying the files or 0 when all has gone ok. Incase
    *                   there is an error, the whole process is reverted.
    * @todo    Add the liberty of specifying the destination to be an array
    * @since   v0.3
    */
   public function MoveFiles($files2Move, $destination, $deleteOriginal = TRUE) {
      $res = $this->CreateDirIfNotExists($destination);
      if (!$res) {
         $this->CreateLogEntry("Error! Could not create the directory '$destination' for saving some files.", 'debug');
         return "There was an error while creating a directory for the final files.";
      }
      
      $movedFiles = array();
      if(is_string($files2Move)) $files2Move = array($files2Move);
      foreach($files2Move as $curFile) {
         $pathInfo = pathinfo($curFile);
//         echo "Moving: $curFile => $destination<br />";
         //move these file
         if(!copy($curFile, "$destination/{$pathInfo['basename']}")) {
            $this->CreateLogEntry("Error! There was an error while copying {$curFile['basename']} from '{$curFile['dirname']}' to '$destination'.", 'debug');
            $this->CreateLogEntry("Error! There was an error while copying {$curFile['basename']} from '{$curFile['dirname']}' to '$destination'.", 'fatal');
            return "There was an error while copying some files to the final destination folder. Please contact the administrator.";
         }
         if($deleteOriginal){
            if(!unlink($curFile)){
               $this->CreateLogEntry("Error! There was an error while deleting {$curFile['basename']} from '{$curFile['dirname']}'.", 'debug');
               //delete all the moved files. If i can write, then I can delete
               foreach($movedFiles as $t) unlink("$destination/{$pathInfo['basename']}");
               return "There was an error while deleting some of the original files. The process has been aborted and reversed. Please contact the administrator.";
            }
         }
         else $movedFiles[] = $pathInfo['basename'];
      }
      return 0;
   }

   /**
    * Downloads an image from a given url.
    *
    * @param   string   $url        The url that we are going to fetch the image from
    * @param   string   $destPath   The path where are going to save the image to
    * @return  mixed    Returns a string with an error message in case an error occurs, else it returns 0
    * @since   v0.3
    */
   public function DownloadPicture($url, $destPath) {
      if(!$ch = curl_init($url)) return 'There was an error while initializing a curl session.';
      if(!$fp = fopen($destPath, 'wb')) return 'There was an error while opening the destination file for writing';
      curl_setopt($ch, CURLOPT_FILE, $fp);
      curl_setopt($ch, CURLOPT_HEADER, 0);
      curl_exec($ch);
      curl_close($ch);
      fclose($fp);
      return 0;
   }

   public function CreateThumbnailsFromJpegDir($directory, $thumbnailDest, $thumbWidth, $extension = ''){
      $dir = opendir($directory);
      while($dirEntry = readdir($dir)) {
         if($dirEntry == '.' || $dirEntry == '..') continue;
         $pathInfo = pathinfo("$directory/$dirEntry");
         
         if(is_dir("$directory/$dirEntry")){
            $res = $this->CreateThumbnailsFromJpegDir("$directory/{$dirEntry}", $thumbnailDest, $thumbWidth, $extension);
            if(is_string($res)) return $res;
         }
         elseif(strtolower($pathInfo['extension']) == 'jpg' || strtolower($pathInfo['extension']) == 'jpeg'){
            $res = $this->CreateThumbnail("$directory/{$dirEntry}", $thumbnailDest, $thumbWidth, $extension);
            if(is_string($res)) return $res;
         }
      }
      closedir($dir);
      return 0;
   }

   /**
    * Creates a thumbnail from an image
    *
    * @param   string   $path2image
    * @param   string   $destination
    * @param   string   $thumbWidth
    * @return  mixed    Returns a strign with an error message in case of an error, else it returns an array with where the thumbnail has been created
    * @since   v0.3
    */
   public function CreateThumbnail($path2image, $destination, $thumbWidth, $extension = ''){
      $pathInfo = pathinfo($path2image);
      $tempImage = "$destination/{$pathInfo['filename']}_$extension.{$pathInfo['extension']}";
      //copy the original image to a temp location
      $this->CreateDirIfNotExists($destination);
      if(!copy($path2image, $tempImage)) return "There was an error while creating an image in the destination folder.";

     // load image and get image size
      $img = imagecreatefromjpeg($tempImage);
      if(!$img) return "There was an error while creating the thumbnail from the image.";
      $width = imagesx($img);
      $height = imagesy($img);
      if(!$width || !$height) return "There was an error while determining the image dimensions.";

      // calculate thumbnail size
      $new_width = $thumbWidth;
      $new_height = floor($height * ($thumbWidth / $width));

      // create a new temporary image
      $tmp_img = imagecreatetruecolor($new_width, $new_height);
      if(!$tmp_img) return "There was an error while determing the true image color.";

      // copy and resize old image into new image
      if(!imagecopyresized( $tmp_img, $img, 0, 0, 0, 0, $new_width, $new_height, $width, $height )) return "There was an error while resizing the image";

      // save thumbnail into a file
      if(imagejpeg($tmp_img, $tempImage)) return 0;
      else return "There was an error while copying the thumbnail to the destination folder.";
   }

   /**
    * Creates a snapshot of a database and saves it to the backups folder
    * 
    * @param   array    $config        An array with the login credentials to the database that we want to take a snapshot
    * @param   string   $backupFolder  The folder where the snapshot will be saved
    * @param   string   $description   (Optional) A very short string(max 30 chars) describing the snapshot. If >30chars the string is truncated
    * @param   string   $dbase         (Optional) A string with the database name that we are taking the snapshot, if not defined, we are expecting the dbname to be in $config['dbase']
    * @return  mixed    Returns an array with the filename on success, else it returns a string with the error message in case of an error
    * @since   v0.3
    */
   public function CreateDbSnapshot($config, $backupFolder, $description = '', $dbase = '') {
//      echo "backup folder -- $backupFolder <br />";
      if($backupFolder == '') return 'Please specify a valid backup folder.';
      if($config['dbase'] == '' && $dbase == '') return 'Please specify a database name to backup.';
      if(!is_array($config)) return 'Please specify the configurations to use when connecting to the database.';
      $dbase = ($config['dbase'] != '') ? $config['dbase'] : $dbase;
      $res = $this->CreateDirIfNotExists($backupFolder);
      if (!$res) return "There was an error while creating the backup folder '$backupFolder'.";
      if(strlen($description) > 30) $description = substr($description, 0, 29);
      $timestamp = date('Y-m-d_H:i:s');
      if($description != '') $description = "_{$description}";    //add some undersign at the beginning
      $fname = "$backupFolder/{$dbase}{$description}_$timestamp.sql.zip";
      $bare_db_statement = "mysqldump --single-transaction -u {$config['user']} -p{$config['pass']} $dbase | gzip > $fname";
      exec($bare_db_statement, $output, $res);
      /**
       * Though we r saying that a return value of 0 means all went well, this is usually not the case, a non-zero value is only returned:
       * - When the destination is not writable to apache
       * 
       * A zero value can be returned even when:
       * - The dump command if messed up
       * 
       * Use it at your own descretion. Remember you were warned
       */
      if($res != 0) return "There was some kind of error while executing the mysql dump command '$bare_db_statement' !";
      return array($fname);
   }
}
?>
