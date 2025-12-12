@extends('layouts.main')

@section('title', 'Security Desk - Visitor Locating System')

@section('content')
    <div class="rounded-2xl bg-white shadow-sm border border-slate-200 overflow-hidden">
        <!-- Header -->
        <div class="px-6 py-4 bg-red-500 text-white">
            <div class="flex items-center gap-4">
                {{-- <div
                    class="h-14 w-44 rounded-2xl bg-white shadow-sm ring-1 ring-black/5 flex items-center justify-center px-3 overflow-hidden">
                    <img src="/logo-kyb.png" alt="Logo" class="h-10 w-full object-contain" />
                </div> --}}

                <div class="leading-tight">
                    <h1 class="text-2xl font-semibold tracking-tight">Visitor Locating System</h1>
                    <p class="text-sm text-white/80">Security Desk (Monitoring)</p>
                </div>
            </div>
        </div>

        <!-- Content -->
        <div class="p-6">

            <!-- Panels: Active Visitors + History -->
            <div class="grid grid-cols-1 xl:grid-cols-3 gap-4 mb-4">
                <!-- Active Visitors -->
                <div class="xl:col-span-2 rounded-2xl border border-slate-200 bg-white p-4">
                    <div class="flex flex-wrap items-center justify-between gap-3">
                        <div>
                            <h3 class="text-sm font-semibold text-slate-900">Visitor Aktif</h3>
                        </div>

                        <div class="flex items-center gap-2">
                            <input id="activeSearch" type="text" autocomplete="off"
                                class="w-64 rounded-xl border border-slate-300 px-3 py-2 text-sm focus:border-red-500 focus:ring-4 focus:ring-red-500/15"
                                placeholder="Cari nama / instansi / kartu..." />
                        </div>
                    </div>

                    <div class="mt-3 overflow-auto">
                        <table class="w-full text-sm">
                            <thead class="text-left text-xs text-slate-500">
                                <tr>
                                    <th class="py-2 pr-3">Kartu</th>
                                    <th class="py-2 pr-3">Nama</th>
                                    <th class="py-2 pr-3">Instansi</th>
                                    <th class="py-2 pr-3">Kontak</th>
                                    <th class="py-2 pr-3">Masuk</th>
                                    <th class="py-2 pr-3">Durasi</th>
                                </tr>
                            </thead>
                            <tbody id="activeTbody" class="divide-y divide-slate-100"></tbody>
                        </table>
                    </div>
                </div>

                <!-- History -->
                <div class="rounded-2xl border border-slate-200 bg-white p-4">
                    <div class="flex items-center justify-between gap-2">
                        <div>
                            <h3 class="text-sm font-semibold text-slate-900">Histori</h3>
                            <p class="text-xs text-slate-500">Filter tanggal + export CSV</p>
                        </div>

                        <button id="exportBtn"
                            class="rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm hover:bg-slate-50">
                            Export
                        </button>
                    </div>

                    <div class="mt-3 space-y-2">
                        <input id="historyQ" type="text" autocomplete="off"
                            class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm focus:border-red-500 focus:ring-4 focus:ring-red-500/15"
                            placeholder="Cari nama / instansi / kartu..." />

                        <div class="grid grid-cols-2 gap-2">
                            <input id="historyFrom" type="date"
                                class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm">
                            <input id="historyTo" type="date"
                                class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm">
                        </div>

                        <button id="historyLoadBtn"
                            class="w-full rounded-xl bg-slate-900 px-3 py-2 text-sm font-semibold text-white hover:bg-slate-800">
                            Tampilkan
                        </button>

                        <div class="mt-2 max-h-56 overflow-auto border border-slate-100 rounded-xl">
                            <table class="w-full text-xs">
                                <thead class="text-left text-slate-500 bg-slate-50">
                                    <tr>
                                        <th class="py-2 px-2">Kartu</th>
                                        <th class="py-2 px-2">Nama</th>
                                        <th class="py-2 px-2">Masuk</th>
                                        <th class="py-2 px-2">Keluar</th>
                                    </tr>
                                </thead>
                                <tbody id="historyTbody" class="divide-y divide-slate-100"></tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>


            <!-- Sticky summary -->
            <div class="sticky top-3 z-20 mb-4 rounded-2xl border border-slate-200 bg-white/80 backdrop-blur px-4 py-3">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <div>
                        <h2 class="text-lg font-semibold">Data Kartu</h2>
                        <p id="summaryText" class="text-xs text-slate-500">Cek data visitor di dashboard ini.</p>
                    </div>

                    <div class="flex items-center gap-2">
                        <div class="flex flex-wrap items-center gap-2 text-sm">
                            <span
                                class="inline-flex items-center gap-2 rounded-full border border-slate-200 bg-white px-3 py-1.5">
                                <span class="h-2.5 w-2.5 rounded-full bg-emerald-500"></span>
                                <span class="text-slate-700 font-semibold">Available</span>
                            </span>

                            <span
                                class="inline-flex items-center gap-2 rounded-full border border-slate-200 bg-white px-3 py-1.5">
                                <span class="h-2.5 w-2.5 rounded-full bg-amber-500"></span>
                                <span class="text-slate-700 font-semibold">Booked</span>
                            </span>

                            <span
                                class="inline-flex items-center gap-2 rounded-full border border-slate-200 bg-white px-3 py-1.5">
                                <span class="h-2.5 w-2.5 rounded-full bg-red-500"></span>
                                <span class="text-slate-700 font-semibold">Digunakan</span>
                            </span>
                        </div>

                        <button id="refreshBtn"
                            class="rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm hover:bg-slate-50">
                                        Refresh
                        </button>
                    </div>
                </div>
            </div>
            <!-- Cards: Office | Plant (divider vertikal) -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Office -->
                <div>
                    <div class="mb-2 flex items-center justify-between">
                        <div class="text-xs font-semibold tracking-wider text-slate-600 uppercase">Office</div>
                        <div id="officeSummary" class="text-xs text-slate-500"></div>
                    </div>

                    <div id="officeGrid" class="grid [grid-template-columns:repeat(auto-fill,minmax(48px,1fr))] gap-1">
                    </div>
                </div>

                <!-- Plant -->
                <div class="lg:border-l lg:border-slate-200 lg:pl-6">
                    <div class="mb-2 flex items-center justify-between">
                        <div class="text-xs font-semibold tracking-wider text-slate-600 uppercase">Plant</div>
                        <div id="plantSummary" class="text-xs text-slate-500"></div>
                    </div>

                    <div id="plantGrid" class="grid [grid-template-columns:repeat(auto-fill,minmax(48px,1fr))] gap-1">
                    </div>
                </div>
            </div>


            <!-- Divider versi mobile-->
            <div class="my-4 h-px w-full bg-slate-200 lg:hidden"></div>
        </div>
    </div>

    @push('scripts')
        <script>
            // ====== Globals ======
            const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ?? '';
            const officeGrid = document.getElementById('officeGrid');
            const plantGrid = document.getElementById('plantGrid');

            let activeCache = [];

            // ===== Helpers =====
            function escapeHtml(str) {
                return String(str ?? "").replace(/[&<>"']/g, (m) => ({
                    "&": "&amp;", "<": "&lt;", ">": "&gt;", '"': "&quot;", "'": "&#039;"
                }[m]));
            }

            function displayCardNo(code) {
                const s = String(code ?? '');
                const m = s.match(/(\d{3})$/);
                if (m) return m[1];
                const n = parseInt(s, 10);
                if (Number.isFinite(n)) return String(n).padStart(3, '0');
                return s;
            }


            function formatTime(iso) {
                if (!iso) return "-";
                const d = new Date(iso);
                return d.toLocaleString('id-ID', {
                    day: '2-digit', month: '2-digit', year: 'numeric',
                    hour: '2-digit', minute: '2-digit'
                });
            }

            function humanizeDuration(fromIso) {
                if (!fromIso) return '-';
                const start = new Date(fromIso).getTime();
                const diff = Math.max(0, Date.now() - start);
                const mins = Math.floor(diff / 60000);
                const h = Math.floor(mins / 60);
                const m = mins % 60;
                return h <= 0 ? `${m}m` : `${h}h ${m}m`;
            }

            function shortDateTime(iso) {
                if (!iso) return '-';
                return new Date(iso).toLocaleString('id-ID', {
                    day: '2-digit', month: '2-digit', hour: '2-digit', minute: '2-digit'
                });
            }

            function isUserTyping() {
                const el = document.activeElement;
                return !!el && ['INPUT', 'TEXTAREA'].includes(el.tagName);
            }

            // Update durasi visitor aktif
            setInterval(() => {
                document.querySelectorAll('[data-duration]').forEach(el => {
                    el.textContent = humanizeDuration(el.getAttribute('data-duration'));
                });
            }, 30000);

            // ===== Card UI =====
            function cardButtonTemplate(card) {
                const status = String(card.status ?? '').toLowerCase();

                // warna dasar per status (kursi kecil)
                let color = "bg-emerald-500 border-emerald-600 text-white"; // available
                if (status === 'booked') {
                    color = "bg-amber-500 border-amber-600 text-white";
                } else if (status === 'in_use') {
                    color = "bg-red-500 border-red-600 text-white";
                }

                const base =
                    "group relative flex items-center justify-center " +
                    "rounded-md border shadow-sm text-xs font-semibold " +
                    "w-10 h-10 sm:w-12 sm:h-12 " + // ukuran kursi
                    "transition active:scale-95 focus:outline-none focus:ring-2 focus:ring-offset-1";

                const vInUse = card.active_visitor ?? null;
                const vBooked = card.booked_visitor ?? null;

                const tooltipTitle =
                    (status === 'in_use' && vInUse)
                        ? `${vInUse.full_name}`
                        : `Kartu ${displayCardNo(card.code)}`;

                // RFID dipotong 10 karakter + "..."
                const fullRfid = String(card.rfid_code ?? '');
                let shortRfid = fullRfid || '-';
                if (fullRfid && fullRfid.length > 10) {
                    shortRfid = fullRfid.slice(0, 10) + '…';
                }

                const tooltipLine1 = `RFID: ${shortRfid}`;

                let tooltipLine2 = '';
                if (status === 'booked' && vBooked) {
                    tooltipLine2 = `Booked by: ${vBooked.full_name}`;
                } else if (status === 'in_use' && vInUse) {
                    tooltipLine2 = `Used by: ${vInUse.full_name}`;
                }

                const tooltipLine3 =
                    (status === 'in_use' && vInUse)
                        ? `Masuk: ${formatTime(vInUse.check_in_at)}`
                        : '';

                // tooltip hanya untuk booked & in_use
                const showTooltip = status === 'booked' || status === 'in_use';

                const tooltipHtml = !showTooltip ? '' : `
                                                              <div class="pointer-events-none absolute left-1/2 top-0 z-30 w-40 -translate-x-1/2 -translate-y-2
                                                                          opacity-0 transition group-hover:-translate-y-4 group-hover:opacity-100">
                                                                <div class="rounded-lg bg-slate-900 text-white shadow-lg ring-1 ring-black/10 px-3 py-2">
                                                                  <div class="text-xs font-semibold">${escapeHtml(tooltipTitle)}</div>
                                                                  <div class="text-[11px] text-slate-200 mt-0.5">${escapeHtml(tooltipLine1)}</div>
                                                                  ${tooltipLine2 ? `<div class="text-[11px] text-slate-300 mt-1">${escapeHtml(tooltipLine2)}</div>` : ``}
                                                                  ${tooltipLine3 ? `<div class="text-[11px] text-slate-300 mt-1">${escapeHtml(tooltipLine3)}</div>` : ``}
                                                                </div>
                                                                <div class="mx-auto h-2 w-2 rotate-45 bg-slate-900 -mt-1"></div>
                                                              </div>
                                                            `;

                return `
                                                              <button data-id="${card.id}" data-status="${status}" class="${base} ${color}">
                                                                ${tooltipHtml}
                                                                <span class="leading-none">${escapeHtml(displayCardNo(card.code))}</span>
                                                              </button>
                                                            `;
            }

            function normalizeCard(c) {
                const codeStr = String(c.code ?? '').toUpperCase();

                // fallback based on code prefix
                const fallbackTipe =
                    codeStr.startsWith('VM') ? 'plant' :
                        codeStr.startsWith('VB') ? 'office' :
                            'office';

                return {
                    ...c,
                    active_visitor: c.active_visitor ?? null,
                    tipe: String(c.tipe ?? fallbackTipe).toLowerCase(),
                    status: String(c.status ?? '').toLowerCase(),
                };
            }

            // ===== Data loaders =====
            async function loadCards() {
                const res = await fetch("/api/cards", { headers: { Accept: "application/json" } });

                const json = await res.json().catch(() => ({}));

                const cards = (json.cards ?? []).map((c) => {
                    const tipe = String(c.tipe ?? "").toLowerCase();
                    const status = String(c.status ?? "").toLowerCase();

                    const rawCode = String(c.code ?? "");
                    const m = rawCode.match(/(\d{1,3})$/);
                    const displayCode = m ? String(m[1]).padStart(3, "0") : rawCode;

                    return { ...c, tipe, status, displayCode, active_visitor: c.active_visitor ?? null };
                });

                const officeCards = cards.filter((c) => c.tipe === "office");
                const plantCards = cards.filter((c) => c.tipe === "plant");

                officeGrid.innerHTML = officeCards.map(cardButtonTemplate).join("");
                plantGrid.innerHTML = plantCards.map(cardButtonTemplate).join("");
            }

            async function loadActiveVisitors(q = '') {
                const res = await fetch(`/api/visitors/active?q=${encodeURIComponent(q)}`, {
                    headers: { 'Accept': 'application/json' }
                });
                const json = await res.json().catch(() => ({ data: [] }));
                activeCache = json.data ?? [];
                renderActiveVisitors();
            }

            function renderActiveVisitors() {
                const tbody = document.getElementById('activeTbody');
                if (!tbody) return;

                tbody.innerHTML = activeCache.map(v => {
                    const cardNo = v.card?.code ?? '-';
                    const kontak = v.no_hp ? v.no_hp : '-';
                    const masuk = shortDateTime(v.check_in_at);
                    const durasi = humanizeDuration(v.check_in_at);

                    return `
                                                                <tr class="hover:bg-slate-50">
                                                                  <td class="py-2 pr-3 font-semibold text-slate-900">${escapeHtml(cardNo)}</td>
                                                                  <td class="py-2 pr-3">${escapeHtml(v.full_name)}</td>
                                                                  <td class="py-2 pr-3 text-slate-600">${escapeHtml(v.institution)}</td>
                                                                  <td class="py-2 pr-3 text-slate-600">${escapeHtml(v.no_hp)}</td>
                                                                  <td class="py-2 pr-3 text-slate-600">${masuk}</td>
                                                                  <td class="py-2 pr-3 font-semibold text-slate-900" data-duration="${v.check_in_at}">${durasi}</td>
                                                                </tr>
                                                              `;
                }).join('');
            }

            async function loadHistory() {
                const q = document.getElementById('historyQ')?.value ?? '';
                const from = document.getElementById('historyFrom')?.value ?? '';
                const to = document.getElementById('historyTo')?.value ?? '';

                const url = `/api/visitors/history?q=${encodeURIComponent(q)}&from=${encodeURIComponent(from)}&to=${encodeURIComponent(to)}`;
                const res = await fetch(url, { headers: { 'Accept': 'application/json' } });
                const json = await res.json().catch(() => ({}));

                const rows = json.data ?? [];
                const tbody = document.getElementById('historyTbody');
                if (!tbody) return;

                tbody.innerHTML = rows.map(v => {
                    const cardNo = v.card?.code ?? '-';
                    return `
                                                                <tr class="hover:bg-slate-50">
                                                                  <td class="py-2 px-2 font-semibold">${escapeHtml(cardNo)}</td>
                                                                  <td class="py-2 px-2">${escapeHtml(v.full_name)}</td>
                                                                  <td class="py-2 px-2 text-slate-600">${shortDateTime(v.check_in_at)}</td>
                                                                  <td class="py-2 px-2 text-slate-600">${shortDateTime(v.check_out_at)}</td>
                                                                </tr>
                                                              `;
                }).join('');
            }

            // ===== Events =====
            // Search active (debounce)
            let activeSearchTimer = null;
            document.getElementById('activeSearch')?.addEventListener('input', (e) => {
                clearTimeout(activeSearchTimer);
                activeSearchTimer = setTimeout(() => loadActiveVisitors(e.target.value ?? ''), 300);
            });

            document.getElementById('historyLoadBtn')?.addEventListener('click', loadHistory);

            document.getElementById('exportBtn')?.addEventListener('click', () => {
                const q = document.getElementById('historyQ')?.value ?? '';
                const from = document.getElementById('historyFrom')?.value ?? '';
                const to = document.getElementById('historyTo')?.value ?? '';
                window.location.href = `/api/visitors/history/export?q=${encodeURIComponent(q)}&from=${encodeURIComponent(from)}&to=${encodeURIComponent(to)}`;
            });

            document.getElementById('refreshBtn')?.addEventListener('click', async () => {
                await loadCards();
                await loadActiveVisitors(document.getElementById('activeSearch')?.value ?? '');
            });

            // ✅ Delegation
            function attachCardDelegation(container) {
                if (!container) return;
                container.addEventListener('click', async (e) => {
                    const btn = e.target.closest('button[data-id]');
                    if (!btn || !container.contains(btn)) return;

                    const id = btn.getAttribute('data-id');
                    const status = btn.getAttribute('data-status');

                    if (status === 'available') return;

                    if (status === 'booked') {
                        await Swal.fire({
                            icon: 'info',
                            title: 'Kartu sudah dibooking',
                            text: 'Kartu ini sudah dibooking oleh calon visitor.',
                            confirmButtonText: 'OK'
                        });
                        return;
                    }

                    const result = await Swal.fire({
                        icon: 'warning',
                        title: 'Konfirmasi Checkout',
                        text: 'Apakah anda yakin visitor akan keluar area perusahaan?',
                        showCancelButton: true,
                        confirmButtonText: 'Ya, keluar',
                        cancelButtonText: 'Batal',
                        reverseButtons: true,
                    });
                    if (!result.isConfirmed) return;

                    const res2 = await fetch(`/api/cards/${id}/checkout`, {
                        method: 'POST',
                        headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' }
                    });
                    const j2 = await res2.json().catch(() => ({}));

                    if (!res2.ok) {
                        await Swal.fire({ icon: 'error', title: 'Gagal', text: j2.message ?? 'Gagal checkout.' });
                        return;
                    }

                    await Swal.fire({
                        icon: 'success',
                        title: 'Checkout berhasil',
                        text: j2.message ?? 'Visitor checkout. Kartu kembali available.',
                        confirmButtonText: 'OK'
                    });

                    await loadCards();
                    await loadActiveVisitors(document.getElementById('activeSearch')?.value ?? '');
                });
            }

            attachCardDelegation(officeGrid);
            attachCardDelegation(plantGrid);

            // Polling
            setInterval(async () => {
                if (isUserTyping()) return;
                await loadCards();
                await loadActiveVisitors(document.getElementById('activeSearch')?.value ?? '');
            }, 5000);

            // init
            loadCards();
            loadActiveVisitors();
            loadHistory();
        </script>
    @endpush
@endsection