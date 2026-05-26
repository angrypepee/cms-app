@extends('layouts.app')
@section('title','Project Baru')

@section('content')
<div class="d-flex align-items-center justify-content-between mb-3">
    <h4 class="mb-0" style="font-size:1.15rem;font-weight:700"><i class="bi bi-kanban me-2 text-primary"></i>Project Baru</h4>
    <a href="{{ route('projects.index') }}" class="btn btn-sm btn-outline-secondary"><i class="bi bi-arrow-left"></i> Kembali</a>
</div>

@if($errors->any())<div class="alert alert-danger py-2"><ul class="mb-0 small">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>@endif

<form method="POST" action="{{ route('projects.store') }}">
    @csrf
    <div class="card mb-3"><div class="card-body">@include('projects._form', ['project' => null])</div></div>
    <div class="d-flex justify-content-end gap-2">
        <a href="{{ route('projects.index') }}" class="btn btn-light btn-sm">Batal</a>
        <button class="btn btn-primary btn-sm"><i class="bi bi-save me-1"></i> Simpan Project</button>
    </div>
</form>
@endsection
