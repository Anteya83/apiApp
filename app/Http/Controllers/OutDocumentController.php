<?php

namespace App\Http\Controllers;

use App\Http\Requests\OutStoreRequest;
use App\Services\OutDocumentService;
use App\Traits\AfterSaveDoc;
use App\Traits\DateToFormat;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use App\Traits\StockTrait;
class OutDocumentController extends Controller
{
    use StockTrait;
    use DateToFormat;
    use AfterSaveDoc;
    private $outDocumentService;

    public function __construct(OutDocumentService $outDocumentService) {
        $this->outDocumentService = $outDocumentService;
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\OutStoreRequest  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(OutStoreRequest $request)
    {
        $validated = $request->validated();
        $date = Carbon::now();
        if(isset($validated['date'])) {
            $date = $this->dateToFormatBd($validated['date']);
        }

        $documentItems = [];
        foreach ($validated['items'] as $item) {
            $stock = $this->calculateStock($item['product_id'], $date);//получаем текущий остаток по товару на дату документа

            if ($stock >= $item['quantity']){
                $item['created_at'] =  $date;
                $item['updated_at'] =  Carbon::now();
                $documentItems[] = $item;
            }
            else {
                return response()->json(['message' => "There are not enough product in the warehouse" ], 422);
            }
        }
        $document = $this->outDocumentService->addDocument($documentItems);//добавляем в базу
        Cache::forget('documents-history');//уничтожаем кэш после создания нового док-та

        if(isset($validated['date']) && $document) { //если дата не текущая- а из request и если документы сохранены в базу
            $result = $this->changeInventoryAfterSavedDoc ($documentItems, $date, "out");
        }

        return response()->json(['message' => 'ALL position from out-document saved'], 201);
    }


}
