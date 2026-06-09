<?php

namespace App\Features\Auth\Actions;

use App\Models\Member;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class RegisterMemberAction
{
    /**
     * @param  array{name:string,email:string,phone:string,password:string,gender:string,birth_date:string}  $data
     */
    public function execute(array $data): User
    {
        return DB::transaction(function () use ($data) {
            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'phone' => $data['phone'],
                'password' => Hash::make($data['password']),
                'status' => 'active',
            ]);

            $user->assignRole(Role::findOrCreate('member', 'web'));

            Member::create([
                'user_id' => $user->id,
                'member_code' => Member::generateMemberCode(),
                'gender' => $data['gender'],
                'birth_date' => $data['birth_date'],
                'joined_at' => now()->toDateString(),
                'status' => 'active',
            ]);

            return $user;
        });
    }
}
