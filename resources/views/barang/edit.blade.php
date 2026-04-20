@extends('layouts.app')

@section('title', 'Edit Barang')

@section('content')
<div class="max-w-3xl mx-auto">

    {{-- Header --}}
    <div class="mb-6">
        <a href="{{ route('barang.show', $barang) }}"
           class="inline-flex items-center gap-1.5 text-sm text-gray-500 hover:text-gray-700 transition mb-3">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
            Kembali ke Detail
        </a>
        <h1 class="text-2xl font-bold text-gray-900">✏️ Edit Barang</h1>
        <p class="text-sm text-gray-500 mt-1">
            Mengubah: <strong>{{ $barang->nama_barang }}</strong>
        </p>
    </div>

    {{-- Form Card --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 sm:p-8">
        @include('barang._form', [
            'barang' => $barang,
            'action' => route('barang.update', $barang),
            'method' => 'PUT',
        ])
    </div>
</div>
@endsection
