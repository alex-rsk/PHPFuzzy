<?php
namespace PHPFuzzy;

/**
 *  Полезные функции
 */
class Utils
{
    
    /**
    * Расчет расстояния Дамерау-Левенштейна 
    * 
    * @param string $str1  - первая сравниваемая строка
    * @param string $str2  - вторая сравниваемая строка
    * 
    * @return int расстояние Д.-Л.
    */
    public static function calcDistance($str1, $str2)
    {        
        $cost      = -1;
        $del       = 0;
        $sub       = 0;
        $ins       = 0;
        $trans     = 0;
        $matrix    = [[]];                
        $size1 = mb_strlen($str1);
        $size2 = mb_strlen($str2); 
        $str1 = preg_split('//u', $str1, -1, PREG_SPLIT_NO_EMPTY);
        $str2 = preg_split('//u', $str2, -1, PREG_SPLIT_NO_EMPTY);
        
        for ($i = 0; $i <= $size1; $i++) {
            $matrix[$i][0] = $i > 0 ? $matrix[$i - 1][0] + 1 : 0;
        }

        for ($i = 0; $i <= $size2; $i++) {
            $matrix[0][$i] = $i > 0 ? $matrix[0][$i - 1] + 1 : 0;
        }

        for ($i = 1; $i <= $size1; $i++) {
            $symbol1 = $str1[$i - 1];
            for ($j = 1; $j <= $size2; $j++) {
                $symbol2 = $str2[$j - 1];
                if (($symbol1 <=> $symbol2) == 0) {
                    $cost  = 0;
                    $trans = 0;
                } else {
                    $cost  = 1;
                    $trans = 1;
                }
                $del = $matrix[$i - 1][$j] + 1;
                $ins = $matrix[$i][$j - 1] + 1;
                $sub = $matrix[$i - 1][$j - 1] + $cost;

                $matrix[$i][$j] = min($del, $ins, $sub);

                if ($i > 1 && $j > 1) {
                    $pref1 = $str1[$i - 2];
                    $pref2 = $str2[$j - 2];

                    if (($symbol1 <=> $pref2) == 0 && ($pref1 <=> $symbol2) == 0) {
                        $matrix[$i][$j] = min($matrix[$i][$j], $matrix[$i - 2][$j - 2] + $trans);
                    }
                }
            }
        }        
        return $matrix[$size1][$size2];
    }
    
    
    
    /**
     * удобочитаемый вывод переменной 
     * 
     * @param mixed $variable
     * @param boolean $web
     * @return string
     */
    public static function prettyPrint($variable, $web = false)
    {

        return ($web ? '<pre>' : '')
            . print_r($variable, true)
            . ($web ? '</pre>' : '') . ($web ? '<BR/>' : PHP_EOL);
    }

     /**
     * логирование переменной 
     * 
     * @param mixed $variable
     * @param boolean $web
     * @return string
     */
    public static function logVar($variable, $flush = false, $file = true)
    {
        $content = prettyPrint($variable);
        if ($file) {
            file_put_contents('log.txt', $content, $flush ? FILE_APPEND : null);
        } else {
            print $content;
        }
    }
    
    /**
     * Прогрессбар в консоли
     * 
     * @param int $done
     * @param int $total
     */
    public static function progressBar($done, $total)
    {
        $percent  = floor(($done / $total) * 100);
        $left  = 100 - $percent;
        $format = "\033[0G\033[2K[%";
        $write = sprintf("$format'={$percent}s>%-{$left}s] - $percent%% - $done/$total", "", "");
        fwrite(STDERR, $write);
    }
    
    /**
     * Код символа со смещением от начала алфавита для UTF-8
     * 
     * @param string $symbol
     * @param array $alphabet
     * @return int -1 если символ за пределами алфавита, или порядкой номер 
     * относительно начала
     */
    public static function ordOffset($symbol, $alphabet)
    {
        if ('ё' == $symbol) { 
            $symbol = 'e'; 
        }
        $symbolOrd = \IntlChar::ord($symbol);
        $minOffset = \IntlChar::ord($alphabet[0]);
        $maxOffset = \IntlChar::ord($alphabet[count($alphabet) - 1]);
        if ($symbolOrd < $minOffset || $symbolOrd > $maxOffset) {
            return -1;
        }
        return $symbolOrd - $minOffset;
    }
}
