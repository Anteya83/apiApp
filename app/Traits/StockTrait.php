<?php
namespace App\Traits;

use App\Models\Documents\OutDocument;
use App\Models\Documents\IncomeDocument;
use App\Models\Documents\InventoryDocument;
use Carbon\Carbon;

trait StockTrait
{
    //текущий остаток по товару для проверки есть ли необходимое кол-во чтобы записать расход по товару на определенную дату
    public function calculateStock($productId, $date)
    {
        // Найдем последнюю инвентаризации по товару сортируя по дате и до даты документа
        $lastInventory = InventoryDocument::where('product_id', $productId)->where('created_at', '<', $date)->latest()->first();

        // Если инвентаризация не найдена, то остаток по инв-ции 0 и дата
        if (!$lastInventory) {
            $lastInventoryValue = 0;
            $lastInventoryDate = Carbon::parse('1970-01-01 00:00:00');
          //  \Log::info(" date: {$lastInventoryDate}");
        }
        else {
            //  значение последней инвентаризации и дата
            $lastInventoryValue = $lastInventory->quantity;
            $lastInventoryDate = $lastInventory->created_at;
        }

        // все приходы по товару после даты последней инв-ции.Но до даты документа
        $incomingQuantity = IncomeDocument::where('product_id', $productId)
            ->where('created_at', '>', $lastInventoryDate)
            ->where('created_at', '<=', $date)
            ->sum('quantity');

        //  все расходы по товару
        $outgoingQuantity = OutDocument::where('product_id', $productId)
            ->where('created_at', '>', $lastInventoryDate)
            ->where('created_at', '<=', $date)
            ->sum('quantity');

        $stock = $lastInventoryValue + $incomingQuantity - $outgoingQuantity;
        return $stock;
    }

}
