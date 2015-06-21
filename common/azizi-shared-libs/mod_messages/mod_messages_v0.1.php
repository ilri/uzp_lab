<?php
/**
 * A collection of error messages that will be displayed to the user when different things happen
 */


/**
 * @var  string   The message to display when the user cannot log in to the system
 */
define('OPTIONS_MSSG_LOGIN_ERROR', '<i>Invalid username or password, please try again.<br> If your log in details are correct, you may not have sufficient rights to access the system.<br> Please contact the System Administrator.</i>');

/**
 * @var  string   The message to display when there is an error while fetching data from the database
 */
define('OPTIONS_MSSG_FETCH_ERROR', "Well this is embarassing! There was an error while fetching data from the database.<br />".Config::$contact);

/**
 * @var  string   Message to display when there is an error while saving data to the db
 */
define('OPTIONS_MSSG_SAVE_ERROR', "Ooops! There was an error while saving data to the database.$contact");

/**
 * @var  string   The message to display when we dont know which module to display
 */
define('OPTIONS_MSSG_MODULE_UNKNOWN', 'Something is amiss! Either I cant figure out which module to take you to, or you do not have enough privileges for that module.');

/**
 * @var  string   Message to display when there is an error while updating the db
 */
define('OPTIONS_MSSG_UPDATE_ERROR', "Ooops! There was an error while updating the database.<br />" . Config::$contact);

/**
 * @var  string   Message to display when there was an error while deleting data from the database
 */
define('OPTIONS_MSSG_DELETE_ERROR', "Ooops! Sorry we did something wrong.<br />" . Config::$contact);

/**
 * @var  string   Message to display when the user is lacking enough privileges to access a module
 */
define('OPTIONS_MSSG_RESTRICTED_MODULE_ACCESS', "Privileged Access Only! You do not have enough privileges to access the '%s' module.");

/**
 * @var  string   Message to display when the user is lacking enough privileges to access an action within a module
 */
define('OPTIONS_MSSG_RESTRICTED_FUNCTION_ACCESS', "Privileged Access Only! You do not have enough privileges to access the <u>'%s'</u> function within the <u>'%s'</u> module.");

/**
 * @var  string   The message to display when a technologist tries to edit an order which already have some results
 */
define('OPTIONS_MSSG_RESTRICTED_EDIT_ORDER_WITH_RESULTS', "Priviliged Access Only! You do not have enough privileges to edit an order which already has some results.");

/**
 * @var  string   The message to display when a technologist tries to delete an order which already have some results
 */
define('OPTIONS_MSSG_RESTRICTED_DELETE_ORDER_WITH_RESULTS', "Priviliged Access Only! You do not have enough privileges to delete an order which already has some results.");

/**
 * @var  string   The message to display when a user tries to finalize an order, yet there are some results pending
 */
define('OPTIONS_MSSG_FINALIZING_INCOMPLETE_ORDER', 'Eeehhh, You are not allowed to do that, Finalize an order with some results pending(%s complete).');

/**
 * @var  string   A message to display when data from the client is tampered with
 */
define('OPTIONS_MSSG_TAMPERED_DATA', 'Gotcha! Stop tampering with the systems data. Rem Big Brother is watching you!');

/**
 * @var  string   A message to display when there are no file selected for upload
 */
define('OPTIONS_MSSG_NO_FILES_FOR_UPLOAD', 'Please select one file to be uploaded.');

/**
 * @var  string   Message to display when the new username is not defined
 */
define('OPTIONS_MSSG_UNDEFINED_NEW_USERNAME', 'Please enter the new username.');

/**
 * @var  string   Message to display when the old password is wrong
 */
define('OPTIONS_MSSG_INCORRECT_OLD_PASSWORD', 'Error! The entered old password is incorrect.');

/**
 * @var  string   Message to display when the new password is not defined
 */
define('OPTIONS_MSSG_UNDEFINED_NEW_PASSWORD', 'Please enter the new password.');

/**
 * @var  string   The message to diplay when there is an error while copying files
 */
define('OPTIONS_MSSG_FILE_COPY_ERROR', 'Unexpected! There was an error while copying the file.');

/**
 * @var  string   Message to display when there is an error opening a file
 */
define('OPTIONS_MSSG_FILE_OPEN_ERROR', 'There was an error while trying to open the %s.');

/**
 * @var  string   A general message to be displayed when there is an error during processing the CD4 results
 */
define('OPTIONS_MSSG_ERROR_PARSING_CD4_FILE', 'There was an error while processing the results file.');

/**
 * @var  string   A message to be displayed in case we are missing the date in the comments
 */
