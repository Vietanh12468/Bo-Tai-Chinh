<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Laravel\Sanctum\PersonalAccessToken;
use Illuminate\Support\Str;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'slug',
        'email',
        'password',
        'phone',
        'image_id',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function persionAccessToken()
    {
        return $this->hasMany(PersonalAccessToken::class, 'user_id', 'id');
    }

    public static function generateUniqueToken($length = 40)
    {
        do {
            $token = Str::random($length);
        } while (PersonalAccessToken::where('token', $token)->exists());

        return $token;
    }

    public function permissions()
    {
        return $this->belongsToMany(Permission::class, 'user_permissions', 'user_id', 'permission_id')
            ->select('permissions.id', 'permissions.name', 'permissions.slug', 'user_permissions.start_at', 'user_permissions.expires_at');
    }

    public function userPermissions()
    {
        return $this->hasMany(UserPermission::class, 'user_id', 'id')->with('permission');
    }

    public function getUserPermissions()
    {
        //get all user permissions that are not expired and are active
        $userPermissions = $this->userPermissions()->where(function ($query) {
            $query->whereNull('expires_at')->orWhere('expires_at', '>', now());
        })->where(function ($query) {
            $query->whereNull('start_at')->orWhere('start_at', '<=', now());
        })->get();

        // merge all routes from all permissions of the user
        $allRoutes = [];
        foreach ($userPermissions as $userPermission) {
            if ($userPermission->permission->routes === ['*']) {
                return ['*'];
            }
            if (!is_array($userPermission->permission->routes)) {
                //convert to array
                $routes = json_decode($userPermission->permission->routes, true);
                $allRoutes = array_merge($allRoutes, $routes);
            } else {
                $allRoutes = array_merge($allRoutes, $userPermission->permission->routes);
            }
        }

        return $allRoutes;
    }

    public function checkPermission($routeName)
    {
        $allPermissionRoutes = $this->getUserPermissions();

        return in_array('*', $allPermissionRoutes) || in_array($routeName, $allPermissionRoutes);
    }

    // generate password
    public static function generateStrongPassword($length = 8)
    {
        $characters = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*()-_[]{}<>~`+=,.;:/|';
        $charactersLength = strlen($characters);
        $password = '';
        $containsUpper = $containsLower = $containsDigit = $containsSpecial = false;

        while (!$containsUpper || !$containsLower || !$containsDigit || !$containsSpecial || strlen($password) < $length) {
            $char = $characters[random_int(0, $charactersLength - 1)];
            $password .= $char;

            if (ctype_upper($char)) {
                $containsUpper = true;
            } elseif (ctype_lower($char)) {
                $containsLower = true;
            } elseif (ctype_digit($char)) {
                $containsDigit = true;
            } elseif (preg_match('/[!@#\$%\^&\*\(\)\-\_\[\]\{\}<>~`\+=,\.\/;\:\/\?\|]/', $char)) {
                $containsSpecial = true;
            }
        }

        return $password;
    }

    public function image()
    {
        return $this->hasOne(File::class, 'id', 'image_id');
    }
}
