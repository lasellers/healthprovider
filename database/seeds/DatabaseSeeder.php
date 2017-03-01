<?php

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;

class DatabaseSeeder extends Seeder {

	/**
	 * Run the database seeds.
	 *
	 * @return void
	 */
	public function run()
	{
		Model::unguard();

		$this->call('UserTableSeeder');
        $this->call('HealthProvidersSeeder');
	}

}


class UserTableSeeder extends Seeder {
 
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $admin = DB::table('users')->insert([
                'name'   => 'admin',
                'email'      => 'admin@localhost.com',
                'password'   => Hash::make('admin'),
                'created_at' => new DateTime(),
                'updated_at' => new DateTime()
            ]);
 
            $staff = DB::table('users')->insert([
                'name'   => 'staff',
                'email'      => 'staff@localhost.com',
                'password'   => Hash::make('staff'),
                'created_at' => new DateTime(),
                'updated_at' => new DateTime()
            ]);
    }
 
}



class HealthProvidersSeeder extends Seeder {
 
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $admin = DB::table('healthproviders')->insert([
                'name'   => 'Park Ridge Health',
                'address'      => '100 Hospital Dr. Hendersonville, NC 28792 USA',
                'phone'   => '8286848501',
                'created_at' => new DateTime(),
                'updated_at' => new DateTime()
            ]);
 
            $staff = DB::table('healthproviders')->insert([
         'name'   => 'Fort Sanders Regional Medical Center',
                'address'      => '1901 Clinch Ave Knoxville, TN',
                'phone'   => '(865) 541-1111',
                'created_at' => new DateTime(),
                'updated_at' => new DateTime()
            ]);
    }
 
}
