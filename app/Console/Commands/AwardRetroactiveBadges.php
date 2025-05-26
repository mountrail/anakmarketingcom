<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\BadgeService;

class AwardRetroactiveBadges extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'badges:award-retroactive';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Award badges to existing users based on their current activity';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting retroactive badge awarding...');

        BadgeService::awardRetroactiveBadges();

        $this->info('Retroactive badge awarding completed!');
        return 0;
    }
}
