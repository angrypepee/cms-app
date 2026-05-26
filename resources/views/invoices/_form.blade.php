@php
    /** @var \App\Models\Invoice|null $document */
    $document = $document ?? null;
    $sc = $selectedClient ?? null;
    $sp = $selectedProject ?? null;
@endphp
<div class="card mb-3"><div class="card-body">
    <div class="row g-3">
        <div class="col-md-4">
            <label class="form-label small fw-semibold">Klien <span class="text-danger">*</span></label>
            <select name="client_id" class="form-select" required>
                <option value="">— Pilih —</option>
                @foreach($clients as $cl)
                    <option value="{{ $cl->id }}" @selected(old('client_id', $document->client_id ?? $sc) == $cl->id)>{{ $cl->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-4">
            <label class="form-label small fw-semibold">Project (opsional)</label>
            <select name="project_id" class="form-select">
                <option value="">— Tanpa Project —</option>
                @foreach($projects as $p)
                    <option value="{{ $p->id }}" @selected(old('project_id', $document->project_id ?? $sp) == $p->id)>{{ $p->code }} — {{ $p->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-4">
            <label class="form-label small fw-semibold">Perusahaan Penerbit</label>
            <select name="company_id" class="form-select">
                <option value="">— Pilih —</option>
                @foreach($companies as $co)
                    <option value="{{ $co->id }}" @selected(old('company_id', $document->company_id ?? '') == $co->id)>{{ $co->name }}</option>
                @endforeach
            </select>
        </div>
        @if(isset($banks))
        <div class="col-md-4">
            <label class="form-label small fw-semibold">Rekening Penerima Pembayaran</label>
            <select name="bank_account_id" class="form-select">
                <option value="">— Pilih (opsional) —</option>
                @foreach($banks as $b)
                    @php $sel = old('bank_account_id', $document->bank_account_id ?? ($b->is_default ? $b->id : null)) == $b->id; @endphp
                    <option value="{{ $b->id }}" @selected($sel)>{{ $b->bank_name }} — {{ $b->account_number }} (a.n. {{ $b->account_name }})</option>
                @endforeach
            </select>
            <div class="form-text">Ditampilkan pada PDF dan link share untuk klien.</div>
        </div>
        @endif
        <div class="col-md-3">
            <label class="form-label small fw-semibold">Tanggal Terbit <span class="text-danger">*</span></label>
            <input type="date" name="issue_date" class="form-control" required value="{{ old('issue_date', optional($document?->issue_date)->toDateString() ?? now()->toDateString()) }}">
        </div>
        <div class="col-md-3">
            <label class="form-label small fw-semibold">Jatuh Tempo</label>
            <input type="date" name="due_date" class="form-control" value="{{ old('due_date', optional($document?->due_date)->toDateString() ?? now()->addDays(30)->toDateString()) }}">
        </div>
        <div class="col-md-6">
            <label class="form-label small fw-semibold">Subjek</label>
            <input type="text" name="subject" class="form-control" maxlength="200" value="{{ old('subject', $document->subject ?? '') }}">
        </div>
    </div>
</div></div>

@php $items = $document?->items ?? collect(); @endphp
@include('b2b._items_form', ['items' => $items, 'document' => $document])

<div class="card mb-3"><div class="card-body">
    <div class="row g-3">
        <div class="col-md-4">
            <label class="form-label small fw-semibold">Status</label>
            <select name="status" class="form-select">
                @foreach(['draft'=>'Draft','sent'=>'Terkirim','cancelled'=>'Dibatalkan'] as $k=>$v)
                    <option value="{{ $k }}" @selected(old('status', $document->status ?? 'draft')===$k)>{{ $v }}</option>
                @endforeach
            </select>
            <div class="form-text small">Status pembayaran (partial/paid/overdue) diatur otomatis oleh sistem.</div>
        </div>
        <div class="col-md-8">
            <label class="form-label small fw-semibold">Catatan</label>
            <textarea name="notes" class="form-control" rows="2" maxlength="2000">{{ old('notes', $document->notes ?? '') }}</textarea>
        </div>
        <div class="col-12">
            <label class="form-label small fw-semibold">Syarat Pembayaran</label>
            <textarea name="terms" class="form-control" rows="3" maxlength="2000">{{ old('terms', $document->terms ?? "Pembayaran ditransfer ke rekening yang tertera pada invoice.\nMohon kirim bukti transfer setelah pembayaran dilakukan.") }}</textarea>
        </div>
    </div>
</div></div>
