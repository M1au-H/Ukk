<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Menu extends Model
{
    protected $fillable = [
        'category_id', 'name', 'description', 'price',
        'image_url', 'is_active', 'is_available', 'supports_spicy_level',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'is_active' => 'boolean',
            'is_available' => 'boolean',
            'supports_spicy_level' => 'boolean',
        ];
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function optionGroups()
    {
        return $this->hasMany(MenuOptionGroup::class)->orderBy('sort_order');
    }
}
