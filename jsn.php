#!/usr/bin/php
<?php

/**
  * Script which convert JSON file to XML file
  *
  * @author Dominik Hlavac Duran xhlava42@stud.fit.vutbr.cz
  */

/**
  * Class containig script arguments and flags to check errors
  */
class Program_arguments {
  var $header_print = true;
  var $string_to_elements = false;
  var $number_to_elements = false;
  var $literals_to_elements = false;
  var $problematic_characters = false;
  var $array_size = false;
  var $types = false;

  var $index_item = 1;
  var $index_item_used = false;
  var $character_substitution = '-';
  var $root_element = '';
  var $root_element_used = false;
  var $array_element = 'array';
  var $array_element_used = false;
  var $item_element = 'item';
  var $item_element_used = false;
  var $start_used = false;

  var $input_file = 'php://stdin';
  var $output_file = 'php://output';
}

/**
  * Function to return error code and error message
  * @param return_code return_code value
  * @param message message that will e printed to STDERR
  * @return return_code returns right value to STDERR when error ocures
  */
function err_msg($return_code,$message){
  file_put_contents('php://stderr',$message);
  exit($return_code);
}

/******************************************************************************/

/**
 * Function that parse script arguments and fill object with script configuration
 * @param object that contains script arguments
 */
function ParsingArguments(&$arguments)
{
  global $argv;
  global $argc;
  $inputUsed = false;
  $outputUsed = false;
  $hUsed = false;
  $nUsed = false;
  $rUsed = false;
  $array_nameUsed = false;
  $item_nameUsed = false;
  $sUsed = false;
  $iUsed = false;
  $lUsed = false;
  $cUsed = false;
  $aUsed = false;
  $tUsed = false;
  $startUsed = false;// need to use with -a
  $typesUsed = false;

  $args = $argv;
  unset($args[0]); // delete the script name

  foreach ($args as $argument) {

    $expansion = explode("=",$argument);

    if (sizeof($expansion) == 1) {
      switch ($expansion[0]) {

    case "-help":
    case "--help":
      if (sizeof($args) == 1){
        $help = "\tFirst project for IPP course: JSON to XML converter\n
  Run command: jsn.php [OPTION]...\n
  OPTIONS (allowed short or long format of options)
  --help
  \tshow this message
  --input=filename 
  \tinput data in JSON format with encoding UTF-8 (implicit STDIN)\n
  --output=filename 
  \toutput file to write XML data with encoding UTF-8 (implicit STDOUT)\n
  -h=subst    
  \t replace invalid characters in XML element  name with 'subst' implicit value '-'
  -n
  \tXML data will be generate without header\n
  -r=root-element
  \t result will be generate into 'root-element' tags\n
  --array-name=array-element  
  \tgenerate array elements into 'array-element' tags \n\timplicit value 'array'\n
  --item-name=item-element    
  \tgenerate array items into 'item-element' tags \n\timplicit value 'item'
  -s  
  \tstring values transform to text elements no to atributes\n
  -i  
  \tnumeric values transform to text elements no to atributes\n
  -l  
  \tvalues of literals (true, false, null) transform into\n\t <true/>, <false/>, <null/> instead of attributes\n
  -c  
  \ttranslation of problematic characters \n\te.g. '&amp;',' &lt;', '&gt;'\n
  -a, --array-size    
  \tadd size atribute to array element with size of array\n
  -t, --index-items   
  \tadd index attribute to array elements implicit value count \n\tfrom 1 unless argument '--start=n' used
  --start=n   
  \tchange implicit value for argument '-t, --index-items ' \n\tcause error if it used without using '-t/--index-items' argument
  --types
  \tadd atribute 'type' to every scalar value element,\n\tdepends on type of scalar value for integer add attribute 'integer'\n\t,for real number add attribute 'real' \n\tand for literals add attribute 'literal'\n";
        print $help;
        exit(0);
      }
      else {
        err_msg(1,"ERROR Help cannot be conbinated with another argument\n");
      }
    break;

    case "-n":
    case "--n":
      if ($nUsed) {
        err_msg(1,"ERROR check arguments, for help type -help\n");
      }
      $nUsed = true;
      $arguments->header_print = false;
      break;

    case "-s":
    case "--s":
      if ($sUsed) {
        err_msg(1,"ERROR check arguments, for help type -help\n");
      }
      $sUsed = true;
      $arguments->string_to_elements = true;
      break;

    case "-i":
    case "--i":
      if ($iUsed) {
        err_msg(1,"ERROR check arguments, for help type -help\n");
      }
      $iUsed = true;
      $arguments->number_to_elements = true;
      break;

    case "-l":
    case "--l":
      if ($lUsed) {
        err_msg(1,"ERROR check arguments, for help type -help\n");
      }
      $lUsed = true;
      $arguments->literals_to_elements = true;
      break;

    case "-c":
    case "--c":
      if ($cUsed) {
        err_msg(1,"ERROR check arguments, for help type -help\n");
      }
      $cUsed = true;
      $arguments->problematic_characters = true;
      break;

    case "-a":
    case "--a":
    case "-array-size":
    case "--array-size":
      if ($aUsed) {
        err_msg(1,"ERROR check arguments, for help type -help\n");
      }
      $aUsed = true;
      $arguments->array_size = true;
      break;

    case "-t":
    case "--t":
    case "-index-items":
    case "--index-items":
      if ($tUsed) {
        err_msg(1,"ERROR check arguments, for help type -help\n");
      }
      $tUsed = true;
      $arguments->index_item_used = true;
      break;

    case "-types":
    case "--types":
      if ($typesUsed) {
        err_msg(1,"ERROR check arguments, for help type -help\n");
      }
      $typesUsed = true;
      $arguments->types = true;
      break;

    default:
      err_msg(1,"ERROR unknown argument\n");
    }
  }
    else if (sizeof($expansion) == 2){
      switch ($expansion[0]) {

    case "-input":
    case "--input":
      if ($inputUsed) {
        err_msg(1,"ERROR check arguments, for help type -help\n");
      }
      else {
        // if file doesnt exist
        if (!(file_exists($expansion[1]))) {
            err_msg(2,"ERROR input file does not exist\n");
        }
        // if file exist
        else {
          $arguments->input_file = $expansion[1];

          if (!fopen($expansion[1], "r")) {
            err_msg(2,"ERROR input file does not exist\n");
          }

          $inputUsed = true;
        }
      }
      break;

    case "-output":
    case "--output":
      if ($outputUsed) {
        err_msg(1,"ERROR this argument is already used\n");
      }
      else {
        $arguments->output_file = $expansion[1];
        if (!fopen($expansion[1], "w")) {
          err_msg(3,"ERROR problem with output file\n");
        }

        if ($arguments->output_file === false) {
            err_msg(3,"ERROR problem with output file\n");
        }
        $outputUsed = true;
      }
      break;

    case "-h":
    case "--h":
      if ($hUsed) {
        err_msg(1,"ERROR this argument is already used\n");
      }
      else {
        $arguments->character_substitution = $expansion[1];
        $hUsed = true;
        }
      break;

    case "-r":
    case "--r":
      if ($rUsed) {
        err_msg(1,"ERROR this argument is already used\n");
        }
      else {
        $arguments->root_element = $expansion[1];
        $arguments->root_element_used = true;
        $rUsed = true;
      }
      break;

    case "-array-name":
    case "--array-name":
      if ($array_nameUsed) {
        err_msg(1,"ERROR this argument is already used\n");
      }
      else {
        $arguments->array_element = $expansion[1];
        if (!is_valid_xml_name($arguments->array_element)) {
          err_msg(50,"Invalid value\n");
        }
        $arguments->array_element_used = true;
        $array_nameUsed = true;
      }
      break;

    case "-item-name":
    case "--item-name":
      if ($item_nameUsed) {
        err_msg(1,"ERROR this argument is already used\n");
      }
      else {
        $arguments->item_element = $expansion[1];
        if (!is_valid_xml_name($arguments->item_element)) {
          err_msg(50,"Invalid value\n");
        }
        $arguments->item_element_used = true;
        $item_nameUsed = true;
      }
      break;

    case "-start":
    case "--start":
      if ($startUsed) {
        err_msg(1,"ERROR this argument is already used\n");
      }
      else {
        (int) $arguments->index_item = $expansion[1];
        if ((is_numeric($arguments->index_item)) && ((int)$arguments->index_item >= 0) && !(determine_float($arguments->index_item))){
          $startUsed = true;
          $arguments->start_used = true;
        }
        else {
          err_msg(1,"ERROR wrong argument in parameter -t\n");
        }
      }
      break;

    default:
      err_msg(1,"ERROR unknown argument\n");
      break;
      }
    }
    else {
      err_msg(1,"ERROR unknown argument\n");
    }
  }
  return 0;
}

