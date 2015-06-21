<?php
/**
 * ChangeLog
 *
 * Version 0.6
 * - 2011-09-21 <soloincc@gmail.com>: Deprecated Populate_Combo. This has now been replaced by the elegant @see GeneralTasks#PopulateCombo
 * - 2011-09-21 <soloincc@gmail.com>: Refactored @see GeneralTasks#PopulateCombo to handle the first option well
 * - 2011-09-29 <soloincc@gmail.com>: Added the option of matching the selected option by name
 *
 * Version 0.5
 * - 2011-08-09 <soloincc@gmail.com>: Changed the GeneralTaks methods to static, meaning that they can be accessed without the class instantiation
 * - 2011-08-09 <soloincc@gmail.com>: Changed the GeneralTasks properties to static, meaning that they can ONLY be accessed without the class instantiation
 *
 * Version 0.4
 * - 2011-08-02 <soloincc@gmail.com>: Re-structred @see GeneralTasks#CreateDbSnapshot so that if a db name is not specified, it uses the db in the config
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
   static public $fileTypes = array(
      'images' => array('image/jpeg', 'image/gif', 'image/bmp', 'image/jpg')
   );

   /**
    * @var  array An array with the excel formats that PHPExcel creator expects
    */
   static public $phpExcelFileTypes = array(
      'xlsx' => 'Excel2007',
      'xls' => 'Excel5',
      'ods' => 'Gnumeric'
   );

   /**
    * Creates a directory if it does not exists and makes it writable. It is capable of creating recursive directories
    *
    * @param string $dir   The path to the folder to be created
    * @return boolean   Returns true if the path is created or exists else returns false in case the directory is not created
    */
   static public function CreateDirIfNotExists($dir){
      if($dir == '') return false;
      if(!file_exists($dir)){ //its not there, lets try and create it
         return mkdir($dir, 0755, true);
      }
      return true;
   }

   /**
    * Get a web file (HTML, XHTML, XML, image, etc.) from a URL.  Returns an array containing the header fields and content.
    */
   static public function GetWebPage($url, $method) {
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
   static public function GenAlphaNumericCode($seed_length=8) {
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
   static public function TruncateFile($filename) {
      $fd=fopen($filename, "wt");
      if(!$fd) {
         return 'There was an error while opening the file for trucating!'; //cant be able to create the file
      }
      ftruncate($fd, 0);
      fclose($fd);
      return 0;
   }

   static public function Compress($filenames){

   }

   /**
    * Creates a drop down based on the passed parameters. An improved version of GeneralTasks#Populate_Combo
    *
    * Example of use
    *
    * <code>
    * $settings = array(
    *    'items' => array('Zero', 'One', 'Two'),                  //The options that will be displayed to the user
    *    'values' => array(0, 1, 2),                              //(Optional) Defaults to integers from 0...n. The hidden variable linking to the displayed values
    *    'firstValue' => 'Select One',                            //(Optional) Defaults to 'Select One'. The value that will be displayed when nothing is selected. In case of a multip drop down, its not required
    *    'name' => 'numbers',                                     //The HTML name of the drop box
    *    'id' => 'numbersId',                                     //(Optional) The HTML id of the drop box
    *    'selected' => '1',                                       //(Optional) The selected value. Defaults to 0.
    *    'enabled' => true,                                       //(Optional) Whether the drop down will be enabled or not. Defaults to true
    *    'onChange' => 'functionToCallWhenChanged("changed")',    //(Optional) The function to call when the drop down is changed. Defaults to nothing
    *    'class' => 'myClass',                                    //(Optional) The class of this drop down. Defaults to nothing
	 *		'matchByName' => true,                                   //(Optional) Whether to check match the selected value by names instead of ids. Defaults to false
    *    'multipleDropDown' => false,                             //(Optional) Whether to create a multiple drop down. Defaults to false
    *    'size' => 10,                                            //(Optional) The size of the drop down. Useful when creating a multiple drop down. Defaults to 0
    *    'width' => 100                                           //(Optional) The width of the drop down. Especially useful when the drop down is empty
    * );
    *
    * </code>
    *
    * @param   array    $settings   An array with the drop down settings that are to be used. Check the example above
    * @return  string   Returns HTML code of the formatted combo
    * @since   v0.3
    */
   static public function PopulateCombo($settings) {
      //autogenerate numericals for hidden values in case we dont have them defined
      if(!is_array($settings['values'])) $settings['values']=range(1, count($settings['items']));
      $onChange = (isset($settings['onChange']) && $settings['onChange'] != '') ? "onChange='{$settings['onChange']};'" : '';
      $selected = (isset($settings['selected'])) ? $settings['selected'] : 0;
      $class = (isset($settings['class'])) ? "class='{$settings['class']}'" : '';
      $multipleDropDown = (!isset($settings['multipleDropDown']) || $settings['multipleDropDown'] == false) ? '' : "multiple='multiple'";
      $enabled = (!isset($settings['enabled']) || $settings['enabled'] == true) ? '' : 'disabled';
      $matchByName = (!isset($settings['matchByName']) || $settings['matchByName'] == false) ? false : true;
      $dropDownId = (isset($settings['id'])) ? "id ='{$settings['id']}'": '';
      $itemCount = count($settings['items'])+1;
      //generate the style
      $style = 'style="';
      if(isset($settings['width'])) $style .= "width: {$settings['width']}px;";
      $style .= '"';

      //down to work
      $combo = "<select name='{$settings['name']}' $dropDownId $class $multipleDropDown $enabled $onChange $style>";
      /**
       * If we have not defined a first value for th ecombo, it is good to have a default one 'Select One'. However when it comes to a multiple dropdown
       * its not really cool to have the 'Select One' appear in the dropdown unless the user explicitly requires it
       */
      if(isset($settings['multipleDropDown']) && $settings['multipleDropDown'] == true) $combo .= '';
      else{
         $combo .= "<option value=0>";
         $combo .= (!is_null($settings['firstValue'])) ? $settings['firstValue'] : 'Select One';
      }
      for($i=0; $i < $itemCount; $i++) {
         if($settings['values'][$i] == '') continue;
         if($i == 0) {
            //display the 1st value
            if(isset($settings['items'][$i]) && $settings['items'][$i] != ''){
               $combo .= "\n<option value='{$settings['values'][$i]}'";
               $combo .= ($selected === $settings['values'][$i]) ? " selected>" : ">";
               $combo .= $settings['items'][$i] . '</option>';
            }
         }
         else {
            $combo .= "\n<option value='{$settings['values'][$i]}'";
            if($matchByName) $combo .= ($selected === $settings['items'][$i]) ? " selected>" : ">";
            else $combo .= ($selected === $settings['values'][$i]) ? " selected>" : ">";
            $combo .= $settings['items'][$i] . '</option>';
         }
      }
      $combo .= "\n</select>";
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
   static public function CustomSaveUploads($uploadDir, $html_name, $allowedTypes, $max_size = 10485760, $filenames=null){
      $err_occ=0;
      //LogError(print_r($_FILES, true));
      if(count($_FILES)==0) return 0;
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
    * @param   integer  $position   The numeric position that we want to convert
    * @param   integer  $rack_size  The size of the tray in question.
    * @return  string   Returns the converted position that LC is comfortable with
    */
   static public function NumericPosition2LCPosition($position, $rack_size){
      $sideLen = sqrt($rack_size);
      if($position % $sideLen == 0) $box_detail = chr(64+floor($position/$sideLen)).$sideLen;
      else $box_detail = chr(65+floor($position/$sideLen)).$position%$sideLen;
      return $box_detail;
   }
   
  /** Converts a numeric size of cryobox to a format used by LabCollector
    *
    * @param   integer  $position   The numeric position that we want to convert
    * @param   integer  $rack_size  The size of the tray in question.
    * @return  string   Returns the converted position that LC is comfortable with
    */
   static public function NumericSize2LCSize($rack_size){
      $sideLen = sqrt($rack_size);
      
      //get value for position 1
      $position = 1;
      if($position % $sideLen == 0) $box_detail = chr(64+floor($position/$sideLen)).":".$sideLen;
      else $box_detail = chr(65+floor($position/$sideLen)).":".$position%$sideLen;
      
      //get value of last position
      $position = $rack_size;
      if($position % $sideLen == 0) $box_detail = $box_detail.".".chr(64+floor($position/$sideLen)).":".$sideLen;
      else $box_detail = $box_details.".".chr(65+floor($position/$sideLen)).":".$position%$sideLen;
      return $box_detail;
   }

   /** Converts box size used by LIMS to numeric size 
    *
    * @param   integer  $rack_size  The size of the tray in question.
    * @return  string   Returns the numeric size
    */
   static public function LCSize2NumericSize($rack_size){
      $parts = explode('.', $rack_size);

      //we only need the last part to determine the size
      if(count($parts) == 2){
          $sizeParts = explode(':',$parts[1]);
          if(count($sizeParts) == 2){
              return $sizeParts[1] * $sizeParts[1];
          }
      }
      return 0;
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
   static public function MoveFiles($files2Move, $destination, $deleteOriginal = TRUE) {
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
   static public function DownloadPicture($url, $destPath) {
      if(!$ch = curl_init($url)) return 'There was an error while initializing a curl session.';
      if(!$fp = fopen($destPath, 'wb')) return 'There was an error while opening the destination file for writing';
      curl_setopt($ch, CURLOPT_FILE, $fp);
      curl_setopt($ch, CURLOPT_HEADER, 0);
      curl_exec($ch);
      curl_close($ch);
      fclose($fp);
      return 0;
   }

   static public function CreateThumbnailsFromJpegDir($directory, $thumbnailDest, $thumbWidth, $extension = ''){
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
   static public function CreateThumbnail($path2image, $destination, $thumbWidth, $extension = ''){
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
   static public function CreateDbSnapshot($config, $backupFolder, $description = '', $dbase = '') {
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

   /**
    * Converts an array with the first row having the column names to a nested array, where all the rows have the column names as the keys
    *
    * @param   array    $inputArray    the array that we are meant to convert
    * @return  mixed    Returns the converted nested array if all is ok, else it returns a string with the error message
    * @since   v0.6
    */
   static public function ConvertArrayToNestedArray($inputArray){
      $output = array();
      $colNames = array();
      //loop through the data and do the conversion
      $i = -1;
      foreach($inputArray as $in){
         $i++;
         if($i == 0){  //we have our array with the column names
            //check that all the columns have a title
            foreach($in as $t){
               if($t == '') return "Error! Please ensure that all columns have headers. The column headers should be the first row of the spreadsheet.";
            }
            $colNames = $in;
         }
         else{
            $curRow = array();
            foreach($in as $key => $value){
               $curRow[$colNames[$key]] = $value;
            }
            $output[] = $curRow;
         }
      }
      return $output;
   }
   
   /**
    * This function gets a url, determines if url is images, if so it downloads the image and returns the name of the image. 
    * Only works in PHP 5.4+
    * 
    * @param    string      $url                The url of the image.
    * @param    string      $$destinationDir    The directory onto which the image will be downloaded
    * 
    * @return   string      Returns the name of the image if $url contained image or $url if $url is not a url or if does not contain an image
    * 
    * @since v0.6
    */
   static public function downloadImage($url, $destinationDir) {
      $contentType = get_headers($url, 1);
      $contentType = $contentType["Content-Type"];
      
      if(strpos($contentType, 'image')!==NULL) {
         if(!file_exists($destinationDir)) {
            mkdir($destinationDir,0777,true);
         }
         
         $timestamp = round(microtime(true) * 1000);
         $name = $timestamp.".".str_replace("image/", "", $contentType);
         $img = $destinationDir.'/'.$name;
         file_put_contents($img, file_get_contents($url));
         return $name;
      }
      else {
         return $url;
      }
   }
   
    /**
    * This function gets a source director, zips everything in that directory and saves the zip as destination
    * 
    * @param    string      $source         Url of the directory to be zipped
    * @param    string      $destination    Url of the destination zip file
    * @param    boolean     $include_dir    Defaults to false. If set to true, the zip file will contain a folder corresponding to the source
    * 
    * @return   boolean     Returns true if zip was successfull
    * 
    * @since v0.6
    */
   static public function zipDir($source, $destination, $include_dir = false) {
      if (!extension_loaded('zip') || !file_exists($source)) {
         return false;
      }

      if (file_exists($destination)) {
         unlink($destination);
      }

      $zip = new ZipArchive();
      if (!$zip->open($destination, ZIPARCHIVE::CREATE)) {
         return false;
      }
      $source = str_replace('\\', '/', realpath($source));

      if (is_dir($source) === true) {

         $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($source), RecursiveIteratorIterator::SELF_FIRST);

         if ($include_dir) {

            $arr = explode("/", $source);
            $maindir = $arr[count($arr) - 1];

            $source = "";
            for ($i = 0; $i < count($arr) - 1; $i++) {
               $source .= '/' . $arr[$i];
            }

            $source = substr($source, 1);

            $zip->addEmptyDir($maindir);
         }

         foreach ($files as $file) {
            $file = str_replace('\\', '/', $file);

            // Ignore "." and ".." folders
            if (in_array(substr($file, strrpos($file, '/') + 1), array('.', '..')))
               continue;

            $file = realpath($file);

            if (is_dir($file) === true) {
               $zip->addEmptyDir(str_replace($source . '/', '', $file . '/'));
            } else if (is_file($file) === true) {
               $zip->addFromString(str_replace($source . '/', '', $file), file_get_contents($file));
            }
         }
      } else if (is_file($source) === true) {
         $zip->addFromString(basename($source), file_get_contents($source));
      }

      return $zip->close();
   }
   
   /**
    * This function delets a directory recurssively
    * 
    * @param    string      $dirPath    Path to the directory
    * 
    * @throws InvalidArgumentException
    * 
    * @since v0.6
    */
   static public function deleteDir($dirPath) {
      if (!is_dir($dirPath)) {
         throw new InvalidArgumentException("$dirPath must be a directory");
      }
      if (substr($dirPath, strlen($dirPath) - 1, 1) != '/') {
         $dirPath .= '/';
      }
      $files = glob($dirPath . '*', GLOB_MARK);
      foreach ($files as $file) {
         if (is_dir($file)) {
            self::deleteDir($file);
         } else {
            unlink($file);
         }
      }
      rmdir($dirPath);
   }
   
   /**
    * This function returns the depth of an array
    * 
    * @param    array   $array     The array whose depth is to be determined
    * 
    * @return   int     The depth of the array 
    * 
    * @since v0.6    
    */
   static public function getArrayDepth($array) {
       $max_depth = 1;
       
       foreach ($array as $value) {
           if (is_array($value)) {
               $depth = GeneralTasks::getArrayDepth($value) + 1;

               if ($depth > $max_depth) {
                   $max_depth = $depth;
               }
           }
       }
       return $max_depth;
   }
    /**
    * This file determines if provided object is a jsonObject
    * 
    * @param    mixed       $json       The object to be determined if is json object
    * @return   boolean     returns true if provided object is json object
    */
    static public function isJson($json) {
      if(is_array($json)){
          $keys = array_keys($json);
          if(sizeof($keys)>0){
            return TRUE;
          }
          else{
            return FALSE;
          }
      }
      else{
          return FALSE;
      }
      
   }
}
?>
