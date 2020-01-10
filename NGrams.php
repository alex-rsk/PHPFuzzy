<?php

require_once 'constants.inc.php';
require_once 'utils.inc.php';
require_once 'Dictionary.php';

class NGrams
{

    //Словарь
    private $dictionary;
    private $alphabet;
    private $ngramMap;    
    private $maxDistance;
    //Арность
    private $arity;

    public function __construct($dictionary, $alphabet, 
        $maxDistance = MAX_DISTANCE, $arity = DEFAULT_ARITY)
    {
        $this->dictionary = $dictionary;
        $this->alphabet   = $alphabet;
        $this->maxDistance = $maxDistance;
        $this->arity = $arity;
        $this->createIndex();
    }

    public function createIndex()
    {        
        $ngramCountMap = [];
        $maxLength     = 0;

        foreach ($this->dictionary as $word) {
            if (mb_strlen($word) > $maxLength) {
                $maxLength = mb_strlen($word);
            }

            for ($k = 0; $k < mb_strlen($word) - $this->arity + 1; $k++) {
                $ngram = $this->getNGram($word, $k);
                if (!isset($ngramCountMap[$ngram]))
                {
                    $ngramCountMap[$ngram] = 0;
                }
                $ngramCountMap[$ngram]++;
            }
        }

        $this->ngramMap = [[]];

        for ($i = 0; $i < count($this->dictionary); $i++) {
            $word = $this->dictionary[$i];
            for ($k = 0; $k < mb_strlen($word) - $this->arity + 1; $k++) {
                $ngram = $this->getNGram($word, $k);
                if (!isset($this->ngramMap[$ngram])) {
                    $this->ngramMap[$ngram] = [];
                }
                $this->ngramMap[$ngram][$ngramCountMap[$ngram]--] = $i;
            }
        }        
    }

    public function getNGram($word, $start)
    {
        $ngram = 0;
        for ($i = $start; $i < $start + $this->arity; $i++) {
            $symbol = mb_substr($word, $i, 1);
            $ngram = $ngram * count($this->alphabet) + $this->ordOffset($symbol);
        }
        return $ngram;
    }

    private function ordOffset($symbol)
    {
        $symbolOrd = IntlChar::ord($symbol);
        $minOffset = IntlChar::ord(ALPHABET[0]);
        $maxOffset = IntlChar::ord(ALPHABET[count(ALPHABET) - 1]);
        if ($symbolOrd < $minOffset || $symbolOrd > $maxOffset) {
            return -1;
        }
        return $symbolOrd - $minOffset;
    }
    
    public function search($term)
    {
        $result = [];
        for ($i = 0; $i < mb_strlen($term)-$this->arity+1; $i++)
        {
            $ngram = $this->getNGram($term, $i);
            $dictIndexes = $this->ngramMap[$ngram];
            if (!empty($dictIndexes))
            {
                foreach ($dictIndexes as $index)
                {
                    $distance = levenshtein($this->dictionary[$index], $term);
                    if ($distance <= $this->maxDistance)
                    {
                        $result[] = $this->dictionary[$index];
                    }
                }
            }
        }
        return array_unique($result);
    }
}

//$dictionary = ['час', 'часы','часм','чесы','чек', 'чеки','печь','пасы','пес','пары'];
$dictionary = (new Dictionary)->getDictionary();
$ngram = new NGrams($dictionary, ALPHABET, MAX_DISTANCE, 2);
$results = $ngram->search('чары');
echo \prettyPrint($results);
