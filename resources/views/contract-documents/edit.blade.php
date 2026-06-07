@extends('layouts.app')
@section('title', 'Edit Dokumen Kontrak')
@section('page-title', 'Edit Dokumen Kontrak')

@section('content')
<div class="card">
    <div class="card-header"><span class="card-title">Edit Dokumen Kontrak: {{ $contractDocument->contract_number }}</span></div>
    <div class="card-body p-4">
        <form method="POST" action="{{ route('contract-documents.update', $contractDocument) }}" enctype="multipart/form-data">
            @method('PUT')
            @include('contract-documents._form', ['contractDocument' => $contractDocument, 'suggestedContractNumber' => null])
        </form>
    </div>
</div>
@endsection