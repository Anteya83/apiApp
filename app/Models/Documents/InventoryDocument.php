<?php

namespace App\Models\Documents;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InventoryDocument extends Model
{
    use HasFactory;
    protected $fillable = [ 'product_id', 'quantity', 'avg_price', 'inventory_mistake','created_at'];
}