/**
 * Function helping function to determine if number input type is float
 * @param $number to determine
 * @return true or false
 */
function determine_float($number){
  $float_num = floatval($number);
  if($float_num && intval($float_num) != $float_num){
    return true;
  }
  else{
    return false;
  }
}

/**
 * Function that replace invalid character
 * @param $arguments options for running script and global variables
 * @param $content_to_write data to create xml
 */
function replace_invalid_character($value){
  $value = str_replace("&", '&amp;', $value);
  $value = str_replace("<", '&lt;', $value);
  $value = str_replace(">", '&gt;', $value);
  $value = str_replace(array("'", "\""), '&quot;', $value);
  return $value;
}

/**
 * Function to choose operation depends of type of data
 * @param $arguments options for running script and global variables
 * @param $object object JSON file
 * @param $content_to_write data to create xml
 */
function select_operation($arguments,$content,$content_to_write){
  if (is_object($content)){
    object_to_xml($arguments,$content,$content_to_write);
  }
  elseif (is_array($content)){
    array_to_xml($arguments,$content,$content_to_write);
  }
  else{
    value_to_xml($arguments,$content,$content_to_write);
  }
}


/**
 * Function that convert json object to xml
 * @param $arguments options for running script and global variables
 * @param $object object JSON file
 * @param $content_to_write data to create xml
 */
