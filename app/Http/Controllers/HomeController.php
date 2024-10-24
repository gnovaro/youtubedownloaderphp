<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Process;
use App\Helpers\Text;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        // If you want to disable home auth just comment this line!
        //$this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index(Request $request)
    {
        $data = [];
        if(!empty($request->url))
        {
            $url = trim($request->url);
            $command_filename = "yt-dlp -f 140 --get-filename $url";
            $file_name = exec(escapeshellcmd($command_filename));
            $file_name = str_replace('  ', '_', $file_name);
            $file_name = str_replace(['|', '[', ']', '(', ')', "'"], '', $file_name);
            $file_name = str_replace(' ', '_', $file_name);
            $command = "yt-dlp $url -f 140 --output $file_name";
            try {
                $result = Process::run(escapeshellcmd($command));
            } catch (\Throwable $t) {
                dd($t->getMessage());
            }

            if ($result->failed()) {
                $data['error'] = true;
                Log::error("Error executing: $command");
            }

            $file_name_destination = str_replace('.m4a','.mp3', $file_name);

            $command_convert = "ffmpeg -i $file_name -f mp3 $file_name_destination";
            try {
                $result = Process::run(escapeshellcmd($command_convert));
            } catch (\Throwable $t) {
                Log::error("Error executing: $command_convert");
            }

            if ($result->failed()) {
                $data['error'] = true;
                Log::error("Error executing: $command_convert");
            }

            if(file_exists($file_name))
            {
                //Delete original .m4a file
                unlink($file_name);
            }
            $data['file_name_destination'] = $file_name_destination;
        }
        return view('home', $data);
    }
}
