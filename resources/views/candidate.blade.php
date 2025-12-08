<!doctype html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <title>Visitor Registration</title>
</head>

<body class="min-h-screen bg-gradient-to-b from-slate-100 to-slate-200">
    <div class="max-w-md mx-auto px-4 py-6">
        <div class="rounded-2xl bg-white shadow-sm border border-slate-200 overflow-hidden">
            <div class="px-5 py-4 bg-red-500 text-white">
                <div class="flex items-center gap-3">
                    <div
                        class="h-12 w-36 rounded-2xl bg-white shadow-sm ring-1 ring-black/5 flex items-center justify-center px-3 overflow-hidden">
                        <img src="/logo-kyb.png" alt="Logo" class="h-9 w-full object-contain" />
                    </div>
                    <div class="leading-tight">
                        <h1 class="text-lg font-semibold">Visitor Registration</h1>
                        <p class="text-xs text-white/80">Silakan isi data Anda</p>
                    </div>
                </div>
            </div>

            <div class="p-5">
                @if(session('error'))
                    <div class="mb-4 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
                        {{ session('error') }}
                    </div>
                @endif

                @if ($errors->any())
                    <div class="mb-4 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
                        <div class="font-semibold mb-1">Periksa input:</div>
                        <ul class="list-disc pl-5">
                            @foreach ($errors->all() as $e)
                                <li>{{ $e }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form id="candidateForm" action="{{ route('candidate.store') }}" method="POST" class="space-y-4"
                    autocomplete="off">
                    @csrf

                    <div>
                        <label class="block text-sm font-semibold text-slate-700">Nama Lengkap</label>
                        <input name="full_name" value="{{ old('full_name') }}" required class="mt-2 w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-slate-800 placeholder-slate-400 shadow-sm
              focus:border-red-500 focus:ring-4 focus:ring-red-500/15" placeholder="Nama sesuai KTP"
                            autocapitalize="words">
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-slate-700">Asal Instansi</label>
                        <input name="institution" value="{{ old('institution') }}" required class="mt-2 w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-slate-800 placeholder-slate-400 shadow-sm
              focus:border-red-500 focus:ring-4 focus:ring-red-500/15" placeholder="Nama perusahaan / instansi">
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-slate-700">No HP</label>
                        <input name="no_hp" value="{{ old('no_hp') }}" class="mt-2 w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-slate-800 placeholder-slate-400 shadow-sm
              focus:border-red-500 focus:ring-4 focus:ring-red-500/15" placeholder="08xxxxxxxxxx" inputmode="tel">
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-slate-700">Yang Ditemui</label>
                        <input name="yang_ditemui" value="{{ old('yang_ditemui') }}" class="mt-2 w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-slate-800 placeholder-slate-400 shadow-sm
              focus:border-red-500 focus:ring-4 focus:ring-red-500/15" placeholder="Nama PIC / Karyawan">
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-slate-700">Urusan</label>
                        <input name="urusan" value="{{ old('urusan') }}" class="mt-2 w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-slate-800 placeholder-slate-400 shadow-sm
              focus:border-red-500 focus:ring-4 focus:ring-red-500/15"
                            placeholder="Contoh: Meeting / Audit / Delivery">
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-sm font-semibold text-slate-700">Jumlah Orang</label>
                            <input name="jumlah" value="{{ old('jumlah') }}" type="number" min="1" max="999" class="mt-2 w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-slate-800 shadow-sm
                focus:border-red-500 focus:ring-4 focus:ring-red-500/15" placeholder="Jumlah orang">

                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-slate-700">Jam Pertemuan</label>
                            <input name="jam_pertemuan" value="{{ old('jam_pertemuan') }}" type="time" class="mt-2 w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-slate-800 shadow-sm
                focus:border-red-500 focus:ring-4 focus:ring-red-500/15">

                        </div>
                    </div>

                    <div class="rounded-xl border border-slate-200 p-4 bg-slate-50">
                        <label class="block text-sm font-semibold text-slate-700 mb-2">Membawa Kendaraan?</label>

                        <div class="flex items-center gap-4">
                            <label class="inline-flex items-center gap-2 text-sm">
                                <input type="radio" name="bawa_kendaraan" value="tidak" class="h-4 w-4"
                                    @checked(old('bawa_kendaraan', 'tidak') === 'tidak')>
                                Tidak
                            </label>

                            <label class="inline-flex items-center gap-2 text-sm">
                                <input type="radio" name="bawa_kendaraan" value="ya" class="h-4 w-4"
                                    @checked(old('bawa_kendaraan') === 'ya')>
                                Ya
                            </label>
                        </div>

                        <div id="noKendaraanWrap" class="mt-3 hidden">
                            <input name="no_kendaraan" id="no_kendaraan" value="{{ old('no_kendaraan') }}" class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-slate-800 placeholder-slate-400 shadow-sm
                focus:border-red-500 focus:ring-4 focus:ring-red-500/15" placeholder="Contoh: B 1234 ABC">
                            <p class="mt-1 text-xs text-slate-500">Wajib diisi jika membawa kendaraan</p>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-slate-700">Tipe Kunjungan</label>
                        <select name="tipe" required class="mt-2 w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-slate-800 shadow-sm
              focus:border-red-500 focus:ring-4 focus:ring-red-500/15">
                            <option value="">Pilih tipe</option>
                            <option value="office" @selected(old('tipe') === 'office')>Office</option>
                            <option value="plant" @selected(old('tipe') === 'plant')>Plant</option>
                        </select>
                        <p class="mt-2 text-xs text-slate-500">Kartu dipilih otomatis sesuai tipe.</p>
                    </div>

                    <button type="submit" class="w-full rounded-xl bg-red-500 px-4 py-3 text-white font-semibold shadow-sm
            hover:bg-red-700 focus:outline-none focus:ring-4 focus:ring-red-500/20 active:bg-red-800">
                        Submit & Dapatkan QR
                    </button>
                </form>
            </div>
        </div>

        <p class="mt-4 text-center text-xs text-slate-500">
            Setelah submit, data booking tersimpan. Saat datang, Security melakukan check-in.
        </p>
    </div>

    <script>
        const wrap = document.getElementById('noKendaraanWrap');
        const input = document.getElementById('no_kendaraan');

        function syncNoKendaraan() {
            const picked = document.querySelector('input[name="bawa_kendaraan"]:checked')?.value ?? 'tidak';
            const show = picked === 'ya';
            wrap.classList.toggle('hidden', !show);
            input.toggleAttribute('required', show);
            if (!show) input.value = '';
        }

        document.querySelectorAll('input[name="bawa_kendaraan"]').forEach(r => {
            r.addEventListener('change', syncNoKendaraan);
        });

        syncNoKendaraan();
    </script>
</body>

</html>