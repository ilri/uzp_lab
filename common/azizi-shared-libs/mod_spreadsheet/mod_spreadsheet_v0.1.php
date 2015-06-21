<?php

/**
 * The base file when it comes to spreadsheet processing. Holds data and metadata fro one spreadsheet
 *
 * @author  Kihara Absolomon <a.kihara@cgiar.org>
 */
class SpreadSheet{
   /**
    *
    * @var array  Holds possible metadata fields and columns and their validators and whether they are required or not
    */
   protected $metadata = array();

   /**
    * @var string    The name of the spreadsheet
    */
   public $name;

   /**
    * @var  string   The sheet name
    */
   public $sheet_name;

   /**
    * @var  integer  The index of the sheet in spreadsheet
    */
   public $sheet_index;

   /**
    * @var string    The path to this spreadsheet
    */
   public $filePath;

   /**
    * @var timestamp The modification time of this file
    */
   protected $mtime;

   /**
    * @var integer   The size of the file in bytes
    */
   protected $size;

   /**
    * @var string    The type of the file we are currently processing
    */
   public $type;

   /**
    * @var array     An array that will hold all the errors
    */
   public $errors;

   /**
    * @var array     An array that will hold all other weired occurences
    */
   public $rest_occurences;

   /**
    * @var array     An array with all the data from this spreadsheet.
    */
   protected $data;

   /**
    * @var bool      The flag that will be checked if this spreadsheet is to be uploaded. Incase of any grave errors this field will be set to false
    */
   public $toBeUploaded = true;

   public $isUploaded = false;

   /**
    * @var  bool     A flag on whether we should fill data from LC
    */
   public $fillLCRef = false;

   /**
    * @var  array    An array with a list of columns that we need to fetch from the db
    */
   public $cols2fill = array();

   /**
    * @var  array    An array that maps the data from the db with the metadata columns
    */
   public $cols2fillMap = array();

   /**
    *
    * @var string    The regex to be applied for each instance of data found under the label column
    */
   protected $labelRegEx;

   /**
    *
    * @var object    The sheet data
    */
   public $readData;

   /**
    * @var string    A HTML representation of the spreadsheet
    */
   public $htmlData;

   public function   __construct($path, $name, $data) {
      global $EntomologyData;
      $this->filePath = $path;
      $this->name = $name;
      $this->errors = array();
      $this->rest_occurences = array();

      //get me the stats of this file
      $res = stat($name);
//      echo "<pre>$curFile<br />".print_r($res, true)."</pre>"; die();
      $this->mtime = date('Y-m-d H:i:s', $res['mtime']);    //the modification time of this file
      $this->size = $res['size'];    //the size of the file in Kb
      $this->readData = $data;
      //intialize the dbase object
//      echo '<pre>'.print_r($EntomologyData->Dbase, true).'wtf1</pre>';
//      echo "<br />am being initialized<br />";
   }

