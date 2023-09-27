<?php

require '../vendor/autoload.php';

use Symfony\Component\Yaml\Yaml;

$parser = new Parsedown();

//API Key
$api_file = '../includes/settings/api_key.txt';
$apifp = fopen($api_file, 'r');
$apiKey = '';
while (!feof($apifp)) {
  $apiKey = fgets($apifp);
}

//Exclusions
$exc_file = '../includes/settings/exclusions.txt';
$excfp = fopen($exc_file, 'r');
$exc = '';
while (!feof($excfp)) {
  $exc = fgets($excfp);
}

$exclusions = explode(',',$exc);


//Text Exclusions
$exc_file2 = '../includes/settings/exclusions_in_text.txt';
$excfp2 = fopen($exc_file2, 'r');
$exc2 = '';
while (!feof($excfp2)) {
  $exc2 = fgets($excfp2);
}

$exclusions2 = explode(',',$exc2);

$files = $_FILES['upload_files'];

if (!isset($_FILES['upload_files'])) {
    header('Location: ../translator.php?success=false&message=No file found to be translated!');   
}

$names = $_FILES['upload_files']['name'];
$full_paths = $_FILES['upload_files']['full_path'];
$types = $_FILES['upload_files']['type'];
$tmp_names = $_FILES['upload_files']['tmp_name'];
$errors = $_FILES['upload_files']['error'];
$sizes = $_FILES['upload_files']['size'];

$translated_files = array();

for($i = 0; $i < count($names); $i++)
{
    if (isset($names[$i])) {
        $target_dir = '../includes/uploaded_files/';
        $target_file = $target_dir . $names[$i];

        $uploader = move_uploaded_file($tmp_names[$i], $target_file);

        if (!$uploader) {
          header('Location: ../translator.php?success=false&message=File failed to upload ('.$names[$i].')');   
        }
    }
}

// Get the target languages
if($_POST['language_group'] == 'list')
{
  if (!isset($_POST['languages'])) {
    header('Location: ../translator.php?success=false&message=No Language selected for translation');   
  }
  $target_languages = $_POST['languages'];
}
elseif($_POST['language_group'] == 'all')
{
  $all_lang_file = '../includes/settings/languages.txt';
  $langfp = fopen($all_lang_file, 'r');
  $langs = '';
  while (!feof($langfp)) {
    $langs = strtoupper(fgets($langfp));
  }
  $target_languages = explode(',',$langs);
}
elseif($_POST['language_group'] == 'top')
{
  $top_lang_file = '../includes/settings/top_languages.txt';
  $langfp = fopen($top_lang_file, 'r');
  $langs = '';
  while (!feof($langfp)) {
    $langs = strtoupper(fgets($langfp));
  }
  $target_languages = explode(',',$langs);
}

