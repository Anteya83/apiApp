<?php

namespace App\Http\Controllers;

use App\Http\Requests\IncomeStoreRequest;
use App\Services\DocumentService;
use App\Services\HistoryDocumentService;
use App\Services\IncomeDocumentService;
use App\Services\InventoryDocumentService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class HistoryDocumentController extends Controller
{
    private $historyDocumentService;
    public function __construct( HistoryDocumentService $historyDocumentService) {
        $this->historyDocumentService = $historyDocumentService;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        $history = $this->historyDocumentService->getAllDocHistory();

        return response()->json($history, 200);
    }

}
