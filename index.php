<?php
namespace PHPFuzzy;

require_once 'autoloader.php';
require_once 'constants.php';

if (php_sapi_name() !== 'cli' || 3 > $argc) {
    die('php -f index.php <search_name> <search_query> <max_distance>');
}

$classKeys = [
    'full' => '\PHPFuzzy\ExhaustiveSearcher',
    'bktree' => '\PHPFuzzy\BKTreeSearcher',
    'ngrams' => '\PHPFuzzy\NGramsSearcher',
    'signature' => '\PHPFuzzy\SignatureHashingSearcher'
];

$input = $argv[2];
$distance = $argv[3] ?? MAX_DISTANCE;
$searchKind = strtolower($argv[1]);
$searchTerm = mb_strtolower($input, 'UTF-8');

if (mb_strlen($searchTerm, 'UTF-8')==0)
{
    die ('Укажите слово для поиска');
}

if (!isset($classKeys[$searchKind]))
{
    die('Метод не указан');
}

$searchObject = new $classKeys[$searchKind]($searchTerm, $distance);
$results = $searchObject->getResults();
echo Utils::prettyPrint($results);