function object_to_xml($arguments,$object,$content_to_write){
  foreach ($object as $key => $value){
    $content_to_write->startElement(convert_invalid_xml_name($arguments,$key,true)); 
    select_operation($arguments,$value,$content_to_write);
    $content_to_write->endElement();
  }
}


/**
 * Function that convert json array to xml
 * @param $arguments options for running script and global variables
 * @param $content content of JSON file
 * @param $content_to_write data to create xml
 */
function array_to_xml($arguments,$array,$content_to_write){
  $index = $arguments->index_item;

  if(is_valid_xml_name($arguments->array_element)) {
    $content_to_write->startElement($arguments->array_element);
  }
  else {
    err_msg(50,"Invalid name of XML element\n");
  } 

  if ($arguments->array_size){
    $content_to_write->writeAttribute("size",count($array));
  }  

  foreach ($array as $key => $value){
    if(is_valid_xml_name($arguments->item_element)) {
      $content_to_write->startElement($arguments->item_element);
    }
    else {
      err_msg(50,"Invalid name of XML element\n");
    }
    if ($arguments->index_item_used){
      $content_to_write->writeAttribute("index",$index);
      $index++;
    } 

    select_operation($arguments,$value,$content_to_write);
    $content_to_write->endElement();
  }

  $content_to_write->endElement();
}

/**
 * Function that convert json value to xml
 * @param $arguments options for running script and global variables
 * @param $content content of JSON file
 * @param $content_to_write data to create xml
 */
function value_to_xml($arguments,$value,$content_to_write){
  if (($value === true || $value === false || $value === null)){ 
    if ($arguments->types) {
      $content_to_write->writeAttribute('type','literal');
    } 
    if ($arguments->literals_to_elements){
      if ($value === true){
        $content_to_write->writeElement("true");
      }
      elseif ($value === false){
        $content_to_write->writeElement("false");
      }
      elseif ($value === null){
        $content_to_write->writeElement("null");
      }
    }
    else{
      if ($value === true){
        $content_to_write->writeAttribute("value","true");
      }
      elseif ($value === false){
        $content_to_write->writeAttribute("value","false");
      }
      elseif ($value === null){
        $content_to_write->writeAttribute("value","null");
      }
    }
  }elseif (is_numeric($value)) {
    if (is_float($value)) {
      if ($arguments->types) {
        $content_to_write->writeAttribute('type','real');
      }
      $value = floor($value);
      if ($arguments->number_to_elements) {
        $content_to_write->text($value);
      }
      else{
        $content_to_write->writeAttribute('value',$value);
      }
    }
    elseif (is_int($value)) {
      if ($arguments->types) {
        $content_to_write->writeAttribute('type','integer');
      }
      $value = floor($value);
      if ($arguments->number_to_elements) {
        $content_to_write->text($value);
      }
      else{
        $content_to_write->writeAttribute('value',$value);
      }
    }
    elseif(is_string($value)){
      if ($arguments->problematic_characters) {
        $value = replace_invalid_character($value);
        $content_to_write->text($value);
        }
      if ($arguments->types) {
        $content_to_write->writeAttribute('type','string');
      }
      if ($arguments->string_to_elements) {
        $content_to_write->writeRaw($value);
      } 
      else {
        $content_to_write->writeAttribute('value',$value);
      }
    }
    }
  elseif (is_string($value)) {
    if ($arguments->types) {
      $content_to_write->writeAttribute('type','string');
    }
    if ($arguments->string_to_elements) {
      if ($arguments->problematic_characters) {
      $value = replace_invalid_character($value);
    }
      $content_to_write->writeRaw($value);
    } 
    else {
      $content_to_write->writeAttribute('value',$value);
    }
  }
}

