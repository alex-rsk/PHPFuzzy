<?php
namespace PHPFuzzy;

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