foreach ($target_languages as $target_language)
{
  for($i = 0; $i < count($names); $i++)
  {
    $target_dir = '../includes/uploaded_files/';
    $filename = $target_dir . $names[$i];
    $boom = explode('.',$names[$i]);

    // $fp = fopen($filename, 'r');

    $markdownContent = str_replace("---","",file_get_contents($filename));
    
    $properties = Yaml::parse($markdownContent);

    // echo '<p>';
    foreach ($properties as $key => $value) {
      if (is_array($value)) {
          // Handle child properties
          // echo $key.':'."<br>";
          foreach ($value as $childKey => $childValue) {
            if(is_array($childValue))
            {
              // echo "----->".$childKey.':'."<br>";
              foreach ($childValue as $gchildKey => $gchildValue) {
                  if(is_array($childValue))
                  {
                      // echo "---------->".$gchildKey.':'."<br>";
                      foreach ($gchildValue as $ggchildKey => $ggchildValue)
                      {
                          // echo "-------------------->".$ggchildKey.': '.$ggchildValue."<br>";
                          // echo "-------------------->".$ggchildKey.': '.translate($ggchildKey, $ggchildValue, $exclusions, $exclusions2, $apiKey, $target_language)."<br>";
                          $properties[$key][$childKey][$gchildKey][$ggchildKey] = translate($ggchildKey, $ggchildValue, $exclusions, $exclusions2, $apiKey, $target_language);
                      }
                  }
                  else
                  {
                      // echo "---------->".$gchildKey.': '.$gchildValue."<br>";
                      // echo "---------->".$gchildKey.': '.translate($gchildKey, $gchildValue, $exclusions, $exclusions2, $apiKey, $target_language)."<br>";
                      $properties[$key][$childKey][$gchildKey] = translate($gchildKey, $gchildValue, $exclusions, $exclusions2, $apiKey, $target_language);
                  }  
              }
            }
            else
            {
              // echo "----->".$childKey.': '.$childValue."<br>";
              // echo "----->".$childKey.': '.translate($childKey, $childValue, $exclusions, $exclusions2, $apiKey, $target_language)."<br>";
              $properties[$key][$childKey] = translate($childKey, $childValue, $exclusions, $exclusions2, $apiKey, $target_language);
            }
          }
      } else {
          // Handle top-level properties
          // echo $key.': '.$value."<br>";
          // echo $key.': '.translate($key, $value, $exclusions, $exclusions2, $apiKey, $target_language)."<br>";
          $properties[$key] = translate($key, $value, $exclusions, $exclusions2, $apiKey, $target_language);
      }
  }

    $filename = '../includes/translated_files/'.$boom[0].'.'.strtolower($target_language).'.md';

    array_push($translated_files, $boom[0].'.'.strtolower($target_language).'.md');

    $fp = fopen($filename, 'w');

    $finalContent = "---\n".Yaml::dump($properties)."---";

    $finalContent = str_replace("[{","\n        -\n           ", $finalContent);
    $finalContent = str_replace("}]","", $finalContent);
    $finalContent = str_replace("}, {","\n        -\n           ", $finalContent);
    $finalContent = str_replace("', ","'\n            ", $finalContent);
    $finalContent = str_replace(": ''",": null", $finalContent);
    $finalContent = str_replace(", title: ","\n            title: ", $finalContent);
    $finalContent = str_replace(", name: ","\n            name: ", $finalContent);
    $finalContent = str_replace(", stars: ","\n            stars: ", $finalContent);
    $finalContent = str_replace(", description: ","\n            description: ", $finalContent);
    $finalContent = str_replace(", image: ","\n            image: ", $finalContent);
    
    fwrite($fp, $finalContent);

    fclose($fp);
  }
}

function translate($key, $value, $exclusions, $exclusions2, $apiKey, $target_language)
{
  if(!in_array($key,$exclusions))
  {
    $back_replace_org = array();
    $back_replace_ph = array();

    $startend_rep_text = "|";
    $mid_rep_text = "_";

    $rep_count = 0;
    foreach($exclusions2 as $exc2)
    {
      if(strstr($value, $exc2))
      {
        $rep_count++;
        $rep_text = "";

        if($rep_count > 1)
        {
          for($o = 0; $o < $rep_count; $o++)
          {
            $rep_text .= $startend_rep_text;
          }

          $rep_text .= $mid_rep_text;

          for($o = 0; $o < $rep_count; $o++)
          {
            $rep_text .= $startend_rep_text;
          }
        }
        else
        {
          $rep_text = $startend_rep_text.$mid_rep_text.$startend_rep_text;
        }
        
        $value = str_replace($exc2,$rep_text, $value);

        array_push($back_replace_org, $exc2);
        array_push($back_replace_ph, $rep_text);
      }
    }

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
    $Value = $response->translations[0]->text;

    for($t = 0; $t < count($back_replace_ph); $t++)
    {
      $Value = str_replace($back_replace_ph[$t],$back_replace_org[$t],$Value);
    }

     return $Value;
  }
  else
  {
    return $value;
  }
}

// $folderPath = '../includes/translated_files';
// $zip = new ZipArchive();
// $zipFileName = 'DeepL Translated Files';


// if ($zip->open($zipFileName, ZipArchive::CREATE) === true) {
//     foreach($translated_files as $translated_file)
//     {
//       $mdfilename = '../includes/translated_files/'.$translated_file;

//       if($zip->addFile($mdfilename, $translated_file))
//       {
//         echo $translated_file.' added in zip<br>';
//       }
//       else
//       {
//         echo $translated_file.' not added in zip<br>';
//       }
//     } 
//     $zip->close();
// }

// // Send the ZIP file as a download
// header('Content-Type: application/zip');
// header('Content-Disposition: attachment; filename="' . $zipFileName . '"');
// header('Content-Length: ' . filesize($zipFileName));

// readfile($zipFileName);

// unlink($zipFileName);

    header('Location: ../translated-files.php?success=true&message=Files have been translated and uploaded!');
    // exit;
// } else {
//     header('Location: ../translated-files.php?success=false&message=Files have been translated and uploaded, but there was an error making the zip file');
// }