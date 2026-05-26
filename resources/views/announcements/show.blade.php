@extends('layouts.app')

@section('page-title', $announcement->title)

@section('content')
<div class="d-flex align-items-center gap-3 mb-4">
    <a href="{{ route('announcements.index') }}" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left me-1"></i> Kembali
    </a>
    <div class="flex-grow-1">
        <h4 class="mb-0 fw-bold">{{ $announcement->title }}</h4>
        <p class="text-muted small mb-0">
            Oleh {{ $announcement->author->name ?? '-' }}
            &bull; {{ $announcement->published_at ? $announcement->published_at->format('d M Y H:i') : 'Draft' }}
        </p>
    </div>
    <a href="{{ route('announcements.edit', $announcement) }}" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-pencil me-1"></i> Edit
    </a>
    <form method="POST" action="{{ route('announcements.destroy', $announcement) }}" onsubmit="return confirm('Hapus pengumuman ini?')">
        @csrf @method('DELETE')
        <button class="btn btn-outline-danger btn-sm"><i class="bi bi-trash me-1"></i> Hapus</button>
    </form>
</div>

<div class="card" style="max-width:800px">
    <div class="card-body">
        @if($announcement->is_pinned)
            <div class="alert alert-warning d-flex align-items-center gap-2 py-2 mb-3">
                <i class="bi bi-pin-fill"></i> Pengumuman ini disematkan
            </div>
        @endif

        <dl class="row mb-3">
            <dt class="col-sm-3 text-muted">Perusahaan</dt>
            <dd class="col-sm-9">{{ $announcement->company->name ?? 'Semua Perusahaan' }}</dd>

            <dt class="col-sm-3 text-muted">Status</dt>
            <dd class="col-sm-9">
                @if(!$announcement->isPublished())
                    <span class="badge badge-pending badge-pill">Draft</span>
                @elseif($announcement->isExpired())
                    <span class="badge badge-rejected badge-pill">Kedaluwarsa</span>
                @else
                    <span class="badge badge-approved badge-pill">Aktif</span>
                @endif
            </dd>

            @if($announcement->expires_at)
                <dt class="col-sm-3 text-muted">Kedaluwarsa</dt>
                <dd class="col-sm-9">{{ $announcement->expires_at->format('d M Y H:i') }}</dd>
            @endif
        </dl>

        <hr>
        <div style="white-space:pre-wrap">{{ $announcement->content }}</div>
    </div>
</div>
@endsection
