<?php
/**
 * Author: Gustavo Novaro
 * @version 1.0.5
 * Youtube2Mp3 Downloader page
 *
 * Requisites
 * youtube-dl
 * https://rg3.github.io/youtube-dl/download.html
 *
 * ffmpeg
 */
//error_reporting(E_ALL);
if(!empty($_POST['url']))
{
    $url = trim($_POST['url']);
    $command_filename = "youtube-dl $url -f 140 --get-filename";

    $file_name = exec(escapeshellcmd($command_filename));

    $command = "youtube-dl $url -f 140";

    $response = exec(escapeshellcmd($command));

    $file_name_destination = str_replace('.m4a','.mp3',$file_name);

    $command_convert = "ffmpeg -i $file_name -f mp3 $file_name_destination";
    
    $response = exec($command_convert);
    if(file_exists($file_name))
    {
        //Delete original .m4a file
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
            <button type="submit">Download</button>
        </form>
        <?php
        if(!empty($file_name_destination)) {
            echo '<a href="'.$file_name_destination.'" target="_blank">Download Mp3</a>';
        }
        ?>
    </div>
</body>
</html>
