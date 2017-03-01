<?php namespace App\Console\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

//
class DebugCommand extends Command
{
  private $command;
	//private $verbose=true;
  // ------------------------------------------------------------------------

 /* public function __construct()
  {
  }*/

	// ------------------------------------------------------------------------
  /*
Don't kill me just yet, I still have some work to do, man.
  */
public function keep_alive()
{
    //ini_set('max_execution_time',50000); //5m
 ini_set('max_execution_time',-1);
 flush();
}

  // ------------------------------------------------------------------------
/*

Prints the list of laravel arguments.

*/
public function print_args()
{
    //
  if(!is_object($this)) return;

//
  $this->line(" Command Name:\t".$this->name);
  $this->line(" Description:\t".$this->description);

  if(!method_exists($this,'getArguments')) return;
    if(!method_exists($this,'argument')) return;
  if(!method_exists($this,'get_argument')) return;

    $args=$this->getArguments();
    $options=$this->getOptions();

    $argument_modes=array(
      InputArgument::REQUIRED=>'REQUIRED', 
      InputArgument::OPTIONAL=>'OPTIONAL',
      );

    $option_modes=array(
      InputOption::VALUE_REQUIRED=>'REQUIRED', 
      InputOption::VALUE_OPTIONAL=>'OPTIONAL',
      InputOption::VALUE_IS_ARRAY=>'ARRAY',
      InputOption::VALUE_NONE=>'NONE'
      );

 
    foreach($args as $arg)
    {
      $this->line(" Argument: ".$argument_modes[$arg[1]]."\t '".$arg[0]."'\t ".$this->argument($arg[0])."");
   }

}
  // ------------------------------------------------------------------------
/*

Prints useful system data.

*/
public function print_system()
{
    //
  if(!is_object($this)) return;
if(!method_exists($this,'writeln')) return;
echo get_class($this);
echo parent_get_class($this);

   // show useful script limitations
  $this->info("max_execution_time = ".ini_get('max_execution_time'));
  $this->info("max_input_time = ".ini_get('max_input_time'));
  $this->info("memory_limit = ".ini_get('memory_limit'));
  $this->info("display_errors = ".ini_get('display_errors'));
  $this->info("log_errors = ".ini_get('log_errors'));
  $this->info("error_log = ".ini_get('error_log'));
  $this->info("post_max_size = ".ini_get('post_max_size'));
  $this->info("upload_max_filesize = ".ini_get('upload_max_filesize'));
}
// ------------------------------------------------------------------------
protected function set_verbose($flag)
{
 $this->verbose=$flag;
}

// ------------------------------------------------------------------------
protected function debug_print_hr()
{
  if($this->verbose===false) return;
  if(!\Config::get('app.debug')) return;
 // \App\CLI::print_hr();
}
// ------------------------------------------------------------------------
protected function print_hr_info()
{
  if($this->verbose===false) return;
  if(!\Config::get('app.debug')) return;
  //\App\CLI::print_hr_info();
}
// ------------------------------------------------------------------------

public function debug_print_r($a,$name="array")
{
  if($this->verbose===false) return;
  if(!\Config::get('app.debug')) return;
  { echo $name."="; print_r($a); echo "\n"; }
}
// ------------------------------------------------------------------------
public function debug_println($s='')
{
 if($this->verbose===false) return;
 if(!\Config::get('app.debug')) return;
 $this->line($s);
}

// ------------------------------------------------------------------------
protected function print_hr()
{
  if(!\Config::get('app.debug')) return;
  //\App\CLI::print_hr();
}
// ------------------------------------------------------------------------
protected function print_hr_strong()
{
  if(!\Config::get('app.debug')) return;
  //\App\CLI::print_hr_strong();
}
// ------------------------------------------------------------------------

protected function print_r($a,$name="array")
{
  if(!\Config::get('app.debug')) return;
  { echo $name."="; print_r($a); echo "\n"; }
}
// ------------------------------------------------------------------------
protected function println($s='')
{
  if(!\Config::get('app.debug')) return;
  $this->line($s);
}

// ------------------------------------------------------------------------
protected function is_debug()
{
 return \Config::get('app.debug');
}

   // --------------------------------------------------------------------

public static function print_memory()
{
  \App\CLI::print_memory();
}

      // --------------------------------------------------------------------
  /**
     * prints bytes in humable readable format such as 1.7GB, etc.
     *
     * @param integer $bytes
     * @return string
     */
  public static function printable_bytes($bytes)
  {
    return CLI::printable_bytes($bytes);
  }

  // ------------------------------------------------------------------------
  protected function abort($string)
  {
   // \CLI::println($string);
    $this->error($string);
    exit;
  }

  // ------------------------------------------------------------------------
  protected function assert($conditional,$string)
  {
    if(!$conditional) return;
   // \CLI::println($string);
    $this->error($string);
    exit;
  }

 // ------------------------------------------------------------------------
  public static function print_last_query()
  {
    $queries = DB::getQueryLog();
    $last_query = end($queries);
  //  if(CLI::is_cli())
    {
      echo("Last Query:"); 
      print_r($last_query); 
      echo("\n"); 
    }
  }

       // --------------------------------------------------------------------

  public function allow_mass_assignment()
  {
    \Eloquent::unguard();
    \DB::connection()->disableQueryLog();

  //public static function boost_innodb_insert_speed()

    if(\Config::get('app.deployment_stage')!=='development') return;
    //
    $show=\DB::select("SHOW SESSION VARIABLES LIKE 'innodb_flush_log%';");
   //   self::print_variables($show);
    $show=\DB::select("SHOW GLOBAL VARIABLES LIKE 'innodb_flush_log%';");
 ///   self::print_variables($show);

    \DB::statement("SET GLOBAL innodb_flush_log_at_trx_commit=2;");

    $show=DB::select("SHOW SESSION VARIABLES LIKE 'innodb_flush_log%';");
    // self::print_variables($show);
    $show=DB::select("SHOW GLOBAL VARIABLES LIKE 'innodb_flush_log%';");
    //self::print_variables($show);
  }
   // --------------------------------------------------------------------

  public function allow_acid_assignment()
  {
          //Eloquent::guard();
  //  DB::connection()->disableQueryLog();

//  unboost_innodb_insert_speed()

   if(\Config::get('app.deployment_stage')!=='development') return;

    //
   $show=\DB::select("SHOW SESSION VARIABLES LIKE 'innodb_flush_log%';");
      //self::print_variables($show);
   $show=\DB::select("SHOW GLOBAL VARIABLES LIKE 'innodb_flush_log%';");
    //self::print_variables($show);

   \DB::statement("SET GLOBAL innodb_flush_log_at_trx_commit=1;");

   $show=\DB::select("SHOW SESSION VARIABLES LIKE 'innodb_flush_log%';");
     //self::print_variables($show);
   $show=\DB::select("SHOW GLOBAL VARIABLES LIKE 'innodb_flush_log%';");
    //self::print_variables($show);
 }
// --------------------------------------------------------------------
 private static function print_variables($objs)
 {
  foreach($objs as $k=>$obj)
  {
    echo($obj->Variable_name." \t ".$obj->Value)."\n";
  }
}
// --------------------------------------------------------------------

}