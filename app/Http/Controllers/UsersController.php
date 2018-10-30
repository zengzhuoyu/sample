<?php

// 注册

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Auth;

class UsersController extends Controller
{
    public function __construct()
    {

        // 除了此处指定的动作以外，所有其他动作都必须登录用户才能访问（设置未登录不能访问的）
        // 也就是未登录和已登录用户都可以访问以下的方法
        // 解决问题：未登录用户可以访问 edit 和 update 动作
        $this->middleware('auth', [            
            'except' => ['show', 'create', 'store','index']
        ]);

        // 只有未登录用户才能访问的方法（设置已登录不能访问的）、
        // 解决问题：
        $this->middleware('guest', [
            'only' => ['create']
        ]);        
    }

    // 注册
    public function create()
    {
        return view('users.create');
    }

    // 个人中心
    public function show(User $user)
    {
        return view('users.show', compact('user'));
    }    

    // 注册操作
    public function store(Request $request)
    {
        $this->validate($request, [
            'name' => 'required|max:50',
            'email' => 'required|email|unique:users|max:255',
            'password' => 'required|confirmed|min:6'
        ]);
        
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => bcrypt($request->password),
        ]);

        Auth::login($user);

        session()->flash('success', '欢迎，您将在这里开启一段新的旅程~');

        return redirect()->route('users.show', [$user]);
    }   

    // 用户信息编辑
    public function edit(User $user)
    {
        $this->authorize('update', $user);//
        return view('users.edit', compact('user'));
    }

    // 编辑操作
    public function update(User $user, Request $request)
    {
        $this->validate($request, [
            'name' => 'required|max:50',
            'password' => 'nullable|confirmed|min:6'
        ]);

        $this->authorize('update', $user);//

        $data = [];
        $data['name'] = $request->name;
        if ($request->password) {
            $data['password'] = bcrypt($request->password);
        }
        $user->update($data);

        session()->flash('success', '个人资料更新成功！');

        return redirect()->route('users.show', $user->id);
    }

    //用户列表
    public function index()
    {
        // $users = User::all();
        $users = User::paginate(10);
        return view('users.index', compact('users'));
    }   

    //删除用户
    public function destroy(User $user)
    {
        $this->authorize('destroy', $user);
        $user->delete();
        session()->flash('success', '成功删除用户！');
        return back();
    } 
}
