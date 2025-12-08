<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Visitor extends Model
{
    protected $fillable = [
        'tanggal',
        'full_name',
        'institution',
        'card_id',
        'no_hp',
        'no_kendaraan',
        'yang_ditemui',
        'urusan',
        'jumlah',
        'jam_pertemuan',
        'check_in_at',
        'check_out_at',
        'batch',
    ];

    protected $casts = [
        'tanggal' => 'date',
        'check_in_at' => 'datetime',
        'check_out_at' => 'datetime',
        // jam_pertemuan biar tidak di-cast aneh-aneh, biarkan string (H:i) juga boleh.
    ];

    public function card()
    {
        return $this->belongsTo(Card::class);
    }
}
