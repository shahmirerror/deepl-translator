<?php

if(isset($_GET['page']))
{
    if($_GET['page'] == 'translator')
    {
        $target_dir = '../includes/settings/languages.txt';
        $fp = fopen($target_dir, 'r');

        $content = '<div class="language_list">';
                while (!feof($fp)) {
                        $line = fgets($fp);
                        $langs = explode(',',$line);
                        $option = '';
                        foreach($langs as $lang)
                        {
                            $option .= '<div class="form-check form-check-inline">
                                            <input type="checkbox" class="form-check-input" name="languages[]" value="'.strtoupper($lang).'">
                                            <label class="form-check-label">'.strtoupper($lang).'</label>
                                        </div>';
                        }
                        $content .= $option;
                    }
        $content .= '</div>';

        echo $content;
    }
    elseif($_GET['page'] == 'settings')
    {
        echo '<div class="row">
            <div class="col-md-3">
                <a href="./settings.html?page=API Key" type="button" class="btn btn-lg btn-info text-white">API Key</a>
            </div>
            <div class="col-md-3">
                <a href="./settings.html?page=Exclusions" type="button" class="btn btn-lg btn-info text-white">Exclusions</a>
            </div>
            <div class="col-md-3">
                <a href="./settings.html?page=Languages" type="button" class="btn btn-lg btn-info text-white">Languages</a>
            </div>
            <div class="col-md-3">
                <a href="./settings.html?page=Top Languages" type="button" class="btn btn-lg btn-info text-white">Top Languages</a>
            </div>
        </div>';
    }
    elseif($_GET['page'] == 'api-settings')
    {
        $target_dir = '../includes/settings/api_key.txt';
        $fp = fopen($target_dir, 'r');
        $line = '';
        while (!feof($fp)) {
            $line = fgets($fp);
        }

        echo '<form class="form-floating" action="./backend/settings.php" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="setting" value="apiKey" />
            <div class="row">
                <div class="col-md-4">
                    <input type="text" name="apiKey" id="apiKey" class="form-control" value="'.$line.'" placeholder="Enter your API key here" />
                </div>
            </div>
            <div class="row">
                <div class="col-md-3 mt-3">
                    <button type="submit" class="btn btn-sm btn-success">Update API Key</button>
                </div>
            </div>
        </form>';
    }
    elseif($_GET['page'] == 'exclusions-settings')
    {
        $target_dir = '../includes/settings/exclusions.txt';
        $fp = fopen($target_dir, 'r');
        $line = '';
        while (!feof($fp)) {
            $line = fgets($fp);
        }
        echo '<form class="form-floating" action="./backend/settings.php" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="setting" value="exclusions" />
            <div class="row">
                <div class="col-md-12">
                    <label class="form-label">Enter labels you want excluded by seperating them with commas</label>
                    <textarea name="excludes" id="excludes" class="form-control">'.$line.'</textarea>
                </div>
            </div>
            <div class="row">
                <div class="col-md-3 mt-3">
                    <button type="submit" class="btn btn-sm btn-success">Update Exclusions List</button>
                </div>
            </div>
        </form>';
    }
    elseif($_GET['page'] == 'lang-settings')
    {
        $target_dir = '../includes/settings/languages.txt';
        $fp = fopen($target_dir, 'r');
        $line = '';
        while (!feof($fp)) {
            $line = fgets($fp);
        }
        echo '<form class="form-floating" action="./backend/settings.php" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="setting" value="languages" />
            <div class="row">
                <div class="col-md-12">
                    <label class="form-label">Enter language codes you want by seperating them with commas</label>
                    <textarea name="langs" id="langs" class="form-control">'.$line.'</textarea>
                </div>
            </div>
            <div class="row">
                <div class="col-md-3 mt-3">
                    <button type="submit" class="btn btn-sm btn-success">Update Languages List</button>
                </div>
            </div>
        </form>';
    }
    elseif($_GET['page'] == 'top_lang-settings')
    {
        $target_dir = '../includes/settings/languages.txt';
        $target_dir2 = '../includes/settings/top_languages.txt';
        $fp = fopen($target_dir, 'r');
        $fp2 = fopen($target_dir2, 'r');
        $line = '';
        $line2 = '';
        while (!feof($fp)) {
            $line = fgets($fp);
        }
        $langS = explode(',',$line);
        while (!feof($fp2)) {
            $line2 = fgets($fp2);
        }
        $toplangS = explode(',',$line2);

        echo '<form class="form-floating" action="./backend/settings.php" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="setting" value="top_languages" />
            <div class="row">
                <div class="col-md-12">';
                        $option = '';
                            foreach($langS as $lang)
                            {
                                if(!in_array($lang, $toplangS))
                                {
                                    $option .= '<div class="form-check form-check-inline">
                                                <input type="checkbox" class="form-check-input" name="toplangs[]" value="'.strtoupper($lang).'">
                                                <label class="form-check-label">'.strtoupper($lang).'</label>
                                            </div>';
                                }
                                else
                                {
                                    $option .= '<div class="form-check form-check-inline">
                                                <input type="checkbox" class="form-check-input" name="toplangs[]" value="'.strtoupper($lang).'" checked>
                                                <label class="form-check-label">'.strtoupper($lang).'</label>
                                            </div>';
                                }
                            }
                            echo $option;
            echo '</div>
            </div>
            <div class="row">
                <div class="col-md-3 mt-3">
                    <button type="submit" class="btn btn-sm btn-success">Update Top Languages List</button>
                </div>
            </div>
        </form>';
    }
}