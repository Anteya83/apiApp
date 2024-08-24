<?php

namespace App\Http\Controllers;

use App\Http\Requests\InventoryStoreRequest;
use App\Services\InventoryDocumentService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use App\Http\Requests\InventoryStoryRequest;
use App\Traits;
class InventoryDocumentController extends Controller
{
    use Traits\StockTrait;
    use Traits\DateToFormat;
    use Traits\AvgPrice;
    use Traits\AfterSaveDoc;
    private $inventoryDocumentService;
    public function __construct(InventoryDocumentService $inventoryDocumentService) {
        $this->inventoryDocumentService = $inventoryDocumentService;
    }


    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\InventoryStoreRequest  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(InventoryStoreRequest $request)
    {
        $validated = $request->validated();
        $date = Carbon::now();
        if(isset($validated['date'])) {
            $date = $this->dateToFormatBd($validated['date']);

        }
        $documentItems = [];

        foreach ($validated['items'] as $item) {
            $stock = $this->calculateStock($item['product_id'],$date); //получаем текущий остаток на дату инв-ции
            $item['inventory_mistake'] = $item['quantity'] - $stock; //получаем ошибку инв-ции

            $averageCost = $this->calculateAvgPrice($item['product_id'],$date);//получаем ср.себ-сть товара(решила сразу сохранять в базу:)

            $item['avg_price'] = (int)$averageCost;
            $item['created_at'] = $date;
            $item['updated_at'] =  Carbon::now();
            $documentItems[] = $item;
        }
        $document = $this->inventoryDocumentService->addDocument($documentItems);
        Cache::forget('documents-history');//уничтожаем кэш после создания нового док-та

        if(isset($validated['date']) && $document) { //если дата не текущая- а из request и если документы сохранены в базу
            $result = $this->changeInventoryAfterSavedDoc ($documentItems, $date, "inventory");
        }

        return response()->json(['message' => 'ALL position from inventory-document saved'], 201);
    }

    public function getInventoryResults(InventoryStoryRequest $request)
    {
        $validated = $request->validated();

        $results = $this->inventoryDocumentService->getInventoryResults($validated['date']);

        return response()->json($results, 200);
    }


}
