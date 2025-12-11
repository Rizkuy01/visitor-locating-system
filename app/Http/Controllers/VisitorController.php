<?php

namespace App\Http\Controllers;

use App\Models\Card;
use App\Models\Visitor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

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
                return response()->json(['message' => 'Kartu sedang digunakan. Pilih kartu lain.'], 409);
            }

            // ===== generate batch =====
            $today  = Carbon::today()->toDateString();
            $prefix = Carbon::now()->format('dmy');

            $lastToday = Visitor::query()
                ->whereDate('tanggal', $today)
                ->whereNotNull('batch')
                ->orderBy('batch', 'desc')
                ->lockForUpdate()
                ->first();

            $nextSeq = 1;
            if ($lastToday && preg_match('/^\d{6}(\d{3})$/', $lastToday->batch, $m)) {
                $nextSeq = ((int)$m[1]) + 1;
            }

            $batchCode = $prefix . str_pad((string)$nextSeq, 3, '0', STR_PAD_LEFT);

            // ===== create visitor =====
            $visitor = Visitor::create([
                'tanggal'=> $today,
                'batch'  => $batchCode,
                'full_name'   => $data['full_name'],
                'institution' => $data['institution'],
                'card_id'     => $card->id,
                'check_in_at' => now(),
                'check_out_at'=> null,
            ]);

            $card->update(['status' => 'in_use']);

            return response()->json([
                'message' => 'Visitor berhasil ditambahkan.',
                'card' => [
                    'id'   => $card->id,
                    'code' => $card->code,
                ],
                'visitor' => [
                    'id'          => $visitor->id,
                    'batch'  => $visitor->batch,
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

    public function active(Request $request)
    {
        $q = trim((string) $request->query('q', ''));

        $rows = Visitor::query()
            ->select(['id','full_name','institution','no_hp','card_id','check_in_at'])
            ->with(['card:id,code,rfid_code'])
            ->whereNull('check_out_at')
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($w) use ($q) {
                    $w->where('full_name', 'like', "%{$q}%")
                    ->orWhere('institution', 'like', "%{$q}%")
                    ->orWhere('no_hp', 'like', "%{$q}%")
                    ->orWhereHas('card', function ($c) use ($q) {
                        $c->where('code', 'like', "%{$q}%")
                            ->orWhere('rfid_code', 'like', "%{$q}%");
                    });
                });
            })
            ->orderBy('check_in_at', 'desc')
            ->get();

        return response()->json(['data' => $rows]);
    }

    public function history(Request $request){
    $q = trim((string) $request->query('q', ''));
    $from = $request->query('from'); // YYYY-MM-DD
    $to   = $request->query('to');   // YYYY-MM-DD

    $rows = Visitor::query()
        ->select(['id','batch','full_name','institution','card_id','check_in_at','check_out_at'])
        ->with(['card:id,code,rfid_code'])
        ->when($from, fn($qq) => $qq->whereDate('check_in_at', '>=', $from))
        ->when($to,   fn($qq) => $qq->whereDate('check_in_at', '<=', $to))
        ->when($q !== '', function ($query) use ($q) {
            $query->where(function ($w) use ($q) {
                $w->where('full_name', 'like', "%{$q}%")
                  ->orWhere('institution', 'like', "%{$q}%")
                  ->orWhereHas('card', function ($c) use ($q) {
                      $c->where('code', 'like', "%{$q}%")
                        ->orWhere('rfid_code', 'like', "%{$q}%");
                  });
            });
        })
        ->orderBy('check_in_at', 'desc')
        ->paginate(20);

        return response()->json($rows);
    }

    public function exportHistoryCsv(Request $request)
    {
        $q = trim((string) $request->query('q', ''));
        $from = $request->query('from');
        $to   = $request->query('to');

        $rows = Visitor::query()
            ->select(['id','batch','full_name','institution','card_id','check_in_at','check_out_at'])
            ->with(['card:id,code,rfid_code'])
            ->when($from, fn($qq) => $qq->whereDate('check_in_at', '>=', $from))
            ->when($to,   fn($qq) => $qq->whereDate('check_in_at', '<=', $to))
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($w) use ($q) {
                    $w->where('full_name', 'like', "%{$q}%")
                    ->orWhere('institution', 'like', "%{$q}%")
                    ->orWhereHas('card', function ($c) use ($q) {
                        $c->where('code', 'like', "%{$q}%")
                            ->orWhere('rfid_code', 'like', "%{$q}%");
                    });
                });
            })
            ->orderBy('check_in_at', 'desc')
            ->get();

        $filename = 'visitor_history_' . now()->format('Ymd_His') . '.csv';

        // Excel Indonesia biasanya nyaman dengan delimiter ;
        $delimiter = ';';

        $headers = [
            "Content-Type" => "text/csv; charset=UTF-8",
            "Content-Disposition" => "attachment; filename={$filename}",
        ];

        $callback = function () use ($rows, $delimiter) {
            $out = fopen('php://output', 'w');

            // BOM UTF-8 biar Excel baca UTF-8 dengan benar
            fwrite($out, "\xEF\xBB\xBF");

            fputcsv($out, ['ID', 'Batch Code', 'Nama', 'Instansi', 'Card Code', 'RFID', 'Check In', 'Check Out'], $delimiter);

            foreach ($rows as $v) {
                $cardCode = optional($v->card)->code ?? '';
                $rfid     = optional($v->card)->rfid_code ?? '';

                // Trik biar Excel tidak ubah jadi scientific notation 
                $cardCodeExcel = $cardCode !== '' ? '="'.$cardCode.'"' : '';
                $rfidExcel     = $rfid !== '' ? '="'.$rfid.'"' : '';

                $checkIn  = $v->check_in_at ? Carbon::parse($v->check_in_at)->format('Y-m-d H:i:s') : '';
                $checkOut = $v->check_out_at ? Carbon::parse($v->check_out_at)->format('Y-m-d H:i:s') : '';

                fputcsv($out, [
                    $v->id,
                    $v->batch,
                    $v->full_name,
                    $v->institution,
                    $cardCodeExcel,
                    $rfidExcel,
                    $checkIn,
                    $checkOut,
                ], $delimiter);
            }

            fclose($out);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function scanPage()
    {
        return view('visitor-scan');
    }

    // GET /api/visitors/scan?batch=081225001
    public function scanLookup(Request $request)
    {
        $batch = trim((string) $request->query('batch', ''));

        if ($batch === '') {
            return response()->json([
                'message' => 'Batch harus diisi.',
            ], 422);
        }

        $visitor = Visitor::query()
            ->with(['card:id,code,tipe,rfid_code,status'])
            ->where('batch', $batch)
            ->orderByDesc('id')
            ->first();

        if (!$visitor) {
            return response()->json([
                'message' => 'Data visitor dengan batch tersebut tidak ditemukan.',
            ], 404);
        }

        return response()->json([
            'id'            => $visitor->id,
            'batch'         => $visitor->batch,
            'tanggal'       => optional($visitor->tanggal)->format('Y-m-d'),
            'full_name'     => $visitor->full_name,
            'institution'   => $visitor->institution,
            'no_hp'         => $visitor->no_hp,
            'no_kendaraan'  => $visitor->no_kendaraan,
            'yang_ditemui'  => $visitor->yang_ditemui,
            'urusan'        => $visitor->urusan,
            'jumlah'        => $visitor->jumlah,
            'jam_pertemuan' => $visitor->jam_pertemuan,
            'check_in_at'   => $visitor->check_in_at,
            'check_out_at'  => $visitor->check_out_at,
            'card'          => $visitor->card ? [
                'id'        => $visitor->card->id,
                'code'      => $visitor->card->code,
                'tipe'      => $visitor->card->tipe,
                'rfid_code' => $visitor->card->rfid_code,
                'status'    => $visitor->card->status,
            ] : null,
        ]);
    }

    // POST /api/visitors/scan/confirm  { batch: '081225001' }
    public function scanConfirm(Request $request)
    {
        $batch = trim((string) $request->input('batch', ''));

        if ($batch === '') {
            return response()->json([
                'message' => 'Batch harus diisi.',
            ], 422);
        }

        return DB::transaction(function () use ($batch) {
            /** @var Visitor|null $visitor */
            $visitor = Visitor::query()
                ->where('batch', $batch)
                ->whereNull('check_in_at')
                ->lockForUpdate()
                ->orderByDesc('id')
                ->first();

            if (!$visitor) {
                return response()->json([
                    'message' => 'Visitor tidak ditemukan atau sudah check-in.',
                ], 404);
            }

            /** @var Card|null $card */
            $card = Card::lockForUpdate()->find($visitor->card_id);

            if (!$card) {
                return response()->json([
                    'message' => 'Kartu tidak ditemukan di sistem.',
                ], 404);
            }

            if ($card->status !== 'booked') {
                return response()->json([
                    'message' => 'Status kartu bukan BOOKED (sudah digunakan / available).',
                ], 409);
            }

            // update visitor + card
            $visitor->update([
                'check_in_at' => now(),
            ]);

            $card->update([
                'status' => 'in_use',
            ]);

            return response()->json([
                'message' => 'Check-in berhasil. Kartu sekarang berstatus DIGUNAKAN.',
                'visitor_id' => $visitor->id,
                'card_id'    => $card->id,
            ]);
        });
    }
}
