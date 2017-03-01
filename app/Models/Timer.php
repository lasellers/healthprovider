<?php namespace App\Models;
/**
*
$t=new Timer();
$t->print_time();
*/

class Timer
{
    // -------------------------------------------------------------------------------------------
    
    private $running=true;
    private $start_time=null;
    private $end_time=null;
    // -------------------------------------------------------------------------------------------
    public function __construct()
    {
        $this->start_time=microtime(true);
        $this->end_time=microtime(true);
    }
    // -------------------------------------------------------------------------------------------
    public function __destruct()
    {
        $this->start_time=null;
        $this->end_time=null;
    }
    // -------------------------------------------------------------------------------------------
    
    public function start()
    {
        $this->running=true;
        $this->start_time=microtime(true);
    }
    // -------------------------------------------------------------------------------------------
    
    public function update()
    {
        $this->end_time=microtime(true);
    }
    // -------------------------------------------------------------------------------------------
    public function stop()
    {
        $this->running=false;
        $this->end_time=microtime(true);
    }
    // -------------------------------------------------------------------------------------------
    
    public function get()
    {
        if($this->running===true) $this->update();
        $time=($this->end_time)-($this->start_time);
        return ($time>0)?$time:0;
    }
    // -------------------------------------------------------------------------------------------
    
    public function dump()
    {
        // $this->stop();
        $t=$this->get();
        \App\CLI::println(sprintf("%8.2f seconds %s", $t, ($this->running?'Running':'Stopped')));
        \App\CLI::println(" start {$this->start_time} end {$this->end_time}");
    }
    // -------------------------------------------------------------------------------------------
    
    public function get_time()
    {
        $t=$this->get();
        if(\App\CLI::is_cli())
        return "$t seconds";
        else
            return "<b>$t</b> seconds";
    }
    // -------------------------------------------------------------------------------------------
    
    public function get_time_on_page()
    {
        $t=$this->get();
        $pps=1.0/$t;
        if(\App\CLI::is_cli())
        return "$t seconds (Execution Time: ".trim(sprintf("%6.2f", $pps))." pages per second)";
        else
            return "<b>$t</b> seconds (<b>Execution Time: ".trim(sprintf("%6.2f", $pps))."</b> pages per second)";
    }
    // -------------------------------------------------------------------------------------------
    
    public function get_elapsed_time()
    {
        $t=$this->get();
        
        $str = "";
        if ($t > 60*60*24) {
            $days = floor($t/60/60/24);
            $str .=  "$days days, ";
            $t = $t - ($days * (60*60*24));
        }
        if ($t > 60*60) {
            $hours = floor($t/60/60);
            $str .=  " $hours hours, ";
            $t = $t - ($hours * (60*60));
        }
        if ($t > 60) {
            $minutes = floor($t/60);
            $str .=  " $minutes minutes, ";
            $t = $t - ($minutes * 60);
        }
        if ($t > 0) {
            $str .= " ".number_format($t,2)." seconds";
        }
        $str=trim($str);
        
        if(\App\CLI::is_cli())
        return "".$this->get()." seconds ($str)";
        else
            return "<b>".$this->get()."</b> seconds (<i>$str</i>)";
    }
    
    // -------------------------------------------------------------------------------------------
    
    public function print_pps_time()
    {
        $t=$this->get();
        $pps=1.0/$t;
        $s1=" Execution Time: ".trim(sprintf("%6.2f", $pps));
        $s2=$this->get_elapsed_time();
        if(\App\CLI::is_cli())
        \App\CLI::println("# $t seconds ($s2) ($s1 pages per second)");
        else
            \App\CLI::println("<span><b>$t</b> seconds ($s2) (<b>$s1</b> pages per second)</span>");
    }
    
    // -------------------------------------------------------------------------------------------
    public function print_elapsed_time()
    {
        $t=$this->get();
        $pps=1.0/$t;
        $s1=" Execution Time: ".trim(sprintf("%6.2f", $pps));
        $s2=$this->get_elapsed_time();
        if(\App\CLI::is_cli())
        \App\CLI::println("# $t seconds ($s2)");
        else
            \App\CLI::println("<span><b>$t</b> seconds ($s2)</span>");
    }
    
    // -------------------------------------------------------------------------------------------
    
    /* */
    public function print_memory_used()
    {
        \App\CLI::println($this->memory_used());
    }
    public function memory_used()
    {
        $size=memory_get_usage();
        $unit=array('b','kb','mb','gb','tb','pb');
        return @round($size/pow(1024,($i=floor(log($size,1024)))),2).' '.$unit[$i];
    }
    // -------------------------------------------------------------------------------------------
    
}