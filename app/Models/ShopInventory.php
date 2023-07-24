<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShopInventory extends Model
{
    use HasFactory;

    public function product()
    {
        return $this->belongsTo(Inventory::class, 'product_id', 'id');
    }
}
