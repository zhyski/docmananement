<?php

namespace App\Repositories\Implementation;

use App\Models\DocumentAuditTrails;
use App\Models\DocumentComments;
use App\Models\Documents;
use App\Models\DocumentMetaDatas;
use App\Models\DocumentRolePermissions;
use App\Models\DocumentTokens;
use App\Models\DocumentUserPermissions;
use App\Models\DocumentVersions;
use App\Models\Reminders;
use App\Models\ReminderSchedulers;
use App\Models\SendEmails;
use App\Models\UserNotifications;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use App\Repositories\Implementation\BaseRepository;
use App\Repositories\Contracts\ArchiveDocumentRepositoryInterface;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

//use Your Model

/**
 * Class UserRepository.
 */
class ArchiveDocumentRepository extends BaseRepository implements ArchiveDocumentRepositoryInterface
{
    /**
     * @var Model
     */
    protected $model;

    /**
     * BaseRepository constructor..
     *
     * @param Model $model
     */


    public static function model()
    {
        return Documents::class;
    }

    public function getArchiveDocuments($attributes)
    {
        try {

            $query = Documents::withoutGlobalScope('isDeleted')->onlyTrashed()
                ->where('documents.isDeleted', '=', 1)
                ->join('categories', 'documents.categoryId', '=', 'categories.id')
                ->join('users', 'documents.deletedBy', '=', 'users.id');

            $orderByArray =  explode(' ', $attributes->orderBy);
            $orderBy = $orderByArray[0];
            $direction = $orderByArray[1] ?? 'asc';

            if ($orderBy == 'categoryName') {
                $query = $query->orderBy('categories.name', $direction);
            } else if ($orderBy == 'name') {
                $query = $query->orderBy('documents.name', $direction);
            } else if ($orderBy == 'deletedAt') {
                $query = $query->orderBy('documents.deleted_at', $direction);
            } else if ($orderBy == 'deletedBy') {
                $query = $query->orderBy('users.firstName', $direction);
            } else if ($orderBy == 'location') {
                $query = $query->orderBy('documents.location', $direction);
            }

            if ($attributes->categoryId) {
                $query = $query->where('categoryId', $attributes->categoryId)
                    ->orWhere('categories.parentId', $attributes->categoryId);
            }

            if ($attributes->name) {
                $query = $query->where('documents.name', 'like', '%' . $attributes->name . '%')
                    ->orWhere('documents.description',  'like', '%' . $attributes->name . '%');
            }

            if ($attributes->location) {
                $query = $query->where('documents.location', '=',  $attributes->location);
            }

            if ($attributes->metaTags) {
                $metaTags = $attributes->metaTags;
                $query = $query->whereExists(function ($query) use ($metaTags) {
                    $query->select(DB::raw(1))
                        ->from('documentMetaDatas')
                        ->whereRaw('documentMetaDatas.documentId = documents.id')
                        ->where('documentMetaDatas.metatag', 'like', '%' . $metaTags . '%');
                });
            }

            if ($attributes->deletedDateString) {
                $startDate = Carbon::parse($attributes->deletedDateString)->setTimezone('UTC');
                $endDate = Carbon::parse($attributes->deletedDateString)->setTimezone('UTC')->addDays(1)->addSeconds(-1);
                $query = $query->whereBetween('documents.deleted_at', [$startDate, $endDate]);
            }

            $results = $query->skip($attributes->skip)->take($attributes->pageSize)->get([
                'documents.id', 'documents.name', 'documents.url', 'documents.description', 'documents.location', 'documents.deletedBy', 'documents.deleted_at',
                'categories.id as categoryId', 'categories.name as categoryName',
                DB::raw("CONCAT(users.firstName,' ', users.lastName) as deletedByName")
            ]);

            return $results;
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function getArchiveDocumentsCount($attributes)
    {
        $query = Documents::withoutGlobalScope('isDeleted')->onlyTrashed()
            ->where('documents.isDeleted', '=', 1)
            ->join('categories', 'documents.categoryId', '=', 'categories.id')
            ->join('users', 'documents.deletedBy', '=', 'users.id');

        $orderByArray =  explode(' ', $attributes->orderBy);
        $orderBy = $orderByArray[0];
        $direction = $orderByArray[1] ?? 'asc';

        if ($orderBy == 'categoryName') {
            $query = $query->orderBy('categories.name', $direction);
        } else if ($orderBy == 'name') {
            $query = $query->orderBy('documents.name', $direction);
        } else if ($orderBy == 'deletedAt') {
            $query = $query->orderBy('documents.deleted_at', $direction);
        } else if ($orderBy == 'deletedBy') {
            $query = $query->orderBy('users.firstName', $direction);
        } else if ($orderBy == 'location') {
            $query = $query->orderBy('documents.location', $direction);
        }

        if ($attributes->categoryId) {
            $query = $query->where('categoryId', $attributes->categoryId)
                ->orWhere('categories.parentId', $attributes->categoryId);
        }

        if ($attributes->name) {
            $query = $query->where('documents.name', 'like', '%' . $attributes->name . '%')
                ->orWhere('documents.description',  'like', '%' . $attributes->name . '%');
        }

        if ($attributes->location) {
            $query = $query->where('documents.location', '=',  $attributes->location);
        }

        if ($attributes->metaTags) {
            $metaTags = $attributes->metaTags;
            $query = $query->whereExists(function ($query) use ($metaTags) {
                $query->select(DB::raw(1))
                    ->from('documentMetaDatas')
                    ->whereRaw('documentMetaDatas.documentId = documents.id')
                    ->where('documentMetaDatas.metatag', 'like', '%' . $metaTags . '%');
            });
        }

        if ($attributes->deletedDateString) {
            $startDate = Carbon::parse($attributes->deletedDateString)->setTimezone('UTC');
            $endDate = Carbon::parse($attributes->deletedDateString)->setTimezone('UTC')->addDays(1)->addSeconds(-1);
            $query = $query->whereBetween('documents.deleted_at', [$startDate, $endDate]);
        }
        $count = $query->count();
        return $count;
    }

    public function restoreDocument($id)
    {
        try {
            $document = Documents::withoutGlobalScope('isDeleted')->onlyTrashed()->findOrFail($id);
            $document->isDeleted = 0;
            $document->deletedBy = null;
            $document->deleted_at = null;
            $document->save();
            return response()->json([]);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'Error in saving data.',
            ], 409);
        }
    }

