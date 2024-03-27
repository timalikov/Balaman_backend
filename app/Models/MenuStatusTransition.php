<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class MenuStatusTransition extends Model
{
    protected $fillable = [
        'menu_id', 'user_id', 'from_status', 'to_status', 'comment'
    ];

    public function menu()
    {
        return $this->belongsTo(Menu::class, 'menu_id', 'menu_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
