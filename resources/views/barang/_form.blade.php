{{--
    Partial: _form.blade.php
    Digunakan oleh: barang/create.blade.php & barang/edit.blade.php

    Variables:
    - $barang  (Barang model, null saat create)
    - $action  (route string, misal route('barang.store'))
    - $method  (POST|PUT)
--}}

<form method="POST" action="{{ $action }}" enctype="multipart/form-data" class="space-y-6">
    @csrf
    @if ($method === 'PUT')
        @method('PUT')
    @endif

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

        {{-- ── Nama Barang ──────────────────────────────────────────────── --}}
        <div class="md:col-span-2">
            <label for="nama_barang" class="block text-sm font-semibold text-gray-700 mb-1.5">
                Nama Barang <span class="text-red-500">*</span>
            </label>
            <input type="text" id="nama_barang" name="nama_barang"
                   value="{{ old('nama_barang', $barang->nama_barang ?? '') }}"
                   placeholder="Masukkan nama barang..."
                   class="w-full border @error('nama_barang') border-red-400 @else border-gray-300 @enderror rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-transparent transition" />
            @error('nama_barang')
                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
            @enderror
        </div>

        {{-- ── Harga ─────────────────────────────────────────────────────── --}}
        <div>
            <label for="harga" class="block text-sm font-semibold text-gray-700 mb-1.5">
                Harga (Rp) <span class="text-red-500">*</span>
            </label>
            <div class="relative">
                <span class="absolute inset-y-0 left-3 flex items-center text-gray-400 text-sm pointer-events-none">Rp</span>
                <input type="number" id="harga" name="harga" min="0" step="0.01"
                       value="{{ old('harga', $barang->harga ?? '') }}"
                       placeholder="0"
                       class="w-full border @error('harga') border-red-400 @else border-gray-300 @enderror rounded-lg pl-10 pr-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500 transition" />
            </div>
            @error('harga')
                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
            @enderror
        </div>

        {{-- ── Deskripsi ─────────────────────────────────────────────────── --}}
        <div class="md:col-span-2">
            <label for="deskripsi" class="block text-sm font-semibold text-gray-700 mb-1.5">
                Deskripsi
            </label>
            <textarea id="deskripsi" name="deskripsi" rows="3"
                      placeholder="Deskripsi barang (opsional)..."
                      class="w-full border @error('deskripsi') border-red-400 @else border-gray-300 @enderror rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500 transition resize-none">{{ old('deskripsi', $barang->deskripsi ?? '') }}</textarea>
            @error('deskripsi')
                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
            @enderror
        </div>

        {{-- ── Upload Gambar ─────────────────────────────────────────────── --}}
        <div class="md:col-span-2">
            <label class="block text-sm font-semibold text-gray-700 mb-1.5">
                Gambar Barang
                @if ($method === 'POST') <span class="text-red-500">*</span> @endif
                <span class="font-normal text-gray-400 text-xs ml-1">(JPEG/PNG, maks 2MB)</span>
            </label>

            {{-- Preview --}}
            <div id="image-preview-wrapper"
                 class="{{ isset($barang) && $barang->gambar_url ? '' : 'hidden' }} mb-3">
                <img id="image-preview"
                     src="{{ isset($barang) && $barang->gambar_url ? $barang->gambar_url : '' }}"
                     alt="Preview"
                     class="h-40 w-auto object-cover rounded-xl border border-gray-200 shadow-sm" />
            </div>

            <div class="flex items-center gap-4">
                <label for="gambar"
                       class="cursor-pointer inline-flex items-center gap-2 bg-white border border-gray-300 hover:border-brand-400 text-gray-700 text-sm font-medium px-4 py-2.5 rounded-lg transition shadow-sm hover:shadow">
                    <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                    {{ isset($barang) && $barang->gambar ? 'Ganti Gambar' : 'Pilih Gambar' }}
                </label>
                <span id="file-name" class="text-xs text-gray-400 italic">Belum ada file dipilih</span>
            </div>

            <input type="file" id="gambar" name="gambar" accept="image/jpeg,image/png"
                   class="hidden"
                   onchange="previewImage(event)" />

            @error('gambar')
                <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
            @enderror
        </div>

        {{-- ── Pesan Rahasia (LSB) ──────────────────────────────────────── --}}
        <div class="md:col-span-2">
            <div class="bg-indigo-50 border border-indigo-100 rounded-xl p-4">
                <div class="flex items-start gap-3 mb-3">
                    <span class="text-2xl">🔐</span>
                    <div>
                        <p class="text-sm font-bold text-indigo-800">Steganografi LSB</p>
                        <p class="text-xs text-indigo-600 mt-0.5">
                            Opsional: sisipkan pesan rahasia ke dalam gambar menggunakan metode
                            <strong>Least Significant Bit</strong>. Pesan tidak terlihat secara kasat mata.
                        </p>
                    </div>
                </div>
                <label for="pesan_rahasia" class="block text-sm font-semibold text-indigo-700 mb-1.5">
                    Pesan Rahasia
                    <span class="font-normal text-indigo-400 text-xs">(opsional)</span>
                </label>
                <textarea id="pesan_rahasia" name="pesan_rahasia" rows="3"
                          placeholder="Tulis pesan rahasia yang akan disisipkan ke gambar..."
                          class="w-full border @error('pesan_rahasia') border-red-400 @else border-indigo-200 @enderror bg-white rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400 transition resize-none">{{ old('pesan_rahasia') }}</textarea>
                <p class="mt-1 text-xs text-indigo-500">Kapasitas tergantung resolusi gambar. Semakin besar gambar, semakin panjang pesan yang bisa disisipkan.</p>
                @error('pesan_rahasia')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>
        </div>
    </div>

    {{-- ── Submit Buttons ──────────────────────────────────────────────────── --}}
    <div class="flex items-center gap-3 pt-2">
        <button type="submit"
                class="bg-brand-600 hover:bg-brand-700 text-white text-sm font-semibold px-6 py-2.5 rounded-lg transition shadow-sm">
            {{ $method === 'POST' ? '➕ Simpan Barang' : '💾 Perbarui Barang' }}
        </button>
        <a href="{{ route('barang.index') }}"
           class="text-sm text-gray-500 hover:text-gray-700 font-medium transition">
            Batal
        </a>
    </div>
</form>

<script>
function previewImage(event) {
    const file     = event.target.files[0];
    const wrapper  = document.getElementById('image-preview-wrapper');
    const preview  = document.getElementById('image-preview');
    const nameSpan = document.getElementById('file-name');

    if (file) {
        const reader = new FileReader();
        reader.onload = e => {
            preview.src = e.target.result;
            wrapper.classList.remove('hidden');
        };
        reader.readAsDataURL(file);
        nameSpan.textContent = file.name;
    }
}
</script>
