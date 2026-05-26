@extends('layouts.app')

@section('page-title', 'Pengumuman')

@section('content')
<div class="mb-4">
    <h4 class="mb-0 fw-bold">Pengumuman</h4>
    <p class="text-muted small mb-0">Informasi terbaru dari perusahaan Anda</p>
</div>

@forelse($announcements as $a)
    <div class="card mb-3 {{ $a->is_pinned ? 'border-warning' : '' }}">
        <div class="card-body">
            <div class="d-flex align-items-start gap-2 mb-2">
                @if($a->is_pinned)
                    <i class="bi bi-pin-fill text-warning mt-1" title="Disematkan"></i>
                @endif
                <div class="flex-grow-1">
                    <h5 class="mb-1 fw-semibold">{{ $a->title }}</h5>
                    <p class="text-muted small mb-0">
                        Oleh {{ $a->author->name ?? 'Admin' }}
                        &bull; {{ $a->published_at->format('d M Y H:i') }}
                        @if($a->company)
                            &bull; {{ $a->company->name }}
                        @endif
                    </p>
                </div>
            </div>
            <div class="mt-2" style="white-space:pre-wrap">{{ $a->content }}</div>
        </div>
    </div>
@empty
    <div class="card">
        <div class="card-body text-center text-muted py-5">
            <i class="bi bi-megaphone fs-2 d-block mb-2"></i>
            Belum ada pengumuman
        </div>
    </div>
@endforelse

@if($announcements->hasPages())
    <div class="mt-3">{{ $announcements->links() }}</div>
@endif
@endsection
