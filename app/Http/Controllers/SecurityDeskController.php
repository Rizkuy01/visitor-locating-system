<?php

namespace App\Http\Controllers;

use App\Models\Card;

class SecurityDeskController extends Controller
{
    public function index()
    {
        return view('desk');
    }

    public function cards()
    {
        $cards = Card::query()
            ->with(['activeVisitor:id,full_name,institution,card_id,check_in_at,check_out_at'])
            ->orderBy('id')
            ->get()
            ->map(function ($c) {
                return [
                    'id' => $c->id,
                    'code' => $c->code,
                    'rfid_code' => $c->rfid_code,
                    'tipe' => $c->tipe,
                    'status' => $c->status,
                    'active_visitor' => $c->activeVisitor ? [
                        'full_name' => $c->activeVisitor->full_name,
                        'institution' => $c->activeVisitor->institution,
                        'check_in_at' => $c->activeVisitor->check_in_at,
                    ] : null,
                ];
            });

        return response()->json(['cards' => $cards]);
    }
}
