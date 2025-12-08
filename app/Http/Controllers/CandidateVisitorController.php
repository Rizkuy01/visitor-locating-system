<?php

namespace App\Http\Controllers;

use App\Models\Card;
use App\Models\Visitor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

class CandidateVisitorController extends Controller
{
    public function create()
    {
        return view('candidate');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'full_name'       => ['required', 'string', 'max:255'],
            'institution'     => ['required', 'string', 'max:255'],
            'tipe'            => ['required', 'in:office,plant'],

            'no_hp'           => ['nullable', 'string', 'max:20'],
            'yang_ditemui'    => ['nullable', 'string', 'max:255'],
            'urusan'          => ['nullable', 'string', 'max:255'],
            'jumlah'          => ['nullable', 'integer', 'min:1', 'max:999'],
            'jam_pertemuan'   => ['nullable', 'date_format:H:i'],

            'bawa_kendaraan'  => ['nullable', 'in:ya,tidak'],
            'no_kendaraan'    => ['nullable', 'string', 'max:20'],
        ]);

        // validasi conditional: kalau bawa kendaraan = ya, no_kendaraan wajib
        if (($data['bawa_kendaraan'] ?? 'tidak') === 'ya' && empty($data['no_kendaraan'])) {
            return back()->withInput()->withErrors([
                'no_kendaraan' => 'No kendaraan wajib diisi jika membawa kendaraan.'
            ]);
        }

        $today = Carbon::today(); // timezone app

        $result = DB::transaction(function () use ($data, $today) {
            // 1) ambil kartu available sesuai tipe
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

            // 2) bikin batch harian (001..dst, reset per tanggal)
            // Lock baris visitor hari ini supaya aman dari race condition
            $lastToday = Visitor::whereDate('tanggal', $today)
                ->orderBy('batch', 'desc')
                ->lockForUpdate()
                ->first();

            $nextNumber = 1;
            if ($lastToday && $lastToday->batch) {
                $nextNumber = ((int) $lastToday->batch) + 1;
            }
            $batch = str_pad((string) $nextNumber, 3, '0', STR_PAD_LEFT);

            // 3) set kartu jadi booked
            $card->update(['status' => 'booked']);

            // 4) simpan booking ke tabel visitors
            $visitor = Visitor::create([
                'tanggal'       => $today->toDateString(),
                'batch'         => $batch,

                'full_name'     => $data['full_name'],
                'institution'   => $data['institution'],
                'card_id'       => $card->id,

                'no_hp'         => $data['no_hp'] ?? null,
                'no_kendaraan'  => (($data['bawa_kendaraan'] ?? 'tidak') === 'ya')
                                    ? ($data['no_kendaraan'] ?? null)
                                    : null,

                'yang_ditemui'  => $data['yang_ditemui'] ?? null,
                'urusan'        => $data['urusan'] ?? null,
                'jumlah'        => $data['jumlah'] ?? null,
                'jam_pertemuan' => $data['jam_pertemuan'] ?? null,

                'check_in_at'   => null,
                'check_out_at'  => null,
            ]);

            return [
                'ok' => true,
                'payload' => [
                    'visitor_id'  => $visitor->id,
                    'tanggal'     => $visitor->tanggal,
                    'batch'       => $visitor->batch,

                    'full_name'   => $visitor->full_name,
                    'institution' => $visitor->institution,
                    'tipe'        => $card->tipe,
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
