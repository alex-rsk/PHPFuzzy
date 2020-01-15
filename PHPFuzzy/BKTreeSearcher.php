<?php
namespace PHPFuzzy;

/**
 *  Класс, предоставляющий интерфейс поиска по BK-дереву 
 */
class BKTreeSearcher extends Searcher
{

    /**
     *
     * @var PHPFuzzy\BKTree
     */
    protected $index;

    /**
     *  Создает индекс поиска
     */
    protected function createIndex()
    {
        if ($this->loadIndex()) {
            return;
        }
        $count                       = $this->dictionary->getDictionaryLength();
        $dictionary                  = $this->dictionary->getDictionary();
        $this->index                 = new \PHPFuzzy\BKTree($dictionary[0], $this->maxDistance);
        $this->measurements['index'] = [microtime(true)];
        foreach ($dictionary as $index => $item) {
            $this->index->addItem($item);
            Utils::progressBar($index, $count-1);
        }
        $this->saveIndex();
        $this->measurements['index'][] = microtime(true);
    }

    /**
     * Создает индекс и осуществляет поиск 
     * 
     * @param string $searchQuery поисковый запрос
     */
    public function __construct($searchQuery, $maxDistance = MAX_DISTANCE)
    {
        parent::__construct($searchQuery, $maxDistance);
        $this->createIndex();

        $this->measurements['search']   = [microtime(true)];
        $this->results                  = $this->search($searchQuery);
        $this->measurements['search'][] = microtime(true);
    }

    public function search($word, $node = null, $distance = false)
    {
        if (!isset($node)) {
            $node = $this->index;
        }
        if (!isset($this->results)) {
            $this->results = [];
        }
        $crossDistance = levenshtein($node->value, $word);
        if ($crossDistance <= $this->maxDistance) {
            $this->results[] = $node->value;
        }
        if (!$distance) {
            $distance = $crossDistance;
        }

        $maxKey = !empty($node->children) ? max(array_keys($node->children)) : 0;
        $from   = $distance + $this->maxDistance > $maxKey ? $maxKey : $distance + $this->maxDistance;
        for ($i = $from; $i > $this->maxDistance - $distance && $i >= 0; $i--) {
            if (isset($node->children[$i])) {
                $this->results += $this->search($word, $node->children[$i], $distance);
            }
        }
        return $this->results;
    }
}
