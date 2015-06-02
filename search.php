<?php
class SynoDLMSearchT411 {
    private $aurl = "http://api.t411.io/auth"; //auth url
    private $qurl = "http://api.t411.io/torrents/search/"; // search url
    private $dlurl = "http://api.t411.io/torrents/download/"; // torrent download url
    private $purl = "http://www.t411.io/torrents/"; // torrent page url
    private $uid = 0;
    private $token = 0;
    private $debug  = 1;


    public function __construct() {
        file_put_contents('/tmp/t411_dlm.log', date('m/d/Y h:i:s a', time()));
    }

    private function DebugLog($str) {
        if ($this->debug==1) {
            file_put_contents('/tmp/t411_dlm.log', $str . "\n************\n", FILE_APPEND);
        }
    }

    private function auth($username, $password) {
        DebugLog('auth:');
        // get auth variables
        $auth = 'username=' . $username . '&password=' . $password;

        // query with curl
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->aurl);
        curl_setopt($ch,CURLOPT_POST, 2);
        curl_setopt($ch,CURLOPT_POSTFIELDS, $auth);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 

        $body = curl_exec($ch);
        curl_close($ch);

        // now, process the JSON string
        $json = json_decode($body);
        $this->token = $json->token;
        $this->uid = $json->uid;

        DebugLog('    DONE');
    }

    public function VerifyAccount($username, $password) {
        DebugLog('VerifyAccount:');
        DebugLog("   username: $username");
        DebugLog("   password: $password");
        DebugLog('    DONE');
        return true;
    }


    public function prepare($curl, $query, $username, $password) { 
        DebugLog('prepare:');
        DebugLog("   username: $username");
        DebugLog("   username: $username");
        DebugLog("   query: $query");
        
        $url = $this->qurl . urlencode($query);

        echo "search: $search\n\n";

        $this->auth($username, $password);

        curl_setopt($curl, CURLOPT_HTTPHEADER, array ('Authorization: '. $this->token) );
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);

        DebugLog('    DONE');
    }

    public function parse($plugin, $response) {
        DebugLog('parse:');
        DebugLog("   response: $response");

        $json = json_decode($response);

        DebugLog("   response: $json->total");

        foreach ($json->torrents as $value) {
            $plugin->addResult(
                $json->name, //title
                $this->dlurl . $json->id, //download link
                $json->size, //size
                $json->added, //datetime, format 2010-12-30 13:20:10
                $this->purl . $json->rewritename, //torrent page
                '', //hash, can be empty
                $json->seeders, // seeds
                $json->leechers, // leechs
                $json->categoryname // category
                );
        }

        DebugLog('    DONE');

        return $json->total;
    }


// $title: string
// The torrent title.
// $download: string
// URL of the torrent file.
// $size: integer or float
// The file size of the torrent.
// $datetime: string
// The added time of torrent file in search server with
// format such as "2010-12-30 13:20:10"
// $page: string
// URL to the page referring this torrent. This page
// usually contain torrent detailed information
// $hash: string
// The hash value of the torrent. The value could be
// empty string.
// $seeds: integer
// The number of seeders of this torrent
// $leechs: integer
// The number of leechers of this torrent
// $category: string
// The category of this torrent returned by server
}
?>