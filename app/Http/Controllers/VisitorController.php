<?php

namespace App\Http\Controllers;

use App\Models\Card;
use App\Models\Visitor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class VisitorController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'full_name'    => ['required', 'string', 'max:120'],
            'institution'  => ['required', 'string', 'max:120'],
            'card_id'      => ['required', 'integer', 'exists:cards,id'],
        ]);

        return DB::transaction(function () use ($data) {
            /** @var Card $card */
            $card = Card::lockForUpdate()->findOrFail($data['card_id']);

            if ($card->status !== 'available') {
                return response()->json([
                    'message' => 'Kartu sedang digunakan. Pilih kartu lain.',
                ], 409);
            }

            $visitor = Visitor::create([
                'full_name'   => $data['full_name'],
                'institution' => $data['institution'],
                'card_id'     => $card->id,
                'check_in_at' => now(),
            ]);

            $card->update(['status' => 'in_use']);

            return response()->json([
                'message' => 'Visitor berhasil ditambahkan.',
                'card' => [
                    'id'   => $card->id,   // “kartu no” (id db)
                    'code' => $card->code, // kode kartu yang tampil di grid
                ],
                'visitor' => [
                    'id'          => $visitor->id,
                    'full_name'   => $visitor->full_name,
                    'institution' => $visitor->institution,
                    'check_in_at' => $visitor->check_in_at,
                ],
            ]);
        });
    }

    public function checkout(Card $card)
    {
        return DB::transaction(function () use ($card) {
            /** @var Card $lockedCard */
            $lockedCard = Card::lockForUpdate()->findOrFail($card->id);

            if ($lockedCard->status !== 'in_use') {
                return response()->json([
                    'message' => 'Kartu sudah available.',
                ], 409);
            }

            $active = Visitor::where('card_id', $lockedCard->id)
                ->whereNull('check_out_at')
                ->lockForUpdate()
                ->first();

            $visitorPayload = null;

            if ($active) {
                $active->update(['check_out_at' => now()]);
                $visitorPayload = [
                    'id'           => $active->id,
                    'full_name'    => $active->full_name,
                    'institution'  => $active->institution,
                    'check_in_at'  => $active->check_in_at,
                    'check_out_at' => $active->check_out_at,
                ];
            }

            $lockedCard->update(['status' => 'available']);

            return response()->json([
                'message' => 'Visitor checkout. Kartu kembali available.',
                'card' => [
                    'id'   => $lockedCard->id,
                    'code' => $lockedCard->code,
                ],
                'visitor' => $visitorPayload,
            ]);
        });
    }
}
