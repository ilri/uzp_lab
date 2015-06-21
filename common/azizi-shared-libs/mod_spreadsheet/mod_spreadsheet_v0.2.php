<?php
/**
 * Change Log
 *
 * v0.2
 * - Added support for uploading data in multiple sheets
 */

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
    * @var  array    An array that will hold all the warnings
    */
   public $warnings;

   /**
    * @var array     An array that will hold all other weired occurences
    */
   public $rest_occurences;

   /**
    * @var array     An array with all the data from this spreadsheet.
    */
   protected $data = array();

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
    * @var  boolean     Whether to attempt to link sheets within the worksheet or not
    */
   protected $linkedSheets = true;

   /**
    * @var string    A HTML representation of the spreadsheet
    */
   public $htmlData;

   public function   __construct($path, $name, $data) {
      global $EntomologyData;
      $this->filePath = $path;
      $this->name = $name;
      $this->errors = array();
      $this->warnings = array();
      $this->rest_occurences = array();

      //get me the stats of this file
      $res = stat("{$path}{$name}");
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
      //Determine whether or not to enforce the metadata. Metadata should be enforced in the main sheet but not in the secondary sheets
      //loop through all the data in the cells looking for errors/ommissions and any other thing out of order
      foreach ($this->readData as $curRowIndex => $currentRow) {
         $curRow = trim(implode('', $currentRow));     //transform the array with the cells to a single string value
//         echo "$curRow<br />";
         //check whether this row has anything interesting for us
         if ($isMetaData == 1) {
            foreach ($this->metadata as $key => $meta_values) {
               if ($key != 'columns') {  //we have some metadata
                  if(preg_match_all($meta_values['regex'], $curRow, $matches)) {   //we have a match so add the other cells as values to this field
//                     echo "<pre>". print_r($matches, true). "????</pre>";
                     $this->metadata[$key]['data'] = trim($matches[2][0]);
                     $isMetaData = 1;
                  }
               }
               else{
                  $foundColumns = array();
                  //we have to iterate thru the defined columns
                  foreach ($meta_values as $col_key => $col_data) {
                     if($col_data['regex'] == '') continue;
                     $res = preg_grep($col_data['regex'], $currentRow);
//                     if (!count($res) || (count($res) == 1 && $res[0] == '')) continue;
                     if (!count($res)) continue;
//                     echo count($res);
//                     echo "--{$col_data['regex']}--";
//                     echo "<pre>". print_r($currentRow, true). "</pre>";
//                     echo "<pre>". print_r($res, true). "</pre>";
                     //if we are here it means that we have some matches, hence its the row with headers to the data
                     $this->metadata[$key][$col_key]['present'] = true;
                     $this->metadata[$key][$col_key]['predefined'] = true;
                     $isMetaData = 0;
                     $data_in = array_keys($res);
                     if (count($data_in) != 1) {
                        $colcount = count($data_in);
                        $this->metadata[$key][$col_key]['data_col'] = $data_in;      //the data we want is in multiple columns, this is weired
                        $this->errors[] = array('mssg' => "It seems like data for {$col_data['name']} is in $colcount columns. ie in cols: " . implode(', ', $data_in));
                     } else {
                        $this->metadata[$key][$col_key]['data_col'] = $data_in[0];
                        $foundColumns[] = $data_in[0];
                     }
                     $this->metadata[$key][$col_key]['column_name'] = $res[$data_in[0]];
                  }
                  //if we need to get all the columns, we have to loop thru all the columns and get the columns which weren't defined
                  if ($this->includeUndefinedColumns) {
                     $i = 0;
                     foreach ($currentRow as $col_index => $col_name) {
                        if (!in_array($col_index, $foundColumns)) {
                           $i++;
                           $this->metadata[$key][] = array(
                               'required' => false, 'predefined' => false, 'present' => true, 'data_col' => $col_index, 'name' => $col_name
                           );
                        }
                     }
                  }     // if($this->includeUndefinedColumns)
               }     // else
            }     // foreach($this->metadata as $key => $meta_values)
         }     // if($isMetaData == 1)
         elseif ($isMetaData == 2 || $isMetaData == 0) {   //this is the green light for us to start processing the main data
            if ($isMetaData == 0) $isMetaData = 2;  //it means we have finished getting the metadata, proceed to the real data. The metadata shall be confirmed later

            //check that each of the required fields in the columns list is there else wika
            $curSetOfData = array();
            foreach ($this->metadata['columns'] as $temp) {
               if (isset($temp['data_col']) || $curRow != '') {
                  $val = trim($currentRow[$temp['data_col']]);
                  if ($val == '') {
                     if ($curRow == '') continue;    //this whole row is empty
                  }
                  if ($temp['required'] && $val == '') {
                     //check if we need to fetch this data from the db using the lc_ref
                     if (isset($temp['lc_ref']) && $temp['lc_ref'] != '') {
                        $this->fillLCRef = true;      /* We shall fill this blank later on */
                        $this->toBeUploaded = false;  //stop uploading untill the refrefence are filled
                     }
                     elseif(!$this->isMain && $this->linkedSheets){
                        //we have a secondary sheet and the sheets are not linked hence this data doesn't have to pass all the requirements
                        //so do nothing and allow the process to continue
                     }
                     else {
                        $this->errors[] = "Serious Error: At row $curRowIndex we dont have data for {$temp['name']} which is required.";
                        $this->toBeUploaded = false;
                     }
                  }
                  else {
                     if (isset($temp['data_regex'])) {     //we have a validator for this column. if it is in a secondary sheet and the sheets are linked, ignore it
                        if (!preg_match($temp['data_regex'], $val) && $temp['required'] === true  && ($this->isMain || $this->linkedSheets)) {
                           $this->errors[] = "Serious Error at row $curRowIndex: Wrong data '$val' for {$temp['name']}. It does not pass the set validation.";
                           $this->toBeUploaded = false;
                           continue;
                        }
                     }
                     if(preg_match('/'. preg_quote($val, '/') .'/i', Config::$emptyValues) === 0) $curSetOfData[$temp['name']] = $val;
                  }     // else
               }     // if(isset($temp['data_col']) || $curRow != '')
            }     // foreach($this->metadata['columns'] as $temp)

            if (count($curSetOfData)){
               if(!$this->isMain && $this->linkedSheets){
                  //since its a secondary sheet, there is need to link to the main sheet. A secondary sheet can either have a single row linked to a single row in the main sheet, or a single row linked to multiple rows in the main sheet
                  //or vice versa. To ensure this linkage is established, the secondary sheet data array will have indexes that link to the main key column
                  $linkColumn = (isset($curSetOfData['pri_key'])) ? $curSetOfData['pri_key'] : $curSetOfData['foreign_key'];
                  if(!array_key_exists($linkColumn, $this->data)) $this->data[$linkColumn] = array();
                  $this->data[$linkColumn][] = $curSetOfData;
               }
               else $this->data[] = $curSetOfData;
            }
         }     // elseif ($isMetaData == 2 || $isMetaData == 0)
         else {
            echo "<br />Fatal Error: Ulikujaje hapa.<br />";
         }
      }
      $this->ConfirmMetadata();    //confirm that we have the metadata
      $this->CheckUniqueData();
      if($this->linkedSheets) $this->confirmRelationalIntegrity();      //if we are to link sheets, check that we have the linking column defined
   }

   /**
    * Confirm the other aspects of the metadata are well defined. This includes all the needed data is in the db.
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
               elseif($t['required'] && !isset($t['data_col']) && ($this->isMain || !$this->linkedSheets)){
                  $this->errors[] = "Serious Error: I cant figure out which column {$t['name']} is saved at!.";    //this will cause the data not to be uploaded
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
      //first get a list of all columns where we are expecting unique values and make sure that the column is present. Might be expecting a unique column, but the column isn't included in the first place
      $uniqueCols = array();
      foreach($this->metadata['columns'] as $key => $data){
         if($data['unique'] && $data['present']){
            $uniqueCols[$data['name']] = array('data_col' => $data['data_col'], 'column_name' => $data['column_name'], 'data' => array() );
         }
      }
      //loop through all the unique columns, and get the data in that column
      foreach($uniqueCols as $colName => $uniq){
         foreach($this->data as $rowIndex => $data){
            //the main sheet and the secondary sheet stores their data in different formats.... the seondary sheet anticipates that there can be a 1:many relationship
            if($this->isMain){
               if(in_array($data[$colName], $uniqueCols[$colName]['data'])){
                  $this->errors[] = "Error! Duplicate entry <b>'{$data[$colName]}'</b>. The data in the column '{$uniq['column_name']}' is expected to be unique." ;    //this will cause the data not to be uploaded
                  $this->toBeUploaded = false;
               }
               $uniqueCols[$colName]['data'][$rowIndex] = $data[$colName];
            }
            else{
               foreach($data as $dt){
                  if(in_array($dt[$colName], $uniqueCols[$colName]['data'])){
                     $this->errors[] = "Error! Duplicate entry <b>'{$dt[$colName]}'</b>. The data in the column '{$uniq['column_name']}' is expected to be unique." ;    //this will cause the data not to be uploaded
                     $this->toBeUploaded = false;
                  }
                  $uniqueCols[$colName]['data'][$rowIndex] = $dt[$colName];
               }
            }
         }
      }
//      if($this->sheet_name == 'Household Data') echo '<pre>'. $this->sheet_name .' --- '. print_r($uniqueCols, true) .'</pre>';
   }

   /**
    * Confirm that we have a column that will be used to link multiple spreadsheet.
    *
    * If it is the main spreadsheet, check that we have a primary key. If it is a secondary sheet, ensure that we have a foreign key
    *
    * @since v0.2
    */
   private function confirmRelationalIntegrity(){
      $keyColumn = ($this->isMain) ? 'pri_key' : 'foreign_key';
      $keyColumnName = ($this->isMain) ? "<b>LINKING COLUMN</b>" : "<b>LINKING COLUMN</b>";

      //check that the sheet has the necessary key column defined
      $hasKey = false;
//      echo '<pre>'. print_r($this->metadata, true) .'</pre>';
      foreach($this->metadata['columns'] as $key => $data){
         if($data['name'] == $keyColumn) $hasKey = TRUE;
      }
      if(!$hasKey){
         $this->errors[] = "Error! The sheet has no $keyColumnName column. This is will cause the data in the multiple sheets not to be linked." ;    //this will cause the data not to be uploaded
         $this->toBeUploaded = false;
      }
   }

   /**
    * Checks whether the uploaded file has linked sheets
    *
    * @return  boolean  Returns true if the file is meant to have linked sheets, else returns false
    */
   public function hasLinkedSheets(){
      return $this->linkedSheets;
   }
}
?>
