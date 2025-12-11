@extends('layouts.main')

@section('title', 'Scan / Konfirmasi Visitor')

@section('content')
    <div class="rounded-2xl bg-white shadow-sm border border-slate-200 overflow-hidden">
        <div class="px-5 py-4 bg-red-500 text-white flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-semibold">QR Scanner</h1>
                <p class="text-sm text-white/70">Scan / ketik batch, lalu konfirmasi visitor.</p>
            </div>
        </div>

        <div class="p-5 space-y-4">
            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1">Batch</label>
                <input id="batchInput" type="text" autofocus class="w-full rounded-xl border border-slate-300 px-4 py-3 text-lg tracking-[0.25em] text-center font-mono
                                                          focus:border-slate-900 focus:ring-4 focus:ring-slate-900/10"
                    placeholder="Scan / ketik batch di sini">
                <p class="mt-2 text-[11px] text-slate-500">
                    Tips: scanner biasanya mengirim ENTER di akhir. Sistem juga akan otomatis mencari setelah 5
                    detik tidak ada input.
                </p>
            </div>

            <div id="status" class="text-xs text-slate-500">Menunggu input batch...</div>

            <div id="result"></div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ?? '';
        const batchInput = document.getElementById('batchInput');
        const resultEl = document.getElementById('result');
        const statusEl = document.getElementById('status');

        let lookupTimer = null;

        function escapeHtml(str) {
            return String(str ?? "").replace(/[&<>"']/g, (m) => ({
                "&": "&amp;", "<": "&lt;", ">": "&gt;", '"': "&quot;", "'": "&#039;"
            }[m]));
        }

        function fmtDateTime(iso) {
            if (!iso) return '-';
            const d = new Date(iso);
            return d.toLocaleString('id-ID', {
                day: '2-digit', month: '2-digit', year: 'numeric',
                hour: '2-digit', minute: '2-digit'
            });
        }

        function clearResult(msg = 'Menunggu input batch...') {
            resultEl.innerHTML = '';
            statusEl.textContent = msg;
        }

        async function lookupBatch() {
            const batch = batchInput.value.trim();
            if (!batch) {
                clearResult('Batch kosong.');
                return;
            }

            clearResult('Mencari data visitor...');
            try {
                const res = await fetch(`/api/visitors/scan?batch=${encodeURIComponent(batch)}`, {
                    headers: { 'Accept': 'application/json' }
                });

                if (!res.ok) {
                    const j = await res.json().catch(() => ({}));
                    clearResult(j.message ?? 'Data tidak ditemukan.');
                    return;
                }

                const v = await res.json();
                renderVisitor(v);
            } catch (e) {
                console.error(e);
                clearResult('Terjadi kesalahan jaringan.');
            }
        }

        function renderVisitor(v) {
            const status =
                v.check_out_at ? 'DONE' :
                    v.check_in_at ? 'IN_USE' :
                        'BOOKED';

            let statusBadgeClass =
                status === 'IN_USE' ? 'bg-red-100 text-red-700' :
                    status === 'BOOKED' ? 'bg-amber-100 text-amber-700' :
                        'bg-emerald-100 text-emerald-700';

            statusEl.textContent = 'Data ditemukan. Mohon konfirmasi.';

            resultEl.innerHTML = `
                                <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4 space-y-3">
                                  <div class="flex items-center justify-between gap-3">
                                    <div>
                                      <div class="text-[11px] text-slate-500">Batch</div>
                                      <div class="font-mono text-lg font-semibold">${escapeHtml(v.batch)}</div>
                                      <div class="text-xs text-slate-500 mt-1">Tanggal: ${escapeHtml(v.tanggal ?? '-')}</div>
                                    </div>
                                    <span class="inline-flex rounded-full px-3 py-1 text-[11px] font-bold ${statusBadgeClass}">
                                      ${status}
                                    </span>
                                  </div>

                                  <div class="grid grid-cols-2 gap-3 text-sm">
                                    <div>
                                      <div class="text-[11px] text-slate-500">Nama</div>
                                      <div class="font-semibold text-slate-900">${escapeHtml(v.full_name)}</div>
                                    </div>
                                    <div>
                                      <div class="text-[11px] text-slate-500">Instansi</div>
                                      <div class="text-slate-800">${escapeHtml(v.institution ?? '-')}</div>
                                    </div>
                                    <div>
                                      <div class="text-[11px] text-slate-500">No HP</div>
                                      <div>${escapeHtml(v.no_hp ?? '-')}</div>
                                    </div>
                                    <div>
                                      <div class="text-[11px] text-slate-500">No Kendaraan</div>
                                      <div>${escapeHtml(v.no_kendaraan ?? '-')}</div>
                                    </div>
                                    <div>
                                      <div class="text-[11px] text-slate-500">Yang Ditemui</div>
                                      <div>${escapeHtml(v.yang_ditemui ?? '-')}</div>
                                    </div>
                                    <div>
                                      <div class="text-[11px] text-slate-500">Urusan</div>
                                      <div>${escapeHtml(v.urusan ?? '-')}</div>
                                    </div>
                                    <div>
                                      <div class="text-[11px] text-slate-500">Jumlah</div>
                                      <div>${escapeHtml(v.jumlah ?? '-')}</div>
                                    </div>
                                    <div>
                                      <div class="text-[11px] text-slate-500">Jam Pertemuan</div>
                                      <div>${escapeHtml(v.jam_pertemuan ?? '-')}</div>
                                    </div>
                                  </div>

                                  <div class="grid grid-cols-3 gap-3 text-sm pt-2 border-t border-slate-200 mt-2">
                                    <div>
                                      <div class="text-[11px] text-slate-500">Kartu</div>
                                      <div class="font-semibold">${escapeHtml(v.card?.code ?? '-')}</div>
                                    </div>
                                    <div>
                                      <div class="text-[11px] text-slate-500">Tipe</div>
                                      <div>${escapeHtml(String(v.card?.tipe ?? '-').toUpperCase())}</div>
                                    </div>
                                    <div>
                                      <div class="text-[11px] text-slate-500">RFID</div>
                                      <div class="font-mono text-[11px] break-all">${escapeHtml(v.card?.rfid_code ?? '-')}</div>
                                    </div>
                                  </div>

                                  <div class="grid grid-cols-2 gap-3 text-xs pt-2">
                                    <div>
                                      <div class="text-[11px] text-slate-500">Check-in</div>
                                      <div>${fmtDateTime(v.check_in_at)}</div>
                                    </div>
                                    <div>
                                      <div class="text-[11px] text-slate-500">Check-out</div>
                                      <div>${fmtDateTime(v.check_out_at)}</div>
                                    </div>
                                  </div>

                                  <div class="pt-3 flex justify-end gap-2">
                                    <button id="btnConfirm"
                                            class="rounded-xl bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-700">
                                      Oke, Check-in
                                    </button>
                                  </div>
                                </div>
                              `;

            const btn = document.getElementById('btnConfirm');
            if (btn) {
                btn.addEventListener('click', () => confirmCheckin(v.batch));
            }
        }

        async function confirmCheckin(batch) {
            if (!batch) return;

            // alert konfirmasi
            if (window.Swal) {
                const conf = await Swal.fire({
                    icon: 'question',
                    title: 'Konfirmasi Check-in',
                    text: 'Apakah data visitor sudah sesuai?',
                    showCancelButton: true,
                    confirmButtonText: 'Ya, Check-in',
                    cancelButtonText: 'Batal',
                    reverseButtons: true,
                });
                if (!conf.isConfirmed) return;
            } else if (!confirm('Konfirmasi check-in visitor ini?')) {
                return;
            }

            statusEl.textContent = 'Memproses check-in...';

            try {
                const res = await fetch('/api/visitors/scan/confirm', {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrf,
                    },
                    body: JSON.stringify({ batch }),
                });

                const j = await res.json().catch(() => ({}));

                if (!res.ok) {
                    if (window.Swal) {
                        await Swal.fire('Gagal', j.message ?? 'Gagal check-in.', 'error');
                    } else {
                        alert(j.message ?? 'Gagal check-in.');
                    }
                    statusEl.textContent = j.message ?? 'Gagal check-in.';
                    return;
                }

                if (window.Swal) {
                    await Swal.fire('Berhasil', j.message ?? 'Check-in berhasil.', 'success');
                } else {
                    alert(j.message ?? 'Check-in berhasil.');
                }

                batchInput.value = '';
                clearResult('Check-in berhasil. Silakan scan batch berikutnya.');
                batchInput.focus();
            } catch (e) {
                console.error(e);
                if (window.Swal) {
                    await Swal.fire('Error', 'Terjadi kesalahan jaringan.', 'error');
                } else {
                    alert('Terjadi kesalahan jaringan.');
                }
                statusEl.textContent = 'Terjadi kesalahan jaringan.';
            }
        }

        // === event debounce ===
        batchInput.addEventListener('input', () => {
            clearTimeout(lookupTimer);
            const val = batchInput.value.trim();
            if (!val) {
                clearResult('Batch kosong.');
                return;
            }
            statusEl.textContent = 'Menunggu selesai input...';
            lookupTimer = setTimeout(lookupBatch, 5000);
        });

        // Auto enter
        batchInput.addEventListener('keydown', (e) => {
            if (e.key === 'Enter') {
                e.preventDefault();
                clearTimeout(lookupTimer);
                lookupBatch();
            }
        });

        clearResult('Menunggu input batch...');
    </script>
@endpush