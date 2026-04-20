#!/usr/bin/env python3
"""
LSB (Least Significant Bit) Steganography
==========================================
Encode dan decode pesan rahasia ke dalam gambar PNG.

Usage:
  python steganography.py encode <input> <output> <message>
  python steganography.py decode <image>

Author: Laravel-Stegano Project
"""

import sys
import os
from PIL import Image


# ─── Constants ────────────────────────────────────────────────────────────────
DELIMITER = "<<END>>"   # Penanda akhir pesan


# ─── Core LSB Functions ───────────────────────────────────────────────────────

def text_to_bits(text: str) -> str:
    """Konversi string teks ke representasi bit (binary string)."""
    bits = []
    for char in text.encode("utf-8"):
        bits.append(format(char, "08b"))
    return "".join(bits)


def bits_to_text(bits: str) -> str:
    """Konversi binary string kembali ke teks UTF-8."""
    chars = []
    for i in range(0, len(bits), 8):
        byte = bits[i:i + 8]
        if len(byte) < 8:
            break
        chars.append(chr(int(byte, 2)))
    return "".join(chars)


def encode(input_path: str, output_path: str, message: str) -> None:
    """
    Sisipkan pesan ke dalam gambar menggunakan LSB steganography.

    Cara kerja:
    1. Konversi pesan + delimiter ke bit
    2. Buka gambar sebagai RGB
    3. Modifikasi bit terakhir (LSB) setiap channel pixel
    4. Simpan gambar hasil sebagai PNG lossless

    Args:
        input_path:  Path gambar sumber
        output_path: Path gambar hasil encode
        message:     Pesan yang akan disisipkan
    """
    # Validasi file input
    if not os.path.isfile(input_path):
        raise FileNotFoundError(f"File tidak ditemukan: {input_path}")

    img = Image.open(input_path).convert("RGB")
    width, height = img.size

    # Siapkan bit payload (pesan + delimiter)
    payload      = message + DELIMITER
    payload_bits = text_to_bits(payload)

    # Cek kapasitas gambar (R, G, B per pixel = 3 bit tersedia)
    max_capacity = width * height * 3
    if len(payload_bits) > max_capacity:
        raise ValueError(
            f"Pesan terlalu panjang. Kapasitas: {max_capacity // 8} bytes, "
            f"dibutuhkan: {len(payload_bits) // 8} bytes."
        )

    bit_index  = 0
    new_pixels = []

    # Iterasi pixel menggunakan koordinat (kompatibel semua versi Pillow)
    for y in range(height):
        for x in range(width):
            r, g, b = img.getpixel((x, y))
            channels     = [r, g, b]
            new_channels = []

            for channel in channels:
                if bit_index < len(payload_bits):
                    bit         = int(payload_bits[bit_index])
                    new_channel = (channel & ~1) | bit   # Clear LSB, set bit baru
                    new_channels.append(new_channel)
                    bit_index += 1
                else:
                    new_channels.append(channel)

            new_pixels.append(tuple(new_channels))

    # Simpan gambar hasil (lossless PNG)
    encoded_img = Image.new("RGB", img.size)
    encoded_img.putdata(new_pixels)

    # Pastikan direktori output ada
    output_dir = os.path.dirname(output_path)
    if output_dir and not os.path.exists(output_dir):
        os.makedirs(output_dir, exist_ok=True)

    encoded_img.save(output_path, format="PNG")
    print(f"SUCCESS: Pesan berhasil di-encode ke '{output_path}'")


def decode(image_path: str) -> str:
    """
    Baca pesan tersembunyi dari gambar yang telah di-encode LSB.

    Cara kerja:
    1. Buka gambar sebagai RGB
    2. Ambil LSB dari setiap channel pixel
    3. Rekonstruksi bit menjadi teks
    4. Cari delimiter untuk menentukan akhir pesan

    Args:
        image_path: Path gambar yang akan di-decode

    Returns:
        Pesan tersembunyi (string)
    """
    if not os.path.isfile(image_path):
        raise FileNotFoundError(f"File tidak ditemukan: {image_path}")

    img          = Image.open(image_path).convert("RGB")
    width, height = img.size

    bits = []
    for y in range(height):
        for x in range(width):
            r, g, b = img.getpixel((x, y))
            bits.append(str(r & 1))   # LSB channel R
            bits.append(str(g & 1))   # LSB channel G
            bits.append(str(b & 1))   # LSB channel B

    # Rekonstruksi teks dari bit
    all_bits = "".join(bits)
    message = bits_to_text(all_bits)

    # Cari delimiter
    if DELIMITER in message:
        extracted = message.split(DELIMITER)[0]
        return extracted
    else:
        raise ValueError(
            "Tidak ditemukan pesan tersembunyi dalam gambar ini, "
            "atau gambar belum di-encode dengan metode LSB."
        )


# ─── CLI Entry Point ──────────────────────────────────────────────────────────

def main():
    if len(sys.argv) < 3:
        print("Usage:")
        print("  python steganography.py encode <input> <output> <message>")
        print("  python steganography.py decode <image>")
        sys.exit(1)

    command = sys.argv[1].lower()

    try:
        if command == "encode":
            if len(sys.argv) < 5:
                print("ERROR: encode membutuhkan: <input> <output> <message>")
                sys.exit(1)

            input_path  = sys.argv[2]
            output_path = sys.argv[3]
            message     = sys.argv[4]

            encode(input_path, output_path, message)

        elif command == "decode":
            image_path = sys.argv[2]
            message = decode(image_path)
            # Output JSON-safe untuk dibaca Laravel
            print(f"DECODED:{message}")

        else:
            print(f"ERROR: Command tidak dikenal: '{command}'")
            print("Gunakan 'encode' atau 'decode'")
            sys.exit(1)

    except FileNotFoundError as e:
        print(f"ERROR: {e}", file=sys.stderr)
        sys.exit(2)
    except ValueError as e:
        print(f"ERROR: {e}", file=sys.stderr)
        sys.exit(3)
    except Exception as e:
        print(f"ERROR: Terjadi kesalahan: {e}", file=sys.stderr)
        sys.exit(99)


if __name__ == "__main__":
    main()
