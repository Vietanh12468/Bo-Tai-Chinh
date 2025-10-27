<?php

namespace App\Http\Controllers\Api;

use App\ApiResponser;
use App\Http\Controllers\Controller;
use App\Http\Requests\Permission\CreatePermissionRequest;
use App\Http\Requests\Permission\IdPermissionRequest;
use App\Http\Requests\Permission\IdsPermissionRequest;
use App\Http\Requests\Permission\ListPermissionRequest;
use App\Http\Requests\Permission\UpdatePermissionRequest;
use App\Repositories\PermissionRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ApiPermissionController extends Controller
{
    use ApiResponser;

    protected $permissionRepository;

    public function __construct(PermissionRepository $permissionRepository)
    {
        $this->permissionRepository = $permissionRepository;
    }

    /**
     * @OA\Get(
     *     path="/api/dashboard/permission/list",
     *     tags={"Permission"},
     *     summary="Retrieve list of permissions",
     *     @OA\Parameter(
     *         name="key_word",
     *         in="query",
     *         description="Keyword to search by name or slug",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="sort_by",
     *         in="query",
     *         description="Field to sort by",
     *         @OA\Schema(type="string", enum={"id","name","slug","created_at","updated_at"}, example="id")
     *     ),
     *     @OA\Parameter(
     *         name="sort_order",
     *         in="query",
     *         description="Sort order",
     *         @OA\Schema(type="string", enum={"asc","desc"}, example="asc")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Permission list retrieved successfully",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="name", type="string", example="View Dashboard"),
     *                 @OA\Property(property="slug", type="string", example="view_dashboard"),
     *                 @OA\Property(property="description", type="string", example="Permission to view the dashboard"),
     *                 @OA\Property(property="routes", type="array", @OA\Items(type="string", example="dashboard"))
     *             )
     *         )
     *     )
     * )
     */
    public function list(ListPermissionRequest $request)
    {
        try {
            $data = $request->only('key_word', 'sort_by', 'sort_order');
            $permissions = $this->permissionRepository->list($data);
            return $this->responseSuccess(__('notification.api.get_data_success'), $permissions);
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return $this->responseError(__('notification.server.error'));
        }
    }

    /**
     * @OA\Post(
     *     path="/api/dashboard/permission/create",
     *     tags={"Permission"},
     *     summary="Create a new permission",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             required={"name", "routes"},
     *             @OA\Property(property="name", type="string", example="View Dashboard"),
     *             @OA\Property(property="description", type="string", example="Permission to view the dashboard"),
     *             @OA\Property(property="routes", type="array",
     *                 @OA\Items(type="string", example="dashboard")
     *             ),
     *             @OA\Property(property="users", type="array",
     *                 @OA\Items(type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="start_at", type="string", format="date-time", example="2023-10-01T00:00:00Z"),
     *                     @OA\Property(property="expires_at", type="string", format="date-time", example="2024-10-01T00:00:00Z")
     *                 )
     *             )
     *        )
     *    ),
     *    @OA\Response(
     *        response=200,
     *        description="Permission created successfully",
     *        @OA\JsonContent(
     *           type="object",
     *           @OA\Property(property="id", type="integer", example=1),
     *           @OA\Property(property="name", type="string", example="View Dashboard"),
     *           @OA\Property(property="slug", type="string", example="view_dashboard"),
     *           @OA\Property(property="description", type="string", example="Permission to view the dashboard"),
     *           @OA\Property(property="routes", type="array",
     *               @OA\Items(type="string", example="dashboard")
     *           ),
     *           @OA\Property(property="users", type="array",
     *               @OA\Items(type="object",
     *                   @OA\Property(property="id", type="integer", example=1),
     *                   @OA\Property(property="start_at", type="string", format="date-time", example="2023-10-01T00:00:00Z"),
     *                   @OA\Property(property="expires_at", type="string", format="date-time", example="2024-10-01T00:00:00Z")
     *               )
     *           )
     *        )
     *    )
     * )
     */

    public function create(CreatePermissionRequest $request)
    {
        try {
            DB::beginTransaction();

            $data = $request->only('name', 'slug', 'description', 'routes', 'users');
            $permission = $this->permissionRepository->create($data);

            DB::commit();
            return $this->responseSuccess(__('notification.api.create_success'), $permission);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error($e->getMessage());
            return $this->responseError(__('notification.server.error'));
        }
    }

    /**
     * @OA\Post(
     *     path="/api/dashboard/permission/{id}/update",
     *     tags={"Permission"},
     *     summary="Update an existing permission",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="name", type="string", example="View Dashboard"),
     *             @OA\Property(property="description", type="string", example="Permission to view the dashboard"),
     *             @OA\Property(property="routes", type="array",
     *                 @OA\Items(type="string", example="dashboard")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Permission updated successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="id", type="integer", example=1),
     *             @OA\Property(property="name", type="string", example="View Dashboard"),
     *             @OA\Property(property="slug", type="string", example="view_dashboard"),
     *             @OA\Property(property="description", type="string", example="Permission to view the dashboard"),
     *             @OA\Property(property="routes", type="array",
     *                 @OA\Items(type="string", example="dashboard")
     *             )
     *         )
     *     )
     * )
     */
    public function update(UpdatePermissionRequest $request, $id)
    {
        try {
            DB::beginTransaction();

            $data = $request->only('name', 'slug', 'description', 'routes');
            $permission = $this->permissionRepository->update($id, $data);

            DB::commit();
            return $this->responseSuccess(__('notification.api.update_success'), $permission);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error($e->getMessage());
            return $this->responseError(__('notification.server.error'));
        }
    }

    /**
     * @OA\Delete(
     *     path="/api/dashboard/permission/delete",
     *     tags={"Permission"},
     *     summary="Delete one or more permissions",
     *     description="Provide a single id or comma-separated ids to delete multiple permissions",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="ids", 
     *                 type="array",
     *                 @OA\Items(type="integer", example=1),
     *                 description="Array of permission IDs to delete"
     *             )
     *         )
     *     ),
     *     @OA\Response(response=200, description="Permission(s) deleted successfully"),
     * )
     */
    public function delete(IdsPermissionRequest $request)
    {
        try {
            $ids = $request->input('ids');
            $this->permissionRepository->delete($ids);
            return $this->responseSuccess(__('notification.api.delete_success'));
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return $this->responseError(__('notification.server.error'));
        }
    }

    /**
     * @OA\Get(
     *     path="/api/dashboard/permission/{id}/detail",
     *     tags={"Permission"},
     *     summary="Get permission details by ID",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Permission details retrieved successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="id", type="integer", example=1),
     *             @OA\Property(property="name", type="string", example="View Dashboard"),
     *             @OA\Property(property="slug", type="string", example="view_dashboard"),
     *             @OA\Property(property="description", type="string", example="Permission to view the dashboard"),
     *             @OA\Property(property="routes", type="array",
     *                 @OA\Items(type="string", example="dashboard")
     *             ),
     *             @OA\Property(property="users", type="array",
     *                 @OA\Items(type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="name", type="string", example="John Doe"),
     *                     @OA\Property(property="slug", type="string", example="john-doe"),
     *                     @OA\Property(property="email", type="string", format="email", example="john@example.com"),
     *                     @OA\Property(property="phone", type="string", example="+1234567890"),
     *                     @OA\Property(property="created_at", type="string", format="date-time", example="2023-10-01T12:00:00Z"),
     *                     @OA\Property(property="updated_at", type="string", format="date-time", example="2023-10-10T12:00:00Z"),
     *                     @OA\Property(property="permission_start_at", type="string", format="date-time", example="2023-10-01T00:00:00Z"),
     *                     @OA\Property(property="permission_expires_at", type="string", format="date-time", example="2024-10-01T00:00:00Z")
     *                 )        
     *             )
     *         )
     *     )
     * )
     */

    public function detail(IdPermissionRequest $request, $id)
    {
        try {
            $permission = $this->permissionRepository->detail($id);
            return $this->responseSuccess(__('notification.api.get_data_success'), $permission);
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return $this->responseError(__('notification.server.error'));
        }
    }
}
