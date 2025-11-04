<?php

namespace App\Repositories;

use App\Models\Permission;
use App\Models\User;
use App\Repositories\Contracts\PermissionRepositoryInterface;

class PermissionRepository implements PermissionRepositoryInterface
{
    public function getBlankModel()
    {
        return Permission::query();
    }

    private function __filter(&$query, array $filters = []): \Illuminate\Database\Eloquent\Builder
    {
        if (isset($filters['key_word'])) {
            $keyWord = mb_strtolower($filters['key_word']);
            $query = $query->where(function ($q) use ($keyWord) {
                $q->whereRaw('LOWER(permissions.name) LIKE ?', ["%$keyWord%"])
                    ->orWhereRaw('LOWER(permissions.slug) LIKE ?', ["%$keyWord%"])
                    ->orWhereRaw('LOWER(permissions.description) LIKE ?', ["%$keyWord%"]);
            });
        }

        if (isset($filters['sort_by'])) {
            $sortOrder = $filters['sort_order'] ?? 'asc';
            $query = $query->orderBy('permissions.' . $filters['sort_by'], $sortOrder);
        } else {
            $query = $query->orderBy('permissions.created_at', 'desc');
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

    public function create(array $data): Permission
    {
        $permission = $this->getBlankModel()->create($data);

        $rootPermissionId = config('app.root_permission_id', 42069);
        if (!empty($data['users'])) {
            // Extract user IDs from the provided data
            $userIds = array_map(fn($user) => $user['id'], $data['users']);

            // Get all users without root permission
            $usersWithoutRoot = User::whereIn('id', $userIds)
                ->whereDoesntHave('userPermissions', function ($q) use ($rootPermissionId) {
                    $q->where('user_permissions.permission_id', $rootPermissionId);
                })
                ->get();

            // extract user IDs from the filtered users
            $filteredUserIds = $usersWithoutRoot->pluck('id')->toArray();
            foreach ($data['users'] as $userData) {
                if (in_array($userData['id'], $filteredUserIds)) {
                    $permission->users()->attach($userData['id'], [
                        'start_at' => $userData['start_at'] ?? null,
                        'expires_at' => $userData['expires_at'] ?? null,
                    ]);
                }
            }
        }

        $permission->load('users');

        return $permission;
    }

    public function update(int $id, array $data): ?Permission
    {
        $permission = $this->getBlankModel()->find($id);
        if (!$permission) {
            return null;
        }

        $permission->update($data);

        return $permission;
    }

    public function delete(array $ids): int
    {
        return $this->getBlankModel()->whereIn('id', $ids)->delete();
    }

    public function detail(int $id): Permission
    {
        return $this->getBlankModel()->with('users')->find($id);
    }
}
