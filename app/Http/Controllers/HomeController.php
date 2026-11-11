<?php

namespace App\Http\Controllers;

use Closure;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Process;

class HomeController extends Controller
{
    /**
     * Hosts accepted as YouTube URLs.
     */
    private const ALLOWED_HOSTS = [
        'youtube.com',
        'www.youtube.com',
        'm.youtube.com',
        'music.youtube.com',
        'youtu.be',
    ];

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        // If you want to disable home auth just comment this line!
        // $this->middleware('auth');
    }

    /**
     * Show the home page and, when a URL is submitted, download it as MP3.
     */
    public function index(Request $request): Renderable
    {
        $data = [];

        if ($request->filled('url')) {
            $validated = $request->validate([
                'url' => ['required', 'string', 'max:2048', 'url:http,https', function (string $attribute, mixed $value, Closure $fail) {
                    $host = strtolower((string) parse_url($value, PHP_URL_HOST));
                    if (! in_array($host, self::ALLOWED_HOSTS, true)) {
                        $fail(__('Only YouTube URLs are allowed.'));
                    }
                }],
            ]);

            $fileName = $this->downloadMp3(trim($validated['url']));

            if ($fileName === null) {
                $data['error'] = true;
            } else {
                $data['file_name_destination'] = $fileName;
            }
        }

        return view('home', $data);
    }

    /**
     * Download the audio of a YouTube URL into public/ as MP3.
     *
     * Arguments are passed as an array (no shell involved) and the URL goes after
     * "--", so it can never be interpreted as a yt-dlp option.
     *
     * @return string|null The MP3 file name relative to public/, or null on failure.
     */
    private function downloadMp3(string $url): ?string
    {
        $config = config('services.ytdlp');

        $command = [
            $config['binary'],
            '--no-playlist',
            '--restrict-filenames',
            '--format', $config['format'],
            '--extract-audio',
            '--audio-format', 'mp3',
            '--output', '%(title)s_%(id)s.%(ext)s',
            '--no-simulate',
            '--print', 'after_move:filepath',
        ];

        if (! empty($config['ffmpeg_location'])) {
            array_push($command, '--ffmpeg-location', $config['ffmpeg_location']);
        }

        array_push($command, '--', $url);

        $result = Process::path(public_path())
            ->timeout($config['timeout'])
            ->run($command);

        $filePath = trim(collect(explode("\n", trim($result->output())))->last() ?? '');

        if ($result->failed() || $filePath === '') {
            Log::error('yt-dlp download failed', [
                'url' => $url,
                'exit_code' => $result->exitCode(),
                'error' => $result->errorOutput(),
            ]);

            return null;
        }

        return basename($filePath);
    }
}
