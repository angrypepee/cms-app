<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProjectFile extends Model
{
    protected $fillable = [
        'project_id', 'uploaded_by', 'label',
        'file_path', 'original_name', 'mime_type', 'file_size',
    ];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

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
}
