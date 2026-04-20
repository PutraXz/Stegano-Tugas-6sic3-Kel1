<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

/**
 * SteganographyService
 * ====================
 * Service layer untuk menjalankan skrip Python LSB steganography
 * menggunakan Symfony Process. Aman dari command injection karena
 * argumen dipisahkan sebagai array (bukan string shell).
 */
class SteganographyService
{
    /**
     * Path ke interpreter Python 3.
     * Konfigurasi via env: PYTHON_PATH (default: python3)
     */
    private string $pythonBin;

    /**
     * Path absolut ke skrip Python steganography.
     */
    private string $scriptPath;

    /**
     * Timeout maksimum proses Python (detik).
     */
    private int $timeout;

    public function __construct()
    {
        $this->pythonBin  = config('steganography.python_bin', 'python3');
        $this->scriptPath = config('steganography.script_path', base_path('python/steganography.py'));
        $this->timeout    = (int) config('steganography.timeout', 60);
    }

    // ─── Public API ───────────────────────────────────────────────────────────

    /**
     * Encode pesan ke dalam gambar menggunakan LSB.
     *
     * @param  string $inputPath   Path absolut gambar sumber
     * @param  string $outputPath  Path absolut gambar hasil encode
     * @param  string $message     Pesan rahasia yang akan disisipkan
     * @return array{success: bool, output: string, error: string}
     */
    public function encode(string $inputPath, string $outputPath, string $message): array
    {
        // Validasi keberadaan file sebelum memanggil Python
        if (! file_exists($inputPath)) {
            return $this->errorResult("File gambar tidak ditemukan: {$inputPath}");
        }

        // Validasi pesan tidak kosong
        if (trim($message) === '') {
            return $this->errorResult("Pesan tidak boleh kosong.");
        }

        // Argumen sebagai array → aman dari command injection
        $command = [
            $this->pythonBin,
            $this->scriptPath,
            'encode',
            $inputPath,
            $outputPath,
            $message,
        ];

        return $this->runProcess($command, 'encode');
    }

    /**
     * Decode pesan tersembunyi dari gambar yang telah di-encode LSB.
     *
     * @param  string $imagePath Path absolut gambar yang akan di-decode
     * @return array{success: bool, message: string, error: string}
     */
    public function decode(string $imagePath): array
    {
        if (! file_exists($imagePath)) {
            return $this->errorResult("File gambar tidak ditemukan: {$imagePath}");
        }

        $command = [
            $this->pythonBin,
            $this->scriptPath,
            'decode',
            $imagePath,
        ];

        $result = $this->runProcess($command, 'decode');

        if (! $result['success']) {
            return $result;
        }

        // Parse output: Python mencetak "DECODED:<pesan>"
        $output = trim($result['output']);
        if (str_starts_with($output, 'DECODED:')) {
            $result['message'] = substr($output, strlen('DECODED:'));
        } else {
            $result['message'] = $output;
        }

        return $result;
    }

    // ─── Private Helpers ──────────────────────────────────────────────────────

    /**
     * Jalankan proses Python dan kembalikan hasilnya.
     *
     * @param  array  $command  Argumen proses (array, bukan string)
     * @param  string $context  Label untuk logging
     */
    private function runProcess(array $command, string $context): array
    {
        try {
            $process = new Process($command);
            $process->setTimeout($this->timeout);
            $process->run();

            $stdout = trim($process->getOutput());
            $stderr = trim($process->getErrorOutput());

            if (! $process->isSuccessful()) {
                $errorMsg = $stderr ?: "Proses Python gagal (exit code: {$process->getExitCode()})";

                Log::error("Steganography [{$context}] FAILED", [
                    'command'   => implode(' ', $command),
                    'exit_code' => $process->getExitCode(),
                    'stderr'    => $stderr,
                    'stdout'    => $stdout,
                ]);

                return $this->errorResult($errorMsg);
            }

            Log::info("Steganography [{$context}] SUCCESS", [
                'output' => $stdout,
            ]);

            return [
                'success' => true,
                'output'  => $stdout,
                'error'   => '',
                'message' => '',
            ];

        } catch (\Symfony\Component\Process\Exception\ProcessTimedOutException $e) {
            $msg = "Proses Python timeout setelah {$this->timeout} detik.";
            Log::error("Steganography [{$context}] TIMEOUT", ['error' => $msg]);
            return $this->errorResult($msg);

        } catch (\Exception $e) {
            Log::error("Steganography [{$context}] EXCEPTION", [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return $this->errorResult("Terjadi kesalahan: " . $e->getMessage());
        }
    }

    /**
     * Buat array error standar.
     */
    private function errorResult(string $errorMessage): array
    {
        return [
            'success' => false,
            'output'  => '',
            'error'   => $errorMessage,
            'message' => '',
        ];
    }

    // ─── Utility ──────────────────────────────────────────────────────────────

    /**
     * Cek apakah Python tersedia di sistem.
     */
    public function isPythonAvailable(): bool
    {
        $process = new Process([$this->pythonBin, '--version']);
        $process->setTimeout(5);
        $process->run();
        return $process->isSuccessful();
    }

    /**
     * Cek apakah library Pillow (PIL) tersedia.
     */
    public function isPillowAvailable(): bool
    {
        $process = new Process([
            $this->pythonBin,
            '-c',
            'from PIL import Image; print("OK")',
        ]);
        $process->setTimeout(5);
        $process->run();
        return $process->isSuccessful() && str_contains($process->getOutput(), 'OK');
    }
}
