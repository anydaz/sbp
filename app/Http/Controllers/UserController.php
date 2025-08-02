<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function getAuthInfo(){
        return response()->json([
            'status_code' => 200,
            'data' => auth()->user(),
        ]);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request){
        $search = $request->search;

        $query = User::query();

        $query =  $query->when($search, function ($q, $search) {
            return $q->where('name', 'like', '%'.$search.'%');
        });

        return $query->paginate(10);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // $request->request->add(['password' => 'Password01']);

        $body = $request->all();
        // dd($body);
        $user = new User;
        $user->name = $request->name;
        $user->email = $request->email;
        $user->role = $request->role;
        $user->password = Hash::make('1234');
        $user->save();
        // $user = User::create($body);

        return response()->json($user);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }

    public function change_password(Request $request)
    {
        $old_password = $request->old_password;
        $new_password = $request->new_password;

        $user = auth()->user();

        if(!Hash::check($old_password, $user->password)){
            return response()->json(['message' => "Incorrect Password"], 403);
        }

        $user->password = Hash::make($new_password);
        $user->save();

        return response()->json($user);
    }
}
