<?php
namespace App\Services;

use App\Models\Documents\OutDocument;
use App\Models\Documents\IncomeDocument;
use App\Models\Documents\InventoryDocument;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
class HistoryDocumentService
{
    public function getAllDocHistory()
    {
        return Cache::remember('documents-history', 60 * 60, function() {
            // Если кэша нет, получаем все документы
            $incomeDocuments = IncomeDocument::all();
            $outDocuments = OutDocument::all();
            $inventoryDocuments = InventoryDocument::all();

            $history = [];

            // Объединяем все документы
            foreach ($incomeDocuments as $document) {
                $history[] = [
                    'date' => $document->created_at->format('Y-m-d H:i:s'),
                    'type' => 'приход',
                    'product_id' => (int)$document->product_id,
                    'quantity' => (int)$document->quantity,
                    'stock_after' => null,
                ];
            }

            foreach ($outDocuments as $document) {
                $history[] = [
                    'date' => $document->created_at->format('Y-m-d H:i:s'),
                    'type' => 'расход',
                    'product_id' => (int)$document->product_id,
                    'quantity' => (int)$document->quantity,
                    'stock_after' => null,
                ];
            }

            foreach ($inventoryDocuments as $document) {
                $history[] = [
                    'date' => $document->created_at->format('Y-m-d H:i:s'),
                    'type' => 'инвентаризация',
                    'product_id' => (int)$document->product_id,
                    'quantity' => (int)$document->quantity,
                    'stock_after' => $document->quantity,
                    'inventory_mistake' => (int)$document->inventory_mistake
                ];
            }

            // Сортируем историю сначала по product_id, затем по дате
            usort($history, function ($a, $b) {
                if ($a['product_id'] === $b['product_id']) {
                    return strtotime($a['date']) - strtotime($b['date']);
                }
                return $a['product_id'] <=> $b['product_id'];
            });


            // заполняем stock_after в массиве хистори- расчетный остаток
            $currentStocks = [];

            foreach ($history as &$entry) {
                $productId = $entry['product_id'];

                // если еще нет счетчика для product_id создаем
                if (!isset($currentStocks[$productId])) {
                    $currentStocks[$productId] = 0;
                }

                if ($entry['type'] === 'приход') {
                    $currentStocks[$productId] += $entry['quantity'];
                } elseif ($entry['type'] === 'расход') {
                    $currentStocks[$productId] -= $entry['quantity'];
                } elseif ($entry['type'] === 'инвентаризация') {
                    $currentStocks[$productId] = $entry['quantity'];
                }

                $entry['stock_after'] = $currentStocks[$productId];
            }

            return $history;
        });
    }


}
