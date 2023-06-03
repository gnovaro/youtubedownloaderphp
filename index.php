<?php
/**
 * Author: Gustavo Novaro
 * @version 2.0.0
 * Youtube2Mp3 Downloader page
 *
 * Requisites
 * yt-dlp
 * https://github.com/yt-dlp/yt-dlp
 *
 * ffmpeg
 */
//error_reporting(E_ALL);
if(!empty($_POST['url']))
{
    $url = trim($_POST['url']);
    $command_filename = "yt-dlp -f 140 --get-filename $url";

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
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>YouTube Downloader</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"
          integrity="sha384-9ndCyUaIbzAi2FUVXJi0CjmCapSmO7SnpJef0486qhLnuZ2cdeRhO02iuK6FUUVM" crossorigin="anonymous">
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
            <label for="url">Youtube URL</label>
            <input type="url" name="url" id="url" placeholder="https://www.youtube.com/watch?v=XXXXX" required="required">
            <button type="submit">Download</button>
        </form>
        <?php
        if(!empty($file_name_destination)) {
            echo '<a href="'.$file_name_destination.'" target="_blank">Download Mp3</a>';
        }
        ?>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"
            integrity="sha384-geWF76RCwLtnZ8qwWowPQNguL3RmwHVBC9FhGdlKrxdiJJigb/j/68SIy3Te4Bkz" crossorigin="anonymous"></script>
    </body>
</html>
