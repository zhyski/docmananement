<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Repositories\Contracts\ArchiveDocumentRepositoryInterface;

class ArchiveDocumentController extends Controller
{
    private $documentRepository;
    protected $queryString;

    public function __construct(ArchiveDocumentRepositoryInterface $documentRepository)
    {
        $this->documentRepository = $documentRepository;
    }

    public function getDocuments(Request $request)
    {
        $queryString = (object) $request->all();

        $count = $this->documentRepository->getArchiveDocumentsCount($queryString);
        return response()->json($this->documentRepository->getArchiveDocuments($queryString))
            ->withHeaders(['totalCount' => $count, 'pageSize' => $queryString->pageSize, 'skip' => $queryString->skip]);
    }

    public function restoreDocument(Request $request, $id)
    {
        return $this->documentRepository->restoreDocument($id);
    }
}
