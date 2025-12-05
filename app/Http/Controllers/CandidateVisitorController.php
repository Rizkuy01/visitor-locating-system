<?php

namespace App\Http\Controllers;

use App\Models\Card;
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
            $range = $data['tipe'] === 'office' ? [1, 10] : [11, 20];

            /** @var Card|null $card */
            $card = Card::lockForUpdate()
                ->where('tipe', $data['tipe'])
                ->where('status', 'available')
                ->whereBetween('code', $range)
                ->orderBy('code')
                ->first();

            if (!$card) {
                return ['ok' => false, 'message' => 'Mohon maaf, kartu untuk tipe ini sedang penuh.'];
            }

            if (!$card->rfid_code) {
                return ['ok' => false, 'message' => 'Kartu terpilih belum memiliki RFID code. Hubungi security/admin.'];
            }

            $card->update(['status' => 'booked']);

            return [
                'ok' => true,
                'payload' => [
                    'full_name'   => $data['full_name'],
                    'institution' => $data['institution'],
                    'tipe'        => $data['tipe'],
                    'card_id'     => $card->id,
                    'card_code'   => $card->code,
                    'rfid_code'   => $card->rfid_code,
                ]
            ];
        });

        if (!$result['ok']) {
            return back()->withInput()->with('error', $result['message']);
        }

        return view('candidate-success', $result['payload']);
    }
}