define('OPTIONS_MSSG_MISSING_DATE_IN_CD4_REPORT', 'Fatal! The date is missing in the %s results.');

/**
 * @var  string   The message to show in case I do not have a record of the entered sample
 */
define('OPTIONS_MSSG_UNKNOWN_SAMPLE', "Fatal! I do not have a record of this <b>'%s'</b> sample.");

/**
 * @var  string   The message to display in case the CD4 Count test is not defined in the database
 */
define('OPTIONS_MSSG_MISSING_CD4_TEST', "Fatal! The test 'CD4 Count' is missing in the database.<br />" . Config::$contact);

/**
 * @var  string   The message to display when the test CD4 Count does not appear in the list of ordered test of a sample
 */
define('OPTIONS_MSSG_MISSING_TEST_ORDER', "Fatal! The test 'CD4 Count' is not ordered for the sample '%s'. Add the test first to this sample before trying to upload any results.");

/**
 * @var  string   A message to display when trying to save a duplicate range
 */
define('OPTIONS_MSSG_DUPLICATE_RANGES', 'There were some duplicate ranges. Not all defined ranges were saved.');

/**
 * @var  string   A message to show when there are conficting results
 */
define('OPTIONS_MSSG_CONFLICTING_RESULTS', "Fatal! There are conflicting results for the <b>'%s'</b> test. <b>Original: %s</b>, <b>saving: %s.</b>");

/**
 * @var  string   A message that is displayed when the module is not known
 */
define('OPTIONS_MSSG_UNKNOWN_MODULE', 'Error! You have requested an unknown module.');

/**
 * @var  string   A message that is displayed when the sub module is not known
 */
define('OPTIONS_MSSG_UNKNOWN_SUB_MODULE', 'Error! You have requested an unknown sub module.');

/**
 * @var  string   The message to be shown where there is an error while printing the labels
 */
define('OPTIONS_MSSG_PRINT_LABEL_ERROR', 'There was an error while printing the label.<br />'. Config::$contact);

/**
 * @var  string   A message to show when something went wrong in the system and I cannot figure out what it is
 */
define('OPTIONS_MSSG_INVALID_SESSION', 'Something unexpected happened and you have automatically been logged out.<br />Please log in again.');

define('OPTIONS_MSSG_INVALID_NAME', "Error! Please enter a valid %s.");
define('OPTIONS_MSSG_INVALID_VARIABLE', "Error! You have input an invalid value for '%s'. Epecting a(an) %s{$contact}");
define('OPTIONS_MSSG_CREATE_DIR_ERROR', "There was an error while creating the %s directory.$contact");
define('OPTIONS_MSSG_CREATE_FILE_ERROR', "There was an error while creating the %s file.$contact");
define('OPTIONS_MSSG_MISSING_FOLDER', "The %s folder does not exists.$contact");
define('OPTIONS_MSSG_FILE_WRITE_ERROR', "There was an error while saving the data to the %s file.$contact");
define('OPTIONS_MSSG_USERREPLY_SYSTEM_ERROR','Well this is embarassing! The system is currently experiencing some problems.');
define('OPTIONS_MSSG_FARMER_REG_ERROR', 'Error! There was an error while completing the registration. Please try again later!');

/**
 * @var  string   The message to display when the default systems administrator is missing
 */
define('OPTIONS_MSSG_NO_SYS_ADMIN', "Fatal Error! Could not find the default system administrator.".Config::$contact);

/**
 * @var  string   The regex to be used when validating emails
 */
define('OPTIONS_VALIDATOR_EMAIL', "/^[_\.0-9a-zA-Z-]+@([0-9a-zA-Z][0-9a-zA-Z-]+\.)+[a-zA-Z]{2,6}$/i");

/**
 * @var  string   The regex to be used when validating dates
 */
define('OPTIONS_VALIDATOR_DATE', "/^[0-9]{4}\-[0-9]{2}\-[0-9]{2}$/");

/**
 * @var  string   The regex to be used when validating telephones
 */
define('OPTIONS_VALIDATOR_TELEPHONE', "/^\+[0-9]{12}|[0-9]{10}$/");

/**
 * @var  string   The regex to be used when validating text
 */
define('OPTIONS_VALIDATOR_TEXT', 'text');

/**
 * @var  string   The regex to be used when validating names
 */
define('OPTIONS_VALIDATOR_NAMES', "/^[a-z ']+$/i");

/**
 * @var  string   The regex to be used when validating gender
 */
define('OPTIONS_VALIDATOR_GENDER', "/^male|female$/i");

/**
 * @var  string   The regex to be used when validating sample name
 */
define('OPTIONS_VALIDATOR_SAMPLE_NAME', "/^[a-z0-9]+$/i");
?>
