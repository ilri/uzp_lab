<?php
/**
 * The base file when it comes to spreadsheet processing. Process the excel file and creates an object that can be used later
 *
 * @package    ExcelProcessing
 * @category   BaseClass
 * @author     Kihara Absolomon <a.kihara@cgiar.org>
 * @since      v0.1
 */
class SpreadSheet {
   /**
    * @var array  Holds possible metadata fields and columns and their validators and whether they are required or not
    */
   protected $metadata = array();

   /**
    *
    * @var  array    An array with this sheet metadata
    */
   public $spreadSheetDetails = array(
       'fileName' => null,           //The name of the spreadsheet
       'filePath' => null,      //The path to this spreadsheet
       'mtime' => null,          //The modification time of this file
       'size' => null,           //The size of the file in bytes
       'type' => null,           //The type of the file we are currently processing
       'sheetIndex' => null,     //The index of this sheet in the main file
       'sheetName' => null       //The name of the sheet, ie the name that is given down there!
   );

   /**
    * @var array     An array that will store the errors and messages that will occur during processing
    */
   public $errorsMessages = array(
       'errors' => null,         //An array that will store any error messages that will occur during processing
       'messages' => null        //An array for storing any other messages that shall occur during processing
   );

   /**
    * @var array     An array that will store the data from this spreadsheet.
    */
   public $spreadSheetData = array(
       'data' => null,            //An array that will the data that will be processed
       'htmlData' => null,       //An ExcelParser object that has the html representation of the spreadsheet
       'actualData' => null      //The actual data that was picked from the processing. Contains data that we actually need
   );

   /**
    * @var  array    An array with the upload status of the spreadsheet
    */
   protected $uploadedData = array(
       'toBeUploaded' => null,   //Boolean, whether the spreadsheet has passed all the necessary validation and can be uploaded to the database
       'isUploaded' => null       //Boolean!  Whether the spreadsheet has been uploaded to the database
   );

   /**
    * @var string    The regex to be applied for each instance of data found under the label column
    */
   protected $labelRegEx;

   /**
    * The constructor to this wonderful piece of code. Creates a new instance of a spreadsheet
    *
    * @param   string   $path                The path to the spreadsheet file
    * @param   string   $name                The name of this spreadsheet
    * @param   object   $data                The data object of this spreadsheet
    * @param   integer  $spreadSheetIndex    The index of this spreadsheet in the file
    * @param   string   $sheetName           The name of this spreadsheet
    */
   public function   __construct($path, $name, $data, $spreadSheetIndex, $sheetName) {
      $this->spreadSheetDetails['filePath'] = $path;
      $this->spreadSheetDetails['fileName'] = $name;
      $this->spreadSheetDetails['sheetIndex'] = $spreadSheetIndex;
      $this->errorsMessages['errors'] = array();
      $this->errorsMessages['messages'] = array();
//         echo '<pre>'.print_r($sheetName, true).'</pre>'; die();

      //get me the stats of this file
      $res = stat("$path/$name");
      $this->spreadSheetDetails['mtime'] = date('Y-m-d H:i:s', $res['mtime']);    //the modification time of this file
      $this->spreadSheetDetails['size'] = $res['size'];    //the size of the file in Kb
      $this->spreadSheetDetails['sheetName'] = $sheetName;
      $this->spreadSheetData['data'] = $data;
      $this->spreadSheetData['actualData'] = array();
   }

   /**
    * The main function of all the spreadsheets. It validates that all the fields are set as defined in the metadata field. It evaluates the
    * fields that need to be evaluated. After all this it formats the data and saves it in the data array of this object. This is used for later processing
    */
   public function ValidateAndProcessSpreadSheet(){
      $isMetaData = 1;
//      echo '<pre>'.print_r($this->spreadSheetData['data'], true).'</pre>';

         foreach($this->spreadSheetData['data']['cells'] as $curRowIndex => $currentRow){
            $curRow = trim(implode('', $currentRow));     //transform the array with the cells to a single string value
//            $this->errors[] = "Current Row\{$curRow\}: At row $curRowIndex we dont have data for {$temp['name']} which is required.";
            //check whether this row has anything interesting for us
            if($isMetaData == 1){
               foreach($this->metadata as $key => $meta_values){
                  if($key != 'columns'){  //we have some metadata
                     if(preg_match_all($meta_values['regex'], $curRow, $matches)){   //we have a match so add the other cells as values to this field
                        $this->metadata[$key]['data'] = trim($matches[2][0]);
                        $isMetaData = 1;
                     }
                  }
                  else{
                     //we have to iterate thru all the defined columns
                     foreach($meta_values as $col_key => $col_data){
                        $res = preg_grep($col_data['regex'], $currentRow);
                        if(!count($res)) continue;
                        //if we are here it means that we have some matches, hence its the row with headers to the data
                        $this->metadata[$key][$col_key]['present'] = true;
                        $isMetaData = 0;
                        $data_in = array_keys($res);
                        if(count($data_in)!=1){
                           $colcount = count($data_in);
                           $this->metadata[$key][$col_key]['data_col'] = $data_in;      //the data we want is in multiple columns, this is weired
                           $this->errorsMessages['messages'][] = array('mssg' => "It seems like data for {$col_data['name']} is in $colcount columns. ie in cols: ".implode(', ', $data_in), 'type'=>'warning');
                        }
                        else $this->metadata[$key][$col_key]['data_col'] = $data_in[0];
                     }
                  }
               }
            }
            elseif($isMetaData == 2 || $isMetaData == 0) {   //this is the green light for us to start processing the main data
               if($isMetaData == 0) $isMetaData = 2;  //it means we have finished getting the metadata, proceed to the real data. The metadata shall be confirmed later
               //check that each of the required fields in the columns list is there else wika
//               if($this->name == 'AVID Mosquito Identification.xls'){
//                  echo '<pre> Found it:'.print_r($this, true).'</pre>'; die();
//               }
               $curSetOfData = array();
               foreach($this->metadata['columns'] as $temp) {
                  if(isset($temp['data_col']) || $curRow != '') {
                     $val = trim($currentRow[$temp['data_col']]);
                     if($val == '') {
                        if($curRow == '') {
                           continue;    //this whole row is empty
                        }
                     }
                     if($temp['required'] && $val=='') {
                        $this->errorsMessages['errors'][] = "Serious Error: At row $curRowIndex we dont have data for {$temp['name']} which is required.";
                        $this->uploadedData['toBeUploaded'] = false;
                     }
                     else {
                        if(isset($temp['data_regex'])) {     //we have a validator for this column
                           if(!preg_match($temp['data_regex'], $val) && $temp['required'] === true) {
                              $this->errorsMessages['errors'][] = "Serious Error at row $curRowIndex: Wrong data '$val' for {$temp['name']}. It does not pass the set validation.";
                              $this->uploadedData['toBeUploaded'] = false;
                              continue;
                           }
                        }
                        $curSetOfData[$temp['name']] = $val;
                     }
                  }
               }
               if(count($curSetOfData)) $this->spreadSheetData['actualData'][] = $curSetOfData;
            }
            else {
               $this->errorsMessages['errors'] = "FATAL ERROR: There was a fatal error during execution! I cannot go on processing. Please contact the system administrator.";
            }
         }
         $this->ConfirmMetada();    //confirm that we have the metadata
//      }
   }

