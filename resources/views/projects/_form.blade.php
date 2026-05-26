@php
    /** @var \App\Models\Project|null $project */
    $project = $project ?? null;
    $isEdit  = !is_null($project) && $project->exists;
@endphp
<div class="row g-3">
    <div class="col-md-6">
        <label class="form-label small fw-semibold">Klien <span class="text-danger">*</span></label>
        <select name="client_id" class="form-select" required>
            <option value="">— Pilih Klien —</option>
            @foreach($clients as $cl)
                <option value="{{ $cl->id }}" @selected(old('client_id', $project->client_id ?? '') == $cl->id)>{{ $cl->name }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-6">
        <label class="form-label small fw-semibold">Perusahaan Penerbit</label>
        <select name="company_id" class="form-select">
            <option value="">— Pilih —</option>
            @foreach($companies as $co)
                <option value="{{ $co->id }}" @selected(old('company_id', $project->company_id ?? '') == $co->id)>{{ $co->name }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-9">
        <label class="form-label small fw-semibold">Nama Project <span class="text-danger">*</span></label>
        <input type="text" name="name" class="form-control" required maxlength="200" value="{{ old('name', $project->name ?? '') }}">
    </div>
    <div class="col-md-3">
        <label class="form-label small fw-semibold">Status</label>
        <select name="status" class="form-select">
            @foreach(['planning'=>'Planning','active'=>'Aktif','on_hold'=>'On Hold','completed'=>'Selesai','cancelled'=>'Dibatalkan'] as $k=>$v)
                <option value="{{ $k }}" @selected(old('status', $project->status ?? 'planning')===$k)>{{ $v }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-4">
        <label class="form-label small fw-semibold">Tanggal Mulai</label>
        <input type="date" name="start_date" class="form-control" value="{{ old('start_date', optional($project?->start_date)->toDateString()) }}">
    </div>
    <div class="col-md-4">
        <label class="form-label small fw-semibold">Tanggal Selesai</label>
        <input type="date" name="end_date" class="form-control" value="{{ old('end_date', optional($project?->end_date)->toDateString()) }}">
    </div>
    <div class="col-md-4">
        <label class="form-label small fw-semibold">Budget (Rp)</label>
        <input type="number" step="0.01" min="0" name="budget" class="form-control" value="{{ old('budget', $project->budget ?? 0) }}">
    </div>
    <div class="col-12">
        <label class="form-label small fw-semibold">Deskripsi</label>
        <textarea name="description" class="form-control" rows="3" maxlength="2000">{{ old('description', $project->description ?? '') }}</textarea>
    </div>
    <div class="col-12">
        <label class="form-label small fw-semibold">Catatan Internal</label>
        <textarea name="notes" class="form-control" rows="2" maxlength="2000">{{ old('notes', $project->notes ?? '') }}</textarea>
    </div>
</div>
