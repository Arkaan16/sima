<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan {{ ucfirst($type) }}</title>
    <style>
        @page {
            size: A4 landscape;
            margin: 15mm;
        }

        body { 
            font-family: Arial, sans-serif; 
            font-size: 11px; 
        }
        
        .header-container {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #333;
            padding-bottom: 10px;
        }
        
        .logo {
            max-width: 150px; 
            height: auto;
            margin-bottom: 0px;
        }

        h1 { 
            text-align: center; 
            font-size: 16px; 
            margin: 5px 0 5px 0;
            font-weight: bold;
        }

        h2 { 
            text-align: center; 
            font-size: 13px; 
            margin: 5px 0 0 0;
            color: #666;
            font-weight: normal;
        }
        
        table { 
            width: 100%; 
            border-collapse: collapse; 
            margin-top: 15px; 
        }
        
        /* --- UBAH DISINI: RATA TENGAH SEMUA --- */
        th, td { 
            border: 1px solid #ddd; 
            padding: 6px 8px; 
            text-align: center; /* Diubah jadi center */
            vertical-align: middle; /* Agar vertikal juga tengah */
            font-size: 10px; 
        }
        
        th { 
            background-color: #f4f4f4; 
            font-weight: bold;
        }
        
        .footer {
            margin-top: 20px;
            font-size: 9px;
            color: #666;
        }

        th:first-child, td:first-child {
            width: 5%; /* Kecilkan sedikit kolom No jika center */
        }
    </style>
</head>
<body>
    {{-- Header dengan Logo --}}
    <div class="header-container">
        <img src="{{ public_path('images/ptba.png') }}" alt="Logo Perusahaan" class="logo">
        <h1>LAPORAN {{ strtoupper($type) }}</h1>
        
        {{-- --- UBAH DISINI: LOGIC JUDUL --- --}}
        {{-- Hanya tampilkan subtitle jika tipe pemeliharaan --}}
        @if($type === 'pemeliharaan')
            <h2>Periode: {{ $month }}</h2>
        @endif
    </div>

    {{-- Tabel Data --}}
    <table>
        <thead>
            <tr>
                @foreach(array_keys($data[0]) as $key)
                    <th>{{ ucfirst($key) }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @foreach($data as $row)
                <tr>
                    @foreach($row as $value)
                        <td>{{ $value }}</td>
                    @endforeach
                </tr>
            @endforeach
        </tbody>
    </table>

    {{-- Footer --}}
    <div class="footer">
        Dicetak pada: {{ now()->format('d-m-Y H:i:s') }}
    </div>
</body>
</html>