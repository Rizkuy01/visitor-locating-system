<?php

namespace App\Http\Controllers;

use App\Models\Card;
use App\Models\Visitor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CardApiController extends Controller
{
    public function checkout(Request $request, Card $card)
    {
        $result = DB::transaction(function () use ($card) {
            $card = Card::lockForUpdate()->find($card->id);

            if (!$card) {
                return ['ok' => false, 'message' => 'Kartu tidak ditemukan.'];
            }

            if ($card->status !== 'in_use') {
                return ['ok' => false, 'message' => 'Kartu tidak sedang digunakan.'];
            }

            // visitor aktif (utama: active_visitor_id)
            $visitor = null;

            if (!empty($card->active_visitor_id)) {
                $visitor = Visitor::lockForUpdate()->find($card->active_visitor_id);
            }

            // fallback kalau active_visitor_id null
            if (!$visitor) {
                $visitor = Visitor::lockForUpdate()
                    ->where('card_id', $card->id)
                    ->whereNotNull('check_in_at')
                    ->whereNull('check_out_at')
                    ->latest('id')
                    ->first();
            }

            if ($visitor) {
                $visitor->update(['check_out_at' => now()]);
            }

            $card->update([
                'status' => 'available',
                'active_visitor_id' => null,
            ]);

            return ['ok' => true, 'message' => 'Checkout berhasil.'];
        });

        return response()->json($result, $result['ok'] ? 200 : 422);
    }
}
