<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class DiningTable extends Model
{
    protected $table = 'tables';

    protected $fillable = ['table_number', 'table_token', 'is_active'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    public function orders()
    {
        return $this->hasMany(Order::class, 'table_id');
    }

    public static function generateToken(): string
    {
        return Str::random(24);
    }
}
