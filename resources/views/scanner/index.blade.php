@extends('layouts.app')
@section('title', 'Scanner QR/Barcode')
@section('page-title', 'Scanner QR/Barcode')
@section('content')
<div style="display:grid;grid-template-columns:1fr 1fr;gap:24px">
<div class="card">
    <div class="card-header"><h3><i class="fas fa-qrcode"></i> Scan Kode</h3></div>
    <div class="card-body">
        <div id="scanner-container" style="width:100%;max-width:400px;margin:0 auto">
            <div id="reader" style="width:100%;border-radius:12px;overflow:hidden"></div>
        </div>
        <div style="text-align:center;margin-top:20px">
            <button onclick="startScanner()" class="btn btn-primary" id="btnStart"><i class="fas fa-camera"></i> Mulai Kamera</button>
            <button onclick="stopScanner()" class="btn btn-danger" id="btnStop" style="display:none"><i class="fas fa-stop"></i> Stop</button>
        </div>
        <div style="margin-top:20px">
            <label style="color:var(--text-muted);font-size:0.85rem">Atau masukkan kode manual:</label>
            <div style="display:flex;gap:8px;margin-top:8px">
                <input type="text" id="manualToken" class="form-control" placeholder="Paste token di sini...">
                <button onclick="verifyToken(document.getElementById('manualToken').value)" class="btn btn-primary"><i class="fas fa-search"></i></button>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header"><h3><i class="fas fa-info-circle"></i> Hasil Scan</h3></div>
    <div class="card-body">
        <div id="scan-result">
            <div class="empty-state"><i class="fas fa-qrcode"></i><p>Scan QR code untuk melihat detail</p></div>
        </div>
    </div>
</div>
</div>
@endsection
@push('scripts')
<script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
<script>
let html5QrCode = null;
function startScanner() {
    html5QrCode = new Html5Qrcode("reader");
    html5QrCode.start({facingMode:"environment"},{fps:10,qrbox:{width:250,height:250}},
        (text) => { stopScanner(); verifyToken(text); },
        () => {}
    ).then(()=>{document.getElementById('btnStart').style.display='none';document.getElementById('btnStop').style.display='inline-flex';});
}
function stopScanner() {
    if(html5QrCode){html5QrCode.stop().catch(()=>{});}
    document.getElementById('btnStart').style.display='inline-flex';document.getElementById('btnStop').style.display='none';
}
function verifyToken(token) {
    if(!token){alert('Token kosong');return;}
    document.getElementById('scan-result').innerHTML='<div class="empty-state"><i class="fas fa-spinner fa-spin"></i><p>Memverifikasi...</p></div>';
    fetch('/scanner/verify',{method:'POST',headers:{'Content-Type':'application/json','X-CSRF-TOKEN':document.querySelector('meta[name=csrf-token]').content},body:JSON.stringify({token:token})})
    .then(r=>r.json()).then(data=>{
        if(data.success){renderResult(data.data);}
        else{document.getElementById('scan-result').innerHTML='<div class="alert alert-danger"><i class="fas fa-times-circle"></i> '+data.message+'</div>';}
    }).catch(e=>{document.getElementById('scan-result').innerHTML='<div class="alert alert-danger">Gagal: '+e.message+'</div>';});
}
function renderResult(d) {
    let html='<div style="margin-bottom:16px"><span class="badge badge-success">Terverifikasi</span> <strong>'+d.label+'</strong></div>';
    html+='<div class="info-box" style="background:var(--bg);border:1px solid var(--border);border-radius:8px;padding:16px">';
    if(d.pengajuan){let p=d.pengajuan;
        html+='<p><strong>Pelanggan:</strong> '+(p.pelanggan?.nama_pelanggan||'-')+'</p>';
        html+='<p><strong>Motor:</strong> '+(p.motor?.nama_motor||'-')+'</p>';
        html+='<p><strong>Status:</strong> <span class="badge badge-info">'+p.status_pengajuan+'</span></p>';
        html+='<p><strong>Harga:</strong> Rp '+Number(p.harga_cash).toLocaleString('id')+'</p>';
        html+='<p style="margin-top:12px"><a href="/pengajuan/'+p.id+'" class="btn btn-primary btn-sm"><i class="fas fa-eye"></i> Lihat Detail</a></p>';
    }
    if(d.angsuran){let a=d.angsuran;
        html+='<p><strong>Pelanggan:</strong> '+(a.kredit?.pengajuan_kredit?.pelanggan?.nama_pelanggan||'-')+'</p>';
        html+='<p><strong>Angsuran Ke:</strong> '+a.angsuran_ke+'</p>';
        html+='<p><strong>Nominal:</strong> Rp '+Number(a.total_bayar).toLocaleString('id')+'</p>';
        html+='<p><strong>Status:</strong> <span class="badge '+(a.status==='Lunas'?'badge-success':'badge-warning')+'">'+a.status+'</span></p>';
        if(d.midtrans_order_id){html+='<p style="margin-top:12px"><button onclick="syncMidtrans('+a.id+')" class="btn btn-info btn-sm"><i class="fas fa-sync"></i> Sync Midtrans</button></p>';}
    }
    html+='</div>';
    document.getElementById('scan-result').innerHTML=html;
}
function syncMidtrans(id){
    fetch('/scanner/sync-midtrans',{method:'POST',headers:{'Content-Type':'application/json','X-CSRF-TOKEN':document.querySelector('meta[name=csrf-token]').content},body:JSON.stringify({angsuran_id:id})})
    .then(r=>r.json()).then(d=>{
        if(d.success){Swal.fire('Berhasil','Status Midtrans: '+d.midtrans_status,'success');}
        else{Swal.fire('Gagal',d.message,'error');}
    });
}
</script>
@endpush
