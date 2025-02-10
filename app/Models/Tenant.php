<?php

namespace App\Models;

use App\Models\Accounting\Contract\Contract;
use App\Traits\Encryptable;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Class Tenant
 *
 * This class is the model for basic tenant metadata.
 *
 * @author Marcel Menk <marcel.menk@ipvx.io>
 *
 * @property int $id
 * @property int $user_id
 * @property int $contract_id
 * @property string $domain
 * @property string $database_driver
 * @property string $database_url
 * @property string $database_host
 * @property string $database_port
 * @property string $database_database
 * @property string $database_username
 * @property string $database_password
 * @property string $database_unix_socket
 * @property string $database_charset
 * @property string $database_collation
 * @property string $database_prefix
 * @property bool $database_prefix_indexes
 * @property bool $database_strict
 * @property string $database_engine
 * @property string $redis_prefix
 * @property Carbon $created_at
 * @property Carbon $updated_at
 *
 * @property User|null $user
 * @property Contract|null $contract
 * @property float $size
 * @property Collection $processes
 */
class Tenant extends Model
{
    use Encryptable;

    /**
     * The connection name for the model.
     *
     * @var string|null
     */
    protected $connection = 'base';

    /**
     * The attributes that aren't mass assignable.
     *
     * @var string[]|bool
     */
    protected $guarded = [
        'id',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'database_prefix_indexes' => 'boolean',
        'database_strict' => 'boolean',
    ];

    /**
     * The attributes that are encryptable.
     *
     * @var string[]|bool
     */
    protected $encryptable = [
        'database_driver',
        'database_url',
        'database_host',
        'database_port',
        'database_database',
        'database_username',
        'database_password',
        'database_unix_socket',
        'redis_prefix',
    ];

    /**
     * Relation to user.
     *
     * @return HasOne
     */
    public function user(): HasOne
    {
        return $this->hasOne(User::class, 'id', 'user_id');
    }

    /**
     * Relation to contract.
     *
     * @return HasOne
     */
    public function contract(): HasOne
    {
        return $this->hasOne(Contract::class, 'id', 'contract_id');
    }

    /**
     * Get tenant size.
     *
     * @return float
     */
    public function getSizeAttribute(): float
    {
        return ! empty(
            $size = DB::table('information_schema.tables')
                ->selectRaw('ROUND(SUM(data_length + index_length) / 1024 / 1024, 1) AS db_size')
                ->where('table_schema', '=', $this->database_database)
                ->first()
        ) ? (float) $size->db_size : 0;
    }

    /**
     * Get tenant size.
     *
     * @return Collection
     */
    public function getProcessesAttribute(): Collection
    {
        return DB::table('information_schema.processlist')
            ->where('DB', '=', $this->database_database)
            ->get();
    }
}
