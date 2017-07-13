<?php

use Illuminate\Database\Seeder;
use App\Models\User;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $users = factory(User::class)->times(50)->make();

        // 生成加数据时，禁用隐藏字段
        (new User)->setHidden([]);
        User::insert($users->toArray());

        $user = User::find(1);
        $user->name = 'James';
        $user->email = '1184323775@qq.com';
        $user->password = bcrypt('123456789');
        $user->is_admin = true;
        $user->save();
    }
}
