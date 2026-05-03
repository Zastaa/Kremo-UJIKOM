@extends('layouts.app')
@section('title', 'Tambah User')
@section('page-title', 'Tambah User')

@section('content')
<div class="card">
    <div class="card-header">
        <h3><i class="fas fa-user-plus"></i> Tambah User Baru</h3>
    </div>
    <div class="card-body">
        <form action="{{ route('users.store') }}" method="POST">
            @csrf
            <div class="form-section">
                <div class="form-section-title"><i class="fas fa-id-badge"></i> Akun & Akses</div>
                <div class="form-grid">
                    <div class="form-group">
                        <label>Nama</label>
                        <input type="text" name="name" class="form-control" value="{{ old('name') }}" required>
                    </div>
                    <div class="form-group">
                        <label>Email</label>
                        <input type="email" name="email" class="form-control" value="{{ old('email') }}" required>
                    </div>
                    <div class="form-group">
                        <label>Password</label>
                        <div class="password-field">
                            <input type="password" id="password" name="password" class="form-control" autocomplete="new-password" required>
                            <button type="button" class="password-toggle" data-password-toggle="password" aria-label="Tampilkan password" aria-pressed="false"><i class="far fa-eye"></i></button>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Role</label>
                        <select name="role" class="form-control" required>
                            @foreach(['customer' => 'Customer', 'marketing' => 'Marketing', 'surveyor' => 'Surveyor', 'approver' => 'Approver', 'admin' => 'Admin'] as $value => $label)
                                <option value="{{ $value }}" {{ old('role', 'customer') === $value ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            <div class="form-section">
                <div class="form-section-title"><i class="fas fa-address-book"></i> Kontak</div>
                <div class="form-grid">
                    <div class="form-group">
                        <label>No. Telepon</label>
                        <input type="text" name="no_telp" class="form-control" maxlength="12" inputmode="numeric" pattern="[0-9]*" data-phone-input value="{{ old('no_telp') }}">
                        <div class="field-hint">Maksimal 12 angka.</div>
                    </div>
                    <div class="form-group">
                        <label>Alamat</label>
                        <textarea name="alamat" class="form-control">{{ old('alamat') }}</textarea>
                    </div>
                </div>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Simpan</button>
                <a href="{{ route('users.index') }}" class="btn btn-outline">Batal</a>
            </div>
        </form>
    </div>
</div>
@endsection
