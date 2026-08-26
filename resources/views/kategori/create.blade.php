<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <a href="{{ route('kategori.index') }}" class="btn-icon btn-ghost">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
            </a>
            <div>
                <h1 class="page-title">Tambah Kategori Baru</h1>
                <p class="page-subtitle">Buat kelompok produk baru untuk katalog barang percetakan Anda</p>
            </div>
        </div>
    </x-slot>

    <div class="max-w-xl">
        <form action="{{ route('kategori.store') }}" method="POST">
            @csrf
            
            <div class="card mb-5">
                <div class="card-header">
                    <h3>Informasi Kategori</h3>
                </div>
                <div class="card-body space-y-4">
                    <div class="form-group">
                        <label for="category_name" class="form-label form-label-required">Nama Kategori</label>
                        <input type="text" name="category_name" id="category_name" 
                               class="{{ $errors->has('category_name') ? 'form-input-error' : 'form-input' }}" 
                               value="{{ old('category_name') }}" required placeholder="Contoh: Banner, Piala, Stempel, Kartu Nama...">
                        
                        @error('category_name')
                            <p class="form-error">
                                <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                                {{ $message }}
                            </p>
                        @enderror
                    </div>
                </div>
            </div>

            <div class="flex items-center gap-3">
                <button type="submit" class="btn-md btn-primary">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    Simpan Kategori
                </button>
                <a href="{{ route('kategori.index') }}" class="btn-md btn-secondary">Batal</a>
            </div>
        </form>
    </div>
</x-app-layout>