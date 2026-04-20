@extends('layouts.app')

@section('title', $barang->nama_barang)

@section('content')
<div class="max-w-4xl mx-auto space-y-6">

    {{-- ── Breadcrumb ─────────────────────────────────────────────────────── --}}
    <div>
        <a href="{{ route('barang.index') }}"
           class="inline-flex items-center gap-1.5 text-sm text-gray-500 hover:text-gray-700 transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
            Daftar Barang
        </a>
        <span class="text-gray-300 mx-2">/</span>
        <span class="text-sm text-gray-700 font-medium">{{ $barang->nama_barang }}</span>
    </div>

    {{-- ── Main Card ───────────────────────────────────────────────────────── --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-0">

            {{-- ── Gambar ──────────────────────────────────────────────────── --}}
            <div class="bg-gray-50 border-b md:border-b-0 md:border-r border-gray-100 p-6 flex flex-col items-center justify-center gap-4">
                @if ($barang->gambar_url)
                    <img id="main-image"
                         src="{{ $barang->gambar_url }}"
                         alt="{{ $barang->nama_barang }}"
                         class="max-h-72 w-auto object-contain rounded-xl border border-gray-200 shadow-md" />

                    {{-- Status Badge --}}
                    @if ($barang->is_encoded)
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-indigo-100 text-indigo-700 text-xs font-semibold">
                            🔒 Gambar ini mengandung pesan LSB
                        </span>
                    @else
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-gray-100 text-gray-500 text-xs font-medium">
                            🖼️ Gambar tanpa pesan tersembunyi
                        </span>
                    @endif

                    {{-- Tombol Download --}}
                    <a href="{{ route('barang.download', $barang) }}"
                       class="w-full inline-flex items-center justify-center gap-2 bg-gray-800 hover:bg-gray-900 text-white text-sm font-semibold px-5 py-2.5 rounded-lg transition shadow-sm">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                        </svg>
                        Download Gambar
                    </a>
                @else
                    <div class="flex flex-col items-center gap-3 text-gray-300">
                        <div class="text-7xl">🖼</div>
                        <p class="text-sm text-gray-400">Tidak ada gambar</p>
                    </div>
                @endif
            </div>

            {{-- ── Info Barang ─────────────────────────────────────────────── --}}
            <div class="p-6 sm:p-8 space-y-5">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">{{ $barang->nama_barang }}</h1>
                    <p class="text-2xl font-bold text-brand-600 mt-1">{{ $barang->harga_formatted }}</p>
                </div>

                @if ($barang->deskripsi)
                    <div>
                        <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-1">Deskripsi</p>
                        <p class="text-sm text-gray-600 leading-relaxed">{{ $barang->deskripsi }}</p>
                    </div>
                @endif

                <div class="grid grid-cols-2 gap-4 text-sm">
                    <div>
                        <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-1">ID Barang</p>
                        <p class="font-mono text-gray-600">#{{ $barang->id }}</p>
                    </div>
                    <div>
                        <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-1">Ditambahkan</p>
                        <p class="text-gray-600">{{ $barang->created_at->format('d M Y') }}</p>
                    </div>
                </div>

                {{-- Action Buttons --}}
                <div class="flex flex-wrap gap-2 pt-2">
                    <a href="{{ route('barang.edit', $barang) }}"
                       class="inline-flex items-center gap-2 bg-yellow-50 hover:bg-yellow-100 border border-yellow-200 text-yellow-700 text-sm font-semibold px-4 py-2 rounded-lg transition">
                        ✏️ Edit
                    </a>
                    <form method="POST" action="{{ route('barang.destroy', $barang) }}"
                          onsubmit="return confirm('Yakin ingin menghapus barang ini?')">
                        @csrf @method('DELETE')
                        <button type="submit"
                                class="inline-flex items-center gap-2 bg-red-50 hover:bg-red-100 border border-red-200 text-red-700 text-sm font-semibold px-4 py-2 rounded-lg transition">
                            🗑️ Hapus
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    {{-- ── Steganography Section ───────────────────────────────────────────── --}}
    @if ($barang->gambar)
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

            {{-- ── Encode Panel ─────────────────────────────────────────────── --}}
            <div class="bg-white rounded-2xl shadow-sm border border-indigo-100 overflow-hidden">
                <div class="bg-indigo-600 px-5 py-4">
                    <h2 class="text-white font-bold text-base flex items-center gap-2">
                        🔐 Encode Pesan (LSB)
                    </h2>
                    <p class="text-indigo-200 text-xs mt-1">Sisipkan pesan rahasia ke dalam gambar</p>
                </div>
                <div class="p-5">
                    <form method="POST" action="{{ route('barang.encode', $barang) }}">
                        @csrf
                        <label for="pesan_rahasia" class="block text-sm font-semibold text-gray-700 mb-1.5">
                            Pesan Rahasia
                        </label>
                        <textarea id="pesan_rahasia" name="pesan_rahasia"
                                  rows="4"
                                  placeholder="Tulis pesan yang ingin disisipkan..."
                                  class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400 resize-none transition"
                                  required></textarea>
                        <p class="text-xs text-gray-400 mt-1 mb-3">
                            Kapasitas ~{{ number_format(($barang->gambar ? 100 : 0)) }}+ karakter (tergantung resolusi)
                        </p>
                        <button type="submit"
                                class="w-full bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-bold py-2.5 rounded-lg transition shadow-sm">
                            🔒 Encode ke Gambar
                        </button>
                    </form>
                </div>
            </div>

            {{-- ── Decode Panel ─────────────────────────────────────────────── --}}
            <div class="bg-white rounded-2xl shadow-sm border border-emerald-100 overflow-hidden">
                <div class="bg-emerald-600 px-5 py-4">
                    <h2 class="text-white font-bold text-base flex items-center gap-2">
                        🔓 Decode Pesan (LSB)
                    </h2>
                    <p class="text-emerald-200 text-xs mt-1">Baca pesan yang tersimpan di dalam gambar</p>
                </div>
                <div class="p-5 flex flex-col gap-4">

                    @if ($barang->is_encoded)
                        <div class="bg-emerald-50 rounded-lg px-4 py-3 text-sm text-emerald-700 border border-emerald-100">
                            ✅ Gambar ini telah di-encode dengan pesan LSB.
                        </div>
                    @else
                        <div class="bg-amber-50 rounded-lg px-4 py-3 text-sm text-amber-700 border border-amber-100">
                            ⚠️ Gambar belum di-encode. Hasil decode mungkin tidak bermakna.
                        </div>
                    @endif

                    <p class="text-sm text-gray-600">
                        Klik tombol di bawah untuk membaca pesan tersembunyi yang disisipkan
                        menggunakan metode LSB.
                    </p>

                    <a href="{{ route('barang.decode', $barang) }}"
                       class="block w-full text-center bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-bold py-2.5 rounded-lg transition shadow-sm">
                        🔍 Decode Pesan
                    </a>

                    {{-- Hasil decode (dari flash session) --}}
                    @if (session('decoded_message'))
                        {{-- Sudah ditampilkan di flash bar atas --}}
                    @endif
                </div>
            </div>
        </div>
    @endif

    {{-- ── How it works ─────────────────────────────────────────────────────── --}}
    <div class="bg-slate-50 rounded-2xl border border-slate-100 p-5">
        <h3 class="text-sm font-bold text-slate-700 mb-3">💡 Cara Kerja LSB Steganography</h3>
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 text-xs text-slate-600">
            <div class="flex flex-col gap-1">
                <span class="font-bold text-indigo-600">1. Konversi Pesan → Bit</span>
                <p>Pesan diubah ke representasi biner (0 dan 1) menggunakan encoding UTF-8.</p>
            </div>
            <div class="flex flex-col gap-1">
                <span class="font-bold text-indigo-600">2. Modifikasi Pixel</span>
                <p>Bit terakhir (LSB) setiap channel RGB pixel diganti dengan bit dari pesan. Perubahan tidak terlihat mata.</p>
            </div>
            <div class="flex flex-col gap-1">
                <span class="font-bold text-indigo-600">3. Rekonstruksi</span>
                <p>Saat decode, LSB setiap pixel dibaca ulang dan direkonstruksi menjadi teks asli.</p>
            </div>
        </div>
    </div>
</div>
@endsection
