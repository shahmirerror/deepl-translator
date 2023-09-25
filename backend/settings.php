<?php

//API Settings
if(isset($_POST['setting']))
{
  if($_POST['setting'] == 'apiKey')
  {
    $api_file = '../includes/settings/api_key.txt';
    $apifp = fopen($api_file, 'w');
    $apiKey = $_POST['apiKey'];
    fwrite($apifp, $apiKey);

    fclose($apifp);

    header('Location: ../settings.php?success=true&message=API Key has been updated!');
  }
  elseif($_POST['setting'] == 'exclusions')
  {
    //Exclusions Settings
    $exc_file = '../includes/settings/exclusions.txt';
    $excfp = fopen($exc_file, 'w');
    $exc = $_POST['excludes'];

    fwrite($excfp, $exc);

    fclose($excfp);

    header('Location: ../settings.php?success=true&message=Exclusions have been updated!');
  }
  elseif($_POST['setting'] == 'languages')
  {
    //Languages Settings
    $lang_file = '../includes/settings/languages.txt';
    $langfp = fopen($lang_file, 'w');
    $langs = strtoupper($_POST['langs']);

    fwrite($langfp, $langs);

    fclose($langfp);

    header('Location: ../settings.php?success=true&message=Language List has been updated!');
  }
  elseif($_POST['setting'] == 'top_languages')
  {
    //Languages Settings
    $toplang_file = '../includes/settings/top_languages.txt';
    $toplangfp = fopen($toplang_file, 'w');
    $toplangs = implode(',',$_POST['toplangs']);

    fwrite($toplangfp, $toplangs);

    fclose($toplangfp);

    header('Location: ../settings.php?success=true&message=Top Languages have been updated!');
  }
}
else
{
  header('Location: ../settings.php');
}