<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

use App\Models\User;

use Auth;
use Mail;

class UsersController extends Controller
{

    public function __construct()
    {
        //已登录的用户访问
        $this->middleware('auth', [
            'only' => ['edit', 'update', 'destroy', 'followings', 'followers']
        ]);

        //未登录的用户访问
        $this->middleware('guest', [
            'only' => ['create']
        ]);
    }

    public function index()
    {
        $users = User::paginate(30);
        return view('users.index', compact('users'));
    }

    public function create()
    {
        return view('users.create');
    }

    public function show($id)
    {
        $user = User::findOrFail($id);
        $statuses = $user->statuses()
                            ->orderBy('created_at', 'desc')
                            ->paginate(30);
        return view('users.show',compact('user', 'statuses'));
    }

    public function store(Request $request)
    {
        $this->validate($request, [
            'name'     => 'required|max:50',
            'email'    => 'required|email|unique:users|max:255',
            'password' => 'required'
        ]);

        $user = User::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'password' => bcrypt($request->password)
        ]);

        $this->sendEmailConfirmationTo($user);
        return redirect('/');
    }

    public function edit($id)
    {
        $user = User::findOrFail($id);
        $this->authorize('update', $user);
        return view('users.edit', compact('user'));
    }

    public function update($id, Request $request)
    {
        $this->validate($request, [
            'name'     => 'required|max:50',
            'password' => 'required|confirmed|min:6'
        ]);

        $user = User::findOrFail($id);
        $this->authorize('update', $user);

        $data = [];
        $data['name'] = $request->name;
        if($request->password){
            $data['password'] = bcrypt($request->password);
        }

        $user->update($data);

        session()->flash('success', '个人资料更新成功！');
        return redirect()->route('users.show', $id);
    }

    public function destroy($id)
    {
        $user = User::findOrFail($id);
        $this->authorize('destroy', $user);
        $user->delete();
        session()->flash('success', '成功删除用户！');
        return back();
    }

    public function sendEmailConfirmationTo($user)
    {
        $view = 'emails.confirm';
        $data = compact('user');
        $from = '1184323775@qq.com';
        $name = 'James';
        $to = $user->email;
        $subject = "感谢注册 Ceshi 应用！请确认你的邮箱。";

        Mail::send($view, $data, function ($message) use ($from, $name, $to, $subject) {
            $message->from($from, $name)->to($to)->subject($subject);
        });
    }

    public function confirmEmail($token)
    {
        $user = User::where('activation_token', $token)->firstOrFail();

        $user->activated = true;
        $user->activation_token = null;
        $user->save();

        Auth::login($user);
        session()->flash('success', '恭喜你，激活成功！');
        return redirect()->route('users.show', [$user]);
    }

    public function followings($id)
    {
        $user  = User::findOrFail($id);
        $users = $user->folloings()->paginate(30);
        $title = '关注的人';
        return view('users.show_follow', compact('users','title'));
    }

    public function followers($id)
    {
        $user  = User::findOrFail($id);
        $users = $user->followers()->paginate(30);
        $title = '粉丝';
        return view('users.show_follow', compact('users', 'title'));
    }


}
