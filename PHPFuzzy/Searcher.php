<?php
namespace PHPFuzzy;

class Searcher
{

    protected $alphabet;
    protected $dictionary;
    protected $maxDistance;
    protected $index;
    protected $results;
    protected $searchQuery;
    protected $measurements;

    public function __construct($searchQuery, $maxDistance = MAX_DISTANCE)
    {
        $this->searchQuery                  = $searchQuery;
        $this->maxDistance                  = $maxDistance;
        $this->measurements['dictionary']   = [microtime(true)];
        $this->dictionary                   = new \PHPFuzzy\Dictionary();
        $this->measurements['dictionary'][] = microtime(true);
    }

    /**
     *  Создает индекс поиска
     */
    protected function createIndex()
    {
        
    }

    /**
     * Получает найденные результаты и замеры
     * 
     * @return array
     */
    public function getResults()
    {
        foreach ($this->measurements as &$item) {
            $item = $item[1] - $item[0];
        }

        return
            [
                'found'  => $this->results,
                'timing' => $this->measurements
        ];
    }

    protected function saveIndex()
    {
        $index     = \serialize($this->index);
        $className = explode('\\', get_class($this))[1];
        $fileName  = 'dictionaries' . DIRECTORY_SEPARATOR . $className . '.index';
        file_put_contents($fileName, $index);
    }

    protected function loadIndex()
    {
        $className = explode('\\', get_class($this))[1];
        $fileName  = 'dictionaries' . DIRECTORY_SEPARATOR . $className . '.index';
        if (\file_exists($fileName)) {
            $index       = \file_get_contents($fileName);
            $this->index = \unserialize($index);
            return true;
        }
        return false;
    }
}
