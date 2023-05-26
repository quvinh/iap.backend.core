<?php

namespace App\Helpers;

use App\Helpers\Enums\UserRoles;
use App\Models\User;
use Illuminate\Contracts\Auth\Authenticatable;
use PHPOpenSourceSaver\JWTAuth\Contracts\JWTSubject;

/**
 * @property string $name
 * @property string $username
 * @property string $email
 * @property string $password
 * @property string $role_id
 * @property int $id
 */
class AuthorizationSubject implements Authenticatable, JWTSubject
{
    private array $claims;
    private  User | null $user;
    public function __construct(array $credentials = [])
    {
        $this->id = $credentials['id'] ?? 0;
        $this->name = '';
        // $this->email = $credentials['email']?? '';
        $this->username = $credentials['username'] ?? '';
        $this->password = $credentials['password'] ?? '';

        $this->user = null;
    }

    public function representFor(User $user)
    {
        $this->id = $user->getKey();
        $this->name = $user->name;
        $this->username = $user->username;
        $this->email = $user->email;
        $this->role_id = $user->role_id ?? null;
        $this->password = $user->getAuthPassword();
        $this->user = $user;
    }

    /**
     * @param string $type
     * @param string $id
     * @param string $idHash
     * @param string $signature
     */
    public function claims(string $type, string $id, string $idHash, string $signature)
    {
        $this->claims = [
            'type' => $type, // handhshake type
            'cnid' => $id, // connection id
            'cnidh' => $idHash, // connection id hash
            'sign' => $signature, // signature
        ];
    }

    public static function parse(array $values)
    {
        $sub = new AuthorizationSubject();
        $claims = [];
        foreach ($values as $key => $value) {
            switch ($key) {
                case 'cnid':
                    $claims['cnid'] = $value;
                    break;
                case 'cnidh':
                    $claims['cnidh'] = $value;
                    break;
                case 'type':
                    $claims['type'] = $value;
                    break;
                case 'sign':
                    $claims['sign'] = $value;
                    break;
                default:
                    break;
            }
        }
        $sub->claims = $claims;
        return $sub;
    }

    public static function getAnonymousUser(): AuthorizationSubject
    {
        return new AuthorizationSubject([]);
    }

    public function getJWTIdentifier()
    {
        return $this->user?->getKey() ?? '0';
    }

    public function getJWTCustomClaims()
    {
        return $this->claims ?? [];
    }

    public function isAnonymous()
    {
        return is_null($this->user) || $this->user->group_id == UserRoles::ANONYMOUS;
    }

    public function getUser(): User | null
    {
        if ($this->isAnonymous()) return null;
        return $this->user;
    }

    public function getAuthIdentifierName()
    {
        if ($this->isAnonymous()) return UserRoles::ANONYMOUS;
    }

    public function getAuthIdentifier()
    {
        if ($this->isAnonymous()) return 0;
        return $this->user->getAuthIdentifier();
    }

    public function getAuthPassword()
    {
        if ($this->isAnonymous()) return 0;
        return $this->user->getAuthPassword();
    }

    public function getRememberToken()
    {
        if ($this->isAnonymous()) return 0;
        return $this->user->getRememberToken();
    }

    public function setRememberToken($value)
    {
        if (!$this->isAnonymous()) {
            $this->user->setRememberToken($value);
        }
    }

    public function getRememberTokenName()
    {
        if ($this->isAnonymous()) return null;
        return $this->user->getRememberToken();
    }
}
