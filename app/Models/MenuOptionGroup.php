<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MenuOptionGroup extends Model
{
    protected $fillable = ['menu_id', 'name', 'selection_type', 'is_required', 'sort_order'];

    protected function casts(): array
    {
        return ['is_required' => 'boolean'];
    }

    public function menu()
    {
        return $this->belongsTo(Menu::class);
    }

    public function values()
    {
        return $this->hasMany(MenuOptionValue::class, 'option_group_id')->orderBy('sort_order');
    }
}
