<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Card extends Model
{
    protected $fillable = ['code', 'status'];

    public function activeVisitor(): HasOne
    {
        return $this->hasOne(Visitor::class)->whereNull('check_out_at');
    }
}
