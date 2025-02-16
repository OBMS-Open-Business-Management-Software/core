<?php

namespace App\Webdav;

use App\Models\FileManager\Lock;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Sabre\DAV\Locks\Backend\AbstractBackend;
use Sabre\DAV\Locks\LockInfo;

/**
 * Class Locks.
 *
 * @package App\Webdav
 */
class Locks extends AbstractBackend
{
    /**
     * Get file locks.
     *
     * @param string $uri
     * @param bool $returnChildLocks
     *
     * @return array
     */
    public function getLocks($uri, $returnChildLocks)
    {
        return Lock::where('created', '>', DB::raw(time() . ' - timeout'))
            ->where(function (Builder $builder) use ($uri, $returnChildLocks) {
                $condition = $builder->where('uri', '=', $uri);

                $currentPath = '';

                collect(collect(explode('/', $uri))->last())->each(function ($part) use ($uri, &$condition, &$currentPath) {
                    if ($currentPath) {
                        $currentPath .= '/';
                    }

                    $currentPath .= $part;

                    $condition = $condition->orWhere(function (Builder $builder) use ($currentPath) {
                        return $builder->where('depth', '!=', 0)
                            ->where('uri', '=', $currentPath);
                    });
                });

                if ($returnChildLocks) {
                    $condition = $condition->orWhere('uri', 'LIKE', $uri . '/%');
                }

                return $condition;
            })
            ->transform(function (Lock $lock) {
                $lockInfo = new LockInfo();
                $lockInfo->owner = $lock->owner;
                $lockInfo->token = $lock->token;
                $lockInfo->timeout = $lock->timeout;
                $lockInfo->created = $lock->created;
                $lockInfo->scope = $lock->scope;
                $lockInfo->depth = $lock->depth;
                $lockInfo->uri = $lock->uri;

                return $lockInfo;
            })
            ->toArray();
    }

    /**
     * Lock file.
     *
     * @param string $uri
     * @param LockInfo $lockInfo
     *
     * @return bool
     */
    public function lock($uri, LockInfo $lockInfo): bool
    {
        $lockInfo->timeout = 30 * 60;
        $lockInfo->created = time();
        $lockInfo->uri = $uri;

        if (
            ! empty(
                $lock = collect($this->getLocks($uri, false))
                    ->where('token', '=', $lockInfo->token)
                    ->first()
            )
        ) {
            return $lock->update([
                "owner" => $lockInfo->owner,
                "timeout" => $lockInfo->timeout,
                "scope" => $lockInfo->scope,
                "depth" => $lockInfo->depth,
                "uri" => $uri,
                "created" => $lockInfo->created,
            ]) > 0;
        } else {
            return Lock::create([
                "owner" => $lockInfo->owner,
                "timeout" => $lockInfo->timeout,
                "scope" => $lockInfo->scope,
                "depth" => $lockInfo->depth,
                "uri" => $uri,
                "created" => $lockInfo->created,
                "token" => $lockInfo->token
            ]) instanceof Lock;
        }
    }

    /**
     * Unlock file.
     *
     * @param string $uri
     * @param LockInfo $lockInfo
     *
     * @return bool
     */
    public function unlock($uri, LockInfo $lockInfo): bool
    {
        return Lock::where([
            "uri" => $uri,
            "token" => $lockInfo->token,
        ])->delete();
    }
}
