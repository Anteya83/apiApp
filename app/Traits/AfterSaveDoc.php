<?php
namespace App\Traits;

use App\Models\Documents\InventoryDocument;
use Carbon\Carbon;
use App\Traits\AvgPrice;
trait AfterSaveDoc
{
    use AvgPrice;
    use StockTrait;
   //получаем все инв-ции после даты документа по id товара это нам понадобится во всех контр по документу
    public function getInventoryAfterDateOfDoc($productId, $date)
    {
        $inventoryDocuments = InventoryDocument::where('product_id', $productId)
            ->where('created_at', '>', $date)
            ->orderBy('created_at', 'asc')
            ->get();

        return $inventoryDocuments ? $inventoryDocuments : false;
    }

//получается, если мы проводим любой документ задним числом, то во всех инв-циях по данному товару с датой позже чем дата документа
//у нас после записи документа расход или документа инвентаризация - требует актуализации данных 'inventory_mistake'
//а при добавлении прихода - также требует пересчета колонка 'avg_price'
// тогда после проведения любого документа с датой отличной от текущей нам нужно пересчитать  и перезаписать эти данные
    public function updateInventoryAfterCreateDocuments($inventoryDocuments, $item, $typeOfDocument):bool
    {
        $result = false;
        switch ($typeOfDocument) {
            case 'out':
                foreach ($inventoryDocuments as $inventory) {
                    $stock = $this->calculateStock($inventory['product_id'],$inventory['created_at']); //получаем текущий остаток на дату инв-ции
                    $inventory->inventory_mistake = $inventory['quantity'] - $stock;
                    $inventory->save();
                }
                $result = true;
                break;
            case 'income':
                foreach ($inventoryDocuments as $inventory) {
                    $stock = $this->calculateStock($inventory['product_id'],$inventory['created_at']); //получаем текущий остаток на дату инв-ции
                   // dd($stock);
                    $inventory->inventory_mistake = $inventory['quantity'] - $stock;
                   // dd((int)$this->calculateAvgPrice($item['product_id'], $inventory['created_at']));
                    $inventory->avg_price = (int)$this->calculateAvgPrice($item['product_id'], $inventory['created_at']);
                    $inventory->save();
                }
                $result = true;
                break;
            case 'inventory':
                foreach ($inventoryDocuments as $inventory) {
                    $stock = $this->calculateStock($inventory['product_id'],$inventory['created_at']); //получаем текущий остаток на дату инв-ции
                    $inventory->inventory_mistake = $inventory['quantity'] - $stock; //получаем и перезаписываем ошибку инв-ции
                    $inventory->save();
                }
                $result = true;
                break;
        }
        return $result;
    }

    public function changeInventoryAfterSavedDoc ($documentItems, $date, $typeOfDocument):bool
    {
        $result = false;
        foreach ($documentItems as $item) {
            $inventoryDocs = $this->getInventoryAfterDateOfDoc($item['product_id'],$date);

            if($inventoryDocs->isNotEmpty()){
             $result =  $this->updateInventoryAfterCreateDocuments($inventoryDocs, $item, $typeOfDocument);
            }
        }
        return $result;
    }
}
