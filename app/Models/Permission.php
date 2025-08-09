<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Spatie\Permission\Models\Permission as SpatiePermission;

class Permission extends SpatiePermission
{
    use HasUlids;
    protected $table = 'permissions';
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
    public $cacheTags = ['permissions'];
    // delete cache when updated and deleted
    protected static function boot()
    {
        parent::boot();

        static::saved(function () {
            Cache::forget('permissions');
        });

        static::deleted(function () {
            Cache::forget('permissions');
        });
        static::created(function () {
            Cache::forget('permissions');
        });
        static::updated(function () {
            Cache::forget('permissions');
        });
    }

    /**
     * Invalidate the cache automatically
     * upon update in the database.
     *
     * @var bool
     */
    protected static $flushCacheOnUpdate = true;
    /**
     * Set the base cache tags that will be present
     * on all queries.
     *
     * @return array
     */
    protected function getCacheBaseTags(): array
    {
        return [
            'custom_permissions',
        ];
    }
}
