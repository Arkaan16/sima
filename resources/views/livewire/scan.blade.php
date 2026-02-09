{{-- 
    View Livewire: Pemindai QR Code Aset (Employee)
    Path: resources/views/livewire/employee/scan/scan.blade.php
    
    Deskripsi:
    Interface pemindai QR code aset dengan Tampilan Lama.
    URL Redirect diperbarui ke /assets/{tag}
--}}

<div class="flex flex-col items-center justify-start pt-1 sm:pt-2 min-h-screen px-2 pb-4 sm:px-4 sm:pb-6" wire:ignore>
    <div class="w-full max-w-sm sm:max-w-lg bg-white shadow-lg rounded-2xl p-4 sm:p-6 text-center">
        {{-- Header --}}
        <h1 class="text-lg sm:text-2xl font-semibold text-gray-800 mb-1 sm:mb-2">Pemindai Kode QR</h1>
        <p class="text-gray-600 text-xs sm:text-sm mb-3 sm:mb-4 px-2">
            Arahkan kamera ke Kode QR untuk memindai aset
        </p>

        {{-- Container Scanner --}}
        <div id="reader-container" class="w-full aspect-square relative border-2 border-blue-300 rounded-xl overflow-hidden bg-gray-50 shadow-inner transition-all duration-300">
            {{-- Loading indicator --}}
            <div id="loading-indicator" class="absolute inset-0 flex items-center justify-center z-10 bg-gray-50">
                <div class="text-gray-600">
                    <svg class="animate-spin h-10 w-10 mx-auto mb-3" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <span class="text-sm font-medium">Memuat kamera...</span>
                </div>
            </div>

            {{-- Pop-up Sukses (di dalam scanner) --}}
            <div id="result" class="absolute inset-0 flex items-center justify-center z-20 hidden">
                <div class="bg-green-500 text-white px-5 py-3 rounded-xl shadow-2xl scale-0 transition-transform duration-200" id="result-popup">
                    <div class="text-center">
                        <svg class="w-10 h-10 mx-auto mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path>
                        </svg>
                        <p class="font-semibold">Aset Ditemukan!</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Pesan Error --}}
        <div id="error" class="mt-4 p-3 bg-red-50 border border-red-200 rounded-lg text-red-600 text-sm hidden"></div>
    </div>
</div>

{{-- Library HTML5 QR Code Scanner --}}
<script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>

