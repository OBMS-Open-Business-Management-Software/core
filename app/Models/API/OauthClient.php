<?php

declare(strict_types=1);

namespace App\Models\API;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class OauthClient
 *
 * This class is the model for basic OAUTH client metadata.
 *
 * @author Marcel Menk <marcel.menk@ipvx.io>
 *
 * @property int    $id
 * @property int    $user_id
 * @property string $name
 * @property string $secret
 * @property string $provider
 * @property string $redirect
 * @property bool   $personal_access_client
 * @property bool   $password_client
 * @property bool   $revoked
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
class OauthClient extends Model
{
    /**
     * The attributes that aren't mass assignable.
     *
     * @var bool|string[]
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
        'personal_access_client' => 'boolean',
        'password_client'        => 'boolean',
        'revoked'                => 'boolean',
    ];
}
