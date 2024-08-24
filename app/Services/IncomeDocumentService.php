<?php
namespace App\Services;

use App\Models\Documents\IncomeDocument;

class IncomeDocumentService
{
public function addDocument($data)
{
return IncomeDocument::insert($data); // Массовое создание записей из документа, чтобы был один запрос в бд
}

public function getHistory()
{
return IncomeDocument::all();
}
}
