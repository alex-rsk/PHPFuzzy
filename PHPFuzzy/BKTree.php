<?php
namespace PHPFuzzy;

/**
 * БК-дерево для нечеткого поиска
 */

class BKTree
{
    

    public $value;        
    
    public $children = [];        
    
    protected $maxDistance;

    public function __construct($value, $maxDistance = MAX_DISTANCE)
    {
        $this->value = $value;
        $this->maxDistance = $maxDistance;
    }

    /**
     * Добавляет в BK дерево новый элемент
     * 
     * @param string $word     
     */
    public function addItem($word)
    {
        $distance = levenshtein($this->value, $word);
        if (isset($this->children[$distance])) {
            $this->children[$distance]->addItem($word);
        } else {
            $this->children[$distance] = new BKTree($word);
        }
        
    }
    
   /**
     * Ищет в дереве соответствия образцу с расстоянием редактирования не > заданного
     * 
     * @param string $word поисковый запрос
     * @param int $distance текущее расстояние редактирования от узла до запроса
     * @param array $results текущий набор результатов
     * 
     * @return array 
     */
    public function search($word, $distance = false, $results = false)
    {
        if (!$results)
        {
            $results = [];
        }
        $crossDistance = levenshtein($this->value, $word);        
        if ($crossDistance <= $this->maxDistance)
        {
            $results[] = $this->value;
        }
        if (!$distance)
        {
            $distance  = $crossDistance;
        }
        
        for ($i=$distance + $this->maxDistance; $i>$this->maxDistance-$distance; $i--)
        {           
            if (isset($this->children[$i]))
            {
                $results += $this->children[$i]->search($word, $distance, $results);
            }
            
        }
        return $results;
    }
}