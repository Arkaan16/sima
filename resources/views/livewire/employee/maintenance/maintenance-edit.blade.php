<div class="container mx-auto px-4">
    <div class="max-w-4xl mx-auto">
        
        {{-- HEADER --}}
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6 gap-4">
            <h1 class="text-2xl md:text-3xl font-bold text-gray-800">Edit Data Pemeliharaan</h1>
            
            <a href="{{ route('employee.maintenances.index') }}" wire:navigate 
            class="group flex items-center justify-center gap-2 w-full sm:w-auto bg-white text-gray-700 py-2 px-6 rounded-lg border border-gray-200 hover:border-blue-400 hover:text-blue-600 transition-all shadow-sm font-medium touch-manipulation">
                <svg class="w-4 h-4 text-gray-400 group-hover:text-blue-500 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                Kembali
            </a>
        </div>

        {{-- FORM CARD --}}
        <div class="bg-white rounded-xl shadow-lg p-6 md:p-8">
            <form 
                wire:submit.prevent="save" 
                x-data="{ loading: false }" 
                x-on:submit="loading = true"
                x-on:validation-fails.window="loading = false"
            >
                <div class="space-y-6">

                    {{-- BAGIAN 1: Aset (Locked) & Judul --}}
                    <div class="grid grid-cols-1 gap-6">
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Kode Aset <span class="text-xs text-gray-400 font-normal">(Tidak dapat diubah)</span>
                            </label>
                            
                            <div class="w-full bg-gray-50 border border-gray-200 rounded-md px-3 py-2 flex items-center justify-between shadow-inner">
                                <span class="flex items-center gap-3 truncate opacity-75">
                                    @if($assetImage)
                                        <img src="{{ asset('storage/'.$assetImage) }}" class="h-6 w-6 rounded-md object-cover bg-gray-200 border border-gray-300 shrink-0 grayscale">
                                    @endif
                                    <span class="block truncate text-gray-600 font-medium">{{ $assetDisplayString }}</span>
                                </span>
                                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                                </svg>
                            </div>
                        </div>

                        <div class="md:col-span-2">
                            <label for="title" class="block text-sm font-medium text-gray-700 mb-1">
                                Judul Aktivitas <span class="text-red-500">*</span>
                            </label>
                            <input type="text" wire:model="form.title" id="title" class="w-full border rounded-md px-3 py-2 focus:ring-blue-500 focus:border-blue-500 sm:text-sm @error('form.title') border-red-500 @else border-gray-300 @enderror">
                            @error('form.title') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    {{-- Deskripsi --}}
                    <div>
                        <label for="description" class="block text-sm font-medium text-gray-700 mb-1">
                            Deskripsi Pekerjaan <span class="text-red-500">*</span>
                        </label>
                        <textarea wire:model="form.description" id="description" rows="4" class="w-full border rounded-lg p-3 focus:ring-blue-500 focus:border-blue-500 @error('form.description') border-red-500 @else border-gray-300 @enderror"></textarea>
                        @error('form.description') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>

                    {{-- BAGIAN 2: Detail Teknis --}}
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="maintenance_type" class="block text-sm font-medium text-gray-700 mb-1">
                                Tipe Pemeliharaan <span class="text-red-500">*</span>
                            </label>
                            <select wire:model="form.maintenance_type" id="maintenance_type" class="w-full border rounded-lg p-3 @error('form.maintenance_type') border-red-500 @else border-gray-300 @enderror">
                                <option value="">-- Pilih Tipe --</option>
                                @foreach($maintenanceTypes as $key => $value)
                                    <option value="{{ $key }}">{{ $value }}</option>
                                @endforeach
                            </select>
                            @error('form.maintenance_type') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label for="execution_date" class="block text-sm font-medium text-gray-700 mb-1">
                                Tanggal Pelaksanaan <span class="text-red-500">*</span>
                            </label>
                            <input type="date" wire:model="form.execution_date" id="execution_date" class="w-full border rounded-lg p-3 @error('form.execution_date') border-red-500 @else border-gray-300 @enderror">
                            @error('form.execution_date') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    {{-- BAGIAN 3: Teknisi --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Tim Teknisi Bertugas <span class="text-red-500">*</span></label>
                        <div class="border rounded-xl p-4 max-h-48 overflow-y-auto bg-gray-50 @error('form.selected_technicians') border-red-500 @else border-gray-300 @enderror">
                            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-2">
                                @foreach($technicians as $technician)
                                    <label class="flex items-center space-x-3 py-1.5 px-2 rounded-lg hover:bg-white transition cursor-pointer">
                                        <input type="checkbox" wire:model="form.selected_technicians" value="{{ $technician->id }}" class="w-5 h-5 text-blue-600 rounded border-gray-300">
                                        <span class="text-sm text-gray-700 font-medium truncate">{{ $technician->name }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </div>
                        @error('form.selected_technicians') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>

                    {{-- BAGIAN 4: UPLOAD FOTO --}}
                    {{-- BAGIAN 4: UPLOAD FOTO --}}
                    <div class="mt-8">
                        @php 
                            $activeDbPhotos = $form->maintenance->images->whereNotIn('id', $photosToDelete);
                            $dbPhotoCount = $activeDbPhotos->count();
                            $newPhotoCount = count($form->photos ?? []);
                            $totalPhotoCount = $dbPhotoCount + $newPhotoCount;
                        @endphp

                        <div class="flex justify-between items-end mb-3">
                            <label class="block text-sm font-medium text-gray-700">
                                Foto Dokumentasi <span class="text-red-500">*</span> 
                                <span class="hidden sm:inline text-gray-500 font-normal">(Maks 3 foto)</span>
                            </label>
                            
                            <span class="text-xs font-bold px-2 py-1 rounded {{ $totalPhotoCount >= 3 ? 'bg-red-100 text-red-600' : 'bg-blue-100 text-blue-600' }}">
                                {{ $totalPhotoCount }} / 3
                            </span>
                        </div>

                        {{-- OPSI UPLOAD (KAMERA & GALERI) --}}
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4 relative transition-all duration-300 {{ $totalPhotoCount >= 3 ? 'opacity-50 pointer-events-none grayscale' : '' }}">
                            
                            {{-- OPSI 1: BUKA KAMERA --}}
                            <button type="button" id="openCameraBtn" 
                                data-current-total="{{ $totalPhotoCount }}"
                                class="border-2 border-dashed border-blue-300 rounded-xl p-6 text-center bg-blue-50 hover:bg-blue-100 hover:border-blue-500 transition duration-200 cursor-pointer w-full">
                                <div class="flex flex-col items-center">
                                    <svg class="h-12 w-12 text-blue-600 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"></path>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                    </svg>
                                    <p class="text-sm text-blue-700 font-semibold">Buka Kamera</p>
                                    <p class="text-xs text-blue-600 mt-1">Ambil Foto Langsung</p>
                                </div>
                            </button>

                            {{-- OPSI 2: PILIH FILE --}}
                            <div class="border-2 border-dashed border-gray-300 rounded-xl p-6 text-center bg-gray-50 hover:bg-green-50 hover:border-green-400 transition duration-200 relative cursor-pointer w-full">
                                <div class="flex flex-col items-center pointer-events-none">
                                    <svg class="h-12 w-12 text-green-600 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                    </svg>
                                    <p class="text-sm text-green-700 font-semibold">Pilih File</p>
                                    <p class="text-xs text-green-600 mt-1">Dari Galeri</p>
                                </div>
                                <input type="file" 
                                    id="fileInput"
                                    wire:model="tempPhotos" 
                                    wire:key="upload-edit-{{ $totalPhotoCount }}"
                                    multiple 
                                    accept="image/*"
                                    {{ $totalPhotoCount >= 3 ? 'disabled' : '' }} 
                                    class="absolute inset-0 w-full h-full opacity-0 cursor-pointer disabled:cursor-not-allowed">
                            </div>

                            {{-- Overlay Maksimal Foto --}}
                            @if($totalPhotoCount >= 3)
                                <div class="absolute inset-0 flex items-center justify-center bg-white/60 z-10 backdrop-blur-[1px] rounded-xl">
                                    <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-2 rounded-lg shadow-sm text-sm font-semibold flex items-center">
                                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                                        Maksimal 3 Foto
                                    </div>
                                </div>
                            @endif
                        </div>

                        <p class="text-xs text-gray-500 mb-4">Format: JPG/PNG. Maksimal 10MB per foto.</p>

                        {{-- INDIKATOR LOADING UPLOAD --}}
                        <div wire:loading wire:target="tempPhotos" class="mb-4 w-full">
                            <div class="flex items-center text-blue-600 text-sm font-medium bg-blue-50 p-3 rounded-lg w-full">
                                <svg class="animate-spin h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                Mengunggah gambar...
                            </div>
                        </div>
                        
                        @error('tempPhotos') <p class="mb-4 text-sm text-red-600 bg-red-50 p-3 rounded-lg border border-red-100">{{ $message }}</p> @enderror
                        @error('form.photos') <p class="mb-4 text-sm text-red-600 bg-red-50 p-3 rounded-lg">{{ $message }}</p> @enderror

                        {{-- PREVIEW GALLERY (GABUNGAN LAMA & BARU) --}}
                        <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                            {{-- A. FOTO DARI DATABASE (LAMA) --}}
                            @foreach($activeDbPhotos as $dbImage)
                                <div wire:key="db-img-{{ $dbImage->id }}" class="relative group rounded-xl overflow-hidden shadow-md border-2 border-gray-200 bg-gray-100">
                                    <img src="{{ asset('storage/' . $dbImage->photo_path) }}" 
                                        class="w-full h-48 object-cover hover:scale-105 transition duration-300">
                                    
                                    <div class="absolute top-2 left-2 bg-gray-600/80 text-white text-[10px] font-bold px-2 py-1 rounded-full">
                                        Lama
                                    </div>
                                    
                                    <button type="button" wire:click="deleteExistingPhoto({{ $dbImage->id }})" 
                                        class="absolute top-2 right-2 bg-white/90 text-red-500 p-1.5 rounded-full hover:bg-red-500 hover:text-white shadow-lg transition-all duration-200 cursor-pointer">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                    </button>
                                </div>
                            @endforeach

                            {{-- B. FOTO BARU (ANTREAN UPLOAD) --}}
                            @foreach($form->photos as $index => $photo)
                                <div wire:key="new-img-{{ $index }}" class="relative group rounded-xl overflow-hidden shadow-md border-2 border-blue-200 bg-blue-50">
                                    <img src="{{ $photo->temporaryUrl() }}" 
                                        class="w-full h-48 object-cover hover:scale-105 transition duration-300">
                                    
                                    <div class="absolute top-2 left-2 bg-blue-600 text-white text-[10px] font-bold px-2 py-1 rounded-full">
                                        Baru
                                    </div>
                                    
                                    <button type="button" wire:click="removeNewPhoto({{ $index }})" 
                                        class="absolute top-2 right-2 bg-white/90 text-red-500 p-1.5 rounded-full hover:bg-red-500 hover:text-white shadow-lg transition-all duration-200 cursor-pointer">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                    </button>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                {{-- FOOTER BUTTONS --}}
                <div class="mt-10 pt-6 border-t border-gray-200 flex flex-col-reverse md:flex-row justify-end gap-3">
                    <a href="{{ route('employee.maintenances.index') }}" wire:navigate class="w-full md:w-auto text-center px-6 py-2.5 rounded-lg border border-gray-300 text-gray-700 bg-white hover:bg-gray-50 font-medium transition duration-150 shadow-sm" :class="{ 'pointer-events-none opacity-50': loading }">
                        Batal
                    </a>

                    <button type="submit" :disabled="loading" class="w-full md:w-auto flex justify-center items-center px-6 py-2.5 rounded-lg bg-blue-600 text-white font-medium shadow-md transition hover:bg-blue-700 disabled:opacity-70 disabled:cursor-not-allowed">
                        <div x-show="!loading" class="flex items-center">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"></path></svg>
                            <span>Simpan Perubahan</span>
                        </div>
                        <div x-show="loading" style="display: none;" class="flex items-center">
                            <svg class="animate-spin w-5 h-5 mr-2 text-white" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                            <span>Memproses...</span>
                        </div>
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- MODAL KAMERA (Sama dengan Create) --}}
    <div id="cameraModal" class="fixed inset-0 bg-black bg-opacity-75 z-50 hidden flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl max-w-2xl w-full p-6">
            <div class="flex justify-between items-center mb-4">
                <div>
                    <h3 class="text-xl font-bold text-gray-800">Ambil Foto</h3>
                    <p id="photoCounter" class="text-sm text-blue-600 font-medium">Foto: 0 dari 0</p>
                </div>
                <button type="button" id="closeCameraBtn" class="text-gray-500 hover:text-gray-700">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>
            
            <div class="relative bg-black rounded-xl overflow-hidden aspect-[4/3]">
                <video id="cameraVideo" autoplay playsinline class="w-full h-full object-cover"></video>
                <canvas id="cameraCanvas" class="hidden"></canvas>
                <div class="absolute bottom-4 left-0 right-0 px-4">
                    <div id="thumbnailStrip" class="flex gap-2 justify-center"></div>
                </div>
            </div>
            
            <div class="mt-4 flex flex-col md:flex-row gap-3">
                <button type="button" id="captureBtn" class="flex-1 justify-center px-6 py-3 bg-blue-600 text-white rounded-lg font-semibold hover:bg-blue-700 transition flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"></path></svg>
                    Ambil Foto
                </button>
                <button type="button" id="finishCaptureBtn" class="flex-1 justify-center px-6 py-3 bg-green-600 text-white rounded-lg font-semibold hover:bg-green-700 transition flex items-center gap-2">
                    Selesai
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    let stream = null;
    let capturedBlobs = []; 
    const MAX_TOTAL_LIMIT = 3; // Limit absolut sistem
    let currentSessionLimit = 0; // Sisa slot yang tersedia

    const modal = document.getElementById('cameraModal');
    const video = document.getElementById('cameraVideo');
    const canvas = document.getElementById('cameraCanvas');
    const openBtn = document.getElementById('openCameraBtn');
    const closeBtn = document.getElementById('closeCameraBtn');
    const captureBtn = document.getElementById('captureBtn');
    const finishBtn = document.getElementById('finishCaptureBtn');
    const thumbnailStrip = document.getElementById('thumbnailStrip');
    const photoCounter = document.getElementById('photoCounter');

    // 1. Fungsi Buka Kamera
    openBtn.addEventListener('click', async () => {
        // Ambil jumlah foto aktif (DB + Antrean Baru) dari atribut data di Blade
        const currentActiveCount = parseInt(openBtn.getAttribute('data-current-total') || 0);
        
        // Hitung sisa slot (Misal: 3 - 2 foto lama = 1 slot sisa)
        currentSessionLimit = MAX_TOTAL_LIMIT - currentActiveCount;

        if (currentSessionLimit <= 0) {
            alert('Slot foto sudah penuh (Maksimal 3). Hapus foto yang ada terlebih dahulu.');
            return;
        }

        capturedBlobs = []; // Reset antrean sesi kamera ini
        updateCameraUI();

        try {
            stream = await navigator.mediaDevices.getUserMedia({ 
                video: { facingMode: 'environment' } 
            });
            video.srcObject = stream;
            modal.classList.remove('hidden');
        } catch (err) {
            alert('Tidak dapat mengakses kamera. Pastikan izin sudah diberikan.');
            console.error(err);
        }
    });

    // 2. Fungsi Ambil Foto (Shutter)
    captureBtn.addEventListener('click', () => {
        if (capturedBlobs.length >= currentSessionLimit) return;

        canvas.width = video.videoWidth;
        canvas.height = video.videoHeight;
        canvas.getContext('2d').drawImage(video, 0, 0);
        
        canvas.toBlob((blob) => {
            capturedBlobs.push(blob);
            updateCameraUI();
        }, 'image/jpeg', 0.8);
    });

    // 3. Update UI di dalam Modal
    function updateCameraUI() {
        // Tampilkan: "Foto: 1 dari 2 sisa slot"
        photoCounter.innerText = `Foto: ${capturedBlobs.length} dari ${currentSessionLimit} sisa slot`;

        thumbnailStrip.innerHTML = '';
        capturedBlobs.forEach((blob) => {
            const url = URL.createObjectURL(blob);
            const thumb = document.createElement('div');
            thumb.className = "w-14 h-14 border-2 border-white rounded-lg overflow-hidden shadow-md bg-gray-800";
            thumb.innerHTML = `<img src="${url}" class="w-full h-full object-cover">`;
            thumbnailStrip.appendChild(thumb);
        });

        // Kunci tombol ambil jika sisa slot habis
        captureBtn.disabled = capturedBlobs.length >= currentSessionLimit;
        if(captureBtn.disabled) {
            captureBtn.classList.add('opacity-50', 'cursor-not-allowed');
        } else {
            captureBtn.classList.remove('opacity-50', 'cursor-not-allowed');
        }
    }

    // 4. Fungsi Selesai (Kirim ke Livewire)
    finishBtn.addEventListener('click', () => {
        if (capturedBlobs.length > 0) {
            const dataTransfer = new DataTransfer();
            
            capturedBlobs.forEach((blob, index) => {
                const file = new File([blob], `edit_cam_${Date.now()}_${index}.jpg`, { type: 'image/jpeg' });
                dataTransfer.items.add(file);
            });

            const fileInput = document.getElementById('fileInput');
            if(fileInput) {
                fileInput.files = dataTransfer.files;
                // Trigger event change agar Livewire menangkap uploadnya
                fileInput.dispatchEvent(new Event('change', { bubbles: true }));
            }
        }
        stopCamera();
    });

    function stopCamera() {
        if (stream) {
            stream.getTracks().forEach(track => track.stop());
            stream = null;
        }
        modal.classList.add('hidden');
    }

    closeBtn.addEventListener('click', stopCamera);
</script>