@extends('layouts.app')
@section('title','Edit '.$quotation->quotation_number)

@section('content')
<div class="d-flex align-items-center justify-content-between mb-3">
    <h4 class="mb-0" style="font-size:1.15rem;font-weight:700"><i class="bi bi-file-earmark-text me-2 text-primary"></i>Edit {{ $quotation->quotation_number }}</h4>
    <a href="{{ route('quotations.show',$quotation) }}" class="btn btn-sm btn-outline-secondary"><i class="bi bi-arrow-left"></i> Kembali</a>
</div>

@if($errors->any())<div class="alert alert-danger py-2"><ul class="mb-0 small">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>@endif

<form method="POST" action="{{ route('quotations.update',$quotation) }}">
    @csrf @method('PUT')
    @include('quotations._form', ['document' => $quotation, 'selectedClient'=>null, 'selectedProject'=>null])
    <div class="d-flex justify-content-end gap-2">
        <a href="{{ route('quotations.show',$quotation) }}" class="btn btn-light btn-sm">Batal</a>
        <button class="btn btn-primary btn-sm"><i class="bi bi-save me-1"></i> Simpan Perubahan</button>
    </div>
</form>
@endsection
