<?php
// script to debug dlm package
require('search.php');
$t411 = new SynoDLMSearchT411;
$t411->debug = 1;
$curl = curl_init();

parse_str(implode('&', array_slice($argv, 1)), $_GET);
$t411->prepare($curl, 'game of throne', $_GET['username'], $_GET['password']);

$response = curl_exec($curl);
curl_close($curl);
$plugin = new plugin;

$count = $t411->parse($plugin, $response);

echo $plugin->count() . "\n";

class plugin {
    private $results;

    public function addResult($title, $download, $size, $datetime, $page, $hash, $seeds, $leechs, $category) {
        $this->results[] = array(
            'tite' => $title,
            'download' => $download,
            'size' => $size,
            'datetime' => $datetime,
            'page' => $page,
            'hash' => $hash,
            'seeds' => $seeds,
            'leechs' => $leechs,
            'category' => $category
            );
    }

    public function count() {
        return count($this->results);
    }
}

?>