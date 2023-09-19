<?php

//API Key
$api_file = 'includes/settings/api_key.txt';
$apifp = fopen($api_file, 'r');
$apiKey = '';
while (!feof($apifp)) {
  $apiKey = fgets($apifp);
}

//Exclusions
$exc_file = 'includes/settings/exclusions.txt';
$excfp = fopen($exc_file, 'r');
$exc = '';
while (!feof($excfp)) {
  $exc = fgets($excfp);
}

$exclusions = explode(',',$exc);

function convert_content_to_properties($properties) {
  $content = "---\n";

  foreach ($properties as $key => $value) {
    $content .= $key . ': ' . $value."\n";
  }

  $content .= "---";

  return $content;
}

$files = $_FILES['upload_files'];

if (isset($_FILES['upload_files'])) {
  header('Location: ../translator.html?success=false&message=No file found to be translated!');   
}

$names = $_FILES['upload_files']['name'];
$full_paths = $_FILES['upload_files']['full_path'];
$types = $_FILES['upload_files']['type'];
$tmp_names = $_FILES['upload_files']['tmp_name'];
$errors = $_FILES['upload_files']['error'];
$sizes = $_FILES['upload_files']['size'];

for($i = 0; $i < count($names); $i++)
{
    if (isset($names[$i])) {
        $target_dir = 'includes/uploaded_files/';
        $target_file = $target_dir . $names[$i];

        $uploader = move_uploaded_file($tmp_names[$i], $target_file);

        if (!$uploader) {
          header('Location: ../translator.html?success=false&message=File failed to upload ('.$names[$i].')');   
        }
    }
}

// Get the target languages
if($_POST['language_group'] == 'list')
{
  $target_languages = $_POST['languages'];
}
elseif($_POST['language_group'] == 'all')
{
  $all_lang_file = 'includes/settings/languages.txt';
  $langfp = fopen($all_lang_file, 'r');
  $langs = '';
  while (!feof($langfp)) {
    $langs = strtoupper(fgets($langfp));
  }
  $target_languages = explode(',',$langs);
}
elseif($_POST['language_group'] == 'top')
{
  $top_lang_file = 'includes/settings/top_languages.txt';
  $langfp = fopen($top_lang_file, 'r');
  $langs = '';
  while (!feof($langfp)) {
    $langs = strtoupper(fgets($langfp));
  }
  $target_languages = explode(',',$langs);
}

if (count($_POST['languages']) == 0) {
  header('Location: ../translator.html?success=false&message=No Language selected for translation');   
}

foreach ($target_languages as $target_language) {
  for($i = 0; $i < count($names); $i++)
  {
    $target_dir = 'includes/uploaded_files/';
    $filename = $target_dir . $names[$i];
    $boom = explode('.',$names[$i]);

    $fp = fopen($filename, 'r');

    $properties = [];

    $regex = '/^([-\w]+):\s+(.*)$/m';

    while (!feof($fp)) {
      $line = fgets($fp);

      if (preg_match($regex, $line, $matches)) {
        $properties[$matches[1]] = $matches[2];
      }
    }

    fclose($fp);

    $lines = array();

    foreach ($properties as $key => $value) {
      if(!in_array($key,$exclusions))
      {
        
        $curl = curl_init();

        curl_setopt_array($curl, array(
          CURLOPT_URL => 'https://api-free.deepl.com/v2/translate',
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_ENCODING => '',
          CURLOPT_MAXREDIRS => 10,
          CURLOPT_TIMEOUT => 0,
          CURLOPT_FOLLOWLOCATION => true,
          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
          CURLOPT_CUSTOMREQUEST => 'POST',
          CURLOPT_POSTFIELDS =>'{
            "text" : ["'.$value.'"],
            "target_lang" : "'.$target_language.'"
        }',
          CURLOPT_HTTPHEADER => array(
            'Authorization: DeepL-Auth-Key '.$apiKey,
            'Content-Type: application/json'
          ),
        ));

        $response = json_decode(curl_exec($curl));

        curl_close($curl);
        $properties[$key] = $response->translations[0]->text;
      }
      else
      {
        $properties[$key] = $value;
      }
    }

    $content = convert_content_to_properties($properties);

    $filename = 'includes/translated_files/'.$boom[0].'.'.strtolower($target_language).'.md';

    $fp = fopen($filename, 'w');
    // print_r($content);
    fwrite($fp, $content);

    fclose($fp);
  }
}

header('Location: ../translated-files.html?success=true&message=Files have been translated and uploaded!');