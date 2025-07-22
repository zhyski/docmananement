<?php

namespace Database\Seeders;

use App\Models\Actions;
use App\Models\Languages;
use App\Models\Pages;
use App\Models\RoleClaims;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use App\Models\Users;

class PermissionSeederV23 extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $page = Pages::where('name', '=', 'Archived Documents')->first();

        if ($page == null) {

            $user = Users::first();

            $pages =
                [
                    [
                        'id' => '05edb281-cddb-4281-9ab3-fb90d1833c82',
                        'name' => 'Archived Documents',
                        'order' => 4,
                        'createdBy' => $user->id,
                        'modifiedBy' => $user->id,
                        'isDeleted' => 0
                    ],
                ];

            $actions =
                [
                    [
                        'id' => '260d1089-46c7-4f53-83e6-f80b9b3fb823',
                        'name' => 'Archive Document',
                        'order' => 4,
                        'pageId' => 'eddf9e8e-0c70-4cde-b5f9-117a879747d6',
                        'code' => 'ALL_DOCUMENTS_ARCHIVE_DOCUMENT',
                        'createdBy' => $user->id,
                        'modifiedBy' => $user->id,
                        'isDeleted' => 0
                    ],
                    [
                        'id' => '18d07817-4b47-4c84-b21f-abe05da5e1ba',
                        'name' => 'Archive Document',
                        'order' => 4,
                        'pageId' => 'fc97dc8f-b4da-46b1-a179-ab206d8b7efd',
                        'code' => 'ASSIGNED_DOCUMENTS_ARCHIVE_DOCUMENT',
                        'createdBy' => $user->id,
                        'modifiedBy' => $user->id,
                        'isDeleted' => 0
                    ],
                    [
                        'id' => 'd9067d75-e3b9-4d2d-8f82-567ad5f2b9ca',
                        'name' => 'View Documents',
                        'order' => 1,
                        'pageId' => '05edb281-cddb-4281-9ab3-fb90d1833c82',
                        'code' => 'ARCHIVE_DOCUMENT_VIEW_DOCUMENTS',
                        'createdBy' => $user->id,
                        'modifiedBy' => $user->id,
                        'isDeleted' => 0
                    ],
                    [
                        'id' => 'f4d8a768-151d-4ec9-a8e3-41216afe0ec0',
                        'name' => 'Delete Document',
                        'order' => 4,
                        'pageId' => '05edb281-cddb-4281-9ab3-fb90d1833c82',
                        'code' => 'ARCHIVE_DOCUMENT_DELETE_DOCUMENTS',
                        'createdBy' => $user->id,
                        'modifiedBy' => $user->id,
                        'isDeleted' => 0
                    ],

                    [
                        'id' => '4071ed2e-56fb-4c5a-887d-8a175cac8d71',
                        'name' => 'Restore Document',
                        'order' => 4,
                        'pageId' => '05edb281-cddb-4281-9ab3-fb90d1833c82',
                        'code' => 'ARCHIVE_DOCUMENT_RESTORE_DOCUMENT',
                        'createdBy' => $user->id,
                        'modifiedBy' => $user->id,
                        'isDeleted' => 0
                    ],
                ];

            $roleClaims = [
                [
                    'id' => Str::uuid(36),
                    'actionId' => '260d1089-46c7-4f53-83e6-f80b9b3fb823',
                    'roleId' => 'f8b6ace9-a625-4397-bdf8-f34060dbd8e4',
                    'claimType' => 'ALL_DOCUMENTS_ARCHIVE_DOCUMENT',
                ],    [
                    'id' => Str::uuid(36),
                    'actionId' => '18d07817-4b47-4c84-b21f-abe05da5e1ba',
                    'roleId' => 'f8b6ace9-a625-4397-bdf8-f34060dbd8e4',
                    'claimType' => 'ASSIGNED_DOCUMENTS_ARCHIVE_DOCUMENT'
                ],
                [
                    'id' => Str::uuid(36),
                    'actionId' => 'd9067d75-e3b9-4d2d-8f82-567ad5f2b9ca',
                    'roleId' => 'f8b6ace9-a625-4397-bdf8-f34060dbd8e4',
                    'claimType' => 'ARCHIVE_DOCUMENT_VIEW_DOCUMENTS',
                ],    [
                    'id' => Str::uuid(36),
                    'actionId' => 'f4d8a768-151d-4ec9-a8e3-41216afe0ec0',
                    'roleId' => 'f8b6ace9-a625-4397-bdf8-f34060dbd8e4',
                    'claimType' => 'ARCHIVE_DOCUMENT_DELETE_DOCUMENTS'
                ],    [
                    'id' => Str::uuid(36),
                    'actionId' => '4071ed2e-56fb-4c5a-887d-8a175cac8d71',
                    'roleId' => 'f8b6ace9-a625-4397-bdf8-f34060dbd8e4',
                    'claimType' => 'ARCHIVE_DOCUMENT_RESTORE_DOCUMENT'
                ]
            ];

            $updatedActions =  collect($actions)->map(function ($item, $key) {
                $item['createdDate'] = Carbon::now();
                $item['modifiedDate'] = Carbon::now();
                return $item;
            });

            $updatedPages =  collect($pages)->map(function ($item, $key) {
                $item['createdDate'] = Carbon::now();
                $item['modifiedDate'] = Carbon::now();
                return $item;
            });

            Pages::insert($updatedPages->toArray());
            Actions::insert($updatedActions->toArray());
            RoleClaims::insert($roleClaims);
        }

        $language = Languages::where('code', 'ar')->first();
        if ($language != null) {
            $language->isRTL = true;
            $language->saveQuietly();
        }
    }
}
