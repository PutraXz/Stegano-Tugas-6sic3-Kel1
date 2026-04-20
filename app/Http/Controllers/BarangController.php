<?php

namespace App\Http\Controllers;

use App\Http\Requests\BarangRequest;
use App\Models\Barang;
use App\Services\SteganographyService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class BarangController extends Controller
{
    public function __construct(
        private readonly SteganographyService $steganography
    ) {}

    // ─── CRUD ─────────────────────────────────────────────────────────────────

    /**
     * GET /barang — Daftar semua barang (pagination).
     */
    public function index(Request $request): View
    {
        $search = $request->query('search');

        $barang = Barang::query()
            ->when($search, fn ($q) => $q->where('nama_barang', 'like', "%{$search}%"))
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('barang.index', compact('barang', 'search'));
    }

    /**
     * GET /barang/create — Form tambah barang.
     */
    public function create(): View
    {
        return view('barang.create');
    }

    /**
     * POST /barang — Simpan barang baru + encode LSB opsional.
     */
    public function store(BarangRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $gambarPath = null;

        if ($request->hasFile('gambar')) {
            $gambarPath = $this->uploadGambar($request, $validated, 'store');
            if ($gambarPath === false) {
                return back()->withInput()->with('error', 'Gagal memproses gambar.');
            }
        }

        $barang = Barang::create([
            'nama_barang' => $validated['nama_barang'],
            'deskripsi'   => $validated['deskripsi'] ?? null,
            'harga'       => $validated['harga'],
            'gambar'      => $gambarPath,
            'is_encoded'  => isset($gambarPath) && $request->filled('pesan_rahasia'),
        ]);

        return redirect()
            ->route('barang.show', $barang)
            ->with('success', 'Barang berhasil ditambahkan.');
    }

    /**
     * GET /barang/{barang} — Detail barang.
     */
    public function show(Barang $barang): View
    {
        return view('barang.show', compact('barang'));
    }

    /**
     * GET /barang/{barang}/edit — Form edit barang.
     */
    public function edit(Barang $barang): View
    {
        return view('barang.edit', compact('barang'));
    }

    /**
     * PUT /barang/{barang} — Update barang.
     */
    public function update(BarangRequest $request, Barang $barang): RedirectResponse
    {
        $validated = $request->validated();

        if ($request->hasFile('gambar')) {
            // Hapus gambar lama
            $barang->deleteGambar();

            $gambarPath = $this->uploadGambar($request, $validated, 'update');
            if ($gambarPath === false) {
                return back()->withInput()->with('error', 'Gagal memproses gambar.');
            }

            $barang->gambar     = $gambarPath;
            $barang->is_encoded = $request->filled('pesan_rahasia');
        }

        $barang->update([
            'nama_barang' => $validated['nama_barang'],
            'deskripsi'   => $validated['deskripsi'] ?? null,
            'harga'       => $validated['harga'],
        ]);

        return redirect()
            ->route('barang.show', $barang)
            ->with('success', 'Barang berhasil diperbarui.');
    }

    /**
     * DELETE /barang/{barang} — Hapus barang + file gambar.
     */
    public function destroy(Barang $barang): RedirectResponse
    {
        $barang->deleteGambar();
        $barang->delete();

        return redirect()
            ->route('barang.index')
            ->with('success', 'Barang berhasil dihapus.');
    }

    // ─── Download ─────────────────────────────────────────────────────────────

    /**
     * GET /barang/{barang}/download — Download gambar barang.
     */
    public function download(Barang $barang): StreamedResponse|RedirectResponse
    {
        if (! $barang->gambar) {
            return back()->with('error', 'Barang ini tidak memiliki gambar.');
        }

        $disk = Storage::disk('public');

        if (! $disk->exists($barang->gambar)) {
            return back()->with('error', 'File gambar tidak ditemukan di server.');
        }

        $filename = Str::slug($barang->nama_barang) . '_' . basename($barang->gambar);

        return $disk->download($barang->gambar, $filename);
    }

    // ─── Steganography ────────────────────────────────────────────────────────

    /**
     * POST /barang/{barang}/encode — Encode pesan LSB ke gambar.
     */
    public function encodeLSB(Request $request, Barang $barang): RedirectResponse
    {
        $request->validate([
            'pesan_rahasia' => ['required', 'string', 'max:5000'],
        ], [
            'pesan_rahasia.required' => 'Pesan rahasia tidak boleh kosong.',
            'pesan_rahasia.max'      => 'Pesan terlalu panjang (maks 5000 karakter).',
        ]);

        if (! $barang->gambar) {
            return back()->with('error', 'Barang tidak memiliki gambar untuk di-encode.');
        }

        $disk      = Storage::disk('public');
        $inputPath = $disk->path($barang->gambar);

        if (! $disk->exists($barang->gambar)) {
            return back()->with('error', 'File gambar tidak ditemukan di server.');
        }

        // Path output: simpan dengan prefix "encoded_"
        $originalFilename = basename($barang->gambar);
        $encodedFilename  = 'encoded_' . pathinfo($originalFilename, PATHINFO_FILENAME) . '.png';
        $encodedRelPath   = 'barang/' . $encodedFilename;
        $outputPath       = $disk->path('barang/' . $encodedFilename);

        // Jalankan Python encode
        $result = $this->steganography->encode(
            inputPath:  $inputPath,
            outputPath: $outputPath,
            message:    $request->input('pesan_rahasia'),
        );

        if (! $result['success']) {
            Log::error('BarangController::encodeLSB failed', [
                'barang_id' => $barang->id,
                'error'     => $result['error'],
            ]);
            return back()->with('error', 'Encode gagal: ' . $result['error']);
        }

        // Hapus gambar lama jika berbeda dengan encoded
        if ($barang->gambar !== $encodedRelPath) {
            $disk->delete($barang->gambar);
        }

        // Update record
        $barang->update([
            'gambar'     => $encodedRelPath,
            'is_encoded' => true,
        ]);

        return redirect()
            ->route('barang.show', $barang)
            ->with('success', '✅ Pesan berhasil di-encode ke dalam gambar menggunakan LSB!');
    }

    /**
     * GET /barang/{barang}/decode — Decode pesan dari gambar.
     */
    public function decodeLSB(Barang $barang): RedirectResponse
    {
        if (! $barang->gambar) {
            return back()->with('error', 'Barang tidak memiliki gambar.');
        }

        $disk      = Storage::disk('public');
        $imagePath = $disk->path($barang->gambar);

        if (! $disk->exists($barang->gambar)) {
            return back()->with('error', 'File gambar tidak ditemukan di server.');
        }

        // Jalankan Python decode
        $result = $this->steganography->decode($imagePath);

        if (! $result['success']) {
            return back()->with('error', 'Decode gagal: ' . $result['error']);
        }

        $decodedMessage = $result['message'] ?? 'Tidak ada pesan.';

        Log::info('BarangController::decodeLSB success', [
            'barang_id' => $barang->id,
            'message'   => substr($decodedMessage, 0, 100) . '...',
        ]);

        return back()->with('decoded_message', $decodedMessage);
    }

    // ─── Private Helpers ──────────────────────────────────────────────────────

    /**
     * Upload gambar dan opsional encode LSB.
     * Mengembalikan path relatif (string) atau false jika gagal.
     */
    private function uploadGambar(Request $request, array $validated, string $context): string|false
    {
        $file     = $request->file('gambar');
        $pesan    = $request->input('pesan_rahasia', '');
        $hasPesan = trim($pesan) !== '';

        // Simpan file asli sementara
        $originalPath    = $file->store('barang', 'public');
        $absoluteOriginal = Storage::disk('public')->path($originalPath);

        // Jika tidak ada pesan, cukup simpan gambar biasa
        if (! $hasPesan) {
            return $originalPath;
        }

        // Jika ada pesan, encode dengan Python
        $encodedFilename = 'encoded_' . pathinfo(basename($originalPath), PATHINFO_FILENAME) . '.png';
        $encodedRelPath  = 'barang/' . $encodedFilename;
        $absoluteOutput  = Storage::disk('public')->path('barang/' . $encodedFilename);

        $result = $this->steganography->encode(
            inputPath:  $absoluteOriginal,
            outputPath: $absoluteOutput,
            message:    $pesan,
        );

        // Hapus file asli (karena pakai encoded)
        Storage::disk('public')->delete($originalPath);

        if (! $result['success']) {
            Log::error("BarangController::{$context} encode failed", ['error' => $result['error']]);
            return false;
        }

        return $encodedRelPath;
    }
}
