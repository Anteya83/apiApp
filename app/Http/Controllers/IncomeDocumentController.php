<?php

namespace App\Http\Controllers;

use App\Http\Requests\IncomeStoreRequest;
use App\Services\IncomeDocumentService;
use App\Traits\AfterSaveDoc;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use App\Traits\DateToFormat;
class IncomeDocumentController extends Controller
{
    use DateToFormat;
    use AfterSaveDoc;
    private $incomeDocumentService;

    public function __construct(IncomeDocumentService $incomeDocumentService) {
        $this->incomeDocumentService = $incomeDocumentService;
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\IncomeStoreRequest  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(IncomeStoreRequest $request)
    {
        $validated = $request->validated();

        if(isset($validated['date'])) {
           $date = $this->dateToFormatBd($validated['date']);
        }
        else {
           $date = Carbon::now();
        }

        $documentItems = [];
        foreach ($validated['items'] as $item) {

            $item['created_at'] =  $date;
            $item['updated_at'] =  Carbon::now();//ставим штампы сами, тк у нас массовое заполнение бд с одним запросом в бд, а не по каждой позиции
            $item['price'] = $item['price'] * 100; // в бд все храним в копейках

            $documentItems[] = $item;
        }
        $document = $this->incomeDocumentService->addDocument($documentItems);
        Cache::forget('documents-history');//уничтожаем кэш после создания нового док-та

        if(isset($validated['date']) && $document) { //если дата не текущая- а из request и если документы сохранены в базу

                $result = $this->changeInventoryAfterSavedDoc ($documentItems, $date, "income");
        }

        return response()->json(['message' => 'ALL position from income-document saved'], 201);
    }


}
