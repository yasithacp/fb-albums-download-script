<?php
//Change this to your access key
define('ACCESS_KEY', 'CAACEdEose0cBAFHsE7L8lXX8gYnZCeYfLrcLYILu0QlRuFIymDyFZAxoCOZBYyZBZAA91qtHxg3C5Joj7FfU6xcPBb8BDnHTtZABAH3mZC4rpzp6KSDCJgSbUyUo37icFBTCcpUYMt9dKcoZBZA5lpF5CVMSPNyjJNQAn9ngHGhRE0gZBZAUZCYT0v5RPClviw8WIMZAQ88mC7vFu9sh3J4tUTZC1ICZCKCwZCwpFw1omftuoTZBPYwZDZD');
define('API_HOST', 'https://graph.facebook.com/');
define('VERSION', 'v2.3');
define('FOLDER_PATH', '/Users/yasitha/facebook_albums/');

//Begin the downloading script
echo "Gathering album's information...\n";
$stock = array();
$albums = get('me?fields=albums{id,name}&');

foreach ($albums->albums->data as $album) {
  array_push($stock, $album);
}

if(isset($albums->albums->paging->next)){
  $nextUrl = $albums->albums->paging->next;

  while(isset($nextUrl)){
    $albums = getNext($nextUrl);
    foreach ($albums->data as $album) {
      array_push($stock, $album);
    }
    $nextUrl = isset($albums->paging->next) ? $albums->paging->next : null;
  }
}

echo sizeof($stock)." albums found...\n";

foreach ($stock as $album) {
  echo "Downloading photos of " . $album->name . " album...\n";
  $photos = get($album->id.'?fields=photos{source}&');
  $picsList = array();

  foreach ($photos->photos->data as $photo) {
    array_push($picsList, $photo);
  }

  if(isset($photos->photos->paging->next)){
    $nextUrl = $photos->photos->paging->next;

    while(isset($nextUrl)){
      $photos = getNext($nextUrl);
      foreach ($photos->data as $photo) {
        array_push($picsList, $photo);
      }
      $nextUrl = isset($photos->paging->next) ? $photos->paging->next : null;
    }
  }

  echo sizeof($picsList)." photos found...\n";
  echo "Creating album directory...\n";

  if (!file_exists(FOLDER_PATH . $album->name)) {
    mkdir(FOLDER_PATH . $album->name, 0777, true);
  }
  $i = 1;
  foreach ($picsList as $pic) {
    file_put_contents(FOLDER_PATH.$album->name.'/image_'.$i.'.jpg' , file_get_contents($pic->source));
    progressBar($i, sizeof($picsList));
    $i++;
  }
  echo "\n";
}

echo "Download completed..." . $i ;

function get($path) {
  $url = API_HOST.VERSION.'/'.$path.'access_token='.ACCESS_KEY;
  $ch = curl_init();
  curl_setopt($ch, CURLOPT_URL, $url);
  curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
  curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 300);
  $res = curl_exec($ch);
  $error_message = curl_error($ch);
  $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
  curl_close($ch);
  return format_result(json_decode($res));
}

function getNext($url) {
  $url = $url;
  $ch = curl_init();
  curl_setopt($ch, CURLOPT_URL, $url);
  curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
  curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 300);
  $res = curl_exec($ch);
  $error_message = curl_error($ch);
  $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
  curl_close($ch);
  return format_result(json_decode($res));
}

function format_result($response) {
  if(isset($response->error)){
    echo "Error occurred: " . $response->error->message . "\n";
    echo "Terminating...\n";
    exit();
  }
  return $response;
}

function progressBar($done, $total){
    $perc = ceil(($done / $total) * 100);
    $bar = "[" . ($perc > 0 ? str_repeat("=", $perc - 1) : "") . ">";
    $bar .= str_repeat(" ", 100 - $perc) . "] - $perc% - $done/$total";
    echo "\033[0G$bar";
}
?>
