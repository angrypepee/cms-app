<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmployeeDocument extends Model
{
    protected $fillable = [
        'employee_id', 'uploaded_by', 'document_type',
        'label', 'file_path', 'original_name', 'mime_type', 'file_size', 'url',
    ];

    // ── Relationships ────────────────────────────────────────
    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    // ── Helpers ──────────────────────────────────────────────
    public function isImage(): bool
    {
        return str_starts_with($this->mime_type ?? '', 'image/');
    }

    public function isPdf(): bool
    {
        return $this->mime_type === 'application/pdf';
    }

    public function isViewable(): bool
    {
        return $this->isImage() || $this->isPdf();
    }

    public function fileSizeFormatted(): string
    {
        $bytes = $this->file_size ?? 0;
        if ($bytes >= 1_048_576) return round($bytes / 1_048_576, 1) . ' MB';
        if ($bytes >= 1_024)    return round($bytes / 1_024, 0)     . ' KB';
        return $bytes . ' B';
    }

    public static function typeOptions(): array
    {
        return [
            'ktp'          => 'KTP / Identitas',
            'npwp'         => 'NPWP',
            'ijazah'       => 'Ijazah / Sertifikat Pendidikan',
            'rekening'     => 'Rekening / Buku Tabungan',
            'syarat'       => 'Syarat Administrasi',
            'sertifikasi'  => 'Sertifikasi Profesi',
            'bpjs'         => 'Kartu BPJS',
            'lainnya'      => 'Lainnya',
        ];
    }

    public static function contractTypeOptions(): array
    {
        return [
            'ktp'      => 'KTP / Identitas',
            'npwp'     => 'NPWP',
            'ijazah'   => 'Ijazah / Sertifikat Pendidikan',
            'rekening' => 'Rekening / Buku Tabungan',
            'syarat'   => 'Syarat Administrasi',
        ];
    }

    public static function allTypeLabels(): array
    {
        return [
            'kontrak'      => 'Surat Kontrak Kerja',
            'ktp'          => 'KTP / Identitas',
            'npwp'         => 'NPWP',
            'ijazah'       => 'Ijazah / Sertifikat Pendidikan',
            'rekening'     => 'Rekening / Buku Tabungan',
            'syarat'       => 'Syarat Administrasi',
            'sertifikasi'  => 'Sertifikasi Profesi',
            'bpjs'         => 'Kartu BPJS',
            'lainnya'      => 'Lainnya',
        ];
    }

    public function typeLabel(): string
    {
        return self::allTypeLabels()[$this->document_type] ?? $this->document_type;
    }

    public function typeIcon(): string
    {
        return match($this->document_type) {
            'ktp'         => 'bi-person-vcard',
            'kontrak'     => 'bi-file-earmark-text',
            'npwp'        => 'bi-receipt',
            'ijazah'      => 'bi-mortarboard',
            'rekening'    => 'bi-bank',
            'syarat'      => 'bi-check2-square',
            'sertifikasi' => 'bi-award',
            'bpjs'        => 'bi-heart-pulse',
            default       => 'bi-file-earmark',
        };
    }
}
