@extends('layouts.app')
@section('title', 'Email Logs')
@section('page-title', 'Log Email')
@section('content')
<div class="card" style="margin-bottom:24px"><div class="card-body">
<form method="GET" style="display:flex;gap:12px;flex-wrap:wrap;align-items:flex-end">
<div class="form-group" style="margin-bottom:0;min-width:200px"><label>Cari Email</label><input type="text" name="search" class="form-control" placeholder="Email penerima..." value="{{ request('search') }}"></div>
<div class="form-group" style="margin-bottom:0;min-width:140px"><label>Status</label><select name="status" class="form-control"><option value="">Semua</option><option value="sent" {{ request('status')==='sent'?'selected':'' }}>Terkirim</option><option value="failed" {{ request('status')==='failed'?'selected':'' }}>Gagal</option><option value="queued" {{ request('status')==='queued'?'selected':'' }}>Antrian</option></select></div>
<button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-search"></i> Cari</button>
</form></div></div>

<div class="card"><div class="card-header"><h3>Riwayat Email</h3></div><div class="table-wrapper"><table>
<thead><tr><th>Waktu</th><th>Penerima</th><th>Subject</th><th>Template</th><th>Status</th><th>Error</th></tr></thead>
<tbody>
@forelse($logs as $log)
<tr><td>{{ $log->created_at->format('d/m/Y H:i') }}</td><td>{{ $log->to_email }}</td><td>{{ Str::limit($log->subject, 40) }}</td><td><span class="badge badge-purple">{{ $log->template }}</span></td>
<td><span class="badge {{ $log->status==='sent'?'badge-success':($log->status==='failed'?'badge-danger':'badge-warning') }}">{{ $log->status }}</span></td>
<td style="max-width:200px;overflow:hidden;text-overflow:ellipsis">{{ Str::limit($log->error_message, 50) ?? '-' }}</td></tr>
@empty<tr><td colspan="6"><div class="empty-state"><p>Belum ada log email</p></div></td></tr>@endforelse
</tbody></table></div>
<div class="card-body">{{ $logs->links() }}</div>
</div>
@endsection
