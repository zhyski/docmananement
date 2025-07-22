<?php

namespace Database\Seeders;

use App\Models\Actions;
use App\Models\RoleClaims;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use App\Models\Users;

class PermissionSeederV22 extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $action = Actions::where([['name', '=', 'Storage Settings'], ['pageId', '=', '8fbb83d6-9fde-4970-ac80-8e235cab1ff2']])->first();

        if ($action == null) {

            $user = Users::first();

            $actions =
                [
                    [
                        'id' => '07ad64e9-9a43-40d0-a205-2adb81e238b1',
                        'name' => 'Storage Settings',
                        'order' => 2,
                        'pageId' => '8fbb83d6-9fde-4970-ac80-8e235cab1ff2',
                        'code' => 'SETTINGS_STORAGE_SETTINGS',
                        'createdBy' => $user->id,
                        'modifiedBy' => $user->id,
                        'isDeleted' => 0
                    ]
                ];

            $roleClaims = [
                [
                    'id' => Str::uuid(36),
                    'actionId' => '07ad64e9-9a43-40d0-a205-2adb81e238b1',
                    'roleId' => 'f8b6ace9-a625-4397-bdf8-f34060dbd8e4',
                    'claimType' => 'SETTINGS_STORAGE_SETTINGS',
                ]
            ];

            $updatedActions =  collect($actions)->map(function ($item, $key) {
                $item['createdDate'] = Carbon::now();
                $item['modifiedDate'] = Carbon::now();
                return $item;
            });

            Actions::insert($updatedActions->toArray());
            RoleClaims::insert($roleClaims);
        }
    }
}
