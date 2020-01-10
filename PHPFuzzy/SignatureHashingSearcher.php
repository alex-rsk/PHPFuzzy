<?php
namespace PHPFuzzy;

class SignatureHashingSearcher extends Searcher
{

    protected $index;
    protected $symbolMap;
    protected $hashSize;

    public function __construct($searchQuery, $maxDistance = MAX_DISTANCE, $hashSize
    = HASH_SIZE)
    {
        parent::__construct($searchQuery, $maxDistance);
        $this->alphabet                 = ALPHABET;
        $this->hashSize                 = $hashSize;
        $this->measurements['index']    = [microtime(true)];
        $this->createIndex();
        $this->measurements['index'][]    = microtime(true);
        $this->measurements['search']   = [microtime(true)];
        $this->search($searchQuery);
        $this->measurements['search'][] = microtime(true);
    }

    /**
     * 
     * @param string $term поисковый запрос     
     */
    public function search(string $term)
    {
        $result     = [];
        $stringHash = $this->makeHash($term);
        $this->innerSearch($term, $stringHash, $result);
        if ($this->maxDistance > 0) {
            $this->getHashVariants($term, $stringHash, 0, $result, $this->maxDistance - 1);
        }
        $this->results = $result;
    }

    /**
     * Получить хэш слова
     *      
     * @param type $word
     * 
     * @return int
     */
    protected function makeHash($word)
    {
        $result = 0;
        //$symbols = preg_split('//u', $word, -1, PREG_SPLIT_NO_EMPTY);
        for ($i = 0; $i < mb_strlen($word); $i++) {
            $symbol = mb_substr($word, $i, 1);;
            $index  = Utils::ordOffset($symbol, $this->alphabet);
            if (-1 == $index) {
                logVar($word, true, false);
            }
            $group  = $this->symbolMap[$index];
            $result |= 1 << $group;
        }
        return $result;
    }

    /**
     * Строит индекс поиска 
     * 
     * @return int
     */
    protected function fillAlphabetMap()
    {
        $aspect       = $sourceAspect = count($this->alphabet) / $this->hashSize;
        $map          = [];
        for ($i = 0; $i < $this->hashSize; $i++) {
            $step    = (int) round($aspect);
            $diff    = $aspect - $step;
            $map[$i] = $step;
            $aspect  = $sourceAspect + $diff;
        }
        $resultIndex = 0;
        for ($i = 0; $i < count($map); $i++) {
            for ($j = 0; $j < $map[$i]; $j++) {
                $this->symbolMap[$resultIndex++] = $i;
            }
        }
    }

    protected function createIndex()
    {
        $this->fillAlphabetMap();
        $dictionary     = $this->dictionary->getDictionary();
        $count = $this->dictionary->getDictionaryLength();
        for ($i = 0; $i < count($dictionary); ++$i) {
            $hash = $this->makeHash($dictionary[$i]);
            if (!isset($this->index[$hash])) {
                $this->index[$hash] = [];
            }
            $this->index[$hash][] = $i;
            Utils::progressBar($i, $count);
        }
    }

    /**
     * Перебирает вариации исходного хэша 
     */
    protected function getHashVariants(string $query, int $hash, int $start, &$set, $depth)
    {
        for ($i = $start; $i < $this->hashSize; ++$i) {
            $queryHash = $hash ^ (1 << $i);
            $this->innerSearch($query, $queryHash, $set);
            if ($depth > 0) {
                $this->getHashVariants($query, $queryHash, $i + 1, $set, $depth - 1);
            }
        }
    }

    /**
     * Перебирает все слова в словаре с заданным хешем
     */
    protected function innerSearch(string $query, int $queryHash, &$set)
    {
        $dictionary = $this->dictionary->getDictionary();
        $hashes     = isset($this->index[$queryHash]) ? $this->index[$queryHash] : null;
        if (isset($hashes)) {
            foreach ($hashes as $dictionaryIndex) {
                $word = $dictionary[$dictionaryIndex];
                if (levenshtein($query, $word) <= $this->maxDistance) {
                    $set[] = $word;
                }
            }
        }
    }
}
