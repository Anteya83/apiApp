<?php
namespace App\Services;

use App\Models\Documents\OutDocument;

class OutDocumentService
{
public function addDocument($data)
{
return OutDocument::insert($data); // Массовое создание записей из документа
}

public function getHistory()
{
return OutDocument::all();
}
}
