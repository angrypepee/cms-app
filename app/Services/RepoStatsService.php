<?php

namespace App\Services;

use App\Models\AppSetting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

class RepoStatsService
{
    /**
     * Detect repo type and owner/repo from a GitHub or GitLab URL.
     * Returns ['type'=>'github'|'gitlab', 'owner'=>'...', 'repo'=>'...'] or null.
     */
    public static function parseUrl(string $url): ?array
    {
        $url = rtrim($url, '/');

        // GitHub: https://github.com/owner/repo
        if (preg_match('~github\.com/([^/]+)/([^/?#]+)~i', $url, $m)) {
            return ['type' => 'github', 'owner' => $m[1], 'repo' => preg_replace('/\.git$/i', '', $m[2])];
        }

        // GitLab: https://gitlab.com/owner/repo (may have subgroups)
        if (preg_match('~gitlab\.com/(.+)~i', $url, $m)) {
            $parts = explode('/', trim($m[1], '/'));
            if (count($parts) >= 2) {
                $repo  = array_pop($parts);
                $owner = implode('/', $parts);
                return ['type' => 'gitlab', 'owner' => $owner, 'repo' => preg_replace('/\.git$/i', '', $repo)];
            }
        }

        return null;
    }

    /**
     * Fetch contributor commit counts for a repository.
     * Returns array of ['login'=>'...', 'avatar_url'=>'...', 'contributions'=>N]
     * Results are cached for 30 minutes.
     * Returns ['contributors'=>[], 'error'=>null|string, 'parsed'=>[...]]
     */
    public static function contributors(string $url): array
    {
        $info = self::parseUrl($url);
        if (!$info) {
            return ['contributors' => [], 'error' => 'URL tidak dikenali sebagai GitHub/GitLab repo.', 'parsed' => null];
        }

        $cacheKey = 'repo_contributors_' . md5($url);

        return Cache::remember($cacheKey, 1800, function () use ($info) {
            if ($info['type'] === 'github') {
                return self::githubContributors($info['owner'], $info['repo']);
            }
            return self::gitlabContributors($info['owner'], $info['repo']);
        });
    }

    private static function githubContributors(string $owner, string $repo): array
    {
        $token = AppSetting::get('github_token');
        $headers = ['Accept' => 'application/vnd.github+json', 'X-GitHub-Api-Version' => '2022-11-28'];
        if ($token) {
            $headers['Authorization'] = 'Bearer ' . $token;
        }

        try {
            $response = Http::timeout(8)->withHeaders($headers)
                ->get("https://api.github.com/repos/{$owner}/{$repo}/contributors", ['per_page' => 100, 'anon' => 0]);
        } catch (\Throwable $e) {
            return ['contributors' => [], 'error' => 'Gagal menghubungi GitHub: ' . $e->getMessage(), 'parsed' => compact('owner', 'repo')];
        }

        if (!$response->successful()) {
            $body = $response->json();
            $msg = $body['message'] ?? $body['error_description'] ?? ('HTTP ' . $response->status());
            return ['contributors' => [], 'error' => "GitHub API: {$msg}", 'parsed' => compact('owner', 'repo')];
        }

        $data = collect($response->json())->map(fn($c) => [
            'login'         => $c['login'] ?? '?',
            'avatar_url'    => $c['avatar_url'] ?? null,
            'profile_url'   => $c['html_url'] ?? "https://github.com/{$c['login']}",
            'contributions' => $c['contributions'] ?? 0,
            'type'          => 'github',
        ])->sortByDesc('contributions')->values()->toArray();

        return ['contributors' => $data, 'error' => null, 'parsed' => compact('owner', 'repo')];
    }

