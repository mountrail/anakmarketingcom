<?php
// test
namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use Spatie\Permission\Models\Role;

class AssignUserRole extends Command
{
    protected $signature = 'users:assign-role';
    protected $description = 'Assign user role to all users without any role';

    public function handle()
    {
        $usersWithoutRoles = User::doesntHave('roles')->get();

        $count = 0;
        foreach ($usersWithoutRoles as $user) {
            $user->assignRole('user');
            $count++;
        }

        $this->info("Assigned 'user' role to {$count} users.");

        return 0;
    }
}
