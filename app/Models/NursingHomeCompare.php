<?php namespace App;

use Illuminate\Database\Eloquent\Model;

//use Monolog\Logger;
//use Monolog\Handler\StreamHandler;

use Illuminate\Support\Facades\Log;

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
        $data=[
        'name'=>isset($array['name'])?$array['name']:'',
        'operation'=>isset($array['operation'])?$array['operation']:'',
        'operations'=>isset($array['operations'])?$array['operations']:'',
        'results'=>isset($array['results'])?$array['results']:'',
        ];
        
        //$log = new Logger($this->table);
        //$log->pushHandler(new StreamHandler(storage_path().DIRECTORY_SEPARATOR.$this->table.'.log', Logger::INFO));
        //$log->info($data);
        
        Log::info($data);
        
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