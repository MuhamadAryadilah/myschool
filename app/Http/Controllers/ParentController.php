<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ParentController extends Controller
{
    public function index(Request $request)
    {
        $parents = User::where('role', 'orang_tua')
            ->with('students')
            ->when($request->filled('q'), function ($query) use ($request) {
                $q = $request->q;
                $query->where(function ($sub) use ($q) {
                    $sub->where('name', 'like', "%{$q}%")
                        ->orWhere('email', 'like', "%{$q}%")
                        ->orWhere('phone', 'like', "%{$q}%")
                        ->orWhereHas('students', fn ($s) => $s->where('name', 'like', "%{$q}%"));
                });
            })
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        return view('admin.parents.index', compact('parents'));
    }

    public function create()
    {
        return view('admin.parents.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8'],
            'phone' => ['nullable', 'string', 'max:20', new \App\Rules\PhoneNumber],
        ]);

        $user = new User($data);
        $user->email_verified_at = now();
        $user->save();

        return redirect()->route('admin.parents.index')
            ->with('success', 'Akun orang tua berhasil dibuat.');
    }

    public function edit(User $parent)
    {
        abort_if($parent->role !== 'orang_tua', 404);

        return view('admin.parents.edit', compact('parent'));
    }

    public function update(Request $request, User $parent)
    {
        abort_if($parent->role !== 'orang_tua', 404);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($parent->id)],
            'password' => ['nullable', 'string', 'min:8'],
            'phone' => ['nullable', 'string', 'max:20', new \App\Rules\PhoneNumber],
        ]);

        $parent->name = $data['name'];
        $parent->email = $data['email'];
        $parent->phone = $data['phone'] ?? null;

        if (! empty($data['password'])) {
            $parent->password = $data['password'];
        }

        $parent->save();

        return redirect()->route('admin.parents.index')
            ->with('success', 'Data orang tua berhasil diubah.');
    }

    public function destroy(User $parent)
    {
        abort_if($parent->role !== 'orang_tua', 404);

        $parent->delete();

        return redirect()->route('admin.parents.index')
            ->with('success', 'Akun orang tua berhasil dihapus.');
    }
    public function resetPassword(User $parent)
    {
        abort_if(config('demo.enabled'), 403);
        abort_if($parent->role !== 'orang_tua', 404);

        $alphabet = 'abcdefghjkmnpqrstuvwxyz23456789';
        $password = '';
        for ($i = 0; $i < 8; $i++) {
            $password .= $alphabet[random_int(0, strlen($alphabet) - 1)];
        }

        $parent->password = $password;
        $parent->setRememberToken(\Illuminate\Support\Str::random(60));
        $parent->save();

        return redirect()->route('admin.parents.index')
            ->with('reset_password', [
                'name' => $parent->name,
                'email' => $parent->email,
                'password' => $password,
                'wa' => $parent->phone ? $parent->whatsappUrl() : null,
            ]);
    }
}
