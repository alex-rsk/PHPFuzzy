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
        $count                       = $this->dictionary->getDictionaryLength();
        $dictionary                  = $this->dictionary->getDictionary();
        //usort($dictionary, function ($el1, $el2) { return mb_strlen($el1, 'UTF-8') <=> mb_strlen($el2, 'UTF-8'); } );
        $root = $dictionary[0];
        $this->index                 = new \PHPFuzzy\BKTree($root);
        $this->measurements['index'] = [microtime(true)];
        foreach ($dictionary as $index => $item) {
            $this->index->addItem($item);
            Utils::progressBar($index, $count);
        }
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
        $this->results = $this->index->search($searchQuery);
        $this->measurements['search'][] = microtime(true);
    }
}
