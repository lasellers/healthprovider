<?php namespace App;
class CLI
{
    /*
    
    Basic CLI printing functions.
    
    Only shows up at the SSH CLI, not on html pages.
    
    @author: Lewis A. Sellers <lasellers@gmail.com>
    @date: 6/2013
    */
    // --------------------------------------------------------------------
    /*
    Detect if we are on the CLI and cache that for later calls.
    */
    public static function is_cli()
    {
        static $cached_is_cli=null;
        if($cached_is_cli===null)
        {
            $sapi_type=php_sapi_name();
            if(substr($sapi_type, 0, 3)=='cli') $cached_is_cli=true;
            else $cached_is_cli=false;
            }
        return $cached_is_cli;
    }
    
    // --------------------------------------------------------------------
    public static function println($a='',$s='')
    {
        if(!self::is_cli()) return;
        if(is_array($a))
        {
            echo $s."=\n";
            print_r($a);
            echo "\n";
        }
        else
        {
            echo $a."\n";
        }
    }
    
    // --------------------------------------------------------------------
    public static function print_r($a,$s='')
    {
        self::println($a,$s);
    }
    // --------------------------------------------------------------------
    public static function print_hr($char='-')
    {
        if(!self::is_cli()) return;
        echo str_repeat($char, 76)."\n";
    }
    public static function print_hr_info()
    {
        if(!self::is_cli()) return;
        return self::print_hr('*');
    }
    public static function print_hr_strong()
    {
        if(!self::is_cli()) return;
        return self::print_hr('=');
    }
    // --------------------------------------------------------------------
    public static function exit_cron()
    {
        if(!self::is_cli()) return;
        self::print_hr();
        self::print_memory();
        self::print_hr();
        exit;
    }
    // --------------------------------------------------------------------
    
    public static function section_title($s)
    {
        if(!self::is_cli()) return;
        self::print_hr();
        CLI::println($s);
        self::print_hr();
    }
    // --------------------------------------------------------------------
    
    public static function print_memory()
    {
        if(!self::is_cli()) return;
        $mb=1024*1024;
        $l=ini_get('memory_limit')>0?"".ini_get('memory_limit')."":"Unlimited";
        $mu=number_format(memory_get_usage()/$mb, 2);
        $mpu=number_format(memory_get_peak_usage()/$mb, 2);
        $drt=date('r',time());
        CLI::println("Memory: usage=$mu MB peak=$mpu MB limit=$l \t $drt");
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
        $b=$bytes;
        $type='B';
        if($b>1024*1024*1024)
        {
            $type='GB';
            $b=$b/(1024*1024*1024);
        }
        else if($b>(1024*1024))
        {
            $type='MB';
            $b=$b/(1024*1024);
        }
        else if($b>1024)
        {
            $type='KB';
            $b=$b/1024;
        }
        return '('.sprintf("%.1f", $b).' '.$type.')';
    }
    // --------------------------------------------------------------------
    
    public static function abort($str)
    {
        CLI::println($str);
        exit;
    }
    // --------------------------------------------------------------------
    
    public static function info($str)
    {
        CLI::println($str);
    }
    // --------------------------------------------------------------------
    
    public static function line($str)
    {
        CLI::println($str);
    }
    // --------------------------------------------------------------------
    
    public static function error($str)
    {
        CLI::println($str);
    }
    // --------------------------------------------------------------------
    
    public static function warning($str)
    {
        CLI::println($str);
    }
    // --------------------------------------------------------------------
    public static function print_last_query()
    {
        $queries = DB::getQueryLog();
        $last_query = end($queries);
        if(CLI::is_cli())
        {
            echo("Last Query:");
            print_r($last_query);
            echo("\n");
        }
    }
    // ------------------------------------------------------------------------
    
}