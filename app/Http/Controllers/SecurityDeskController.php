<?php

namespace App\Http\Controllers;

use App\Models\Card;

class SecurityDeskController extends Controller
{
    public function index()
    {
        // Render halaman (data akan di-refresh via /api/cards)
        return view('desk');
    }

    public function cards()
    {
        $cards = Card::query()
            ->with(['activeVisitor:id,card_id,full_name,institution,check_in_at'])
            ->orderByRaw('CAST(code AS UNSIGNED) ASC')
            ->get(['id', 'code', 'rfid_code', 'status']);

        return response()->json([
            'cards' => $cards,
        ]);
    }
}

