<?php
class SynoDLMSearchNGPirateBay {
    private $url = "http://api.t411.io/";
    private $token = 0;
        private $aurl = 'http://www.webproxy.net/view?q=';
        private $qurl = 'https://pirateproxy.sx/search/%s/0/7/0';
        private $purl = 'https://pirateproxy.sx';
    private $debug  = 1;

    public function __construct() {
    }

    private function DebugLog($str) {
        if ($this->debug==1) {
            file_put_contents('/tmp/t411_dlm.log',$str,FILE_APPEND);
        }
    }

    private function auth() {
        require('config.php');
    }

    public function prepare($curl, $query) {

    }

        public function old_prepare($curl, $query) {
                $url = $this->aurl.$this->qurl;
                curl_setopt($curl, CURLOPT_COOKIE, "language=en_EN");
                curl_setopt($curl, CURLOPT_FAILONERROR, 1);
                curl_setopt($curl, CURLOPT_REFERER, $url);
                curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
                curl_setopt($curl, CURLOPT_RETURNTRANSFER,1);
                curl_setopt($curl, CURLOPT_TIMEOUT, 20);
                curl_setopt($curl, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows; U; Windows NT 5.1; en; rv:1.9.0.4) Gecko/2008102920 AdCentriaIM/1.7 Firefox/3.0.4');
                //curl_setopt($curl, CURLOPT_ENCODING, 'deflate');
                curl_setopt($curl, CURLOPT_URL, sprintf($url, urlencode($query)));
                if($this->aurl==''){
                        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
                        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);                
                }
        }

        public function old_parse($plugin, $response) {

                $this->DebugLog($response);

                $regexp2 = "<tr(.*)<\/tr>";

                $regexp_category      = ".*category\">(.*)<\/a>.*category\">(.*)<\/a>";
                if($this->aurl==''){
                        $regexp_title_page    = "(\/torrent\/.*)\".*>(.*)<\/a>";
                }else{
                        $regexp_title_page    = "(\%2Ftorrent\%2F.*)\".*>(.*)<\/a>";
                }
                $regexp_download_mg   = "<a href=\"(magnet:.*)\"";
                $regexp_datetime_size = "Uploaded (.*), Size (.*)&nbsp;(.*),";
                $regexp_seeds_leechs  = "<td .*>([0-9]+)<\/td>.*<td .*>([0-9]+)<\/td>";

                $res=0;
                if(preg_match_all("/$regexp2/siU", $response, $matches2, PREG_SET_ORDER)) {
                        foreach($matches2 as $match2) {

                                $title="Unknown title";
                                $download="Unknown download";
                                $size=0;
                                $datetime="1900-12-31";
                                $page="Default page";
                                $hash="Hash unknown";
                                $seeds=0; 
                                $leechs=0;
                                $category="Unknown category";                                                                

                                if(preg_match_all("/$regexp_category/siU", $match2[0], $matches, PREG_SET_ORDER)) {
                                        foreach($matches as $match) {
                                                $category =  $match[1].": ".$match[2];
                                        }
                                }

                                if(preg_match_all("/$regexp_title_page/siU", $match2[0], $matches, PREG_SET_ORDER)) {
                                        foreach($matches as $match) {
                                                $page = $this->aurl.$this->purl.$match[1];
                                                $title =  $match[2];
                                                $hash = md5($title);
                                        }
                                }

                                if(preg_match_all("/$regexp_download_mg/siU", $match2[0], $matches, PREG_SET_ORDER)) {
                                        foreach($matches as $match) {
                                                $download = $match[1];
                                        }
                                }


                                if(preg_match_all("/$regexp_datetime_size/siU", $match2[0], $matches, PREG_SET_ORDER)) {
                                        foreach($matches as $match) {
                                                $datetime = $match[1];  // casting various date formats to synology-friendly as UTC
                                                if (preg_match('/^(\d{2})-(\d{2})&nbsp;(\d{4})$/i', $datetime, $dateparts)==1){
                                                        $datetime = $dateparts[3]."-".$dateparts[1]."-".$dateparts[2];
                                                }elseif (preg_match('/^(\d{2}-\d{2}&nbsp;\d{2}:\d{2})$/i', $datetime, $dateparts)==1){
                                                        $datetime = date("Y")."-".$dateparts[1];
                                                }elseif (preg_match('/^(Today)&nbsp;(\d{2}:\d{2})$/i', $datetime, $dateparts)==1){
                                                        $datetime = date("Y-m-d")." ".$dateparts[2];
                                                }elseif (preg_match('/^(Y-day)&nbsp;(\d{2}:\d{2})$/i', $datetime, $dateparts)==1){
                                                        $datetime = date('Y-m-d', strtotime('-1 day'))." ".$dateparts[2];
                                                }elseif (preg_match('/^<b>(\d{1,2})&nbsp;mins&nbsp;ago<\/b>$/i', $datetime, $dateparts)==1){
                                                        $datetime = date('Y-m-d G:i', strtotime("-$dateparts[1] min ".date("Z")*(-1)." sec"));
                                                }

                                                $size = str_replace(",",".",$match[2]);
                                                $size_dim =  $match[3];
                                                switch ($size_dim){
                                                     case 'KiB':
                                                             $size = $size * 1024;
                                                             break;
                                                     case 'MiB':
                                                             $size = $size * 1024 * 1024;
                                                             break;
                                                     case 'GiB': 
                                                             $size = $size * 1024 * 1024 * 1024;
                                                             break;
                                                }
                                                $size = floor($size);
                                        }
                                }

                                if(preg_match_all("/$regexp_seeds_leechs/siU", $match2[0], $matches, PREG_SET_ORDER)) {
                                        foreach($matches as $match) {
                                                $seeds = $match[1];
                                                $leechs= $match[2];
                                        }
                                }
                

                                if ($title!="Unknown title") {
                                        $plugin->addResult($title, $download, $size, $datetime, $page, $hash, $seeds, $leechs, $category);
                                        $res++;
                                }
                        }
                }

                return $res;       
        }
}
?>