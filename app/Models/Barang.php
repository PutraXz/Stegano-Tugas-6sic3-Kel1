<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Barang extends Model
{
    use HasFactory;

    protected $table = 'barang';

    protected $fillable = [
        'nama_barang',
        'deskripsi',
        'harga',
        'gambar',
        'is_encoded',
    ];

    protected $casts = [
        'harga'      => 'decimal:2',
        'is_encoded' => 'boolean',
    ];

    // ─── Accessors ────────────────────────────────────────────────────────────

    /**
     * URL publik gambar (via storage symlink).
     */
    public function getGambarUrlAttribute(): ?string
    {
        if (! $this->gambar) {
            return null;
        }
        return Storage::url($this->gambar);
    }

    /**
     * Path absolut file gambar di server.
     */
    public function getGambarPathAttribute(): ?string
    {
        if (! $this->gambar) {
            return null;
        }
        return Storage::disk('public')->path(
            str_replace('barang/', '', basename($this->gambar))
        );
    }

    // ─── Helpers ──────────────────────────────────────────────────────────────

    /**
     * Hapus file gambar dari storage.
     */
    public function deleteGambar(): bool
    {
        if ($this->gambar && Storage::disk('public')->exists($this->gambar)) {
            return Storage::disk('public')->delete($this->gambar);
        }
        return false;
    }

    /**
     * Format harga ke rupiah.
     */
    public function getHargaFormattedAttribute(): string
    {
        return 'Rp ' . number_format($this->harga, 0, ',', '.');
    }
}
