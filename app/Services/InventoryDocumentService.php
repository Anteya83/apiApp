<?php
namespace App\Services;

use App\Models\Documents\IncomeDocument;
use App\Models\Documents\InventoryDocument;
use Carbon\Carbon;

class InventoryDocumentService
{
    public function addDocument($data)
    {
    return InventoryDocument::insert($data); // Массовое создание записей из документа т.о. у нас один запрос в бд вместо по каждой позиции
    }

    public function getInventoryResults($date)
    {
        $date = Carbon::parse($date);

        // инвентаризации за указанную дату
        $inventories = InventoryDocument::whereDate('created_at', $date->toDateString())->get();

        $results = [];

        // Груп-ем инв-ции по product_id и выбираем последние по времени
        $latestInventories = $inventories->groupBy('product_id')->map(function ($group) {
            return $group->sortByDesc('created_at')->first();
        });
//Для указанной даты нужно получить все инвентаризации и для каждого товара получить:
// остаток по инвентаризации (в штуках и в рублях), ошибку инвентаризации (в штуках и в рублях)
        // Проходим по последним инвентаризациям
        foreach ($latestInventories as $inventory) {
            $productId = (int) $inventory->product_id;
            $inventoryQuantity = (int) $inventory->quantity;

            // остаток по инвентаризации в рублях
            $stockSum = round(($inventory->avg_price / 100) *  $inventoryQuantity, 2);

            $inventoryMistake =(int) $inventory->inventory_mistake;

            // ошибка инв-ции в рублях
            $mistakeSum = round($inventory->avg_price / 100 * $inventoryMistake,2);

            $results[] = [
                'product_id' => $productId,
                'inventory_quantity' => $inventoryQuantity,
                'inventory_quantity_sum' => $stockSum,
                'inventory_mistake' => $inventoryMistake,
                'inventory_mistake_sum' => $mistakeSum,
            ];
        }
        return $results;
    }

}
