<?php

if(isset($_GET['page']))
{
    if($_GET['page'] == 'translated-files')
    {
        $dir = "../includes/translated_files";
        $files = scandir($dir);

        $i = 0;
        $rows = '';
        foreach ($files as $file) {
            if ($file != "." && $file != "..") {
                $i++;
                $size = filesize($dir . "/" . $file);
                $modified = date("Y-m-d H:i:s", filemtime($dir . "/" . $file));
            $rows .= '<tr>
                    <td>'.$i.'</td>
                    <td>'.$file.'</td>
                    <td>'.number_format($size/1024, 2).' KB</td>
                    <td>'.date('F d, Y (H:i:s A)', strtotime($modified)).'</td>
                    <td>
                    <a type="button" class="btn btn-sm btn-primary text-white" download="'.$file.'" href="'.$dir.'/'.$file.'">Download File</a>
                    </td>
                </tr>';
            }
        }

        echo $rows;
    }
    elseif($_GET['page'] == 'uploaded-files')
    {
        $dir = "../includes/uploaded_files";
        $files = scandir($dir);

        $i = 0;
        $rows = '';
        foreach ($files as $file) {
            if ($file != "." && $file != "..") {
                $i++;
                $size = filesize($dir . "/" . $file);
                $modified = date("Y-m-d H:i:s", filemtime($dir . "/" . $file));
            $rows .= '<tr>
                    <td>'.$i.'</td>
                    <td>'.$file.'</td>
                    <td>'.number_format($size/1024, 2).' KB</td>
                    <td>'.date('F d, Y (H:i:s A)', strtotime($modified)).'</td>
                    <td>
                    <a type="button" class="btn btn-sm btn-primary text-white" download="'.$file.'" href="'.$dir.'/'.$file.'">Download File</a>
                    </td>
                </tr>';
            }
        }

        echo $rows;
    }
}