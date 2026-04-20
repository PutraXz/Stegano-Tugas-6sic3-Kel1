<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Python Binary Path
    |--------------------------------------------------------------------------
    | Path ke interpreter Python 3. Contoh:
    |   - Linux/macOS: python3
    |   - Windows:     C:/Python311/python.exe
    |   - Virtual env: /var/www/html/venv/bin/python
    |
    */
    'python_bin' => env('PYTHON_BIN', 'python3'),

    /*
    |--------------------------------------------------------------------------
    | Script Path
    |--------------------------------------------------------------------------
    | Path absolut ke skrip Python steganography.py
    |
    */
    'script_path' => env('STEGANO_SCRIPT_PATH', base_path('python/steganography.py')),

    /*
    |--------------------------------------------------------------------------
    | Process Timeout
    |--------------------------------------------------------------------------
    | Timeout maksimum (detik) untuk proses Python.
    | Untuk gambar besar, naikkan nilai ini.
    |
    */
    'timeout' => (int) env('STEGANO_TIMEOUT', 60),
];
