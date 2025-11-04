<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CleanExpiredTokensCommand extends Command
{
    protected $signature = 'tokens:clean';
    protected $description = 'Remove expired password reset tokens';

    public function handle()
    {
        $exipres = config('auth.token_expire_time', 60);
        $deleted = DB::table('personal_access_tokens')
            ->where('created_at', '<', Carbon::now()->subMinutes($exipres))
            ->delete();

        $this->info("Deleted {$deleted} expired tokens.");
    }
}
