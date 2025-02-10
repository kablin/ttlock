<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class CreateUser extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:create-user';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Добавление нового пользователя';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $name = $this->ask('Имя пользователя');
        $email = $this->ask('Email пользователя');
        $password = $this->secret('Укажите пароль');


        $user = User::create([
            'name' => $name,
            'email' => $email,
            'password' => Hash::make($password),
        ]);

        $this->info('Пользователь создан. Used_id: ' . $user->id);
    }
}
