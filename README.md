# IPP-JSN2XML
IPP - First project script to converting JSON to XML

## Usage:
- `php jsn.php [option]...`

## Options:
- --help
  show this message
- --input=filename 
  input data in JSON format with encoding UTF-8 (implicit STDIN)
- --output=filename 
  toutput file to write XML data with encoding UTF-8 (implicit STDOUT)
- -h=subst    
  replace invalid characters in XML element  name with 'subst' implicit value '-'
- -n
  XML data will be generate without header
- -r=root-element
  result will be generate into 'root-element' tags
- --array-name=array-element  
  generate array elements into 'array-element' tags implicit value 'array'
- --item-name=item-element    
  generate array items into 'item-element' tags \n\timplicit value 'item'
- -s  
  string values transform to text elements no to atributes
- -i  
  numeric values transform to text elements no to atributes
- -l  
  values of literals (true, false, null) transform into\n\t <true/>, <false/>, <null/> instead of attributes
- -c  
  translation of problematic characters e.g. '&amp;',' &lt;', '&gt;'
- -a, --array-size    
  add size atribute to array element with size of array
- -t, --index-items   
  add index attribute to array elements implicit value count from 1 unless argument '--start=n' used
- --start=n   
  change implicit value for argument '-t, --index-items ' cause error if it used without using '-t/--index-items' argument
- --types
  add atribute 'type' to every scalar value element,depends on type of scalar value for integer add attribute 'integer',for real number add attribute 'real' and for literals add attribute 'literal'
