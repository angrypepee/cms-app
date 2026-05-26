@extends('layouts.app')
@section('title','Invoice Baru')

@section('content')
<div class="d-flex align-items-center justify-content-between mb-3">
    <h4 class="mb-0" style="font-size:1.15rem;font-weight:700"><i class="bi bi-file-earmark-spreadsheet me-2 text-primary"></i>Invoice Baru</h4>
    <a href="{{ route('invoices.index') }}" class="btn btn-sm btn-outline-secondary"><i class="bi bi-arrow-left"></i> Kembali</a>
</div>

@if($errors->any())<div class="alert alert-danger py-2"><ul class="mb-0 small">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>@endif

<form method="POST" action="{{ route('invoices.store') }}">
    @csrf
    @include('invoices._form', ['document' => null])
    <div class="d-flex justify-content-end gap-2">
        <a href="{{ route('invoices.index') }}" class="btn btn-light btn-sm">Batal</a>
        <button class="btn btn-primary btn-sm"><i class="bi bi-save me-1"></i> Simpan Invoice</button>
    </div>
</form>
@endsection
