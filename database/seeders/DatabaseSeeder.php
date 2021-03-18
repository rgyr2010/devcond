<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\User;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
    DB::table('units')->insert([
        'name'=>'APT 100',
        'id_owner'=>1
    ]);
    DB::table('units')->insert([
        'name'=>'APT 101',
        'id_owner'=>1
    ]);
    DB::table('units')->insert([
        'name'=>'APT 200',
        'id_owner'=>'0'
    ]);
    DB::table('units')->insert([
        'name'=>'APT 201',
        'id_owner'=>'0'
    ]);


    DB::table('areas')->insert([
        'allowed'=>'1',
        'title'=>'Academia',
        'couver'=>'gim.jpg',
        'day'=>'1,2,3,4,5',
        'start_time'=>'08:00:00',
        'end_time'=>'22:00:00'
    ]);

    DB::table('areas')->insert([
        'allowed'=>'1',
        'title'=>'Academia',
        'couver'=>'pool.jpg',
        'day'=>'4,5,6',
        'start_time'=>'08:00:00',
        'end_time'=>'23:00:00'
    ]);

    DB::table('areas')->insert([
        'allowed'=>'1',
        'title'=>'churrasqueira',
        'couver'=>'barbecue.jpg',
        'day'=>'4,5,6',
        'start_time'=>'09:00:00',
        'end_time'=>'23:00:00'
    ]);


    DB::table('walls')->insert([
        'title'=>'Titulo de Aviso de teste',
        'body'=>'Loren ipsum e mais sinistra que nos',
        'datecreated'=>'2021-03-15 09:49:31'
    ]);




    }
}
