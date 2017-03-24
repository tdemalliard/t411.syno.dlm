<?php
// api download torrent url
$ext = 'ai';
$url = 'https://api.t411.' . $ext .'/torrents/download/'. $_GET['torrentid'];

// curl for download
$curl = curl_init();
curl_setopt($curl, CURLOPT_HTTPHEADER, array ('Authorization: '. $_GET['token'] ) );
curl_setopt($curl, CURLOPT_URL, $url);
curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);

$response = curl_exec($curl);
curl_close($curl);

// serve torrent file
header( 'Content-Type: application/x-bittorrent' );
header( 'Content-Disposition: inline; filename="' . $_GET['torrentid'] . '.torrent"' );
echo $response;


?>
