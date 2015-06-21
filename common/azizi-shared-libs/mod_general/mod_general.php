<?php

class GeneralTasks{
   /**
    * Creates a directory if it does not exists and makes it writable
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
}
?>
