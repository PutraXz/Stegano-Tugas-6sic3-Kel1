@extends('layouts.app')

@section('title', 'Daftar Barang')

@section('content')
<div class="space-y-6">

    {{-- ── Header ─────────────────────────────────────────────────────────── --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">📦 Daftar Barang</h1>
            <p class="text-sm text-gray-500 mt-1">Kelola barang dengan steganografi LSB</p>
        </div>
        <a href="{{ route('barang.create') }}"
           class="inline-flex items-center gap-2 bg-brand-600 hover:bg-brand-700 text-white text-sm font-semibold px-5 py-2.5 rounded-lg transition shadow-sm">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Tambah Barang
        </a>
    </div>

    {{-- ── Search ───────────────────────────────────────────────────────────── --}}
    <form method="GET" action="{{ route('barang.index') }}" class="flex gap-3">
        <input type="text" name="search" value="{{ $search }}"
               placeholder="Cari nama barang..."
               class="flex-1 border border-gray-300 rounded-lg px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-transparent" />
        <button type="submit"
                class="bg-gray-700 hover:bg-gray-800 text-white text-sm font-medium px-5 py-2 rounded-lg transition">
            Cari
        </button>
        @if($search)
            <a href="{{ route('barang.index') }}"
               class="bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-medium px-4 py-2 rounded-lg transition">
                Reset
            </a>
        @endif
    </form>

    {{-- ── Table ───────────────────────────────────────────────────────────── --}}
    @if ($barang->isEmpty())
        <div class="text-center py-20 bg-white rounded-2xl border border-dashed border-gray-300">
            <div class="text-5xl mb-4">🗂️</div>
            <p class="text-gray-500 font-medium">Belum ada barang.</p>
            <a href="{{ route('barang.create') }}" class="mt-3 inline-block text-brand-600 hover:underline text-sm font-medium">
                Tambah barang pertama →
            </a>
        </div>
    @else
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 border-b border-gray-100">
                        <tr>
                            <th class="px-4 py-3 text-left font-semibold text-gray-600 w-16">#</th>
                            <th class="px-4 py-3 text-left font-semibold text-gray-600 w-20">Gambar</th>
                            <th class="px-4 py-3 text-left font-semibold text-gray-600">Nama Barang</th>
                            <th class="px-4 py-3 text-left font-semibold text-gray-600">Harga</th>
                            <th class="px-4 py-3 text-center font-semibold text-gray-600">Status</th>
                            <th class="px-4 py-3 text-left font-semibold text-gray-600">Tanggal</th>
                            <th class="px-4 py-3 text-center font-semibold text-gray-600">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @foreach ($barang as $item)
                            <tr class="hover:bg-gray-50 transition">
                                {{-- No --}}
                                <td class="px-4 py-3 text-gray-400 font-mono">
                                    {{ $barang->firstItem() + $loop->index }}
                                </td>

                                {{-- Gambar --}}
                                <td class="px-4 py-3">
                                    @if ($item->gambar_url)
                                        <img src="{{ $item->gambar_url }}"
                                             alt="{{ $item->nama_barang }}"
                                             class="w-12 h-12 object-cover rounded-lg border border-gray-200 shadow-sm" />
                                    @else
                                        <div class="w-12 h-12 bg-gray-100 rounded-lg flex items-center justify-center text-gray-300 text-xl">
                                            🖼
                                        </div>
                                    @endif
                                </td>

                                {{-- Nama --}}
                                <td class="px-4 py-3">
                                    <a href="{{ route('barang.show', $item) }}"
                                       class="font-semibold text-gray-800 hover:text-brand-600 transition">
                                        {{ $item->nama_barang }}
                                    </a>
                                    @if ($item->deskripsi)
                                        <p class="text-gray-400 text-xs mt-0.5 truncate max-w-xs">{{ $item->deskripsi }}</p>
                                    @endif
                                </td>

                                {{-- Harga --}}
                                <td class="px-4 py-3 font-medium text-gray-700">
                                    {{ $item->harga_formatted }}
                                </td>

                                {{-- Status LSB --}}
                                <td class="px-4 py-3 text-center">
                                    @if ($item->is_encoded)
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-indigo-100 text-indigo-700 text-xs font-medium">
                                            🔒 Encoded
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-gray-100 text-gray-500 text-xs font-medium">
                                            — Normal
                                        </span>
                                    @endif
                                </td>

                                {{-- Tanggal --}}
                                <td class="px-4 py-3 text-gray-400 text-xs">
                                    {{ $item->created_at->format('d/m/Y H:i') }}
                                </td>

                                {{-- Aksi --}}
                                <td class="px-4 py-3">
                                    <div class="flex items-center justify-center gap-2">
                                        <a href="{{ route('barang.show', $item) }}"
                                           class="text-gray-400 hover:text-brand-600 transition" title="Detail">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                      d="M15 12a3 3 0 11-6 0 3 3 0 016 0z M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                            </svg>
                                        </a>
                                        <a href="{{ route('barang.edit', $item) }}"
                                           class="text-gray-400 hover:text-yellow-500 transition" title="Edit">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                      d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                            </svg>
                                        </a>
                                        <form method="POST" action="{{ route('barang.destroy', $item) }}"
                                              onsubmit="return confirm('Yakin ingin menghapus barang ini?')">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="text-gray-400 hover:text-red-500 transition" title="Hapus">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                          d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                </svg>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Pagination --}}
        <div class="mt-4">
            {{ $barang->links() }}
        </div>
    @endif
</div>
@endsection
