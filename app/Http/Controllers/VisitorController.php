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

    public function active(Request $request)
    {
        $q = trim((string) $request->query('q', ''));

        $rows = Visitor::query()
            ->select(['id','full_name','institution','card_id','check_in_at'])
            ->with(['card:id,code,rfid_code'])
            ->whereNull('check_out_at')
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

        return response()->json(['data' => $rows]);
    }

    public function history(Request $request)
{
    $q = trim((string) $request->query('q', ''));
    $from = $request->query('from'); // YYYY-MM-DD
    $to   = $request->query('to');   // YYYY-MM-DD

    $rows = Visitor::query()
        ->select(['id','full_name','institution','card_id','check_in_at','check_out_at'])
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
        ->select(['id','full_name','institution','card_id','check_in_at','check_out_at'])
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

        fputcsv($out, ['ID', 'Nama', 'Instansi', 'Card Code', 'RFID', 'Check In', 'Check Out'], $delimiter);

        foreach ($rows as $v) {
            $cardCode = optional($v->card)->code ?? '';
            $rfid     = optional($v->card)->rfid_code ?? '';

            // Trik biar Excel tidak ubah jadi scientific notation / auto-format
            // akan tampil sebagai teks
            $cardCodeExcel = $cardCode !== '' ? '="'.$cardCode.'"' : '';
            $rfidExcel     = $rfid !== '' ? '="'.$rfid.'"' : '';

            $checkIn  = $v->check_in_at ? Carbon::parse($v->check_in_at)->format('Y-m-d H:i:s') : '';
            $checkOut = $v->check_out_at ? Carbon::parse($v->check_out_at)->format('Y-m-d H:i:s') : '';

            fputcsv($out, [
                $v->id,
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

}
