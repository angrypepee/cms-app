@extends('layouts.app')
@section('title','Edit Project '.$project->code)

@section('content')
<div class="d-flex align-items-center justify-content-between mb-3">
    <h4 class="mb-0" style="font-size:1.15rem;font-weight:700"><i class="bi bi-kanban me-2 text-primary"></i>Edit {{ $project->code }}</h4>
    <a href="{{ route('projects.show',$project) }}" class="btn btn-sm btn-outline-secondary"><i class="bi bi-arrow-left"></i> Kembali</a>
</div>

@if($errors->any())<div class="alert alert-danger py-2"><ul class="mb-0 small">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>@endif

<form method="POST" action="{{ route('projects.update',$project) }}">
    @csrf @method('PUT')
    <div class="card mb-3"><div class="card-body">@include('projects._form', ['project' => $project])</div></div>
    <div class="d-flex justify-content-between">
        <button type="button" class="btn btn-outline-danger btn-sm"
            onclick="if(confirm('Hapus project {{ $project->code }}?')) document.getElementById('projectDeleteForm').submit();">
            <i class="bi bi-trash me-1"></i>Hapus
        </button>
        <div class="d-flex gap-2">
            <a href="{{ route('projects.show',$project) }}" class="btn btn-light btn-sm">Batal</a>
            <button class="btn btn-primary btn-sm"><i class="bi bi-save me-1"></i> Simpan Perubahan</button>
        </div>
    </div>
</form>

<form id="projectDeleteForm" action="{{ route('projects.destroy',$project) }}" method="POST" class="d-none">
    @csrf @method('DELETE')
</form>
@endsection
