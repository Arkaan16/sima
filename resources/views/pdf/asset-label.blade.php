<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <style>
        /* Reset Margin Halaman */
        @page { margin: 0px; padding: 0px; }
        body { margin: 0px; padding: 0px; font-family: sans-serif; }

        /* Container Utama */
        .container {
            width: 100%;
            height: 100%;
            position: relative;
            text-align: center;
            background-color: #fff; /* Pastikan background putih bersih */
        }

        /* --- BAGIAN ATAS (LOGO & NAMA) --- */
        .header {
            position: absolute;
            top: 2px; /* Jarak sedikit dari atas */
            left: 0;
            width: 100%;
            /* Sedikit diperbesar tingginya untuk menampung logo & teks bertumpuk */
            height: 20%; 
            text-align: center;
        }

        .logo-img {
            /* Agar logo di tengah dan bisa ditumpuk */
            display: block; 
            margin: 0 auto; 
            height: 14px; /* Ukuran fix agar proporsional di label kecil */
            width: auto;
        }

        .company-name {
            /* Agar teks di bawah logo */
            display: block;
            margin-top: 1px; 
            font-size: 7px; /* Font disesuaikan agar muat */
            font-weight: bold;
            text-transform: uppercase;
            color: #333;
        }

        /* --- BAGIAN TENGAH (QR CODE) --- */
        .qr-wrapper {
            position: absolute;
            top: 22%; /* Mulai setelah header selesai */
            left: 0;
            width: 100%;
            height: 63%; /* Sisa ruang untuk QR */
            text-align: center;
        }

        .qr-img {
            height: 95%; /* Dimaksimalkan dalam wrapper */
            width: auto;
            margin: 0 auto;
            display: block;
        }

        /* --- BAGIAN BAWAH (ASSET TAG) --- */
        .footer {
            position: absolute;
            bottom: 0;
            left: 0;
            width: 100%;
            height: 15%;
            /* PERBAIKAN DISINI: Background hitam dihapus */
            background-color: transparent; 
            color: #000; /* Teks jadi hitam */
            text-align: center;
        }

        .asset-tag {
            font-weight: bold;
            /* Ukuran font sedikit diperbesar karena tidak ada kotak hitam */
            font-size: 11px; 
            display: block;
            /* Trik centering vertikal */
            position: relative;
            top: 50%;
            transform: translateY(-50%);
        }
    </style>
</head>
<body>
    <div class="container">
        {{-- HEADER: Logo di atas, Nama di bawah, rata tengah --}}
        <div class="header">
            <img src="{{ public_path('images/ptba.png') }}" class="logo-img" alt="Logo">
            <span class="company-name">PT. Bukit Asam Tbk.</span>
        </div>

        {{-- QR CODE --}}
        <div class="qr-wrapper">
            <img src="{{ $qrImage }}" class="qr-img" alt="QR Code">
        </div>

        {{-- FOOTER: Asset Tag tanpa background hitam --}}
        <div class="footer">
            <span class="asset-tag">{{ $asset->asset_tag }}</span>
        </div>
    </div>
</body>
</html>