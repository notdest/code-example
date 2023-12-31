<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Gate;

class UserController extends Controller
{
    public function index(){
        $users  = User::paginate(20);

        return view('users.index',[
            'users'  => $users
        ]);
    }

    public function create(){
        return view('users.create',[
            'user'   => new User(),
        ]);
    }

    public function store(Request $request){

        $fields = $request->except('_token');
        $validator  = \Validator::make($fields,[
            'email'         => 'required|email|unique:users,email',
            'password'      => 'required|min:8',
            'blocked'       => 'boolean',
            'role'          => 'between:1,3',
            'show_posts'    => 'boolean',
            'show_articles' => 'boolean',
        ]);

        if($validator->passes()){
            $fields['surname']      = $fields['surname']    ?: ''; // браузер null подставляет вместо пустой строки
            $fields['name']         = $fields['name']       ?: '';
            $fields['department']   = $fields['department'] ?: '';

            $fields['password'] = Hash::make($fields['password']);
            $user   = User::create($fields);
            return redirect('users/edit/'.$user->id)->with('success', true);
        }else{
            $user   = new User();
            $user->fill($fields);
            return view('users.create',[
                'user'   => $user,
                'errors' => $validator->errors(),
            ]);
        }
    }


    public function edit(Request $request){
        return view('users.edit',[
            'user'      => User::findOrFail((int) $request->id),
            'success'   => $request->session()->get('success', false),
        ]);
    }


    public function save(Request $request){
        $user       = User::findOrFail((int) $request->id);

        $fields     = $request->except('_token');
        if(!$fields['password']){
            unset($fields['password']);
        }
        $fields['surname']      = $fields['surname']    ?: ''; // браузер null подставляет вместо пустой строки
        $fields['name']         = $fields['name']       ?: '';
        $fields['department']   = $fields['department'] ?: '';
        $user->fill($fields);

        $validator  = \Validator::make($fields,[
            'email'         => ['required', 'email', Rule::unique('users')->ignore($user->id)],
            'blocked'       => 'boolean',
            'role'          => 'between:1,3',
            'show_posts'    => 'boolean',
            'show_articles' => 'boolean',
        ]);

        $success = false;
        if($validator->passes()){
            if(isset($fields['password'])){
                $user->password = Hash::make($fields['password']);
            }
            $success = $user->save();
        }

        return view('users.edit',[
            'user'      => $user,
            'errors'    => $validator->errors(),
            'success'   => $success,
        ]);
    }

    public function token(Request $request){
        $user   = User::findOrFail((int) $request->id);

        $user->tokens()->delete();
        $token  = $user->createToken('main');

        return $token->plainTextToken."\n";
    }

    public function defaultPage(){
        if (Gate::allows('post-viewer')) {
            return redirect('posts');
        }

        if (Gate::allows('article-viewer')) {
            return redirect('articles');
        }

        return view('users.defaultPage');
    }
}
