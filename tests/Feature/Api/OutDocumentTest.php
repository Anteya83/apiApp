<?php

namespace Tests\Feature\Api;

use App\Models\Documents\OutDocument;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

use App\Http\Requests\OutStoreRequest;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;


class OutDocumentTest extends TestCase

{

    public function testStoreWithValidData()
    {
        $validatedData = [
            'date' => '2023-08-24 08:01:02',
            'items' => [
                [
                    'product_id' => 1,
                    'quantity' => 1,
                ],

            ],
        ];

        //  запрос
        $response = $this->json('POST', route('out-documents.store'), $validatedData);

        $response->assertStatus(201)
            ->assertJson(['message' => 'ALL position from out-document saved']);

        // а документы сохранены в базе данных
        $this->assertDatabaseHas('out_documents', [
            'product_id' => 1,
            'quantity' => 1,
        ]);
       //кэша нет
        $this->assertFalse(Cache::has('documents-history'));
    }

    public function testStoreWithInsufficientStock()
    {
        // с недостаточным количеством
        $validatedData = [
            'items' => [
                [
                    'product_id' => 7,
                    'quantity' => 100,
                ],
            ],
        ];

        // запрос
        $response = $this->json('POST', route('out-documents.store'), $validatedData);

        // сообщение об ошибке
        $response->assertStatus(422)
            ->assertJson(['message' => 'There are not enough product in the warehouse']);
    }
}
