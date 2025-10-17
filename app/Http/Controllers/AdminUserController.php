<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Http\Resources\AdminUserResource;
use App\Http\Responses\ApiResponse;
use App\Models\User;
use App\Services\UserQueryService;
use Illuminate\Http\Request;

class AdminUserController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct(
        protected UserQueryService $queryService
    ) {
    }

    public function index(Request $request)
    {
        $users = $this->queryService->getPaginated($request);

        return ApiResponse::paginated(
            $users,
            AdminUserResource::class
        );
    }

    /**
     * Store a newly created user.
     */
    public function store(StoreUserRequest $request)
    {
        $user = User::create($request->validated());

        return ApiResponse::success(
            new AdminUserResource($user),
            'User created successfully',
            201
        );
    }

    public function show(User $user)
    {
        return ApiResponse::success(
            new AdminUserResource($user),
            'User retrieved successfully'
        );
    }

    public function update(UpdateUserRequest $request, User $user)
    {
        $user->update($request->validated());

        return ApiResponse::success(
            new AdminUserResource($user->fresh()),
            'User updated successfully'
        );
    }

    public function destroy(Request $request, User $user)
    {
        if (!$request->user()->can('delete', $user)) {
            return ApiResponse::forbidden('You cannot delete your own account');
        }

        $user->delete();

        return ApiResponse::success(
            null,
            'User deleted successfully'
        );
    }
}

