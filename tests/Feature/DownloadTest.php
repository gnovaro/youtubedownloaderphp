<?php

namespace Tests\Feature;

use Illuminate\Process\PendingProcess;
use Illuminate\Support\Facades\Process;
use Tests\TestCase;

class DownloadTest extends TestCase
{
    private const VIDEO_URL = 'https://www.youtube.com/watch?v=lwZqbQL4H4Q';

    public function test_valid_youtube_url_is_downloaded_with_safe_arguments(): void
    {
        Process::fake([
            '*' => Process::result(output: public_path('Some_Song_lwZqbQL4H4Q.mp3')."\n"),
        ]);

        $response = $this->post('/', ['url' => self::VIDEO_URL]);

        $response->assertOk();
        $response->assertSee('Some_Song_lwZqbQL4H4Q.mp3');

        Process::assertRan(function (PendingProcess $process) {
            $command = $process->command;

            return is_array($command)
                && $process->path === public_path()
                && array_slice($command, -2) === ['--', self::VIDEO_URL]
                && in_array('--no-playlist', $command, true);
        });
    }

    public function test_option_injection_is_rejected(): void
    {
        Process::fake();

        $response = $this->post('/', ['url' => "--exec 'touch /tmp/pwned'"]);

        $response->assertSessionHasErrors('url');
        Process::assertNothingRan();
    }

    public function test_non_youtube_url_is_rejected(): void
    {
        Process::fake();

        $response = $this->post('/', ['url' => 'https://example.com/watch?v=lwZqbQL4H4Q']);

        $response->assertSessionHasErrors('url');
        Process::assertNothingRan();
    }

    public function test_youtube_lookalike_host_is_rejected(): void
    {
        Process::fake();

        $response = $this->post('/', ['url' => 'https://youtube.com.evil.example/watch?v=x']);

        $response->assertSessionHasErrors('url');
        Process::assertNothingRan();
    }

    public function test_failed_download_shows_error_and_no_link(): void
    {
        Process::fake([
            '*' => Process::result(output: '', errorOutput: 'ERROR: Requested format is not available', exitCode: 1),
        ]);

        $response = $this->post('/', ['url' => self::VIDEO_URL]);

        $response->assertOk();
        $response->assertSee('The audio could not be downloaded');
        $response->assertDontSee('Download Mp3');
    }
}