/**
 * Function that check if string is valid xml name
 * @param $string from input
 * @return true or false depends on validity of string
 */
function is_valid_xml_name($name)
{
  try{
    new DOMElement($name);
    
    if(stripos($name, 'xml') === 0){
      throw new DOMException;
    }
    return true;
  }  
  catch(DOMException $e){
    return false;
  }
}

/**
 * Function that convert invalid xml name to valid 
 * @param $arguments options for running script and global variables
 * @param $name string to be replaced
 * @param $second_validity_check true or false depends of scenario (arg -h)
 * @return replaced name
 */
function convert_invalid_xml_name($arguments,$name,$second_validity_check) {
  $zero = 0;
  $one = 1;
  if(is_valid_xml_name($name)){
    return $name;
  }
  
  if ($second_validity_check) {
    if (!(substr($name, 0, 1) === '_') || !ctype_alpha($name[0])) {
      $name = substr_replace($name,$arguments->character_substitution,$zero,$one);
    }

    $name = str_replace(array("<",">","\"","'","&","\\","/"), $arguments->character_substitution, $name);
  if(is_valid_xml_name($name)){
    return $name;
  }
  else {
    err_msg(51,"Invalid element name\n");
  }
  }
  return $name;
}

/**
 * Function for converting JSON file to XML
 * @param $arguments options for running script and global variables
 * @param $content content of JSON file
 * @param $content_to_write data to create xml
 * @return object that contains script arguments
 */
function converter($arguments, $content,&$content_to_write) {
  $content_to_write->openMemory();
  $content_to_write->setIndent(true);

  if ($arguments->header_print) { // parameter -n
    $content_to_write->startDocument('1.0','UTF-8');
  }

  if ($arguments->root_element_used) {
    if(is_valid_xml_name($arguments->root_element)) {
      $content_to_write->startElement($arguments->root_element);
    }
    else {
      err_msg(50,"Invalid name of XML element\n");
    }  
  }

  select_operation($arguments,$content,$content_to_write);

  if ($arguments->root_element_used){
    $content_to_write->endElement();
  }

  $content_to_write->endDocument();
}

/**
 * Main script function
 */
function main(){
  $arguments = new Program_arguments();

  ParsingArguments($arguments);

  if (!$arguments->index_item_used && $arguments->start_used) {
    err_msg(1,"ERROR option -start cannot run without option -index-item\n");
  }

  if (($content = file_get_contents($arguments->input_file, FILE_USE_INCLUDE_PATH)) === false) {
    err_msg(2,"ERROR input file does not exist or is broken\n");
  }
  if ((($decoded_json = json_decode($content)) === NULL) || json_last_error() != JSON_ERROR_NONE) {
    err_msg(4,"ERROR wrong input data \n");
  }
  if (empty($decoded_json)) {
    err_msg(2,"ERROR empty input file \n");
  }
  //initialization of xmlwriter
  $content_to_write = new XMLWriter();
  //converting json to xml
  converter($arguments,$decoded_json,$content_to_write);
  //writing xml to file
  $output_file = fopen($arguments->output_file, "w");

  if ((fwrite($output_file,$content_to_write->outputMemory(TRUE))) === false || !fclose($output_file)){
    err_msg(3,"ERROR problem with output file");
  }

  $content_to_write->flush();
}

main();
?>
