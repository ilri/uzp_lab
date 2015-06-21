<?php

class GeneralTasks{
   /**
    * Creates a directory if it does not exists and makes it writable. It is capable of creating recursive directories
    *
    * @param string $dir   The path to the folder to be created
    * @return boolean   Returns true if the path is created or exists else returns false in case the directory is not created
    */
   public function CreateDirIfNotExists($dir){
      if(!file_exists($dir)){ //its not there, lets try and create it
         return mkdir($dir, 0755, true);
      }
      return true;
   }

   public function CheckIfDirExists($dir){

   }

   /**
    * Get a web file (HTML, XHTML, XML, image, etc.) from a URL.  Return an
    * array containing the header fields and content.
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
    * @param int $seed_length The length of the code to be generated
    * @return string Returns the generated code 
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
    * @param string $filename The file to truncate. The file should be writable
    * @return mixed  Returns 0 on successfull truncation, else it returns a string with the error message
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
    * @param array $items      The options that will be displayed to the user
    * @param array $values     The hidden variable linking to the displayed values
    * @param string $defval    Default value to be displayed when nothing is selected
    * @param string $name      The combobox name. The string 'id' will be appedned to create its Id
    * @param integer $selected The value to select
    * @param bool $enabled     To enable it or not
    * @param string $changewat A function to call when a value is selected
    * @return string  Returns HTML code of the formatted combo
    */
   public function Populate_Combo($items, $values, $defval, $name, $selected, $enabled, $changewat) {
      //reset($items);reset($values);
      if(!is_array($values)) $values=range(1,count($items));
      ($enabled)?$dis="":$dis="disabled";
      $change=($changewat!='' && isset($changewat))?"onChange='$changewat;'":'';
      $selected=(!isset($selected))?0:$selected;
      for($i=0;$i<count($items)+1;$i++) {
         if($i==0) {
            $combo="<select name='$name' id=\"{$name}id\" $dis $change>\n\t\t\t<option value=\"0\"";
            $combo.=($selected==$i)?" selected>":">";
            $combo.=$defval;
         }else {
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
    * A custom upload function that allows multiple uploads of files and saves them in the specified location with their original names. This function assumes that
    * we are uploading multiple files.
    *
    * @param array $uploadedFiles    It contains the location setting where the files are to be saved and the max size allowed to upload a file. The location should
    *                                  be set relative to this file, general.php, since it will attempt to create a file using this path, else use an absolute path
    * @param string $html_name       The file name used in the HTML form
    * @param array $allowedTypes     An array of allowed file types
    * @param array $filename         An array with the destination file names, can be ommitted
    * @return type    Returns an array with the path to the uploaded files on success and a string with an error message on error
    */
   public function CustomSaveUploads($uploadedFiles, $html_name, $allowedTypes, $filenames=null){
      $err_occ=0;
      //LogError(print_r($_FILES, true));
      if(count($_FILES)==0) return 'There are no files selected for upload.';
      //save the samples file
      $err_msg='';
      $realUploadedFiles=array();
      for($i=0;$i<count($_FILES[$html_name]['name']);$i++){
         $err_code=$_FILES[$html_name]['error'][$i];
         //LogError("Error Code: $err_code".count($_FILES[$html_name]));
         if($err_code==4) continue;        //no file selected
         //check for the errors that might have occurred
         if($err_code!=0 && $err_code!=4){
            if($err_code<3){
               $err_msg.=addslashes($_FILES[$html_name]['name'][$i])." exceeded the maximum allowed upload size.<br>";
            }
            if($err_code==3){
               $err_msg.=addslashes($_FILES[$html_name]['name'][$i])." was partially uploaded.<br>";
            }
            $err_occ=1; continue;
         }

         //only allow xml files to be uploaded
         //LogError($_FILES[$html_name]['type'][$i]);
         if(!in_array($_FILES[$html_name]['type'][$i], $allowedTypes)){
            $err_msg.=addslashes($_FILES[$html_name]['name'][$i])." is not an allowed file type.<br>";
//echo '<pre>'.print_r($_FILES, true).'</pre>';
            $err_occ=1; continue;
         }

         //Dont allow importation of files larger than 10Mb
         if($_FILES[$html_name]['size'][$i] > $uploadedFiles['max_size']){
            $err_msg.=addslashes($_FILES[$html_name]['name'][$i])." is bigger than 10Mb. You are only allowed to import files less than 10Mb.<br>";
            $err_occ=1;  continue;
         }
         //check if the destination folder exists, and if it doesnt try and create it
         $uploadDir=$uploadedFiles['location'];
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
            $err_occ=1;  continue;
         }
         else $realUploadedFiles[]=$destfile;
      }
      if($err_occ) return $err_msg;
      elseif(count($realUploadedFiles)==0) return 'There are no saved files. Possibly there were no files selected for upload.';
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
}
?>
