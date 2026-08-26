<?php

namespace Modules\Core\Support;

class Permissions
{
    /**
     * The granular actions every feature gets a permission for.
     *
     * @return string[]
     */
    public static function actions(): array
    {
        return ['view', 'write', 'delete'];
    }

    /**
     * The "{feature}.{action}" permission names for a single feature.
     *
     * @return string[]
     */
    public static function for(string $feature): array
    {
        return array_map(fn (string $action) => "{$feature}.{$action}", static::actions());
    }

    /**
     * Every "{feature}.{action}" permission name across all features.
     *
     * @return string[]
     */
    public static function all(): array
    {
        return array_merge(...array_map(
            fn (string $feature) => static::for($feature),
            Features::keys()
        ));
    }
}
