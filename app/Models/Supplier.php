<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Supplier extends Model
{
    use HasFactory;

    public function items()
    {
        return $this->hasMany(SupplierItem::class, 'supplier_id', 'id');
    }
}
