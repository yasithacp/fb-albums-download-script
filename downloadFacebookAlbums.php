<?php
//Change this to your access key
define('ACCESS_KEY', 'CAACEdEose0cBAOTVQAby8CEOxZAqqPgO1xycy0apF4nyp2G40CfKZCs9Iuy2UWxp54TYILYgRatQ1fodklpKKi9YYkpiCyTHs1XA6d6qwcZBLDgCkMu5p8C0fnpuUoUHXAvmO6ZBscAvvbqMEZC1a7AVXXFSgAYrUNZB5rAqQYI9xXVxZCRzQzjDWGJW5K8ZCaFjK8r7OFX9ZCX8aEmwZBp8IJCFxVURUAvZARZCoYjiRUBQtAZDZD');
define('API_HOST', 'https://graph.facebook.com/');
define('VERSION', 'v2.3');
define('FOLDER_PATH', '/Users/yasitha/facebook_albums/');

//Begin the downloading script
$albums = get('me?fields=albums{id,name}&');

echo sizeof($albums->albums->data)." albums found...\n";

foreach ($albums->albums->data as $album) {
  echo "Downloading photos of " . $album->name . " album...\n";
  $photos = get($album->id.'?fields=photos{source}&');
  echo sizeof($photos->photos->data)." photos found...\n";
  if (!file_exists(FOLDER_PATH . $album->name)) {
    mkdir(FOLDER_PATH . $album->name, 0777, true);
  }
  $i = 1;
  foreach ($photos->photos->data as $photo) {
    //file_put_contents(FOLDER_PATH.$album->name.'/image_'.$i.'.jpg' , file_get_contents($photo->source));
    $i++;
  }
}

echo "Download completed...";

function get($path) {
  $url = API_HOST.VERSION.'/'.$path.'access_token='.ACCESS_KEY;
  print_r($url);
  $ch = curl_init();
  curl_setopt($ch, CURLOPT_URL, $url);
  curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
  curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 300);
  $res = curl_exec($ch);
  $error_message = curl_error($ch);
  $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
  curl_close($ch);
  return format_result(json_decode($res), $code, $error_message);
}

function format_result($obj, $http_code, $error_message=NULL) {
  if (empty($obj)) { $obj = new \stdClass(); }
  if(is_array($obj)) {
    $temp = $obj;
    $obj = new \stdClass();
    $obj->result_set = $temp;
  }
  if (isset($obj->error)) {
    $obj->success = false;
    if (is_a($obj->error, "stdClass")) {
      $e = Array();
      foreach ($obj->error as $key => $values) {
        foreach ($values as $value) {
          array_push($e, $key . ': ' . $value);
        }
      }
      $obj->error = $e;
    }
    else {
      $obj->error = Array('Error: ' . $obj->error);
    }
  }
  else if (!in_array($http_code, array(200, 201, 204))) {
    $obj->success = false;
    $obj->error = array($error_message);
  }
  else {
    $obj->success = true;
  }
  $obj->api_http_code = $http_code;
  return $obj;
}
?>
