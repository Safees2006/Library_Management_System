<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class MemberController extends Controller
{
    public function index(Request $request): View
    {
        $request->validate(['q' => ['nullable', 'string', 'max:100']]);

        $query = User::where('role', 'member');

        if ($search = trim((string) $request->query('q'))) {
            $query->where(fn ($members) => $members->where('name', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%")
                ->orWhere('membership_number', 'like', "%{$search}%"));
        }

        $members = $query->withCount(['loans as active_loans_count' => fn ($loans) => $loans->where('status', 'active')])
            ->orderBy('name')->paginate(12)->withQueryString();

        return view('admin.members.index', compact('members'));
    }

    public function create(): View
    {
        return view('admin.members.form', ['member' => new User(), 'isEditing' => false]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'membership_number' => ['required', 'string', 'max:40', 'unique:users,membership_number'],
            'phone' => ['nullable', 'string', 'max:40'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        User::create([
            ...$data,
            'role' => 'member',
            'status' => 'active',
        ]);

        return redirect()->route('admin.members.index')->with('status', 'Member account created.');
    }

    public function edit(User $member): View
    {
        abort_unless($member->isMember(), 404);

        return view('admin.members.form', ['member' => $member, 'isEditing' => true]);
    }

    public function update(Request $request, User $member): RedirectResponse
    {
        abort_unless($member->isMember(), 404);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($member->id)],
            'membership_number' => ['required', 'string', 'max:40', Rule::unique('users', 'membership_number')->ignore($member->id)],
            'phone' => ['nullable', 'string', 'max:40'],
            'status' => ['required', Rule::in(['active', 'inactive'])],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
        ]);

        if (blank($data['password'] ?? null)) {
            unset($data['password']);
        }

        $member->update($data);

        return redirect()->route('admin.members.index')->with('status', 'Member details updated.');
    }
}
