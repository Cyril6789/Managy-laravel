<?php

namespace App\Support;

use App\Models\Society;

class TenantUrl
{
    public static function central(string $path = '/'): string
    {
        return static::build(config('saas.domain'), $path);
    }

    public static function forSociety(Society|string $society, string $path = '/'): string
    {
        $slug = $society instanceof Society ? $society->slug : $society;

        return static::build($slug.'.'.config('saas.domain'), $path);
    }

    private static function build(?string $host, string $path): string
    {
        $host = trim((string) $host);

        if ($host === '') {
            return url($path);
        }

        $scheme = config('saas.scheme') ?: (request()->isSecure() ? 'https' : 'http');
        $path = '/'.ltrim($path, '/');

        return $scheme.'://'.$host.($path === '/' ? '' : $path);
    }
}
