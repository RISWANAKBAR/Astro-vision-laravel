<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Category;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string',
            'email' => 'required|email|unique:users',
            'password' => 'required|string',
            'phoneNumber' => 'required|string',
            'profilePicture' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048', 
        ]);
    
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }
    
    
        $profilePicturePath = null;
        if ($request->hasFile('profilePicture')) {
            $profilePicture = $request->file('profilePicture');
            $profilePicturePath = $profilePicture->store('profile_pictures', 'public');
            $profilePicturePath = asset('storage/' . $profilePicturePath);
        }
    
       
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => bcrypt($request->password),
            'phoneNumber' => $request->phoneNumber,
            'profilePicture' => $profilePicturePath,
        ]);
    
        return response()->json(['user' => $user, 'message' => 'User registered successfully']);
    }

    public function login(Request $request)
    {
       
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

      
        if (!auth()->attempt($request->only('email', 'password'))) {
            return response()->json(['message' => 'Invalid login credentials'], 401);
        }

        $user = auth()->user();
        $token = $user->createToken('authToken')->accessToken;

        return response()->json(['user' => $user, 'access_token' => $token, 'message' => 'Login successful']);
    }
    public function getAllUsers()
    {
       
        $users = User::all();

        return response()->json(['users' => $users, 'message' => 'All users retrieved successfully']);
    }


    public function getUserById($userId)
{
   
    $user = User::find($userId);

    if (!$user) {
        return response()->json(['message' => 'User not found'], 404);
    }

    return response()->json(['user' => $user, 'message' => 'User retrieved successfully']);
}

public function createCategoryWithUser(Request $request)
{
  
    $request->validate([
        'category_name' => 'required|string',
        'user_id' => 'required|exists:users,id',
    ]);

    $userId = $request->input('user_id');
    $categoryName = $request->input('category_name');

    $existingCategory = Category::where('user_id', $userId)->first();

    if ($existingCategory) {
      
        $existingCategory->delete();
    }

    // Create a new category
    $category = Category::create([
        'category' => $categoryName,
        'user_id' => $userId,
    ]);

    return response()->json(['message' => 'Category created successfully'], 201);
}
public function Categorydata($userId)
{
   
    $category = Category::where('user_id', $userId)->first();

    if (!$category) {
        return response()->json(['message' => 'Category not found for the given user ID'], 404);
    }

    return response()->json(['category' => $category, 'message' => 'Category retrieved successfully']);
}
}
