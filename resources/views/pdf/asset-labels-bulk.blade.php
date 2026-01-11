<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <style>
        /* Reset Margin agar full */
        @page { margin: 0px; padding: 0px; }
        body { margin: 0px; padding: 0px; font-family: sans-serif; }

        /* Class untuk Page Break setiap aset */
        .page-break {
            page-break-after: always;
        }

        /* Container Utama (Sama persis dengan single) */
        .container {
            width: 100%;
            height: 100%; /* Height 100% dari halaman kertas */
            position: relative;
            text-align: center;
            background-color: #fff;
            overflow: hidden; /* Mencegah konten tumpah ke halaman baru tanpa sengaja */
        }

        /* --- STYLING (COPY DARI SINGLE VIEW) --- */
        .header {
            position: absolute;
            top: 2px;
            left: 0;
            width: 100%;
            height: 20%; 
            text-align: center;
        }

        .logo-img {
            display: block; 
            margin: 0 auto; 
            height: 14px; 
            width: auto;
        }

        .company-name {
            display: block;
            margin-top: 1px; 
            font-size: 7px;
            font-weight: bold;
            text-transform: uppercase;
            color: #333;
        }

        .qr-wrapper {
            position: absolute;
            top: 22%;
            left: 0;
            width: 100%;
            height: 63%;
            text-align: center;
        }

        .qr-img {
            height: 95%;
            width: auto;
            margin: 0 auto;
            display: block;
        }

        .footer {
            position: absolute;
            bottom: 0;
            left: 0;
            width: 100%;
            height: 15%;
            background-color: transparent; 
            color: #000;
            text-align: center;
        }

        .asset-tag {
            font-weight: bold;
            font-size: {{ $fontSize }}; 
            display: block;
            position: relative;
            top: 50%;
            transform: translateY(-50%);
        }
    </style>
</head>
<body>

    @foreach($assets as $asset)
        @if($asset->qr_base64)
            <div class="container">
                {{-- HEADER --}}
                <div class="header">
                    <img src="{{ public_path('images/ptba.png') }}" class="logo-img">
                    <span class="company-name">PT. Bukit Asam Tbk.</span>
                </div>

                {{-- QR --}}
                <div class="qr-wrapper">
                    <img src="{{ $asset->qr_base64 }}" class="qr-img">
                </div>

                {{-- FOOTER --}}
                <div class="footer">
                    <span class="asset-tag">{{ $asset->asset_tag }}</span>
                </div>
            </div>

            {{-- Buat halaman baru setelah setiap aset, KECUALI aset terakhir --}}
            @if(!$loop->last)
                <div class="page-break"></div>
            @endif
        @endif
    @endforeach

</body>
</html>