<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Item extends Model
{
    protected $table = 'items';
    
    protected $fillable = [
        'category_id',
        'name',
        'code',
        'stock',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id', 'id');
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class, 'item_id', 'id');
    }
}
