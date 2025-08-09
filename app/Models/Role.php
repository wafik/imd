<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Support\Facades\Cache;
use Spatie\Permission\Models\Role as SpatieRole;

class Role extends SpatieRole
{
    use HasUlids;
    protected $table = 'roles';
    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = true;
    /**
     * Specify the amount of time to cache queries.
     * Do not specify or set it to null to disable caching.
     *
     * @var int|\DateTime
     */
    public $cacheFor = 3600;

    /**
     * The tags for the query cache. Can be useful
     * if flushing cache for specific tags only.
     *
     * @var null|array
     */
    public $cacheTags = ['roles'];
    // delete cache when updated and deleted
    protected static function boot()
    {
        parent::boot();

        static::saved(function () {
            Cache::forget('roles');
        });

        static::deleted(function () {
            Cache::forget('roles');
        });
        static::created(function () {
            Cache::forget('roles');
        });
        static::updated(function () {
            Cache::forget('roles');
        });
    }
}
