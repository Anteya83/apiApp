<?php

namespace App\Models\Documents;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class IncomeDocument extends Model
{
    use HasFactory;
    protected $fillable = [ 'product_id', 'quantity', 'price', 'created_at'];
}
