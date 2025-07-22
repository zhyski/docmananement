<?php

namespace App\Repositories\Contracts;

use App\Repositories\Contracts\BaseRepositoryInterface;

interface ArchiveDocumentRepositoryInterface extends BaseRepositoryInterface
{
    public function getArchiveDocuments($attributes);
    public function getArchiveDocumentsCount($attributes);
    public function restoreDocument($id);
    public function deleteDocument($id);
}
