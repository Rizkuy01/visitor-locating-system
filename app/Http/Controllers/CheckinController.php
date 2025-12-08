<?php

namespace App\Http\Controllers;

use App\Models\Card;
use App\Models\Visitor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CheckinController extends Controller
{
    public function checkin(Request $request)
    {
        $data = $request->validate([
            'card_code' => ['required', 'string'], // contoh: VB001 / VM001
        ]);

        $result = DB::transaction(function () use ($data) {
            /** @var Card|null $card */
            $card = Card::lockForUpdate()
                ->where('code', $data['card_code'])
                ->first();

            if (!$card) {
                return ['ok' => false, 'message' => 'Kartu tidak ditemukan.'];
            }

            if ($card->status === 'in_use') {
                return ['ok' => false, 'message' => 'Kartu sedang digunakan.'];
            }

            if ($card->status !== 'booked') {
                return ['ok' => false, 'message' => 'Kartu belum dibooking.'];
            }

            // Ambil visitor booking terbaru untuk card ini yang belum check-in
            /** @var Visitor|null $visitor */
            $visitor = Visitor::lockForUpdate()
                ->where('card_id', $card->id)
                ->whereNull('check_in_at')
                ->latest('id')
                ->first();

            if (!$visitor) {
                return ['ok' => false, 'message' => 'Data booking visitor tidak ditemukan.'];
            }

            $visitor->update(['check_in_at' => now()]);

            // Jika kamu sudah punya kolom active_visitor_id
            $card->update([
                'status' => 'in_use',
                'active_visitor_id' => $visitor->id,
            ]);

            return ['ok' => true, 'message' => 'Check-in berhasil.', 'visitor_id' => $visitor->id];
        });

        return response()->json($result, $result['ok'] ? 200 : 422);
    }
}
