<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Interest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class ClientController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        // Get only clients created by the logged-in admin
        $clients = User::with(['interests', 'creator'])
            ->clients()
            ->where('created_by', Auth::id())
            ->orderBy('created_at', 'desc')
            ->get();

        $interests = Interest::all();

        return view('clients.index', compact('clients', 'interests'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'contact_no' => 'nullable|string|max:15|regex:/^[0-9]+$/',
            'birthday' => 'nullable|date|before:today',
            'password' => [
                'required',
                'string',
                'min:8',
                'regex:/[a-z]/',      // must contain at least one lowercase letter
                'regex:/[A-Z]/',      // must contain at least one uppercase letter
                'regex:/[0-9]/',      // must contain at least one digit
                'regex:/[@$!%*?&]/',  // must contain a special character
            ],
            'interests' => 'nullable|array',
            'interests.*' => 'exists:interests,id',
        ], [
            'contact_no.regex' => 'Contact number must contain only numbers.',
            'birthday.before' => 'Birthday must be a date before today.',
            'password.regex' => 'Password must contain at least one uppercase letter, one lowercase letter, one number, and one special character (@$!%*?&).',
        ]);

        $client = User::create([
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'email' => $request->email,
            'contact_no' => $request->contact_no,
            'birthday' => $request->birthday,
            'password' => Hash::make($request->password),
            'role_id' => 2, // Client role
            'created_by' => Auth::id(),
            'updated_by' => Auth::id(),
        ]);

        // Attach interests
        if ($request->has('interests')) {
            $client->interests()->attach($request->interests);
        }

        return response()->json([
            'message' => 'Client created successfully',
            'client' => $client->load('interests')
        ], 201);
    }

    public function show($id)
    {
        $client = User::with('interests')
            ->clients()
            ->where('created_by', Auth::id())
            ->findOrFail($id);

        return response()->json($client);
    }

    public function update(Request $request, $id)
    {
        $client = User::clients()
            ->where('created_by', Auth::id())
            ->findOrFail($id);

        $rules = [
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => ['required', 'email', Rule::unique('users')->ignore($client->id)],
            'contact_no' => 'nullable|string|max:15|regex:/^[0-9]+$/',
            'birthday' => 'nullable|date|before:today',
            'interests' => 'nullable|array',
            'interests.*' => 'exists:interests,id',
        ];

        // Only validate password if it's provided
        if ($request->filled('password')) {
            $rules['password'] = [
                'string',
                'min:8',
                'regex:/[a-z]/',      // must contain at least one lowercase letter
                'regex:/[A-Z]/',      // must contain at least one uppercase letter
                'regex:/[0-9]/',      // must contain at least one digit
                'regex:/[@$!%*?&]/',  // must contain a special character
            ];
        }

        $request->validate($rules, [
            'contact_no.regex' => 'Contact number must contain only numbers.',
            'birthday.before' => 'Birthday must be a date before today.',
            'password.regex' => 'Password must contain at least one uppercase letter, one lowercase letter, one number, and one special character (@$!%*?&).',
        ]);

        $data = [
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'email' => $request->email,
            'contact_no' => $request->contact_no,
            'birthday' => $request->birthday,
            'updated_by' => Auth::id(),
        ];

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $client->update($data);

        // Sync interests
        if ($request->has('interests')) {
            $client->interests()->sync($request->interests);
        } else {
            $client->interests()->detach();
        }

        return response()->json([
            'message' => 'Client updated successfully',
            'client' => $client->load('interests')
        ]);
    }

    public function destroy($id)
    {
        $client = User::clients()
            ->where('created_by', Auth::id())
            ->findOrFail($id);

        $client->delete();

        return response()->json([
            'message' => 'Client deleted successfully'
        ]);
    }
}