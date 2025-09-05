<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;


class LearningController extends Controller
{
    // Display a listing of the resource.
    public function index()
    {
        //
    }

    // Show the form for creating a new resource.
    public function create()
    {
        //
    }

    // Store a newly created resource in storage.
    public function store(Request $request)
    {
        //
    }

    // Display the specified resource.
    public function show($id)
    {
        //
    }

    // Show the form for editing the specified resource.
    public function edit($id)
    {
        //
    }

    // Update the specified resource in storage.
    public function update(Request $request, $id)
    {
        //
    }

    // Remove the specified resource from storage.
    public function destroy($id)
    {
        //
    }

    public function ajax()
    {
        return view('learn.ajax');
    }

    public function fetchUserData(Request $request, User $user)
    {
        // Example data to return the one user data as JSON
        $data = $user->find($request->user);
        return response()->json($data);
    }

    public function dropdown()
    {
        return view('learn.dropdown');
    }

    public function fetchNames(Request $request)
    {
        $search = $request->get('name');

        // Fetch names from the User model based on the search term
        $users = User::where('first_name', 'like', '%' . $search . '%')
            ->orWhere('middle_name', 'like', '%' . $search . '%')
            ->orWhere('last_name', 'like', '%' . $search . '%')
            ->limit(10) // Limit results for performance
            ->get();

        $names = $users->map(function ($user) {
            $name = trim($user->first_name . ' ' . ($user->middle_name ?? '') . ' ' . $user->last_name);
            return ['id' => $user->id, 'name' => $name, 'text' => $name, 'date_of_birth' => $user->date_of_birth, 'email' => $user->email];
        });


        return response()->json(['results' => $names]);
    }
}