    private static function gitlabContributors(string $owner, string $repo): array
    {
        $token = AppSetting::get('gitlab_token');
        $projectPath = urlencode("{$owner}/{$repo}");
        $headers = ['Accept' => 'application/json'];
        if ($token) {
            $headers['PRIVATE-TOKEN'] = $token;
        }

        try {
            $response = Http::timeout(8)->withHeaders($headers)
                ->get("https://gitlab.com/api/v4/projects/{$projectPath}/repository/contributors", [
                    'sort' => 'desc', 'per_page' => 100,
                ]);
        } catch (\Throwable $e) {
            return ['contributors' => [], 'error' => 'Gagal menghubungi GitLab: ' . $e->getMessage(), 'parsed' => compact('owner', 'repo')];
        }

        if (!$response->successful()) {
            $body = $response->json();
            $msg = $body['message'] ?? $body['error_description'] ?? ('HTTP ' . $response->status());
            $hint = $response->status() === 403
                ? ' — Pastikan token memiliki scope <code>read_repository</code> atau <code>api</code>, dan pengguna token adalah anggota project.'
                : ($response->status() === 404 ? ' — Project tidak ditemukan atau private tanpa akses.' : '');
            return ['contributors' => [], 'error' => "GitLab API: {$msg}{$hint}", 'parsed' => compact('owner', 'repo')];
        }

        $data = collect($response->json())->map(fn($c) => [
            'login'         => $c['name'] ?? ($c['email'] ?? '?'),
            'avatar_url'    => null,
            'profile_url'   => null,
            'contributions' => $c['commits'] ?? 0,
            'type'          => 'gitlab',
        ])->sortByDesc('contributions')->values()->toArray();

        return ['contributors' => $data, 'error' => null, 'parsed' => compact('owner', 'repo')];
    }

    /**
     * Try to match a contributor login/email to an employee based on github_url / gitlab_url.
     * Returns employee id => contributor data map.
     */
    public static function matchContributorsToEmployees(array $contributors, \Illuminate\Support\Collection $employees): array
    {
        $matched = [];

        foreach ($contributors as $c) {
            foreach ($employees as $emp) {
                $empLogin = null;

                if ($c['type'] === 'github' && $emp->github_url) {
                    // Extract username from github.com/username
                    if (preg_match('~github\.com/([^/?#]+)~i', $emp->github_url, $m)) {
                        $empLogin = strtolower($m[1]);
                    }
                } elseif ($c['type'] === 'gitlab' && $emp->gitlab_url) {
                    if (preg_match('~gitlab\.com/([^/?#]+)~i', $emp->gitlab_url, $m)) {
                        $empLogin = strtolower($m[1]);
                    }
                }

                if ($empLogin && strtolower($c['login']) === $empLogin) {
                    $matched[$emp->id] = $c;
                    break;
                }
            }
        }

        return $matched;
    }

    /**
     * Fetch commits list from a repo, optionally filtered by author name/email.
     * Returns ['commits'=>[], 'error'=>null|string, 'parsed'=>[...]]
     */
    public static function commitsByAuthor(string $url, ?string $authorName = null, ?string $authorEmail = null, int $limit = 50): array
    {
        $info = self::parseUrl($url);
        if (!$info) {
            return ['commits' => [], 'error' => 'URL tidak dikenali.', 'parsed' => null];
        }

        $cacheKey = 'repo_commits_' . md5($url . '|' . $authorName . '|' . $authorEmail . '|' . $limit);

        return Cache::remember($cacheKey, 900, function () use ($info, $authorName, $authorEmail, $limit) {
            if ($info['type'] === 'github') {
                return self::githubCommits($info['owner'], $info['repo'], $authorName ?? $authorEmail, $limit);
            }
            return self::gitlabCommits($info['owner'], $info['repo'], $authorName ?? $authorEmail, $limit);
        });
    }

    /**
     * Fetch all commits grouped by author.
     * Returns ['byAuthor' => ['authorName' => [commits]], 'error'=>null, 'parsed'=>[...]]
     */
    public static function allCommitsByAuthor(string $url, int $limit = 200): array
    {
        $info = self::parseUrl($url);
        if (!$info) {
            return ['byAuthor' => [], 'error' => 'URL tidak dikenali.', 'parsed' => null];
        }

        $cacheKey = 'repo_all_commits_' . md5($url . '|' . $limit);

        return Cache::remember($cacheKey, 900, function () use ($info, $limit) {
            if ($info['type'] === 'github') {
                return self::githubAllCommits($info['owner'], $info['repo'], $limit);
            }
            return self::gitlabAllCommits($info['owner'], $info['repo'], $limit);
        });
    }

