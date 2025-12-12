@extends('layouts.main')

@section('title', 'Daftar Visitor')

@section('content')
    <div class="max-w-7xl mx-auto pr-8">
        <div class="rounded-2xl bg-white shadow-sm border border-slate-200 overflow-hidden">
            <div class="px-5 py-4 bg-red-500 text-white rounded-t-2xl">
                <div class="flex items-center justify-between gap-4 flex-wrap">
                    <div>
                        <h1 class="text-2xl font-semibold">Daftar Visitor</h1>
                        <p class="text-sm text-white/70">Data visitor PT. Kayaba Indonesia</p>
                    </div>
                </div>
            </div>

            <div class="p-5 space-y-4">
                <div class="flex flex-nowrap items-center gap-3 overflow-x-auto pb-2">
                    <!-- Search -->
                    <div class="min-w-[320px] flex-1">
                        <input id="q" type="text"
                            class="w-full h-11 rounded-xl border border-slate-300 px-3 text-sm focus:border-red-500 focus:ring-4 focus:ring-red-500/10"
                            placeholder="batch / nama / instansi / kartu / rfid / no kendaraan..." />
                    </div>

                    <!-- Tanggal dari -->
                    <div class="min-w-[180px]">
                        <input id="from" type="date" class="w-full h-11 rounded-xl border border-slate-300 px-3 text-sm" />
                    </div>

                    <!-- Tanggal sampai -->
                    <div class="min-w-[180px]">
                        <input id="to" type="date" class="w-full h-11 rounded-xl border border-slate-300 px-3 text-sm" />
                    </div>

                    <!-- Buttons -->
                    <div class="flex items-center gap-2 min-w-[210px]">
                        <button id="btnApply"
                            class="h-11 rounded-xl bg-red-500 px-6 text-sm font-semibold text-white hover:bg-slate-800 whitespace-nowrap">
                            Terapkan
                        </button>
                        <button id="btnReset"
                            class="h-11 rounded-xl border border-slate-300 bg-white px-5 text-sm hover:bg-slate-50 whitespace-nowrap">
                            Reset
                        </button>
                    </div>
                </div>

                <p class="text-[11px] text-slate-500">
                    Tips: kosongkan tanggal untuk tampilkan semua data.
                </p>

                {{-- TABLE WRAPPER --}}
                <div class="rounded-2xl border border-slate-200 overflow-hidden">
                    <div class="w-full overflow-x-auto pb-1">
                        <table class="w-full text-sm">
                            <thead class="bg-slate-400 text-white text-xs">
                                <tr>
                                    <th class="px-3 py-3 text-center rounded-tl-2xl">Tanggal</th>
                                    <th class="px-3 py-3 text-center">Batch</th>
                                    <th class="px-3 py-3 text-center">Nama</th>
                                    <th class="px-3 py-3 text-center">Instansi</th>
                                    <th class="px-3 py-3 text-center">No HP</th>
                                    <th class="px-3 py-3 text-center">No Kendaraan</th>
                                    <th class="px-3 py-3 text-center">Yang Ditemui</th>
                                    <th class="px-3 py-3 text-center">Urusan</th>
                                    <th class="px-3 py-3 text-center">Jumlah</th>
                                    <th class="px-3 py-3 text-center">Jam</th>
                                    <th class="px-3 py-3 text-center">Kartu</th>
                                    <th class="px-3 py-3 text-center">Tipe</th>
                                    <th class="px-3 py-3 text-center">Check-in</th>
                                    <th class="px-3 py-3 text-center">Check-out</th>
                                    <th class="px-3 py-3 text-center rounded-tr-2xl">Status</th>
                                </tr>
                            </thead>
                            <tbody id="tbody" class="divide-y divide-slate-100 bg-white"></tbody>
                        </table>
                    </div>

                    <div class="flex items-center justify-between gap-3 px-4 py-3 bg-white border-t border-slate-200">
                        <div id="info" class="text-xs text-slate-600">-</div>
                        <div class="flex items-center gap-2">
                            <button id="prev"
                                class="rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm hover:bg-slate-50 disabled:opacity-50 disabled:cursor-not-allowed">
                                Prev
                            </button>
                            <button id="next"
                                class="rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm hover:bg-slate-50 disabled:opacity-50 disabled:cursor-not-allowed">
                                Next
                            </button>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
@endsection


