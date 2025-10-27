<?php

namespace App\Repositories;

use App\Models\PersonalAccessToken;
use App\Repositories\Contracts\userRepositoryInterface;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserRepository implements UserRepositoryInterface
{
    public function getBlankModel()
    {
        return User::query()->with('permissions');
    }

    public function __filter(&$query, array $filters = []): \Illuminate\Database\Eloquent\Builder
    {
        if (isset($filters['key_word'])) {
            $keyWord = mb_strtolower($filters['key_word']);
            $query = $query->where(function ($q) use ($keyWord) {
                $q->whereRaw('LOWER(users.name) LIKE ?', ["%$keyWord%"])
                    ->orWhereRaw('LOWER(users.slug) LIKE ?', ["%$keyWord%"]);
            });
        }

        if (isset($filters['permission_id'])) {
            $query = $query->whereHas('permissions', function ($q) use ($filters) {
                $q->where('permission_id', $filters['permission_id']);
            });
        }

        if (isset($filters['sort_by'])) {
            $sortOrder = $filters['sort_order'] ?? 'asc';
            $query = $query->orderBy('users.' . $filters['sort_by'], $sortOrder);
        } else {
            $query = $query->orderBy('users.created_at', 'desc');
        }

        return $query;
    }

    public function list(array $filters = []): \Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        $query = $this->getBlankModel();

        $query = $this->__filter($query, $filters);

        $limit = request()->get('limit', 10);

        return $query->paginate($limit);
    }

    public function generateAndSaveToken($user): string
    {
        $token = User::generateUniqueToken();
        $user->persionAccessToken()->create([
            'token' => hash('sha256', $token),
        ]);

        return $token;
    }

    public function create(array $data): array
    {
        //generate or get original password to display after create user only
        $originalPassword = '';
        if (isset($data['password'])) {
            $originalPassword = $data['password'];
        } else {
            $originalPassword = User::generateStrongPassword();
        }
        $data['password'] = Hash::make($originalPassword);

        // save user data and token
        $user = $this->getBlankModel()->create($data);
        $token = $this->generateAndSaveToken($user);

        // save and load user permissions
        if (isset($data['permissions'])) {
            $this->setPermissions($user, $data['permissions']);
        }
        $user->load('permissions');

        // make user to array to insert display user password when created only
        $user = $user->toArray();
        $user['password'] = $originalPassword;

        return ['user' => $user, 'token' => $token];
    }

    public function update(User $user, array $data): User
    {
        $user->update($data);

        if (isset($data['permissions'])) {
            //delete old permissions
            $user->userPermissions()->delete();

            //set new permissions
            foreach ($data['permissions'] as $permission) {
                $user->userPermissions()->create([
                    'permission_id' => $permission['id'],
                    'start_at' => $permission['start_at'] ?? null,
                    'expires_at' => $permission['expires_at'] ?? null,
                ]);
            }
        }

        $user->load('permissions');

        return $user;
    }

    public function setPermissions(User &$user, array $permissions): void
    {
        //delete old permissions
        $user->userPermissions()->delete();

        //set new permissions
        foreach ($permissions as $permission) {
            $user->userPermissions()->create([
                'permission_id' => $permission['id'],
                'start_at' => $permission['start_at'] ?? null,
                'expires_at' => $permission['expires_at'] ?? null,
            ]);
        }
    }

    public function login(array $data): array
    {
        $user = $this->getBlankModel()->where('phone', $data['phone'])->first();
        if (!$user) {
            return [];
        }

        if (!Hash::check($data['password'], $user->password)) {
            return [];
        }

        $token = User::generateUniqueToken();
        $user->persionAccessToken()->create([
            'token' => hash('sha256', $token),
        ]);

        return ['user' => $user, 'token' => $token];
    }

    public function logout(int $id, string $token): void
    {
        PersonalAccessToken::where('user_id', $id)
            ->where('token', hash('sha256', $token))
            ->delete();
    }

    public function resetPassword(string $token, string $newPassword, bool $useOTP, string|null $oldPassword): bool
    {
        $user = $this->getBlankModel()
            ->whereHas('persionAccessToken', function ($query) use ($token) {
                $query->where('token', hash('sha256', $token));
            })
            ->first();

        if (!$user) {
            return false;
        }

        if (!$useOTP && !Hash::check($oldPassword, $user->password)) {
            return false;
        }

        $user->password = Hash::make($newPassword);
        $user->save();

        return true;
    }

    public function delete(array $ids)
    {
        return $this->getBlankModel()->whereIn('id', $ids)->delete();
    }

    public function detail(int $id): User
    {
        return $this->getBlankModel()->with('permissions')->find($id);
    }

    public function getUserByPhone(string $phone): User
    {
        return $this->getBlankModel()->where('phone', $phone)->first();
    }

    public function getUserByEmail(string $email): User
    {
        return $this->getBlankModel()->where('email', $email)->first();
    }
}
