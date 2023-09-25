<?php

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

function convert_content_to_properties($properties) {
  $content = "---\n";

  foreach ($properties as $key => $value) {
    $content .= $key . ': ' . $value."\n";
  }

  $content .= "---";

  return $content;
}

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

if (count($_POST['languages']) == 0) {
  header('Location: ../translator.php?success=false&message=No Language selected for translation');   
}

foreach ($target_languages as $target_language) {
  for($i = 0; $i < count($names); $i++)
  {
    $target_dir = '../includes/uploaded_files/';
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

    $filename = '../includes/translated_files/'.$boom[0].'.'.strtolower($target_language).'.md';

    array_push($translated_files, $boom[0].'.'.strtolower($target_language).'.md');

    $fp = fopen($filename, 'w');
    // print_r($content);
    fwrite($fp, $content);

    fclose($fp);
  }
}
$folderPath = '../includes/translated_files';
$zipFilePath = 'output.zip'; // Name of the output zip file

$zip = fopen($zipFilePath, 'wb');

// if ($zip) {
//     // Create a recursive directory iterator to traverse the folder
//     $files = new RecursiveIteratorIterator(
//         new RecursiveDirectoryIterator($folderPath),
//         RecursiveIteratorIterator::LEAVES_ONLY
//     );

//     foreach ($files as $file) {
//         if (!$file->isDir() && in_array($file->getSubPathName(), $translated_files)) {
//             // Get real path and relative path
//             $filePath = $file->getRealPath();
//             $relativePath = substr($filePath, strlen($folderPath) + 1);

//             // Write local file header
//             $header = "\x50\x4B\x03\x04";
//             $header .= "\x14\x00"; // Version needed to extract (minimum)
//             $header .= "\x00\x00"; // General purpose bit flag
//             $header .= "\x08\x00"; // Compression method (deflate)
//             $header .= pack('V', filemtime($filePath)); // Last modified time
//             $header .= pack('V', crc32(file_get_contents($filePath))); // CRC32 checksum
//             $header .= pack('V', filesize($filePath)); // Compressed size
//             $header .= pack('V', filesize($filePath)); // Uncompressed size
//             $header .= pack('v', strlen($relativePath)); // File name length
//             $header .= "\x00\x00"; // Extra field length
//             fwrite($zip, $header . $relativePath);

//             // Write file content
//             fwrite($zip, file_get_contents($filePath));
//         }
//     }

//     // Write central directory end
//     $cdEnd = "\x50\x4B\x05\x06\x00\x00\x00\x00";
//     $cdEnd .= pack('v', iterator_count($files)); // Number of entries on this disk
//     $cdEnd .= pack('v', iterator_count($files)); // Total number of entries
//     $cdEnd .= pack('V', strlen($cdEnd)); // Size of central directory
//     $cdEnd .= pack('V', ftell($zip)); // Offset of start of central directory
//     $cdEnd .= "\x00\x00"; // Comment length
//     fwrite($zip, $cdEnd);

//     fclose($zip);

//     // Set headers for download
//     header('Content-Type: application/zip');
//     header('Content-Disposition: attachment; filename="' . $zipFilePath . '"');
//     header('Content-Length: ' . filesize($zipFilePath));

//     // Output the zip file
//     readfile($zipFilePath);

//     // Optionally, delete the zip file after download
//     unlink($zipFilePath);
    header('Location: ../translated-files.php?success=true&message=Files have been translated and uploaded!');
    exit;
// } else {
//     header('Location: ../translated-files.php?success=false&message=Files have been translated and uploaded, but there was an error making the zip file');
// }