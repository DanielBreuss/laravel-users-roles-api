<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\RoleRequest;
use App\Http\Resources\RoleResource;
use App\Models\Role;
use Illuminate\Http\Response;


class RoleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $roles = Role::paginate(10);

       return response()->json([
           'success' => true,
           'data' => RoleResource::collection($roles),
           'message' => 'Roles retrieved successfully',
           'meta' => [
               'current_page' => $roles->currentPage(),
               'last_page' => $roles->lastPage(),
               'per_page' => $roles->perPage(),
               'total' => $roles->total(),
           ],
       ], Response::HTTP_OK);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(RoleRequest $request)
    {
        $role = Role::create($request->validated());

        return response()->json([
            'success' => true,
            'data' => new RoleResource($role),
            'message' => 'Role stored successfully',
        ], Response::HTTP_CREATED);
    }

    /**
     * Display the specified resource.
     */
    public function show(Role $role)
    {
        return response()->json([
            'success' => true,
            'data' => new RoleResource($role),
            'message' => 'Role retrieved successfully',
        ], Response::HTTP_OK);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(RoleRequest $request, Role $role)
    {
        $role->update($request->validated());

        return response()->json([
            'success' => true,
            'data' => new RoleResource($role),
            'message' => 'Role updated successfully',
        ], Response::HTTP_OK);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Role $role)
    {
        $role->delete();
        return response()->noContent();
    }
}
