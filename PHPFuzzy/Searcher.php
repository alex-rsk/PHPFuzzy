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
}
