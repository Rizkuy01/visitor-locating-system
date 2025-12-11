<?php

namespace App\Http\Controllers;

use App\Models\Visitor;
use Illuminate\Http\Request;

class VisitorTableController extends Controller
{
    public function index()
    {
        return view('visitor-table');
    }

    public function data(Request $request)
    {
        $q = trim((string) $request->query('q', ''));
        $from = $request->query('from');
        $to   = $request->query('to');

        $rows = Visitor::query()
            ->select([
                'id',
                'tanggal',
                'batch',
                'full_name',
                'institution',
                'card_id',
                'no_hp',
                'no_kendaraan',
                'yang_ditemui',
                'urusan',
                'jumlah',
                'jam_pertemuan',
                'check_in_at',
                'check_out_at',
                'created_at',
            ])
            ->with(['card:id,code,tipe,rfid_code'])
            ->when($from, fn($qq) => $qq->whereDate('tanggal', '>=', $from))
            ->when($to,   fn($qq) => $qq->whereDate('tanggal', '<=', $to))
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($w) use ($q) {
                    $w->where('batch', 'like', "%{$q}%")
                        ->orWhere('full_name', 'like', "%{$q}%")
                        ->orWhere('institution', 'like', "%{$q}%")
                        ->orWhere('no_kendaraan', 'like', "%{$q}%")
                        ->orWhereHas('card', function ($c) use ($q) {
                            $c->where('code', 'like', "%{$q}%")
                              ->orWhere('rfid_code', 'like', "%{$q}%");
                        });
                });
            })
            ->orderBy('id', 'desc')
            ->paginate(7);

        // payload transformation
        $rows->getCollection()->transform(function ($v) {
            return [
                'id' => $v->id,
                'tanggal' => optional($v->tanggal)->format('Y-m-d'),
                'batch' => $v->batch,
                'full_name' => $v->full_name,
                'institution' => $v->institution,
                'no_hp' => $v->no_hp,
                'no_kendaraan' => $v->no_kendaraan,
                'yang_ditemui' => $v->yang_ditemui,
                'urusan' => $v->urusan,
                'jumlah' => $v->jumlah,
                'jam_pertemuan' => $v->jam_pertemuan,
                'check_in_at' => $v->check_in_at?->toISOString(),
                'check_out_at' => $v->check_out_at?->toISOString(),
                'created_at' => $v->created_at?->toISOString(),
                'card' => $v->card ? [
                    'id' => $v->card->id,
                    'code' => $v->card->code,
                    'tipe' => $v->card->tipe,
                    'rfid_code' => $v->card->rfid_code,
                ] : null,
            ];
        });

        return response()->json($rows);
    }
}
