<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Monolog\Logger;
use Monolog\Handler\StreamHandler;

use Illuminate\Support\Facades\Log;

class HomeHealthCompareModel extends CamelAwareModel {
	protected $table = 'healthproviders';
	public $timestamps = true;
	protected $primaryKey = 'healthprovider_id';
	public $incrementing = true;

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
	public static function log( $array ) {
		$data = [
			'name'       => isset( $array['name'] ) ? $array['name'] : '',
			'operation'  => isset( $array['operation'] ) ? $array['operation'] : '',
			'operations' => isset( $array['operations'] ) ? $array['operations'] : '',
			'results'    => isset( $array['results'] ) ? $array['results'] : '',
		];

		Log::info( $data );
	}
	// --------------------------------------------------------------------
}