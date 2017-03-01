<?php namespace App;

use Illuminate\Database\Eloquent\Model;

class HE3B extends Model  {

	protected $table='healthproviders';
	public $timestamps=true;
	protected $primaryKey = 'healthprovider_id';
	public $incrementing=true;

// we allow mass assignment of the following fields...
	protected $fillable = array(
		'healthprovider_id',
		'name',
		'address',
		'phone',
		'domain',
		'email',
		'created_at',
		'updated_at'
		);
 //protected $guarded = array('*');
     // --------------------------------------------------------------------
	public static function log($array)
	{
	/*				//
	$log=new \App\CronLogs([
		'name'=>isset($array['name'])?$array['name']:'',
		'operation'=>isset($array['operation'])?$array['operation']:'',
		'operations'=>isset($array['operations'])?$array['operations']:'',
		'results'=>isset($array['results'])?$array['results']:'',
		]);
	$log->save();
*/
}
     // --------------------------------------------------------------------
}
