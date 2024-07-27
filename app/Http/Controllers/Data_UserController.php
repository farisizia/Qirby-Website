<?php

namespace App\Http\Controllers;

use App\Models\Jadwal;
use App\Models\Data_user;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\RedirectResponse;

class Data_UserController extends Controller
{
    public function index()
    {
        $title = 'Data User';
        $users = Data_user::get();

        return view('pages.data-user', compact('title', 'users'));
    }

    public function edit(Request $request)
    {
        if (!$request->ajax())
            abort(403);

        $user = Data_user::find($request->id);

        return response()->json($user);
    }

    public function update(Request $request)
    {
        if (!$request->ajax())
            abort(403);

        $user = Data_user::find($request->id);

        if (!$user)
            return response()->json(['error' => 'User not found'], 404);

        $user->update($request->all());

        return response()->json();
    }

    // public function destroy(Request $request)
    // {

    //     $user = Data_user::find($request->id);

    //     if (!$user)
    //         return response()->json(['error' => 'User not found'], 404);

    //     $user->delete();

    //     return response()->json();
    // }

    // public function destroy(int $id): RedirectResponse
    // {
    //     try {
    //         $user = Data_user::findOrFail($id);

    //         // Check if the user has any related records
    //         if (Jadwal::where('id_pengguna', $id)->exists()) {
    //             return to_route('users.index')->with('error', 'Cannot delete user with existing bookings.');
    //         }

    //         $user->delete();
    //         return to_route('users.index')->with('success', 'User deleted');
    //     } catch (\Illuminate\Database\QueryException $e) {
    //         return to_route('users.index')->with('error', 'An error occurred while deleting the user.');
    //     } catch (\Exception $e) {
    //         return to_route('users.index')->with('error', 'An unexpected error occurred.');
    //     }
    // }

    public function destroy($id)
    {
        try {
            $user = Data_user::findOrFail($id);

            // Check if the user has any related records
            if (Jadwal::where('id_pengguna', $id)->exists()) {
                return response()->json(['error' => 'Cannot delete user with existing bookings.'], 422);
            }

            $user->delete();
            return response()->json(['success' => 'User deleted successfully.']);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['error' => 'User not found.'], 404);
        } catch (\Illuminate\Database\QueryException $e) {
            return response()->json(['error' => 'An error occurred while deleting the user.'], 500);
        } catch (\Exception $e) {
            return response()->json(['error' => 'An unexpected error occurred.'], 500);
        }
    }
}
