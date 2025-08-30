<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\UserRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $page = request('page', 1);
        $users = User::getAllUsingCache("users_page_{$page}",10);

        return response()->json([
            'success' => true,
            'data' => UserResource::collection($users),
            'message' => 'Users retrieved successfully',
            'meta' => [
                'current_page' => $users->currentPage(),
                'last_page' => $users->lastPage(),
                'per_page' => $users->perPage(),
                'total' => $users->total(),
            ],
        ], Response::HTTP_OK);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(UserRequest $request)
    {
       $user = User::create($request->validated());

        if($request->has('roles')) {
            $user->assignRole($request->roles);
        }

        return response()->json([
            'success' => true,
            'data' => new UserResource($user),
            'message' => 'User stored successfully',
        ], Response::HTTP_CREATED);
    }

    /**
     * Display the specified resource.
     */
    public function show($userId)
    {
        $userData = User::getUserByIdUsingCache($userId);

        return response()->json([
            'success' => true,
            'data' => new UserResource($userData),
            'message' => 'User retrieved successfully',
        ], Response::HTTP_OK);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UserRequest $request, User $user)
    {
        $user->update($request->validated());

        if($request->has('roles')) {
            $user->assignRole($request->roles);
        }

        return response()->json([
            'success' => true,
            'data' => new UserResource($user->load('roles')),
            'message' => 'User updated successfully',
        ], Response::HTTP_OK);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user)
    {
        $user->delete();
        return response()->noContent();
    }
}
