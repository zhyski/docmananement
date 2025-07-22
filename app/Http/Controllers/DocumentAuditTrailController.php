<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Documents;
use App\Repositories\Contracts\DocumentAuditTrailRepositoryInterface;
use Illuminate\Support\Facades\Storage;

class DocumentAuditTrailController extends Controller
{
    private $documentAuditTrailRepository;
    protected $queryString;

    public function __construct(DocumentAuditTrailRepositoryInterface $documentAuditTrailRepository)
    {
        $this->documentAuditTrailRepository = $documentAuditTrailRepository;
    }

    public function getDocumentAuditTrails(Request $request)
    {
        $queryString = (object) $request->all();

        $count = $this->documentAuditTrailRepository->getDocumentAuditTrailsCount($queryString);
        return response()->json($this->documentAuditTrailRepository->getDocumentAuditTrails($queryString))
            ->withHeaders(['totalCount' => $count, 'pageSize' => $queryString->pageSize, 'skip' => $queryString->skip]);
    }

    public function saveDocumentAuditTrail(Request $request)
    {
        return response()->json($this->documentAuditTrailRepository->saveDocumentAuditTrail($request));
    }
}
