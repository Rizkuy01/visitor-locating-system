<!doctype html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <title>Visitor Locating System</title>
</head>

<body class="min-h-screen bg-gradient-to-b from-slate-100 to-slate-200">
    <div class="max-w-7xl mx-auto px-4 py-6">
        <div class="rounded-2xl bg-white shadow-sm border border-slate-200 overflow-hidden">
            <div class="px-6 py-4 bg-red-500 text-white">
                <div class="flex items-center gap-4">
                    <!-- LOGO -->
                    <div
                        class="h-14 w-44 rounded-2xl bg-white shadow-sm ring-1 ring-black/5 flex items-center justify-center px-3 overflow-hidden">
                        <img src="/logo-kyb.png" alt="Logo" class="h-10 w-full object-contain" />
                    </div>


                    <div class="leading-tight">
                        <h1 class="text-2xl font-semibold tracking-tight">Visitor Locating System</h1>
                        <p class="text-sm text-slate-300">Security Desk</p>
                    </div>
                </div>
            </div>


            <div class="grid grid-cols-1 lg:grid-cols-4 gap-0">
                <!-- Left: Form (1/4) -->
                <section class="lg:col-span-1 p-6 border-b lg:border-b-0 lg:border-r border-slate-200">
                    <h2 class="text-xl font-semibold mb-4">Add Visitor</h2>

                    <div id="alert" class="hidden mb-4 rounded-xl border px-4 py-3 text-sm"></div>

                    <form id="visitorForm" class="space-y-4" autocomplete="off">
                        <div>
                            <label class="block text-sm font-semibold text-slate-700">Nama Lengkap</label>
                            <input name="full_name" type="text" class="mt-2 w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-slate-800 placeholder-slate-400 shadow-sm
             focus:border-blue-500 focus:ring-4 focus:ring-blue-500/15" placeholder="Nama Lengkap" autocomplete="off"
                                autocapitalize="none" autocorrect="off" spellcheck="false" required>
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-slate-700">Asal Instansi</label>
                            <input name="institution" type="text" class="mt-2 w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-slate-800 placeholder-slate-400 shadow-sm
             focus:border-blue-500 focus:ring-4 focus:ring-blue-500/15" placeholder="Asal Instansi" autocomplete="off"
                                autocapitalize="none" autocorrect="off" spellcheck="false" required>
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-slate-700">Kartu (klik kotak di
                                kanan)</label>
                            <div class="mt-2 flex items-center gap-2">
                                <input id="cardId" name="card_id" type="number" class="w-full rounded-xl border border-slate-300 bg-slate-50 px-4 py-3 text-slate-900 shadow-sm
               focus:border-blue-500 focus:ring-4 focus:ring-blue-500/15" placeholder="Belum dipilih" readonly
                                    required>
                                <button type="button" id="clearCard"
                                    class="shrink-0 rounded-xl border border-slate-300 bg-white px-3 py-3 text-sm font-semibold text-slate-700 hover:bg-slate-50">
                                    Reset
                                </button>
                            </div>
                            <p class="mt-2 text-xs text-slate-500">Akan terisi otomatis setelah klik kartu hijau.</p>
                        </div>

                        <button type="submit" class="w-full rounded-xl bg-red-500 px-4 py-3 text-white font-semibold shadow-sm
           hover:bg-red-700 focus:outline-none focus:ring-4 focus:ring-red-500/20 active:bg-red-800">
                            Tambah
                        </button>
                    </form>


                    <div class="mt-6 text-xs text-slate-500 leading-relaxed">
                        <div class="flex items-center gap-2">
                            <span class="inline-block h-3 w-3 rounded bg-emerald-500"></span> Available
                        </div>
                        <div class="flex items-center gap-2 mt-1">
                            <span class="inline-block h-3 w-3 rounded bg-red-500"></span> Digunakan
                        </div>
                    </div>
                </section>

                <!-- Right: Cards (3/4) -->
                <section class="lg:col-span-3 p-6">
                    <div
                        class="sticky top-3 z-20 mb-4 rounded-2xl border border-slate-200 bg-white/80 backdrop-blur px-4 py-3">
                        <div class="flex flex-wrap items-center justify-between gap-3">
                            <div>
                                <h2 class="text-lg font-semibold">Data Kartu</h2>
                                <p id="summaryText" class="text-xs text-slate-500">Memuat...</p>
                            </div>

                            <div class="flex items-center gap-2">
                                <div class="flex items-center gap-2 text-sm">
                                    <span
                                        class="inline-flex items-center gap-2 rounded-full border border-slate-200 bg-white px-3 py-1.5">
                                        <span class="h-2.5 w-2.5 rounded-full bg-emerald-500"></span>
                                        <span class="text-slate-700 font-semibold">Available</span>
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

                    <!-- Panels: Active Visitors + History -->
                    <div class="grid grid-cols-1 xl:grid-cols-3 gap-4 mb-4">
                        <!-- Active Visitors -->
                        <div class="xl:col-span-2 rounded-2xl border border-slate-200 bg-white p-4">
                            <div class="flex flex-wrap items-center justify-between gap-3">
                                <div>
                                    <h3 class="text-sm font-semibold text-slate-900">Visitor Aktif</h3>
                                    <p class="text-xs text-slate-500">Yang masih berada di area perusahaan</p>
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

                    <div id="cardsGrid"
                        class="grid [grid-template-columns:repeat(auto-fill,minmax(140px,1fr))] gap-3 lg:gap-3">
                    </div>
                </section>
            </div>
        </div>
    </div>

    <script>
        // ===== Helpers =====
        const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ?? '';
        const alertBox = document.getElementById('alert');
        const cardIdInput = document.getElementById('cardId');
        const grid = document.getElementById('cardsGrid');

        function showAlert(type, msg) {
            if (!alertBox) return;
            alertBox.classList.remove('hidden');
            alertBox.className = "mb-4 rounded-xl border px-4 py-3 text-sm";
            if (type === 'success') {
                alertBox.classList.add('border-emerald-200', 'bg-emerald-50', 'text-emerald-800');
            } else {
                alertBox.classList.add('border-red-200', 'bg-red-50', 'text-red-800');
            }
            alertBox.textContent = msg;
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
            return new Date(iso).toLocaleString('id-ID', { day: '2-digit', month: '2-digit', hour: '2-digit', minute: '2-digit' });
        }

        function isUserTyping() {
            const el = document.activeElement;
            if (!el) return false;
            return ['INPUT', 'TEXTAREA'].includes(el.tagName);
        }

        setInterval(async () => {
            if (isUserTyping()) return;

            await loadCards();
            const q = document.getElementById('activeSearch')?.value ?? '';
            await loadActiveVisitors(q);
        }, 5000);

        function escapeHtml(str) {
            return String(str ?? "").replace(/[&<>"']/g, (m) => ({
                "&": "&amp;", "<": "&lt;", ">": "&gt;", '"': "&quot;", "'": "&#039;"
            }[m]));
        }

        let selectedCardId = null;

        function cardButtonTemplate(card) {
            const isSelected = String(card.id) === String(selectedCardId);

            const selectedStyle = isSelected
                ? "ring-4 ring-blue-600/80 outline outline-2 outline-blue-200 shadow-lg scale-[1.01]"
                : "";

            const inUse = card.status === 'in_use';
            const base = "group relative rounded-2xl border p-4 text-left shadow-sm transition active:scale-[0.99] focus:outline-none focus:ring-4";
            const color = inUse
                ? "bg-red-500 border-red-600 hover:bg-red-600 text-white focus:ring-red-200/40"
                : "bg-emerald-500 border-emerald-600 hover:bg-emerald-600 text-white focus:ring-emerald-200/40";

            const statusText = inUse ? "DIGUNAKAN" : "AVAILABLE";

            const v = card.active_visitor;
            const tooltipTitle = inUse && v ? `${v.full_name}` : `Kartu ${card.code}`;
            const tooltipLine1 = card.rfid_code ? `RFID: ${card.rfid_code}` : `RFID: -`;
            const tooltipLine2 = inUse && v ? `Masuk: ${formatTime(v.check_in_at)}` : ``;

            const tooltipHtml = `
      <div class="pointer-events-none absolute left-1/2 top-0 z-30 w-56 -translate-x-1/2 -translate-y-3 opacity-0
                  transition group-hover:-translate-y-4 group-hover:opacity-100">
        <div class="rounded-xl bg-slate-900 text-white shadow-lg ring-1 ring-black/10 px-3 py-2">
          <div class="text-sm font-semibold">${escapeHtml(tooltipTitle)}</div>
          <div class="text-xs text-slate-200 mt-0.5">${escapeHtml(tooltipLine1)}</div>
          ${tooltipLine2 ? `<div class="text-xs text-slate-300 mt-1">${escapeHtml(tooltipLine2)}</div>` : ``}
        </div>
        <div class="mx-auto h-2 w-2 rotate-45 bg-slate-900 -mt-1"></div>
      </div>
    `;

            return `
      <button data-id="${card.id}" data-status="${card.status}" class="${base} ${color} ${selectedStyle}">
        ${tooltipHtml}

        <div class="flex items-start justify-between gap-2">
          <div class="text-3xl font-extrabold leading-none">${card.code}</div>
          <span class="rounded-full bg-white/18 px-2.5 py-1 text-[11px] font-bold uppercase tracking-wide">
            ${statusText}
          </span>
        </div>

        <div class="mt-3">
          ${inUse && v
                    ? `<div class="text-xs font-semibold text-white/95">${escapeHtml(v.full_name)}</div>
                 <div class="text-[11px] text-white/85">${escapeHtml(v.institution)}</div>
                 <div class="mt-2 inline-flex rounded-lg bg-white/18 px-2.5 py-1 text-[11px] font-semibold">Checkout</div>`
                    : `<div class="inline-flex rounded-lg bg-white/18 px-2.5 py-1 text-xs font-semibold">Klik untuk pilih</div>`
                }
        </div>
      </button>
    `;
        }

        // ===== Main =====
        async function loadCards() {
            const res = await fetch("{{ route('cards') }}", { headers: { 'Accept': 'application/json' } });
            const json = await res.json().catch(() => ({}));

            const cards = (json.cards ?? []).map(c => ({
                ...c,
                active_visitor: c.active_visitor ?? null,
            }));

            // Summary
            const total = cards.length;
            const used = cards.filter(c => c.status === 'in_use').length;
            const available = total - used;
            const summaryEl = document.getElementById('summaryText');
            if (summaryEl) summaryEl.textContent = `Total ${total} kartu • Available ${available} • Digunakan ${used}`;

            // Render
            grid.innerHTML = cards.map(cardButtonTemplate).join('');

            // Bind click cards
            grid.querySelectorAll('button[data-id]').forEach(btn => {
                btn.addEventListener('click', async () => {
                    const id = btn.getAttribute('data-id');
                    const status = btn.getAttribute('data-status');

                    if (status === 'available') {
                        selectedCardId = String(id);
                        cardIdInput.value = id;
                        showAlert('success', `Kartu terpilih: ${btn.querySelector('.text-3xl')?.textContent ?? ''}`);
                        await loadCards();
                        return;
                    }

                    // checkout confirm
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
                        headers: {
                            'X-CSRF-TOKEN': csrf,
                            'Accept': 'application/json'
                        }
                    });

                    const j2 = await res2.json().catch(() => ({}));

                    if (!res2.ok) {
                        await Swal.fire({
                            icon: 'error',
                            title: 'Gagal',
                            text: j2.message ?? 'Gagal checkout.'
                        });
                        return;
                    }

                    const name = j2.visitor?.full_name ?? '-';
                    const inst = j2.visitor?.institution ?? '-';

                    await Swal.fire({
                        icon: 'success',
                        title: 'Checkout berhasil',
                        html: `
            <div style="text-align:left">
              kartu no <b>${j2.card?.id ?? '-'}</b>, dengan kode <b>${j2.card?.code ?? '-'}</b> dikembalikan oleh
              <b>${escapeHtml(name)}</b> dari <b>${escapeHtml(inst)}</b>
            </div>
          `,
                        confirmButtonText: 'OK'
                    });

                    await loadCards();
                });
            });
        }

        let activeCache = [];

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
                const masuk = shortDateTime(v.check_in_at);
                const durasi = humanizeDuration(v.check_in_at);

                return `
                <tr class="hover:bg-slate-50">
                    <td class="py-2 pr-3 font-semibold text-slate-900">${escapeHtml(cardNo)}</td>
                    <td class="py-2 pr-3">${escapeHtml(v.full_name)}</td>
                    <td class="py-2 pr-3 text-slate-600">${escapeHtml(v.institution)}</td>
                    <td class="py-2 pr-3 text-slate-600">${masuk}</td>
                    <td class="py-2 pr-3 font-semibold text-slate-900" data-duration="${v.check_in_at}">
                        ${durasi}
                    </td>
                </tr>`;
            }).join('');
        }

        // update durasi tanpa fetch, tiap 30 detik
        setInterval(() => {
            document.querySelectorAll('[data-duration]').forEach(el => {
                el.textContent = humanizeDuration(el.getAttribute('data-duration'));
            });
        }, 30000);

        // search (debounce)
        let activeSearchTimer = null;
        document.getElementById('activeSearch')?.addEventListener('input', (e) => {
            clearTimeout(activeSearchTimer);
            activeSearchTimer = setTimeout(() => {
                loadActiveVisitors(e.target.value ?? '');
            }, 300);
        });

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
                </tr>`;
            }).join('');
        }

        document.getElementById('historyLoadBtn')?.addEventListener('click', loadHistory);

        document.getElementById('exportBtn')?.addEventListener('click', () => {
            const q = document.getElementById('historyQ')?.value ?? '';
            const from = document.getElementById('historyFrom')?.value ?? '';
            const to = document.getElementById('historyTo')?.value ?? '';

            const url = `/api/visitors/history/export?q=${encodeURIComponent(q)}&from=${encodeURIComponent(from)}&to=${encodeURIComponent(to)}`;
            window.location.href = url;
        });


        // ===== Events (bukan di dalam loadCards) =====
        document.getElementById('refreshBtn')?.addEventListener('click', loadCards);

        document.getElementById('clearCard')?.addEventListener('click', () => {
            selectedCardId = null;
            if (cardIdInput) cardIdInput.value = "";
            showAlert('success', 'Pilihan kartu di-reset.');
            loadCards();
        });

        document.getElementById('visitorForm')?.addEventListener('submit', async (e) => {
            e.preventDefault();

            const fd = new FormData(e.target);
            const payload = Object.fromEntries(fd.entries());

            if (!payload.card_id) {
                await Swal.fire({
                    icon: 'error',
                    title: 'Kartu belum dipilih',
                    text: 'Silakan klik kartu hijau yang available terlebih dahulu.',
                    confirmButtonText: 'OK'
                });
                return;
            }

            const res = await fetch("{{ route('visitors.store') }}", {
                method: "POST",
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrf,
                    'Accept': 'application/json'
                },
                body: JSON.stringify(payload)
            });

            const json = await res.json().catch(() => ({}));

            if (!res.ok) {
                await Swal.fire({
                    icon: 'error',
                    title: 'Gagal',
                    text: json.message ?? 'Gagal menambah visitor.'
                });
                return;
            }

            await Swal.fire({
                icon: 'success',
                title: 'Berhasil',
                html: `
        <div style="text-align:left">
          kartu no <b>${json.card?.id ?? '-'}</b>, dengan kode <b>${json.card?.code ?? '-'}</b> digunakan oleh
          <b>${escapeHtml(json.visitor?.full_name ?? '-')}</b> dari <b>${escapeHtml(json.visitor?.institution ?? '-')}</b>
        </div>
      `,
                confirmButtonText: 'OK'
            });

            e.target.reset();
            if (cardIdInput) cardIdInput.value = "";
            await loadCards();
        });

        // init
        loadCards();
        loadActiveVisitors();
        loadHistory(); 
    </script>


</body>

</html>