<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Paket Portabilitas Data Pribadi - {{ $request->employee->full_name }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @media print {
            .no-print { display: none !important; }
            body { font-size: 11pt; }
        }
    </style>
</head>
<body class="bg-slate-100 p-6 min-h-screen text-slate-800">

    <div class="max-w-3xl mx-auto bg-white p-8 rounded-2xl border border-slate-200 shadow-sm print:border-none print:shadow-none space-y-6">

        <!-- No Print Bar -->
        <div class="no-print flex justify-between items-center border-b border-slate-100 pb-4">
            <span class="text-xs text-slate-500 font-medium">Paket Resmi Pemenuhan Hak Portabilitas Data (Pasal 13 UU PDP)</span>
            <div class="flex items-center gap-2">
                <button onclick="downloadJson()" class="px-3 py-1.5 rounded-xl bg-purple-600 text-white text-xs font-bold hover:bg-purple-700 transition">
                    Unduh JSON
                </button>
                <button onclick="window.print()" class="px-4 py-1.5 rounded-xl bg-slate-900 text-white text-xs font-bold hover:bg-black transition">
                    Cetak Dokumen
                </button>
            </div>
        </div>

        <!-- Header -->
        <div class="border-b-2 border-slate-900 pb-4 flex justify-between items-start">
            <div>
                <h1 class="text-base font-black uppercase text-slate-900">{{ $request->company->name ?? 'PT DEMO INDONESIA' }}</h1>
                <p class="text-xs font-bold text-slate-700">DOKUMEN PORTABILITAS DATA PRIBADI (DATA SUBJECT ACCESS REQUEST)</p>
                <p class="text-[10px] text-slate-500">Sesuai Ketentuan UU No. 27 Tahun 2022 Pasal 13 jo. Pasal 7</p>
            </div>
            <div class="text-right">
                <span class="text-xs font-mono font-bold text-slate-900 block">{{ $request->request_number }}</span>
                <span class="text-[11px] text-slate-500 block">Tgl Selesai: {{ $request->fulfilled_at ? $request->fulfilled_at->format('d M Y') : date('d M Y') }}</span>
                <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase bg-emerald-100 text-emerald-800">
                    Status: {{ $request->status }}
                </span>
            </div>
        </div>

        <!-- Data Subject Profile -->
        <div class="space-y-2 text-xs">
            <h3 class="font-bold uppercase tracking-wider text-slate-800 border-b border-slate-200 pb-1">I. Data Identitas Subjek Data (Karyawan)</h3>
            <div class="grid grid-cols-2 gap-4 bg-slate-50 p-4 rounded-xl border border-slate-100">
                <div class="space-y-1">
                    <div><span class="text-slate-400 w-32 inline-block">Nama Lengkap:</span> <strong>{{ $exportData['data_subject']['full_name'] }}</strong></div>
                    <div><span class="text-slate-400 w-32 inline-block">NIK KTP:</span> <span class="font-mono font-bold">{{ $exportData['data_subject']['nik'] }}</span></div>
                    <div><span class="text-slate-400 w-32 inline-block">Alamat Email:</span> {{ $exportData['data_subject']['email'] }}</div>
                    <div><span class="text-slate-400 w-32 inline-block">No. Telepon / HP:</span> {{ $exportData['data_subject']['phone'] ?? '-' }}</div>
                </div>
                <div class="space-y-1">
                    <div><span class="text-slate-400 w-36 inline-block">Departemen:</span> {{ $exportData['data_subject']['department'] ?? '-' }}</div>
                    <div><span class="text-slate-400 w-36 inline-block">Jabatan:</span> {{ $exportData['data_subject']['position'] ?? '-' }}</div>
                    <div><span class="text-slate-400 w-36 inline-block">Status Hubungan Kerja:</span> <span class="uppercase font-semibold">{{ $exportData['data_subject']['employment_type'] }}</span></div>
                    <div><span class="text-slate-400 w-36 inline-block">Tgl Mulai Bekerja:</span> {{ $exportData['data_subject']['join_date'] }}</div>
                </div>
            </div>
        </div>

        <!-- Consents History -->
        <div class="space-y-2 text-xs">
            <h3 class="font-bold uppercase tracking-wider text-slate-800 border-b border-slate-200 pb-1">II. Riwayat Persetujuan Pemrosesan (Consent Audit Trail)</h3>
            <table class="w-full text-slate-700 border border-slate-200 rounded-lg overflow-hidden">
                <thead class="bg-slate-100 text-[11px] font-bold uppercase text-slate-600">
                    <tr>
                        <th class="py-2 px-3 text-left">Jenis Persetujuan</th>
                        <th class="py-2 px-3 text-left">Tujuan Pemrosesan Sah</th>
                        <th class="py-2 px-3 text-center">Waktu Persetujuan</th>
                        <th class="py-2 px-3 text-center">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($exportData['consents_history'] as $c)
                        <tr>
                            <td class="py-2 px-3 font-semibold text-slate-900">{{ str_replace('_', ' ', $c['type']) }}</td>
                            <td class="py-2 px-3 text-slate-600">{{ $c['purpose'] }}</td>
                            <td class="py-2 px-3 text-center text-slate-500 font-mono text-[11px]">{{ \Carbon\Carbon::parse($c['consented_at'])->format('d/m/Y H:i') }}</td>
                            <td class="py-2 px-3 text-center">
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase {{ $c['status'] === 'granted' ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-800' }}">
                                    {{ $c['status'] }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="py-3 text-center text-slate-400">Tidak ada riwayat persetujuan spesifik.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Raw JSON Payload Section (Machine-Readable per Pasal 13) -->
        <div class="space-y-2 text-xs">
            <h3 class="font-bold uppercase tracking-wider text-slate-800 border-b border-slate-200 pb-1">III. Struktur Data Portabilitas Mesin (JSON Payload)</h3>
            <pre id="jsonContent" class="bg-slate-900 text-emerald-400 p-4 rounded-xl text-[10px] font-mono overflow-x-auto max-h-48 leading-relaxed">{{ json_encode($exportData, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) }}</pre>
        </div>

        <!-- Signatures -->
        <div class="grid grid-cols-2 gap-8 text-xs text-center pt-8">
            <div class="space-y-16">
                <div class="space-y-0.5">
                    <span class="text-slate-500 block">Diverifikasi & Diekspor oleh,</span>
                    <strong class="text-slate-800 block">Pejabat Pelindungan Data Pribadi (DPO)</strong>
                </div>
                <div class="border-t border-slate-300 pt-1">
                    <strong class="block text-slate-900">( Data Protection Officer )</strong>
                    <span class="text-[10px] text-purple-700 font-mono font-bold">Lisensi DPO Terverifikasi Kemkominfo/PDP</span>
                </div>
            </div>
            <div class="space-y-16">
                <div class="space-y-0.5">
                    <span class="text-slate-500 block">Diterima oleh,</span>
                    <strong class="text-slate-800 block">Subjek Data Pribadi (Karyawan)</strong>
                </div>
                <div class="border-t border-slate-300 pt-1">
                    <strong class="block text-slate-900">( {{ $request->employee->full_name }} )</strong>
                    <span class="text-[10px] text-slate-400">Pemohon Hak Portabilitas</span>
                </div>
            </div>
        </div>

    </div>

    <script>
        function downloadJson() {
            const dataStr = "data:text/json;charset=utf-8," + encodeURIComponent(document.getElementById('jsonContent').innerText);
            const downloadAnchor = document.createElement('a');
            downloadAnchor.setAttribute("href", dataStr);
            downloadAnchor.setAttribute("download", "DSAR-Export-{{ $request->employee->nik }}-{{ date('Ymd') }}.json");
            document.body.appendChild(downloadAnchor);
            downloadAnchor.click();
            downloadAnchor.remove();
        }
    </script>

</body>
</html>
