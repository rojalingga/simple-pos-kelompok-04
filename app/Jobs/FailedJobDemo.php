<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use RuntimeException;

/**
 * Job demo untuk menghasilkan entri di tabel failed_jobs.
 * Hapus file ini setelah screenshot queue:failed diambil.
 */
class FailedJobDemo implements ShouldQueue
{
    use Queueable;

    public int $tries = 1;

    public function handle(): void
    {
        throw new RuntimeException('Uji job gagal untuk demo queue:failed.');
    }
}
