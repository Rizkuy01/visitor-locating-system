<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Card extends Model
{
    protected $fillable = ['code', 'rfid_code', 'tipe', 'status'];

    public function activeVisitor()
    {
        return $this->hasOne(Visitor::class, 'card_id')
            ->whereNotNull('check_in_at')
            ->whereNull('check_out_at')
            ->orderByDesc('id');
    }

    public function bookedVisitor()
    {
        return $this->hasOne(Visitor::class, 'card_id')
            ->whereNull('check_in_at')
            ->whereNull('check_out_at')
            ->orderByDesc('id');
    }
}