@push('scripts')
    <script>
        const tbody = document.getElementById('tbody');
        const info = document.getElementById('info');
        const prevBtn = document.getElementById('prev');
        const nextBtn = document.getElementById('next');
        const qEl = document.getElementById('q');
        const fromEl = document.getElementById('from');
        const toEl = document.getElementById('to');

        let page = 1;

        function escapeHtml(str) {
            return String(str ?? "").replace(/[&<>"']/g, (m) => ({
                "&": "&amp;", "<": "&lt;", ">": "&gt;", '"': "&quot;", "'": "&#039;"
            }[m]));
        }

        function fmt(iso) {
            if (!iso) return '-';
            const d = new Date(iso);
            return d.toLocaleString('id-ID', {
                day: '2-digit',
                month: '2-digit',
                year: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            });
        }

        function getStatus(row) {
            if (row.check_out_at) return 'DONE';
            if (row.check_in_at) return 'IN_USE';
            return 'BOOKED';
        }

        async function load() {
            const params = new URLSearchParams();
            params.set('page', String(page));
            if (qEl.value.trim()) params.set('q', qEl.value.trim());
            if (fromEl.value) params.set('from', fromEl.value);
            if (toEl.value) params.set('to', toEl.value);

            const res = await fetch(`/api/visitors/table?${params.toString()}`, {
                headers: { Accept: 'application/json' }
            });
            const json = await res.json();

            const rows = json.data ?? [];
            tbody.innerHTML = rows.map(r => {
                const st = getStatus(r);
                const badge =
                    st === 'IN_USE' ? 'bg-red-100 text-red-700' :
                        st === 'BOOKED' ? 'bg-amber-100 text-amber-700' :
                            'bg-emerald-100 text-emerald-700';

                return `
                                                            <tr class="hover:bg-slate-50">
                                                                <td class="px-3 py-2 whitespace-nowrap">${escapeHtml(r.tanggal)}</td>
                                                                <td class="px-3 py-2 font-mono whitespace-nowrap">${escapeHtml(r.batch)}</td>
                                                                <td class="px-3 py-2 font-semibold">${escapeHtml(r.full_name)}</td>
                                                                <td class="px-3 py-2 font-semibold">${escapeHtml(r.institution)}</td>
                                                                <td class="px-3 py-2 whitespace-nowrap">${escapeHtml(r.no_hp || '-')}</td>
                                                                <td class="px-3 py-2 whitespace-nowrap">${escapeHtml(r.no_kendaraan || '-')}</td>
                                                                <td class="px-3 py-2">${escapeHtml(r.yang_ditemui || '-')}</td>
                                                                <td class="px-3 py-2">${escapeHtml(r.urusan || '-')}</td>
                                                                <td class="px-3 py-2 whitespace-nowrap">${escapeHtml(r.jumlah ?? '-')} Orang</td>
                                                                <td class="px-3 py-2 whitespace-nowrap">${escapeHtml(r.jam_pertemuan || '-')}</td>
                                                                <td class="px-3 py-2 whitespace-nowrap font-semibold">${escapeHtml(r.card?.code ?? '-')}</td>
                                                                <td class="px-3 py-2 whitespace-nowrap">${escapeHtml(String(r.card?.tipe ?? '-').toUpperCase())}</td>
                                                                <td class="px-3 py-2 whitespace-nowrap">${fmt(r.check_in_at)}</td>
                                                                <td class="px-3 py-2 whitespace-nowrap">${fmt(r.check_out_at)}</td>
                                                                <td class="px-3 py-2 whitespace-nowrap">
                                                                    <span class="inline-flex rounded-full px-2 py-1 text-[11px] font-bold ${badge}">${st}</span>
                                                                </td>
                                                            </tr>
                                                        `;
            }).join('');

            info.textContent = `Total: ${json.total ?? 0} • Page ${json.current_page ?? 1} / ${json.last_page ?? 1}`;

            prevBtn.disabled = !(json.prev_page_url);
            nextBtn.disabled = !(json.next_page_url);
        }

        document.getElementById('btnApply').addEventListener('click', () => {
            page = 1;
            load();
        });

        document.getElementById('btnReset').addEventListener('click', () => {
            qEl.value = '';
            fromEl.value = '';
            toEl.value = '';
            page = 1;
            load();
        });

        prevBtn.addEventListener('click', () => {
            if (page > 1) {
                page--;
                load();
            }
        });

        nextBtn.addEventListener('click', () => {
            page++;
            load();
        });

        qEl.addEventListener('keydown', (e) => {
            if (e.key === 'Enter') {
                e.preventDefault();
                page = 1;
                load();
            }
        });

        load();
    </script>
@endpush