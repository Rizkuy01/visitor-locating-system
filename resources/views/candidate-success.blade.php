<!doctype html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <title>QR Visitor</title>
</head>

<body class="min-h-screen bg-gradient-to-b from-slate-100 to-slate-200">
    <div class="max-w-md mx-auto px-4 py-6">
        <div class="rounded-2xl bg-white shadow-sm border border-slate-200 overflow-hidden">
            <div class="px-5 py-4 bg-emerald-600 text-white">
                <h1 class="text-lg font-semibold">Berhasil!</h1>
                <p class="text-xs text-white/80">Tunjukkan QR ini ke Security</p>
            </div>

            <div class="p-5 space-y-4">
                <div class="rounded-xl border border-slate-200 bg-slate-50 p-4 text-sm">
                    <div class="font-semibold text-slate-900">{{ $full_name }}</div>
                    <div class="text-slate-600">{{ $institution }}</div>
                    <div class="mt-2 text-xs text-slate-500">
                        Tipe: <span class="font-semibold text-slate-800">{{ strtoupper($tipe) }}</span> •
                        Kartu: <span class="font-semibold text-slate-800">#{{ $card_code }}</span>
                    </div>
                </div>

                <div class="rounded-2xl border border-slate-200 bg-white p-4 flex flex-col items-center">
                    <div id="qrWrap" class="w-full flex justify-center"></div>
                    <div class="mt-3 text-xs text-slate-500">
                        RFID: <span class="font-mono text-slate-800">{{ $rfid_code }}</span>
                    </div>
                </div>

                <a href="{{ route('candidate.create') }}"
                    class="block w-full text-center rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm font-semibold text-slate-700 hover:bg-slate-50">
                    Buat QR Baru
                </a>
            </div>
        </div>
    </div>

    <script>
        window.CANDIDATE_QR = @json($rfid_code);
    </script>
</body>

</html>