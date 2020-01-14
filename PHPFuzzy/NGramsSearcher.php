<?php
namespace PHPFuzzy;

class NGramsSearcher extends Searcher
{

    protected $index;

    public function __construct($searchQuery, $maxDistance = MAX_DISTANCE, $arity
    = DEFAULT_ARITY)
    {
        parent::__construct($searchQuery, $maxDistance);
        $this->arity                    = $arity;        
        $this->alphabet                 = ALPHABET;
        
        $this->measurements['index']   = [microtime(true)];
        $this->createIndex();
        $this->measurements['index'][] = microtime(true);
        $this->measurements['search']   = [microtime(true)];
        $this->search($searchQuery);
        $this->measurements['search'][] = microtime(true);
    }

    /**
     * Получить хэш н-граммы
     * 
     * @param string $word исходная строка
     * @param int $start позиция, с которой получаем н-группу
     * @return type
     */
    protected function getNGram($word, $start)
    {
        $ngram = 0;
        for ($i = $start; $i < $start + $this->arity; $i++) {
            $symbol = mb_substr($word, $i, 1);
            $ngram  = $ngram * count($this->alphabet) 
                + Utils::ordOffset($symbol, $this->alphabet);
        }
        return $ngram;
    }
    
    /**
     * Поиск
     *
    */
    public function search($term)
    {        
        for ($i = 0; $i < mb_strlen($term) - $this->arity + 1; $i++) {
            $ngram       = $this->getNGram($term, $i);
            $dictIndexes = $this->index[$ngram];
            $dictionary    = $this->dictionary->getDictionary();
            if (!empty($dictIndexes)) {
                foreach ($dictIndexes as $index) {
                    $distance = levenshtein($dictionary[$index], $term);
                    if ($distance <= $this->maxDistance) {
                        $this->results[] = $dictionary[$index];
                    }
                }
            }
        }
        $this->results = array_unique($this->results);
    }

    /**
     *  Построить индекс 
     */
    public function createIndex()
    {    
        $count = $this->dictionary->getDictionaryLength();
        $dictionary    = $this->dictionary->getDictionary();
        $this->index = [[]];

        for ($i = 0; $i < count($dictionary); $i++) {
            $word = $dictionary[$i];
            if (mb_strlen($word) === $this->arity) {
                $ngram = $this->getNGram($word, 0);
                if (!isset($this->index[$ngram])) {
                    $this->index[$ngram] = [];
                }
                $this->index[$ngram][] = $i;
            } else {                            
                for ($k = 0; $k < mb_strlen($word) - $this->arity + 1; $k++) {
                    $ngram = $this->getNGram($word, $k);
                    if (!isset($this->index[$ngram])) {
                        $this->index[$ngram] = [];
                    }
                    $this->index[$ngram][] = $i;
                }
            }
            Utils::progressBar($i, $count);
        }
    }
}
