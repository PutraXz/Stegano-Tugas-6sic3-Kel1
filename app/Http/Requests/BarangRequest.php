<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class BarangRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Sudah diproteksi middleware auth di routes
    }

    public function rules(): array
    {
        $gambarRules = ['nullable', 'image', 'mimes:jpeg,jpg,png', 'max:2048'];

        // Jika create (POST), gambar wajib
        if ($this->isMethod('POST')) {
            $gambarRules = ['required', 'image', 'mimes:jpeg,jpg,png', 'max:2048'];
        }

        return [
            'nama_barang'   => ['required', 'string', 'max:255'],
            'deskripsi'     => ['nullable', 'string', 'max:5000'],
            'harga'         => ['required', 'numeric', 'min:0', 'max:999999999999.99'],
            'gambar'        => $gambarRules,
            'pesan_rahasia' => ['nullable', 'string', 'max:5000'],
        ];
    }

    public function messages(): array
    {
        return [
            'nama_barang.required' => 'Nama barang wajib diisi.',
            'nama_barang.max'      => 'Nama barang maksimal 255 karakter.',
            'harga.required'       => 'Harga wajib diisi.',
            'harga.numeric'        => 'Harga harus berupa angka.',
            'harga.min'            => 'Harga tidak boleh negatif.',
            'gambar.required'      => 'Gambar wajib diunggah.',
            'gambar.image'         => 'File harus berupa gambar.',
            'gambar.mimes'         => 'Format gambar harus JPEG atau PNG.',
            'gambar.max'           => 'Ukuran gambar maksimal 2 MB.',
            'pesan_rahasia.max'    => 'Pesan rahasia maksimal 5000 karakter.',
        ];
    }
}
