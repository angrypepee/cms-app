<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProjectLink extends Model
{
    protected $fillable = ['project_id', 'label', 'url', 'type'];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public static function typeOptions(): array
    {
        return [
            'github'     => ['GitHub',       'bi-github',         '#24292f'],
            'gitlab'     => ['GitLab',        'bi-gitlab',         '#fc6d26'],
            'figma'      => ['Figma',         'bi-vector-pen',     '#a259ff'],
            'notion'     => ['Notion',        'bi-journal-text',   '#1e1e1e'],
            'trello'     => ['Trello',        'bi-kanban',         '#0052cc'],
            'staging'    => ['Staging',       'bi-server',         '#0891b2'],
            'production' => ['Production',    'bi-globe2',         '#16a34a'],
            'docs'       => ['Dokumentasi',   'bi-file-earmark-text', '#7c3aed'],
            'other'      => ['Tautan Lainnya','bi-link-45deg',     '#64748b'],
        ];
    }

    public function typeLabel(): string
    {
        return self::typeOptions()[$this->type][0] ?? $this->type;
    }

    public function typeIcon(): string
    {
        return self::typeOptions()[$this->type][1] ?? 'bi-link-45deg';
    }

    public function typeColor(): string
    {
        return self::typeOptions()[$this->type][2] ?? '#64748b';
    }
}