    private static function githubCommits(string $owner, string $repo, ?string $author, int $limit): array
    {
        $token = AppSetting::get('github_token');
        $headers = ['Accept' => 'application/vnd.github+json', 'X-GitHub-Api-Version' => '2022-11-28'];
        if ($token) $headers['Authorization'] = 'Bearer ' . $token;

        $params = ['per_page' => min($limit, 100)];
        if ($author) $params['author'] = $author;

        try {
            $response = Http::timeout(10)->withHeaders($headers)
                ->get("https://api.github.com/repos/{$owner}/{$repo}/commits", $params);
        } catch (\Throwable $e) {
            return ['commits' => [], 'error' => $e->getMessage(), 'parsed' => compact('owner', 'repo')];
        }

        if (!$response->successful()) {
            return ['commits' => [], 'error' => 'HTTP ' . $response->status(), 'parsed' => compact('owner', 'repo')];
        }

        $commits = collect($response->json())->map(fn($c) => [
            'sha'      => substr($c['sha'] ?? '', 0, 8),
            'message'  => strtok($c['commit']['message'] ?? '', "\n"),
            'author'   => $c['commit']['author']['name'] ?? ($c['author']['login'] ?? '?'),
            'email'    => $c['commit']['author']['email'] ?? '',
            'date'     => $c['commit']['author']['date'] ?? null,
            'url'      => $c['html_url'] ?? null,
        ])->toArray();

        return ['commits' => $commits, 'error' => null, 'parsed' => compact('owner', 'repo')];
    }

    private static function githubAllCommits(string $owner, string $repo, int $limit): array
    {
        $result = self::githubCommits($owner, $repo, null, $limit);
        if ($result['error']) return ['byAuthor' => [], 'error' => $result['error'], 'parsed' => $result['parsed']];

        $grouped = collect($result['commits'])->groupBy('author')
            ->map(fn($commits) => $commits->sortByDesc('date')->values()->toArray())
            ->toArray();

        return ['byAuthor' => $grouped, 'error' => null, 'parsed' => $result['parsed'], 'total' => count($result['commits'])];
    }

    private static function gitlabCommits(string $owner, string $repo, ?string $author, int $limit): array
    {
        $token = AppSetting::get('gitlab_token');
        $projectPath = urlencode("{$owner}/{$repo}");
        $headers = ['Accept' => 'application/json'];
        if ($token) $headers['PRIVATE-TOKEN'] = $token;

        $params = ['per_page' => min($limit, 100), 'all' => 'true'];
        if ($author) $params['author'] = $author;

        try {
            $response = Http::timeout(10)->withHeaders($headers)
                ->get("https://gitlab.com/api/v4/projects/{$projectPath}/repository/commits", $params);
        } catch (\Throwable $e) {
            return ['commits' => [], 'error' => $e->getMessage(), 'parsed' => compact('owner', 'repo')];
        }

        if (!$response->successful()) {
            $body = $response->json();
            $msg = $body['message'] ?? $body['error_description'] ?? ('HTTP ' . $response->status());
            return ['commits' => [], 'error' => "GitLab: {$msg}", 'parsed' => compact('owner', 'repo')];
        }

        $commits = collect($response->json())->map(fn($c) => [
            'sha'      => substr($c['id'] ?? $c['short_id'] ?? '', 0, 8),
            'message'  => strtok($c['title'] ?? $c['message'] ?? '', "\n"),
            'author'   => $c['author_name'] ?? '?',
            'email'    => $c['author_email'] ?? '',
            'date'     => $c['created_at'] ?? $c['authored_date'] ?? null,
            'url'      => $c['web_url'] ?? null,
        ])->toArray();

        return ['commits' => $commits, 'error' => null, 'parsed' => compact('owner', 'repo')];
    }

    private static function gitlabAllCommits(string $owner, string $repo, int $limit): array
    {
        $result = self::gitlabCommits($owner, $repo, null, $limit);
        if ($result['error']) return ['byAuthor' => [], 'error' => $result['error'], 'parsed' => $result['parsed']];

        $grouped = collect($result['commits'])->groupBy('author')
            ->map(fn($commits) => $commits->sortByDesc('date')->values()->toArray())
            ->toArray();

        return ['byAuthor' => $grouped, 'error' => null, 'parsed' => $result['parsed'], 'total' => count($result['commits'])];
    }
}
