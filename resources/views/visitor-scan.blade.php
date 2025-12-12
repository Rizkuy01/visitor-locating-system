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
        <input id="batchInput" type="text" autofocus inputmode="numeric" autocomplete="off" autocorrect="off"
          autocapitalize="off" spellcheck="false" class="w-full rounded-xl border border-slate-300 px-4 py-3 text-lg tracking-[0.25em] text-center font-mono
                   focus:border-slate-900 focus:ring-4 focus:ring-slate-900/10" placeholder="Scan / ketik batch di sini">
        <p class="mt-2 text-[11px] text-slate-500">
          Tips: scanner biasanya mengirim ENTER di akhir. Sistem juga akan otomatis mencari setelah beberapa detik tidak
          ada input.
        </p>
      </div>

      <div id="status" class="text-xs text-slate-500">Menunggu input batch...</div>
      <div id="result"></div>
    </div>
  </div>
@endsection

@push('scripts')
  <script>
    (() => {
      const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ?? '';
      const batchInput = document.getElementById('batchInput');
      const resultEl = document.getElementById('result');
      const statusEl = document.getElementById('status');

      // ===== Guard biar gak blank kalau element gak ketemu =====
      if (!batchInput || !resultEl || !statusEl) {
        console.warn('Scan page: element tidak ditemukan (batchInput/result/status).');
        return;
      }

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

        const showCheckinButton = status === 'BOOKED';
        const showCheckoutButton = status === 'IN_USE';
        const cardCode = v.card?.code ?? '-';

        // badge status kecil di kanan
        const statusBadgeClass =
          status === 'IN_USE' ? 'bg-red-100 text-red-700' :
            status === 'BOOKED' ? 'bg-amber-100 text-amber-700' :
              'bg-emerald-100 text-emerald-700';

        // banner atas sesuai status
        let bannerBgClass, bannerTitle, bannerSubtitle;

        if (status === 'BOOKED') {
          bannerBgClass = 'bg-amber-500';
          bannerTitle = 'Berikan kartu nomor';
          bannerSubtitle = 'Serahkan kartu ini ke visitor.';
        } else if (status === 'IN_USE') {
          bannerBgClass = 'bg-red-500';
          bannerTitle = 'Visitor masih di area';
          bannerSubtitle = 'Tekan Check-out jika visitor sudah keluar & kartu kembali.';
        } else { // DONE
          bannerBgClass = 'bg-emerald-600';
          bannerTitle = 'Kunjungan selesai';
          bannerSubtitle = 'Kartu sudah tersedia untuk visitor lain.';
        }

        statusEl.textContent = 'Data ditemukan.';

        resultEl.innerHTML = `
          <div class="space-y-3">
            <!-- BANNER -->
            <div class="rounded-2xl ${bannerBgClass} text-white px-4 py-3 flex items-center justify-between gap-3 shadow-md">
              <div>
                <div class="text-[11px] uppercase tracking-wide opacity-80">${escapeHtml(bannerTitle)}</div>
                <div class="text-3xl font-extrabold font-mono">${escapeHtml(cardCode)}</div>
              </div>
              <div class="flex items-center gap-2 text-xs">
                <span class="inline-flex h-3 w-3 rounded-full bg-white ${status === 'BOOKED' ? 'animate-pulse' : ''}"></span>
                <span>${escapeHtml(bannerSubtitle)}</span>
              </div>
            </div>

            <!-- DETAIL -->
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

              <div class="grid grid-cols-2 gap-3 text-xs pt-2 border-t border-slate-200 mt-2">
                <div>
                  <div class="text-[11px] text-slate-500">Check-in</div>
                  <div>${fmtDateTime(v.check_in_at)}</div>
                </div>
                <div>
                  <div class="text-[11px] text-slate-500">Check-out</div>
                  <div>${fmtDateTime(v.check_out_at)}</div>
                </div>
              </div>

              ${showCheckinButton ? `
                <div class="pt-3 flex justify-end gap-2">
                  <button id="btnConfirm"
                          class="rounded-xl bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-700">
                    Check-in
                  </button>
                </div>
              ` : ''}

              ${showCheckoutButton ? `
                <div class="pt-3 flex justify-end gap-2">
                  <button id="btnCheckout"
                          class="rounded-xl bg-red-600 px-4 py-2 text-sm font-semibold text-white hover:bg-red-700">
                    Check-out
                  </button>
                </div>
              ` : ''}
            </div>
          </div>
        `;

        // listener check-in
        const btnConfirm = document.getElementById('btnConfirm');
        if (btnConfirm && showCheckinButton) {
          btnConfirm.addEventListener('click', () => confirmCheckin(v.batch));
        }

        // listener check-out
        const btnCheckout = document.getElementById('btnCheckout');
        if (btnCheckout && showCheckoutButton) {
          btnCheckout.addEventListener('click', () => confirmCheckout(v.batch));
        }

        resultEl.scrollIntoView({ behavior: 'smooth', block: 'start' });
      }

      async function confirmCheckin(batch) {
        if (!batch) return;

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
            if (window.Swal) await Swal.fire('Gagal', j.message ?? 'Gagal check-in.', 'error');
            else alert(j.message ?? 'Gagal check-in.');
            statusEl.textContent = j.message ?? 'Gagal check-in.';
            return;
          }

          if (window.Swal) await Swal.fire('Berhasil', j.message ?? 'Check-in berhasil.', 'success');
          else alert(j.message ?? 'Check-in berhasil.');

          batchInput.value = '';
          clearResult('Check-in berhasil. Silakan scan batch berikutnya.');
          batchInput.focus();
        } catch (e) {
          console.error(e);
          if (window.Swal) await Swal.fire('Error', 'Terjadi kesalahan jaringan.', 'error');
          else alert('Terjadi kesalahan jaringan.');
          statusEl.textContent = 'Terjadi kesalahan jaringan.';
        }
      }

      async function confirmCheckout(batch) {
        if (!batch) return;

        if (window.Swal) {
          const conf = await Swal.fire({
            icon: 'warning',
            title: 'Konfirmasi Check-out',
            text: 'Visitor akan dicatat keluar, dan kartu menjadi AVAILABLE.',
            showCancelButton: true,
            confirmButtonText: 'Ya, Check-out',
            cancelButtonText: 'Batal',
            reverseButtons: true,
          });
          if (!conf.isConfirmed) return;
        } else if (!confirm('Konfirmasi check-out visitor ini?')) {
          return;
        }

        statusEl.textContent = 'Memproses check-out...';

        try {
          const res = await fetch('/api/visitors/scan/checkout', {
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
            if (window.Swal) await Swal.fire('Gagal', j.message ?? 'Gagal check-out.', 'error');
            else alert(j.message ?? 'Gagal check-out.');
            statusEl.textContent = j.message ?? 'Gagal check-out.';
            return;
          }

          if (window.Swal) await Swal.fire('Berhasil', j.message ?? 'Check-out berhasil.', 'success');
          else alert(j.message ?? 'Check-out berhasil.');

          // auto lookup data
          await lookupBatch();
        } catch (e) {
          console.error(e);
          if (window.Swal) await Swal.fire('Error', 'Terjadi kesalahan jaringan.', 'error');
          else alert('Terjadi kesalahan jaringan.');
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
        lookupTimer = setTimeout(lookupBatch, 500);
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
    })();
  </script>
@endpush