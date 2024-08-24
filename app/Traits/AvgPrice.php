<?php
namespace App\Traits;

use App\Models\Documents\IncomeDocument;
use App\Models\Documents\InventoryDocument;
use Carbon\Carbon;

trait AvgPrice
{
    //нужно почитать среднюю стоимость всех приходов по данному товару за последние 20 дней, предшествующих инвентаризации.
    // Если за 20 дней не было ни единого прихода, нужно взять стоимость из последнего прихода по данному товару.
    public function calculateAvgPrice($productId, $date)
    {
        if(is_string($date)){
            $date = Carbon::parse($date);
        }

        $dateFrom = $date->copy()->subDays(20); // 20 дней назад от текущей даты


        // все приходы по товару за последние 20 дней до даты инв-ции
        $incomingDocuments = IncomeDocument::where('product_id', $productId)
            ->where('created_at', '>', $dateFrom)
            ->where('created_at', '<=', $date)
            ->get();

        // Если приходов за 20д нет, берем последний и вернем цену
        if ($incomingDocuments->isEmpty()) {
            $lastIncome = IncomeDocument::where('product_id', $productId)
                ->where('created_at', '<=', $date)
                ->latest()
                ->first();

            return $lastIncome ? $lastIncome->price : null;
        }

        // Подсчет и возврат средней стоимости
        $totalCost = $incomingDocuments->sum(function ($income) {
            return $income->price * $income->quantity; // в копейках
        });

        $totalQuantity = $incomingDocuments->sum('quantity');

        return $totalQuantity > 0 ? round($totalCost / $totalQuantity) : null;
    }

}
