<?php
namespace PHPFuzzy;
/*require_once 'constants.inc.php';
require_once 'utils.inc.php';
require_once 'Dictionary.php';
require_once 'levdam.inc.php';
*/

class Dictionary
{
    private $dictionary;
    
    public function __construct($dictionary = 'ruswords.txt')
    {

        $this->dictionary             = [];
        $dictionaryFilename = 'dictionaries' . DIRECTORY_SEPARATOR . $dictionary;
        $file               = fopen($dictionaryFilename, 'r');
        while (false != ($str                = fgets($file))) {
            if ($str[0] != '#') {
                $this->dictionary[] = preg_replace('#\W#u', '', $str);
            }
        }
        fclose($file);
/* 
       $this->dictionary  = ['час', 'часы', 'танк', 'часм','чесы','чек', 
           'чеки','печь','пасы','пес','пары','барк'];*/
    }        

    public function getDictionary()
    {
        return $this->dictionary;
    }
    
    public function getDictionaryLength()
    {
        return count($this->dictionary);
    }
    
}
