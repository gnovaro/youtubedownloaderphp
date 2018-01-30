<?php
/**
 * Author: Gustavo Novaro
 * @version 1.0.4
 * Youtube2Mp3 Downloader page
 *
 * Requisites
 * youtube-dl
 * https://rg3.github.io/youtube-dl/download.html
 *
 * ffmpeg
 */
error_reporting(E_ALL);
if(!empty($_POST['url']))
{
    $url = trim($_POST['url']);
    $command_filename = "youtube-dl $url -f 140 --get-filename";

    $file_name = exec(escapeshellcmd($command_filename));
    //echo $file_name.'<br />';

    $command = "youtube-dl $url -f 140";

    $response = exec(escapeshellcmd($command));

    $file_name_destination = str_replace('.m4a','.mp3',$file_name);

    $command_convert = "ffmpeg -i $file_name -f mp3 $file_name_destination";
    //echo  $command_convert."<br />";
    $response = exec($command_convert);
    //var_dump($response);
    if(file_exists($file_name))
    {
        //Borro el archivo original el .m4a
        unlink($file_name);
    }
}
?>
<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>YouTube Downloader</title>
    <style>
    .container {
        width: 90%;
        margin-left: auto;
        margin-right: auto;
    }
    </style>
</head>
<body>
    <div class="container">
        <form method="post">
            <label>Youtube URL</label>
            <input type="url" name="url" placeholder="https://www.youtube.com/watch?v=XXXXX" required="required">
            <button type="submit">Descargar</button>
        </form>
        <?php
        if(!empty($file_name_destination)) {
            echo '<a href="'.$file_name_destination.'" target="_blank">Descargar Mp3</a>';
        }
        ?>
    </div>
</body>
</html>
