<?php

namespace App\Http\Controllers\Api;

use App\ApiResponser;
use App\Services\FileService;
use App\Http\Controllers\Controller;
use App\Http\Requests\User\CreateUserRequest;
use App\Http\Requests\User\IdsUserRequest;
use App\Http\Requests\User\IdUserRequest;
use App\Http\Requests\User\ListUserRequest;
use App\Http\Requests\User\UpdateUserRequest;
use App\Jobs\SendUserAccountEmailJob;
use App\Repositories\UserRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ApiUserController extends Controller
{
    use ApiResponser;

    protected $userRepository;

    protected $fileService;

    public function __construct(UserRepository $userRepository, FileService $fileService)
    {
        $this->userRepository = $userRepository;
        $this->fileService = $fileService;
    }

    /**
     * @OA\Post(
     *     path="/api/dashboard/user/create",
     *     tags={"User"},
     *     summary="Create a new user",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             required={"name","phone","email"},
     *             @OA\Property(property="name", type="string", example="John Doe"),
     *             @OA\Property(property="phone", type="string", example="+1234567890"),
     *             @OA\Property(property="email", type="string", format="email", example="john@example.com"),
     *             @OA\Property(property="password", type="string", format="password", example="password123"),
     *             @OA\Property(property="confirm_password", type="string", format="password", example="password123"),
     *             @OA\Property(property="image", type="string", format="binary", description="profile image file"),
     *             @OA\Property(property="permissions", type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="start_at", type="string", format="date-time", example="2023-10-01T00:00:00Z"),
     *                     @OA\Property(property="expires_at", type="string", format="date-time", example="2024-10-01T00:00:00Z")
     *                 )
     *             )
     *        )
     *    ),
     *    @OA\Response(
     *         response=200,
     *         description="User created successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="id", type="integer", example=1),
     *             @OA\Property(property="name", type="string", example="John Doe"),
     *             @OA\Property(property="slug", type="string", example="john-doe"),
     *             @OA\Property(property="email", type="string", format="email", example="john@example.com"),
     *             @OA\Property(property="phone", type="string", example="+1234567890"),
     *             @OA\Property(property="created_at", type="string", format="date-time", example="2023-10-01T12:00:00Z"),
     *             @OA\Property(property="updated_at", type="string", format="date-time", example="2023-10-10T12:00:00Z"),
     *             @OA\Property(
     *                 property="permissions",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="id", type="integer", example=2),
     *                     @OA\Property(property="name", type="string", example="Edit Articles"),
     *                     @OA\Property(property="slug", type="string", example="edit-articles"),
     *                     @OA\Property(property="start_at", type="string", format="date-time", example="2023-10-01T00:00:00Z"),
     *                     @OA\Property(property="expires_at", type="string", format="date-time", example="2024-10-01T00:00:00Z")
     *                 )
     *             ),
     *             OA\Property(
     *                 property="image",
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=5),
     *                 @OA\Property(property="name", type="string", example="1696345600.webp"),
     *                 @OA\Property(property="path", type="string", example="uploads/images/users/1696345600.webp"),
     *                 @OA\Property(property="mime_type", type="string", example="image/webp"),
     *                 @OA\Property(property="size", type="integer", example=204800),
     *                 @OA\Property(property="path_asset", type="string", example="http://127.0.0.1:8000/uploads/images/users/1696345600.webp")
     *             )
     *         )
     *     )
     * )
     */

    public function create(CreateUserRequest $request)
    {
        try {
            DB::beginTransaction();

            $data = $request->only('name', 'slug', 'email', 'phone', 'password', 'confirm_password', 'permissions');

            $file = $request->file('image');
            if ($file) {
                // get user id from account info to save image file
                $userId = $request->get('accountInfo')['id'] ?? null;
                $image = $this->fileService->uploadAndConvertToWebp($file, 'uploads/images/users', $userId);
                if ($image) {
                    $data['image_id'] = $image['id'];
                }
            }

            $user = $this->userRepository->create($data);

            SendUserAccountEmailJob::dispatch($data['email'], $data['phone'], $data['password']);

            DB::commit();
            return $this->responseSuccess(__('notification.api.create_success'), $user);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error($e->getMessage());
            return $this->responseError(__('notification.server.error'));
        }
    }

    /**
     * @OA\Post(
     *     path="/api/dashboard/user/{id}/update",
     *     summary="Update an existing user",
     *     description="Updates a user's attributes (name, slug, email, phone). Transactional: changes are committed on success and rolled back on failure.",
     *     tags={"User"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the user to update",
     *         required=true,
     *         @OA\Schema(type="integer", format="int64", example=123)
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="name", type="string", example="Jane Doe", description="Full name of the user"),
     *             @OA\Property(property="email", type="string", format="email", example="jane@example.com", description="User email address"),
     *             @OA\Property(property="phone", type="string", example="+15551234567", description="Contact phone number"),
     *             @OA\Property(property="image", type="string", format="binary", description="profile image file"),
     *             @OA\Property(property="permissions", type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="start_at", type="string", format="date-time", example="2023-10-01T00:00:00Z"),
     *                     @OA\Property(property="expires_at", type="string", format="date-time", example="2024-10-01T00:00:00Z")
     *                )
     *             )
     *         )
     *     ),
     *    @OA\Response(
     *         response=200,
     *         description="User update successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="id", type="integer", example=1),
     *             @OA\Property(property="name", type="string", example="John Doe"),
     *             @OA\Property(property="slug", type="string", example="john-doe"),
     *             @OA\Property(property="email", type="string", format="email", example="john@example.com"),
     *             @OA\Property(property="phone", type="string", example="+1234567890"),
     *             @OA\Property(property="created_at", type="string", format="date-time", example="2023-10-01T12:00:00Z"),
     *             @OA\Property(property="updated_at", type="string", format="date-time", example="2023-10-10T12:00:00Z"),
     *             @OA\Property(
     *                 property="permissions",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="id", type="integer", example=2),
     *                     @OA\Property(property="name", type="string", example="Edit Articles"),
     *                     @OA\Property(property="slug", type="string", example="edit-articles"),
     *                     @OA\Property(property="start_at", type="string", format="date-time", example="2023-10-01T00:00:00Z"),
     *                     @OA\Property(property="expires_at", type="string", format="date-time", example="2024-10-01T00:00:00Z")
     *                 )
     *             ),
     *             OA\Property(
     *                 property="image",
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=5),
     *                 @OA\Property(property="name", type="string", example="1696345600.webp"),
     *                 @OA\Property(property="path", type="string", example="uploads/images/users/1696345600.webp"),
     *                 @OA\Property(property="mime_type", type="string", example="image/webp"),
     *                 @OA\Property(property="size", type="integer", example=204800),
     *                 @OA\Property(property="path_asset", type="string", example="http://127.0.0.1:8000/uploads/images/users/1696345600.webp")
     *             )
     *         )
     *     )
     * )
     */

    public function update(UpdateUserRequest $request, $id)
    {
        try {
            DB::beginTransaction();

            $data = $request->only('name', 'slug', 'email', 'phone', 'permissions');

            $file = $request->file('image');
            if ($file) {
                // get user id from account info to save image file
                $userId = $request->get('accountInfo')['id'] ?? null;
                $image = $this->fileService->uploadAndConvertToWebp($file, 'uploads/images/users/', $userId);
                if ($image) {
                    $data['image_id'] = $image['id'];
                }
            }

            $user = $this->userRepository->update($id, $data);

            DB::commit();
            return $this->responseSuccess(__('notification.api.update_success'), $user);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error($e->getMessage());
            return $this->responseError(__('notification.server.error'));
        }
    }

    /**
     * @OA\Get(
     *     path="/api/dashboard/user/list",
     *     tags={"User"},
     *     summary="Get list of users",
     *     @OA\Parameter(
     *         name="key_word",
     *         in="query",
     *         description="Keyword to search in user name or slug",
     *         required=false,
     *         @OA\Schema(type="string", example="john")
     *     ),
     *     @OA\Parameter(
     *         name="permission_id",
     *         in="query",
     *         description="Filter users by permission ID",
     *         required=false,
     *         @OA\Schema(type="integer", example=2)
     *     ),
     *     @OA\Parameter(
     *         name="sort_by",
     *         in="query",
     *         description="Field to sort by (e.g., name, email)",
     *         required=false,
     *         @OA\Schema(type="string", enum={"id","name","slug","phone","email","created_at","updated_at"}, example="id")
     *     ),
     *     @OA\Parameter(
     *         name="sort_order",
     *         in="query",
     *         description="Sort order (asc or desc)",
     *         required=false,
     *         @OA\Schema(type="string", enum={"asc","desc"}, example="asc")
     *     ),
     *     @OA\Parameter(
     *         name="limit",
     *         in="query",
     *         description="Number of items per page",
     *         @OA\Schema(type="integer", example=10)
     *     ),
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="Page number",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="List of users retrieved successfully",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="name", type="string", example="John Doe"),
     *                 @OA\Property(property="slug", type="string", example="john-doe"),
     *                 @OA\Property(property="email", type="string", format="email", example="john@example.com"),
     *                 @OA\Property(property="phone", type="string", example="+1234567890"),
     *                 @OA\Property(property="created_at", type="string", format="date-time", example="2023-10-01T12:00:00Z"),
     *                 @OA\Property(property="updated_at", type="string", format="date-time", example="2023-10-10T12:00:00Z"),
     *                 @OA\Property(
     *                     property="permissions",
     *                     type="array",
     *                     @OA\Items(
     *                         type="object",
     *                         @OA\Property(property="id", type="integer", example=2),
     *                         @OA\Property(property="name", type="string", example="Edit Articles"),
     *                         @OA\Property(property="slug", type="string", example="edit-articles"),
     *                         @OA\Property(property="start_at", type="string", format="date-time", example="2023-10-01T00:00:00Z"),
     *                         @OA\Property(property="expires_at", type="string", format="date-time", example="2024-10-01T00:00:00Z")
     *                     )
     *                 )
     *             )
     *         )
     *     )
     * )
     */

    public function list(ListUserRequest $request)
    {
        try {
            $data = $request->only('key_word', 'permission_id', 'sort_by', 'sort_order');
            $users = $this->userRepository->list($data);
            return $this->responseSuccess(__('notification.api.get_data_success'), $users);
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return $this->responseError(__('notification.server.error'));
        }
    }

    /**
     * @OA\Delete(
     *     path="/api/dashboard/user/delete",
     *     tags={"User"},
     *     summary="Delete users",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             required={"ids"},
     *             @OA\Property(
     *                 property="ids",
     *                 type="array",
     *                 @OA\Items(type="integer", example=1),
     *                 description="Array of user IDs to delete"
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Users deleted successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Users deleted successfully.")
     *         )
     *     )
     * )
     */

    public function delete(IdsUserRequest $request)
    {
        try {
            $ids = $request->input('ids', []);
            $this->userRepository->delete($ids);
            return $this->responseSuccess(__('notification.api.delete_success'));
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return $this->responseError(__('notification.server.error'));
        }
    }

    /**
     * @OA\Get(
     *     path="/api/dashboard/user/{id}/detail",
     *     tags={"User"},
     *     summary="Get user details",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the user to retrieve",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *    @OA\Response(
     *         response=200,
     *         description="User detail successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="id", type="integer", example=1),
     *             @OA\Property(property="name", type="string", example="John Doe"),
     *             @OA\Property(property="slug", type="string", example="john-doe"),
     *             @OA\Property(property="email", type="string", format="email", example="john@example.com"),
     *             @OA\Property(property="phone", type="string", example="+1234567890"),
     *             @OA\Property(property="created_at", type="string", format="date-time", example="2023-10-01T12:00:00Z"),
     *             @OA\Property(property="updated_at", type="string", format="date-time", example="2023-10-10T12:00:00Z"),
     *             @OA\Property(
     *                 property="permissions",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="id", type="integer", example=2),
     *                     @OA\Property(property="name", type="string", example="Edit Articles"),
     *                     @OA\Property(property="slug", type="string", example="edit-articles"),
     *                     @OA\Property(property="start_at", type="string", format="date-time", example="2023-10-01T00:00:00Z"),
     *                     @OA\Property(property="expires_at", type="string", format="date-time", example="2024-10-01T00:00:00Z")
     *                 )
     *             )
     *         )
     *     )
     * )
     */

    public function detail(IdUserRequest $request, $id)
    {
        try {
            $user = $this->userRepository->detail($id);
            return $this->responseSuccess(__('notification.api.get_data_success'), $user);
        } catch (\Exception $e) {
            Log::error('Error retrieving permission details: ' . $e->getMessage());
            return $this->responseError(__('notification.server.error'));
        }
    }
}