   /**
    * Confirms that all the required metadata is there
    */
   private function ConfirmMetada(){
      foreach($this->metadata as $key => $data){
         if($key != 'columns'){
            //we are expecting that all required fields have the data field set
            if($data['required'] && !isset($data['data'])){
               $this->errorsMessages['errors'][] = "Serious Error: The $key is not set for this spreadsheet.";    //this will cause the data not to be uploaded
               $this->uploadedData['toBeUploaded'] = false;
            }
         }
         else{
            //we are expecting that all required columns have the data_col set
            foreach($data as $t){
               if($t['required'] && !isset($t['data_col'])){
                  $this->errorsMessages['errors'][] = "Serious Error: I cant figure out which column the data for {$t['name']} is saved at!.";    //this will cause the data not to be uploaded
                  $this->uploadedData['toBeUploaded'] = false;
               }
            }
         }
      }
   }

   /**
    * Checks whether this file has been uploaded before
    *
    * @return mixed Returns a string in case an error occurs, else returns 0 if the file hasnt been uploaded, 1 if it has been uploaded and 2 if there is something wrong
    */
   protected function HaveIBeenUploadedB4(){
      global $EntomologyData;
      //check if there is a file like this in the database. We shall use mtime and type as the first criteria and then size and name
      $filename = $EntomologyData->Dbase->dbcon->real_escape_string($this->name);
      $EntomologyData->Dbase->query = "select name, path, mtime, size from files where type='{$this->type}' and size={$this->size} and name='$filename'";
//      $EntomologyData->Dbase->CreateLogEntry($EntomologyData->Dbase->query);
      $res = $EntomologyData->Dbase->ExecuteQuery(MYSQLI_ASSOC);
//      echo "<pre>".print_r($res, true)."</pre>"; die();
      $count_res = count($res);
      if($res == 1) return $EntomologyData->Dbase->lastError;
      elseif($count_res == 0) return 0;      //I havent been uploaded b4
      elseif($count_res == 1){
         $this->isUploaded = true;
         return 1;      //Niko poa
      }
      else{
         //wow. we have more than one uploaded files.
         $mssg = "Something is amiss. The file '{$this->name}' has been uploaded $count_res times before. Whats happening!!<pre>".print_r($res, true)."</pre>";
         $this->errors[] = $mssg;
         $EntomologyData->Dbase->CreateLogEntry(strip_tags($mssg));
         $EntomologyData->Dbase->CreateLogEntry(strip_tags($mssg), 'fatal');
         return 2;
      }
   }


   protected function SaveFileInfo(){
      global  $EntomologyData;
      //saves the info about this file in the files dbase
      $cols = array('type','name','path','mtime','size','processed');
      $filename = $EntomologyData->Dbase->dbcon->real_escape_string($this->name);
      $path = $EntomologyData->Dbase->dbcon->real_escape_string($this->filePath);
      $colvals = array($this->type, $filename, $path, $this->mtime, $this->size, 1);
      if($this->type == 'cbg' && isset($this->metadata['no_trap']['data'])){
         $cols[] = 'no_trap';
         $colvals[] = $this->metadata['no_trap']['data'];
      }
      $res = $EntomologyData->Dbase->InsertData('files', $cols, $colvals);
//      echo "<pre>$curFile<br />".print_r($this, true)."</pre>"; die();
//      echo "{$EntomologyData->Dbase->query}<br />";
      if($res == 1) {
         if($EntomologyData->Dbase->dbcon->errno == 1062) {    //we have a duplicate entry, so just skip it, but create a log of it
            $EntomologyData->Dbase->query = "select id from files where name ='$filename'";
            $res = $EntomologyData->Dbase->ExecuteQuery(MYSQLI_ASSOC);
            if($res == 1) return -1;
            else return $res[0]['id'];
         }
         return -1;
      }
      else return $EntomologyData->Dbase->dbcon->insert_id;
   }
}
?>
