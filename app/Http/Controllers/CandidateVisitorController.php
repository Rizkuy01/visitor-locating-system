<?php

namespace App\Http\Controllers;

use App\Models\Card;
use App\Models\Visitor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CandidateVisitorController extends Controller
{
    public function create()
    {
        return view('candidate');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'full_name'   => ['required', 'string', 'max:120'],
            'institution' => ['required', 'string', 'max:120'],
            'tipe'        => ['required', 'in:office,plant'],
        ]);

        $result = DB::transaction(function () use ($data) {
            $card = Card::lockForUpdate()
                ->where('tipe', $data['tipe'])
                ->where('status', 'available')
                ->orderBy('id')
                ->first();

            if (!$card) {
                return ['ok' => false, 'message' => 'Mohon maaf, kartu untuk tipe ini sedang penuh.'];
            }

            if (!$card->rfid_code) {
                return ['ok' => false, 'message' => 'Kartu terpilih belum memiliki RFID code. Hubungi security/admin.'];
            }

            // 1) set kartu jadi booked
            $card->update(['status' => 'booked']);

            // 2) simpan data visitor (booking) ke tabel visitors
            $visitor = Visitor::create([
                'full_name'   => $data['full_name'],
                'institution' => $data['institution'],
                'card_id'     => $card->id,
                'check_in_at' => null,
                'check_out_at'=> null,
            ]);

            return [
                'ok' => true,
                'payload' => [
                    'visitor_id'  => $visitor->id,
                    'full_name'   => $visitor->full_name,
                    'institution' => $visitor->institution,
                    'tipe'        => $card->tipe,      // pastikan sama dengan kartu terpilih
                    'card_id'     => $card->id,
                    'card_code'   => $card->code,      // VB001/VM001
                    'rfid_code'   => $card->rfid_code, // kalau masih kamu butuhkan
                ]
            ];
        });

        if (!$result['ok']) {
            return back()->withInput()->with('error', $result['message']);
        }

        return view('candidate-success', $result['payload']);
    }
}
