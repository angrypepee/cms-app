@extends('layouts.app')

@section('page-title', 'Notifikasi')

@section('content')
<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h4 class="mb-0 fw-bold" style="color:#1e293b">Notifikasi</h4>
        @php $unreadTotal = auth()->user()->unreadNotifications()->count(); @endphp
        @if($unreadTotal > 0)
        <span class="badge rounded-pill" style="background:#dbeafe;color:#1d4ed8;font-size:.72rem;margin-top:.25rem">{{ $unreadTotal }} belum dibaca</span>
        @else
        <span style="font-size:.78rem;color:#94a3b8">Semua sudah dibaca</span>
        @endif
    </div>
    <div class="d-flex gap-2">
        @if(auth()->user()->unreadNotifications->count() > 0)
        <form method="POST" action="{{ route('notifications.read-all') }}">
            @csrf @method('PATCH')
            <button class="btn btn-outline-primary btn-sm">
                <i class="bi bi-check2-all me-1"></i> Tandai Semua Dibaca
            </button>
        </form>
        @endif
        @if($notifications->total() > 0)
        <form method="POST" action="{{ route('notifications.delete-all') }}"
              onsubmit="return confirm('Hapus semua notifikasi?')">
            @csrf @method('DELETE')
            <button class="btn btn-outline-danger btn-sm">
                <i class="bi bi-trash me-1"></i> Hapus Semua
            </button>
        </form>
        @endif
    </div>
</div>

@if($notifications->isEmpty())
<div class="card">
    <div class="card-body text-center py-5">
        <i class="bi bi-bell-slash d-block mb-3" style="font-size:2.5rem;color:#cbd5e1"></i>
        <p class="mb-0" style="color:#94a3b8">Belum ada notifikasi</p>
    </div>
</div>
@else
<div class="card p-0 overflow-hidden">
    <ul class="list-group list-group-flush">
        @foreach($notifications as $n)
        @php
            $isUnread = is_null($n->read_at);
            $nType   = $n->data['type'] ?? '';
            $nStatus = $n->data['status'] ?? '';
            $iconCls = match($nType) {
                'leave_request_new'          => 'bi-calendar-plus',
                'leave_status_changed'       => $nStatus === 'approved' ? 'bi-calendar-check' : 'bi-calendar-x',
                'internal_request_new'       => 'bi-inbox',
                'internal_request_responded' => 'bi-reply-fill',
                default                      => 'bi-bell',
            };
            $iconBg = match($nType) {
                'leave_request_new'          => '#2563eb',
                'leave_status_changed'       => $nStatus === 'approved' ? '#16a34a' : '#dc2626',
                'internal_request_new'       => '#d97706',
                'internal_request_responded' => '#0891b2',
                default                      => '#64748b',
            };
        @endphp
        <li class="list-group-item d-flex gap-3 align-items-start py-3 px-4"
            style="background:{{ $isUnread ? '#eff6ff' : '#fff' }};border-left:4px solid {{ $isUnread ? '#2563eb' : 'transparent' }}">
            {{-- Icon circle --}}
            <div class="flex-shrink-0" style="width:42px;height:42px;border-radius:50%;background:{{ $iconBg }};display:flex;align-items:center;justify-content:center;margin-top:2px">
                <i class="bi {{ $iconCls }}" style="font-size:1.1rem;color:#fff"></i>
            </div>
            {{-- Content --}}
            <div class="flex-grow-1">
                <div class="d-flex justify-content-between align-items-start">
                    <span style="font-size:.875rem;font-weight:{{ $isUnread ? '600' : '400' }};color:#0f172a">{{ $n->data['title'] ?? 'Notifikasi' }}</span>
                    <span style="font-size:.72rem;color:#94a3b8;white-space:nowrap;margin-left:1rem">{{ $n->created_at->diffForHumans() }}</span>
                </div>
                <p class="mb-2" style="font-size:.83rem;color:#475569;margin-top:.2rem">{{ $n->data['message'] ?? '' }}</p>
                <div class="d-flex gap-2 flex-wrap">
                    @if(isset($n->data['url']))
                    <a href="{{ route('notifications.read', $n->id) }}" class="btn btn-primary btn-sm" style="font-size:.75rem;padding:.25rem .65rem;border-radius:.4rem">
                        <i class="bi bi-arrow-right me-1"></i>Lihat Detail
                    </a>
                    @endif
                    @if($isUnread)
                    <form method="POST" action="{{ route('notifications.read', $n->id) }}" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-outline-secondary btn-sm" style="font-size:.75rem;padding:.25rem .65rem;border-radius:.4rem">
                            <i class="bi bi-check2 me-1"></i>Tandai Dibaca
                        </button>
                    </form>
                    @endif
                    <form method="POST" action="{{ route('notifications.destroy', $n->id) }}"
                          class="d-inline" onsubmit="return confirm('Hapus notifikasi ini?')">
                        @csrf @method('DELETE')
                        <button type="submit" class="btn btn-outline-danger btn-sm" style="font-size:.75rem;padding:.25rem .65rem;border-radius:.4rem">
                            <i class="bi bi-trash me-1"></i>Hapus
                        </button>
                    </form>
                </div>
            </div>
            {{-- Unread dot --}}
            @if($isUnread)
            <div class="flex-shrink-0" style="width:10px;height:10px;border-radius:50%;background:#2563eb;margin-top:.4rem"></div>
            @endif
        </li>
        @endforeach
    </ul>
</div>
<div class="mt-3">{{ $notifications->links() }}</div>
@endif
@endsection
