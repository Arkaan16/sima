<?php

namespace App\Livewire\Forms;

use Livewire\Form;
use Livewire\Attributes\Validate;
use App\Models\Maintenance;
use App\Models\MaintenanceImage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

class MaintenanceForm extends Form
{
    // PROPERTIES
    public $asset_id = '';
    public $title = '';
    public $description = '';
    public $maintenance_type = '';
    public $execution_date = '';
    public $selected_technicians = [];
    
    public ?Maintenance $maintenance = null;
    public $photos = [];

    // VALIDATION RULES
    public function rules()
    {
        return [
            'asset_id' => 'required|exists:assets,id',
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'maintenance_type' => 'required|string',
            'execution_date' => 'required|date',
            'selected_technicians' => 'required|array|min:1',
            'selected_technicians.*' => 'exists:users,id',
            'photos.*' => 'image|max:10240', 
        ];
    }

    public function messages()
    {
        return [
            'asset_id.required' => 'Aset wajib dipilih.',
            'asset_id.exists' => 'Aset yang dipilih tidak valid.',        
            'title.required' => 'Judul pemeliharaan wajib diisi.',        
            'description.required' => 'Deskripsi pengerjaan wajib diisi.',         
            'maintenance_type.required' => 'Jenis pemeliharaan wajib dipilih.',
            'execution_date.required' => 'Tanggal pelaksanaan wajib diisi.',
            'selected_technicians.required' => 'Harap pilih minimal satu teknisi.',
            'selected_technicians.min' => 'Harap pilih minimal satu teknisi.',
            'photos.required' => 'Bukti foto wajib diupload.',
            'photos.min' => 'Harap upload minimal 1 foto sebagai bukti.',
            'photos.max' => 'Maksimal hanya boleh 3 foto.',
            'photos.*.max' => 'Ukuran foto terlalu besar (Maks 10MB).',
        ];
    }

    public function setMaintenance(Maintenance $maintenance)
    {
        $this->maintenance = $maintenance;
        
        $this->asset_id = $maintenance->asset_id;
        $this->title = $maintenance->title;
        $this->description = $maintenance->description;
        $this->maintenance_type = $maintenance->maintenance_type;
        
        $this->execution_date = $maintenance->execution_date instanceof \DateTime 
            ? $maintenance->execution_date->format('Y-m-d') 
            : $maintenance->execution_date;

        $this->selected_technicians = $maintenance->technicians->pluck('id')->map(fn($id) => (string) $id)->toArray();
    }

    public function store()
    {
        // FIX: Gabungkan validasi foto dan form standar
        // Kita ambil rules dasar, lalu timpa rules 'photos' agar required saat CREATE
        $rules = $this->rules();
        $rules['photos'] = 'required|array|min:1|max:3';
        
        // Jalankan validasi SEKALIGUS agar semua error muncul
        $this->validate($rules);

        try {
            DB::transaction(function () {
                
                $maintenance = Maintenance::create(
                    $this->except(['photos', 'selected_technicians', 'maintenance'])
                );

                $maintenance->technicians()->attach($this->selected_technicians);

                if (!empty($this->photos)) {
                    if(!Storage::disk('public')->exists('maintenance-photos')) {
                        Storage::disk('public')->makeDirectory('maintenance-photos');
                    }

                    foreach ($this->photos as $photo) {
                        $path = $this->compressAndStore($photo);

                        MaintenanceImage::create([
                            'maintenance_id' => $maintenance->id,
                            'photo_path' => $path,
                        ]);
                    }
                }
            });
            

        } catch (\Exception $e) {
            throw $e; 
        }
    }

    public function update(array $photosToDelete = [])
    {
        $this->validate();

        $existingCount = $this->maintenance->images()
            ->whereNotIn('id', $photosToDelete)
            ->count();
            
        $newCount = count($this->photos);
        $totalPhotos = $existingCount + $newCount;

        if ($totalPhotos < 1) {
            throw ValidationException::withMessages([
                'photos' => 'Harus ada minimal 1 foto dokumentasi (Foto lama atau baru).',
            ]);
        }

        if ($totalPhotos > 3) {
            throw ValidationException::withMessages([
                'photos' => 'Maksimal hanya 3 foto dokumentasi.',
            ]);
        }

        try {
            DB::transaction(function () use ($photosToDelete) {
                $this->maintenance->update(
                    $this->except(['photos', 'selected_technicians', 'maintenance', 'asset_id'])
                );

                $this->maintenance->technicians()->sync($this->selected_technicians);

                if (!empty($photosToDelete)) {
                    $imagesToDelete = $this->maintenance->images()
                        ->whereIn('id', $photosToDelete)
                        ->get();

                    foreach ($imagesToDelete as $img) {
                        if ($img->photo_path && Storage::disk('public')->exists($img->photo_path)) {
                            Storage::disk('public')->delete($img->photo_path);
                        }
                        $img->delete();
                    }
                }

                if (!empty($this->photos)) {
                    if(!Storage::disk('public')->exists('maintenance-photos')) {
                        Storage::disk('public')->makeDirectory('maintenance-photos');
                    }

                    foreach ($this->photos as $photo) {
                        $path = $this->compressAndStore($photo);
                        MaintenanceImage::create([
                            'maintenance_id' => $this->maintenance->id,
                            'photo_path' => $path,
                        ]);
                    }
                }
            });
            
            $this->photos = [];
            
        } catch (\Exception $e) {
            throw $e;
        }
    }

    public function deleteExistingImage($imageId)
    {
        $image = MaintenanceImage::find($imageId);
        
        if ($image && $image->maintenance_id == $this->maintenance->id) {
            if (Storage::disk('public')->exists($image->photo_path)) {
                Storage::disk('public')->delete($image->photo_path);
            }
            $image->delete();
            $this->maintenance->load('images');
        }
    }

    public function removePhoto($index)
    {
        unset($this->photos[$index]);
        $this->photos = array_values($this->photos); 
    }

    protected function compressAndStore($file)
    {
        $filename = md5($file->getClientOriginalName() . microtime()) . '.jpg';
        $path = 'maintenance-photos/' . $filename;

        try {
            $manager = new ImageManager(new Driver());
            $image = $manager->read($file->getRealPath());
            $image->scaleDown(width: 1200);
            $encoded = $image->toJpeg(quality: 80);
            Storage::disk('public')->put($path, $encoded);
        } catch (\Exception $e) {
            $path = $file->storeAs('maintenance-photos', $filename, 'public');
        }

        return $path;
    }
}