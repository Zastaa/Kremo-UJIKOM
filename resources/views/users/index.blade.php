@extends('layouts.app')
@section('title', 'Pengguna')
@section('page-title', 'Kelola Pengguna')
@section('content')
<div class="card">
    <div class="card-header"><h3><i class="fas fa-users-cog"></i> Daftar Pengguna</h3><a href="{{ route('users.create') }}" class="btn btn-primary btn-sm"><i class="fas fa-plus"></i> Tambah</a></div>
    <div class="card-body table-wrapper">
        <table>
            <thead><tr><th>Nama</th><th>Email</th><th>Role</th><th>Telepon</th><th>Aksi</th></tr></thead>
            <tbody>
                @forelse($users as $user)
                <tr>
                    <td style="font-weight:600;">{{ $user->name }}</td>
                    <td>{{ $user->email }}</td>
                    <td><span class="badge badge-purple">{{ $user->role }}</span></td>
                    <td>{{ $user->no_telp ?? '-' }}</td>
                    <td>
                        <div class="btn-group">
                            <a href="{{ route('users.edit', $user) }}" class="btn btn-sm btn-warning"><i class="fas fa-edit"></i></a>
                            <form action="{{ route('users.destroy', $user) }}" method="POST" onsubmit="return confirm('Hapus user ini?')">@csrf @method('DELETE')<button class="btn btn-sm btn-danger"><i class="fas fa-trash"></i></button></form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="5">Belum ada data</td></tr>
                @endforelse
            </tbody>
        </table>
        <div class="pagination">{{ $users->links('pagination.simple') }}</div>
    </div>
</div>
@endsection
