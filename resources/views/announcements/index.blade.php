@extends('layouts.app')

@section('page-title', 'Pengumuman')

@section('content')
<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h4 class="mb-0 fw-bold">Pengumuman</h4>
        <p class="text-muted small mb-0">Kelola pengumuman untuk karyawan</p>
    </div>
    <a href="{{ route('announcements.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-lg me-1"></i> Buat Pengumuman
    </a>
</div>

<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Judul</th>
                        <th>Perusahaan</th>
                        <th>Dibuat oleh</th>
                        <th>Dipublikasi</th>
                        <th>Kedaluwarsa</th>
                        <th>Pin</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($announcements as $a)
                        <tr class="{{ !$a->isPublished() ? 'table-warning' : '' }}">
                            <td>
                                <div class="fw-semibold">{{ $a->title }}</div>
                                @if(!$a->isPublished())
                                    <span class="badge badge-pending badge-pill">Draft</span>
                                @elseif($a->isExpired())
                                    <span class="badge badge-rejected badge-pill">Kedaluwarsa</span>
                                @else
                                    <span class="badge badge-approved badge-pill">Aktif</span>
                                @endif
                            </td>
                            <td class="small">{{ $a->company->name ?? 'Semua Perusahaan' }}</td>
                            <td class="small">{{ $a->author->name ?? '-' }}</td>
                            <td class="small">{{ $a->published_at ? $a->published_at->format('d M Y H:i') : '-' }}</td>
                            <td class="small">{{ $a->expires_at ? $a->expires_at->format('d M Y') : 'Tidak' }}</td>
                            <td>
                                @if($a->is_pinned)
                                    <i class="bi bi-pin-fill text-warning" title="Disematkan"></i>
                                @endif
                            </td>
                            <td>
                                <div class="d-flex gap-1">
                                    <a href="{{ route('announcements.show', $a) }}" class="btn btn-sm btn-outline-primary" title="Lihat">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <a href="{{ route('announcements.edit', $a) }}" class="btn btn-sm btn-outline-secondary" title="Edit">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <form method="POST" action="{{ route('announcements.destroy', $a) }}" onsubmit="return confirm('Hapus pengumuman ini?')">
                                        @csrf @method('DELETE')
                                        <button class="btn btn-sm btn-outline-danger" title="Hapus"><i class="bi bi-trash"></i></button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-5">
                                <i class="bi bi-megaphone fs-2 d-block mb-2"></i>
                                Belum ada pengumuman
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($announcements->hasPages())
        <div class="card-footer">{{ $announcements->links() }}</div>
    @endif
</div>
@endsection