    public function deleteDocument($id)
    {
        try {

            DB::beginTransaction();

            $document = Documents::withoutGlobalScope('isDeleted')->withTrashed()->findOrFail($id);
            $medatDatas = DocumentMetaDatas::where('documentId', $id)->get();
            $comments = DocumentComments::where('documentId', $id)->get();
            $userNotifications = UserNotifications::where('documentId', $id)->get();
            $reminders = Reminders::withoutGlobalScope('isDeleted')->where('documentId', $id)->get();
            $reminderSchedulers = ReminderSchedulers::where('documentId', $id)->get();
            $documentVersions = DocumentVersions::withoutGlobalScope('isDeleted')->where('documentId', $id)->get();
            $userPermissions = DocumentUserPermissions::where('documentId', $id)->get();
            $rolePermissions = DocumentRolePermissions::where('documentId', $id)->get();
            $sendEmail = SendEmails::withoutGlobalScope('isDeleted')->where('documentId', $id)->get();
            $tokens = DocumentTokens::where('documentId', $id)->get();

            foreach ($medatDatas as $medatData) {
                $medatData->forceDelete();
            }
            foreach ($comments as $comment) {
                $comment->forceDelete();
            }

            foreach ($userNotifications as $userNotification) {
                $userNotification->forceDelete();
            }

            foreach ($reminders as $reminder) {
                $reminder->forceDelete();
            }

            foreach ($reminderSchedulers as $reminderScheduler) {
                $reminderScheduler->forceDelete();
            }

            foreach ($documentVersions as $documentVersion) {
                $documentVersion->forceDelete();
            }

            foreach ($userPermissions as $userPermission) {
                $userPermission->forceDelete();
            }

            foreach ($rolePermissions as $rolePermission) {
                $rolePermission->forceDelete();
            }

            foreach ($sendEmail as $sendEmail) {
                $sendEmail->forceDelete();
            }

            foreach ($tokens as $token) {
                $token->forceDelete();
            }

            $path = $document->url;
            $location = $document->location;

            $userId = Auth::parseToken()->getPayload()->get('userId');
            $document->isDeleted = true;
            $document->ispermanentDelete = true;
            $document->deletedBy = $userId;
            $document->deleted_at = Carbon::now();
            $document->save();

            DB::commit();

            try {
                Storage::disk($location)->delete($path);
            } catch (\Throwable $th) {
            }

            foreach ($documentVersions as $documentVersion) {

                $versionUrl = $documentVersion->url;
                $versionLocation = $documentVersion->location;

                try {
                    Storage::disk($versionLocation)->delete($versionUrl);
                } catch (\Throwable $th) {
                }
            }

            return response()->json([]);
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
            return response()->json([
                'message' => $th->getMessage(),
            ], 409);
        }
    }
}
