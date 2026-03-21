<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\GroupProfile;
use App\Models\AccessCode;

class SyncAccessCodes extends Command
{
    protected $signature = 'sync:access-codes';
    protected $description = 'Sync access_codes.code with users.username for all groups';

    public function handle()
    {
        $groups = User::where('type_role', 'groupe')->get();
        foreach ($groups as $group) {
            $profile = GroupProfile::where('user_id', $group->id)->first();
            if ($profile && $profile->access_code_id) {
                $ac = AccessCode::find($profile->access_code_id);
                if ($ac) {
                    $old = $ac->code;
                    $ac->update(['code' => $group->username]);
                    $this->info("User #{$group->id}: {$old} -> {$group->username}");
                }
            }
        }
        $this->info('Sync complete.');
    }
}