<script>
(function() {
    'use strict';
    
    let scannerInstance = null;
    let isScanning = false;
    let currentReaderId = null;
    
    // ==========================================
    // LOGIC REDIRECT TERBARU
    // ==========================================
    const handleRedirect = (scannedContent) => {
        let assetTag = scannedContent;

        // Jika hasil scan berupa URL (misal: http://domain.com/admin/assets/TAG-001)
        // Kita ambil bagian paling belakangnya saja (TAG-001)
        if (scannedContent.includes('http')) {
            const urlParts = scannedContent.split('/');
            // Filter part yang kosong untuk menghindari error double slash
            const cleanParts = urlParts.filter(part => part !== '');
            // Ambil elemen terakhir (ID Aset)
            assetTag = cleanParts[cleanParts.length - 1];
        }

        // Redirect ke URL aset terbaru: /assets/{tag}
        window.location.href = "{{ url('/assets') }}/" + assetTag;
    };

    const generateReaderId = () => {
        return 'reader-' + Date.now() + '-' + Math.random().toString(36).substr(2, 9);
    };

    const destroyScanner = async () => {
        if (scannerInstance) {
            try {
                if (isScanning) {
                    await scannerInstance.stop();
                }
                await scannerInstance.clear();
            } catch (err) {
                console.error('Error destroying scanner:', err);
            }
            scannerInstance = null;
            isScanning = false;
        }
        if (currentReaderId) {
            const oldReader = document.getElementById(currentReaderId);
            if (oldReader) oldReader.remove();
            currentReaderId = null;
        }
    };
    
    const initializeScanner = async () => {
        const container = document.getElementById('reader-container');
        const loadingIndicator = document.getElementById('loading-indicator');
        const resultDiv = document.getElementById('result');
        const resultPopup = document.getElementById('result-popup');
        const errorDiv = document.getElementById('error');
        
        if (!container) return;
        
        // Reset UI
        if (loadingIndicator) loadingIndicator.classList.remove('hidden');
        if (resultDiv) resultDiv.classList.add('hidden');
        if (errorDiv) errorDiv.classList.add('hidden');
        
        // Reset border ke biru
        container.classList.remove('border-green-500', 'border-4', 'shadow-2xl', 'shadow-green-500/50');
        container.classList.add('border-blue-300', 'border-2');
        
        await destroyScanner();
        
        currentReaderId = generateReaderId();
        const readerDiv = document.createElement('div');
        readerDiv.id = currentReaderId;
        readerDiv.className = 'w-full h-full';
        container.appendChild(readerDiv);
        
        await new Promise(resolve => setTimeout(resolve, 50));
        
        try {
            scannerInstance = new Html5Qrcode(currentReaderId);
            
            const containerWidth = container.offsetWidth;
            const containerHeight = container.offsetHeight;
            const minDimension = Math.min(containerWidth, containerHeight);
            const qrBoxSize = Math.floor(minDimension * 0.85);
            
            const config = {
                fps: 15,
                qrbox: qrBoxSize,
                aspectRatio: 1.0,
                disableFlip: false
            };
            
            let cameraConfig = { facingMode: "environment" };
            
            const onScanSuccess = (decodedText) => {
                // Cegah eksekusi ganda
                if (!isScanning) return;
                isScanning = false; 

                // Pause scanner
                try {
                    scannerInstance.pause(true); 
                } catch(e) {}

                // ANIMASI BORDER HIJAU + POP-UP
                container.classList.remove('border-blue-300', 'border-2');
                container.classList.add('border-green-500', 'border-4', 'shadow-2xl', 'shadow-green-500/50');
                
                // Tampilkan pop-up sukses dengan animasi scale
                if (resultDiv && resultPopup) {
                    resultDiv.classList.remove('hidden');
                    setTimeout(() => {
                        resultPopup.classList.remove('scale-0');
                        resultPopup.classList.add('scale-100');
                    }, 10);
                }

                // Getaran HP
                if (navigator.vibrate) navigator.vibrate(200);

                // Redirect setelah animasi selesai
                setTimeout(() => {
                    handleRedirect(decodedText);
                }, 400);
            };

            try {
                // Coba kamera belakang (exact)
                await scannerInstance.start(
                    { facingMode: { exact: "environment" } },
                    config,
                    onScanSuccess,
                    (err) => {}
                );
                isScanning = true;
                if (loadingIndicator) loadingIndicator.classList.add('hidden');
                
            } catch (backCameraError) {
                console.log('Kamera belakang tidak tersedia, mencoba kamera lain...');
                
                // Fallback ke kamera apapun
                await scannerInstance.start(
                    cameraConfig,
                    config,
                    onScanSuccess,
                    (err) => {}
                );
                isScanning = true;
                if (loadingIndicator) loadingIndicator.classList.add('hidden');
            }
            
        } catch (err) {
            console.error('Scanner error:', err);
            isScanning = false;
            if (loadingIndicator) loadingIndicator.classList.add('hidden');
            if (errorDiv) {
                errorDiv.textContent = 'Gagal mengakses kamera. Pastikan izin kamera telah diberikan.';
                errorDiv.classList.remove('hidden');
            }
        }
    };
    
    const handleBeforeUnload = () => { destroyScanner(); };
    const handleVisibilityChange = () => { if (document.hidden) destroyScanner(); };
    
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initializeScanner);
    } else {
        initializeScanner();
    }
    
    window.addEventListener('beforeunload', handleBeforeUnload);
    window.addEventListener('pagehide', handleBeforeUnload);
    document.addEventListener('visibilitychange', handleVisibilityChange);
    document.addEventListener('livewire:navigating', destroyScanner);
    
})();
</script>