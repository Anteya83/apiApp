<?php

namespace App\Models\Documents;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OutDocument extends Model
{
    use HasFactory;
    protected $fillable = [ 'product_id', 'quantity','created_at'];
}
