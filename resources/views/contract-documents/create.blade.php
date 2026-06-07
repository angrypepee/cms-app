@extends('layouts.app')
@section('title', 'Tambah Dokumen Kontrak')
@section('page-title', 'Tambah Dokumen Kontrak')

@section('content')
<div class="card">
    <div class="card-header"><span class="card-title">Buat Dokumen Kontrak Kerja</span></div>
    <div class="card-body p-4">
        <form method="POST" action="{{ route('contract-documents.store') }}" enctype="multipart/form-data">
            @include('contract-documents._form', ['contractDocument' => null, 'suggestedContractNumber' => $suggestedContractNumber ?? null])
        </form>
    </div>
</div>
@endsection