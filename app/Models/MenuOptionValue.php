<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MenuOptionValue extends Model
{
    protected $fillable = ['option_group_id', 'label', 'extra_price', 'sort_order'];

    protected function casts(): array
    {
        return ['extra_price' => 'decimal:2'];
    }

    public function optionGroup()
    {
        return $this->belongsTo(MenuOptionGroup::class, 'option_group_id');
    }
}
