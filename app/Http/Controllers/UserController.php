<?php

namespace App\Http\Controllers;

use App\Models\Person;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class UserController extends Controller
{
    public $storageController;

    public function __construct()
    {
        $this->middleware('auth');
        $this->storageController = new StorageController();
    }

    public function show($id)
    {
        $this->custom_authorize('read_users');

        $userDojoId = Auth::user()->dojo_id;

        $user = User::with(['person', 'dojo', 'role'])
            ->when($userDojoId, fn($q) => $q->where('dojo_id', $userDojoId))
            ->findOrFail($id);

        return view('vendor.voyager.users.read', compact('user'));
    }

    public function list()
    {
        $roleId     = Auth::user()->role->id;
        $userDojoId = Auth::user()->dojo_id;
        $search     = request('search') ?? null;
        $paginate   = request('paginate') ?? 10;

        $data = User::with(['person', 'dojo', 'role'])
            ->when($search, function ($query, $search) {
                $query->where(function ($subQuery) use ($search) {
                    $subQuery->where('id', $search)
                        ->orWhere('name', 'like', "%$search%")
                        ->orWhere('email', 'like', "%$search%");
                });
            })
            ->when($roleId != 1, function ($query) {
                $query->where('role_id', '!=', 1);
            })
            ->when($userDojoId, fn($q) => $q->where('dojo_id', $userDojoId))
            ->orderBy('id', 'DESC')
            ->paginate($paginate);

        return view('vendor.voyager.users.list', compact('data'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'person_id' => 'required|exists:people,id',
            'role_id' => 'required|exists:roles,id',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6',
        ]);

        $person = Person::whereNull('deleted_at')
            ->where('status', 1)
            ->findOrFail($request->person_id);

        DB::beginTransaction();

        try {
            User::create([
                'person_id' => $request->person_id,
                'dojo_id' => $person->dojo_id,
                'name' => $person->first_name,
                'role_id' => $request->role_id,
                'email' => $request->email,
                'avatar' => 'users/default.png',
                'password' => bcrypt($request->password),
            ]);

            DB::commit();

            return redirect()->route('voyager.users.index')->with([
                'message' => 'Registrado exitosamente.',
                'alert-type' => 'success',
            ]);
        } catch (\Throwable $e) {
            DB::rollback();

            return redirect()->route('voyager.users.index')->with([
                'message' => 'Ocurrio un error.',
                'alert-type' => 'error',
            ]);
        }
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'role_id' => 'nullable|exists:roles,id',
            'password' => 'nullable|string|min:6',
        ]);

        DB::beginTransaction();

        try {
            $user = User::with('person')->findOrFail($id);

            $user->update([
                'status' => $request->status ? 1 : 0,
                'dojo_id' => $user->person->dojo_id ?? null,
            ]);

            if ($request->role_id) {
                $user->update([
                    'role_id' => $request->role_id,
                ]);
            }

            if ($request->password) {
                $user->update([
                    'password' => bcrypt($request->password),
                ]);
            }

            DB::commit();

            return redirect()->route('voyager.users.index')->with([
                'message' => 'Actualizado exitosamente.',
                'alert-type' => 'success',
            ]);
        } catch (\Throwable $e) {
            DB::rollback();

            return redirect()->route('voyager.users.index')->with([
                'message' => 'Ocurrio un error.',
                'alert-type' => 'error',
            ]);
        }
    }

    public function destroy(Request $request, $id)
    {
        DB::beginTransaction();

        try {
            $user = User::whereNull('deleted_at')->findOrFail($id);
            $user->delete();

            DB::commit();

            return redirect()->route('voyager.users.index')->with([
                'message' => 'Eliminado exitosamente.',
                'alert-type' => 'success',
            ]);
        } catch (\Throwable $e) {
            DB::rollback();

            return redirect()->route('voyager.users.index')->with([
                'message' => 'Ocurrio un error.',
                'alert-type' => 'error',
            ]);
        }
    }
}