   /**
    * The main function of all the files. It validates that all the fields are set as defined in the metadata field. It evaluates the
    * fields that need to be evaluated. After all this it formats the data and saves it in the data array of this object. This is used for later processing
    */
   public function ValidateAndProcessFile(){
      $isMetaData = 1;

//      if(isset($this->readData->sheets[$sheet_index]) && isset($this->readData->sheets[$sheet_index]['cells'])){
         foreach($this->readData['cells'] as $curRowIndex => $currentRow){
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
                     $foundColumns = array();
                     //we have to iterate thru the defined columns
                     foreach($meta_values as $col_key => $col_data){
                        $res = preg_grep($col_data['regex'], $currentRow);
//                        echo '<pre>'. print_r($currentRow, true) .'</pre>';
   //                $this->errors[] = "Current Row\{$curRow\}: At row $curRowIndex we dont have data for {$temp['name']} which is required.";
   //                $this->errors[] = $currentRow;
                        if(!count($res)) continue;
                        //if we are here it means that we have some matches, hence its the row with headers to the data
                        $this->metadata[$key][$col_key]['present'] = true;
                        $this->metadata[$key][$col_key]['predefined'] = true;
                        $isMetaData = 0;
                        $data_in = array_keys($res);
                        if(count($data_in) != 1){
                           $colcount = count($data_in);
                           $this->metadata[$key][$col_key]['data_col'] = $data_in;      //the data we want is in multiple columns, this is weired
                           $this->errors[] = array('mssg' => "It seems like data for {$col_data['name']} is in $colcount columns. ie in cols: ". implode(', ', $data_in));
                        }
                        else{
                           $this->metadata[$key][$col_key]['data_col'] = $data_in[0];
                           $foundColumns[] = $data_in[0];
                        }
                        $this->metadata[$key][$col_key]['column_name'] = $res[$data_in[0]];
                     }
                     //if we need to get all the columns, we have to loop thru all the columns and get the columns which weren't defined
                     if($this->includeUndefinedColumns){
                        $i = 0;
                        foreach($currentRow as $col_index => $col_name){
                           if(!in_array($col_index, $foundColumns)){
                              $i++;
                              $this->metadata[$key][] = array(
                                 'required' => false, 'predefined' => false, 'present' => true, 'data_col' => $col_index, 'name' => $col_name
                              );
                           }
                        }
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
                     if($temp['required'] && $val == '') {
                        //check if we need to fetch this data from the db using the lc_ref
                        if(isset($temp['lc_ref']) && $temp['lc_ref'] != ''){
                           $this->fillLCRef = true;      /* We shall fill this blank later on */
                           $this->toBeUploaded = false;  //stop uploading untill the refrefence are filled
                        }
                        else{
                           $this->errors[] = "Serious Error: At row $curRowIndex we dont have data for {$temp['name']} which is required.";
                           $this->toBeUploaded = false;
                        }
                     }
                     else {
                        if(isset($temp['data_regex'])) {     //we have a validator for this column
                           if(!preg_match($temp['data_regex'], $val) && $temp['required'] === true) {
                              $this->errors[] = "Serious Error at row $curRowIndex: Wrong data '$val' for {$temp['name']}. It does not pass the set validation.";
                              $this->toBeUploaded = false;
                              continue;
                           }
                        }
                        $curSetOfData[$temp['name']] = $val;
                     }
                  }
               }
               if(count($curSetOfData)) $this->data[] = $curSetOfData;
            }
            else {
               echo "<br />Fatal Error: Ulikujaje hapa.<br />";
            }
         }
         $this->ConfirmMetadata();    //confirm that we have the metadata
         $this->CheckUniqueData();
   }

   /**
    * Confirm the other aspects of the metadata are well defined. This includes all the needed data is in the db
    */
   private function ConfirmMetadata(){
      foreach($this->metadata as $key => $data){
         if($key != 'columns'){
            //we are expecting that all required fields have the data field set
            if($data['required'] && !isset($data['data'])){
               $this->errors[] = "Serious Error: The $key is not set for this spreadsheet.";    //this will cause the data not to be uploaded
               $this->toBeUploaded = false;
            }
         }
         else{
            //we are expecting that all required columns have the data_col set
            foreach($data as $t){
               if(isset($t['lc_ref'])){
                  $this->cols2fill[] = $t['lc_ref'];
                  $this->cols2fillMap[$t['lc_ref']] = $t['name'];
               }
               elseif($t['required'] && !isset($t['data_col'])){
                  $this->errors[] = "Serious Error: I cant figure out which column the data for {$t['name']} is saved at!.";    //this will cause the data not to be uploaded
                  $this->toBeUploaded = false;
               }
            }
         }
      }
   }

   /**
    * Confirms that all the data that needs to be unique is unique
    */
   private function CheckUniqueData(){
      //check that if we have a column expecting unique data, all the data is unique
      //first get a list of all columns where we are expecting unique values
      $uniqueCols = array();
      foreach($this->metadata['columns'] as $key => $data){
         if($data['unique']){
            $uniqueCols[$data['name']] = array('data_col' => $data['data_col'], 'column_name' => $data['column_name'], 'data' => array() );
         }
      }
      //loop through all the unique columns, and get the data in that column
      foreach($uniqueCols as $colName => $uniq){
         foreach($this->data as $rowIndex => $data){
            if(in_array($data[$colName], $uniqueCols[$colName]['data'])){
               $this->errors[] = "Error! Duplicate entry <b>'{$data[$colName]}'</b>. The data in the column '{$uniq['column_name']}' is expected to be unique." ;    //this will cause the data not to be uploaded
               $this->toBeUploaded = false;
            }
            $uniqueCols[$colName]['data'][$rowIndex] = $data[$colName];
         }
      }
   }
}
?>
