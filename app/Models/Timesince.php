<?php namespace App\Models;
/**
*
* This is a stream-lined date printing class that has only on formatting option: get.
* It prints a date showing relative time from web servers current time
* and the supplied timedate stamp.
*
@author: Lewis A. Sellers <lasellers@gmail.com>
@date: 8/2012
*/
class Timesince
{
    private $TIMEZONE=0;
    private $language='en';
    // --------------------------------------------------------------------
    //  change to TZ http://www.theprojects.org/dev/zone.txt
    /**
    *
    *
    
    @author: Lewis A. Sellers <lasellers@gmail.com>
    @date: 8/2012
    */
    public function __construct($options=array())
    {
        
        $tz = Config::get('app.timezone', 'UTC');
        $language = Config::get('app.language', 'en');
        //
        $zonelist=array('Kwajalein'=>-12.00,'Pacific/Midway'=>-11.00,'Pacific/Honolulu'=>-10.00,'America/Anchorage'=>-9.00,'America/Los_Angeles'=>-8.00,'America/Denver'=>-7.00,'America/Tegucigalpa'=>-6.00,'America/New_York'=>-5.00,'America/Caracas'=>-4.30,'America/Halifax'=>-4.00,'America/St_Johns'=>-3.30,'America/Argentina/Buenos_Aires'=>-3.00,'America/Sao_Paulo'=>-3.00,'Atlantic/South_Georgia'=>-2.00,'Atlantic/Azores'=>-1.00,'Europe/Dublin'=>0,'Europe/Belgrade'=>1.00,'Europe/Minsk'=>2.00,'Asia/Kuwait'=>3.00,'Asia/Tehran'=>3.30,'Asia/Muscat'=>4.00,'Asia/Yekaterinburg'=>5.00,'Asia/Kolkata'=>5.30,'Asia/Katmandu'=>5.45,'Asia/Dhaka'=>6.00,'Asia/Rangoon'=>6.30,'Asia/Krasnoyarsk'=>7.00,'Asia/Brunei'=>8.00,'Asia/Seoul'=>9.00,'Australia/Darwin'=>9.30,'Australia/Canberra'=>10.00,'Asia/Magadan'=>11.00,'Pacific/Fiji'=>12.00,'Pacific/Tongatapu'=>13.00);
        $index=array_keys($zonelist, $tz);
        
        if(isset($options['tz']))
        $tz=$options['tz'];
        if(isset($options['language']))
        $language=$options['language'];
        
        //
        $this->set_language($language);
    }
    // --------------------------------------------------------------------
    /**
    *
    *
    @author: Lewis A. Sellers <lasellers@gmail.com>
    @date: 8/2012
    */
    public function set_tz($tz)
    {
        $this->TIMEZONE=$tz;
    }
    // --------------------------------------------------------------------
    /**
    *
    *
    @author: Lewis A. Sellers <lasellers@gmail.com>
    @date: 8/2012
    */
    public function set_language($language)
    {
        $this->language=$language;
    }
    // --------------------------------------------------------------------
    /**$REF_TIMEZONE = Config::get('app.timezone', 'UTC');
    *
    *
    * @author		Lewis A. Sellers
    * @param
    */
    public function get($timestamp)
    {
        //
        if($this->language=='es')
        $labels=array(
        'unknown'=>'Unknown','minutes'=>'minutos hace','hour'=>'ayer hace','hours'=>'ayer hace','days'=>'días hace','never'=>'No'
        );
        else
            $labels=array(
        'unknown'=>'Unknown','minutes'=>'minutes ago','hour'=>'hour ago','hours'=>'hours ago','days'=>'days ago','never'=>'Never'
        );
        
        //
        $REF_TIMEZONE = Config::get('app.timezone', 'UTC');
        
        if($this->TIMEZONE===null) $this->init();
        $tz=($this->TIMEZONE-$REF_TIMEZONE);
        $TIMEOFFSET=($tz*60*60);
        if(is_numeric($timestamp)) $t=$timestamp;
        else $t=@strtotime($timestamp);
            if($this->is_null_date($timestamp)) return $labels['unknown'];
        
        $diff=(time()+$TIMEOFFSET)-$t;
        
        $m=$diff/(60);
        if($m<60) return (int)($m).' '.$labels['minutes'];
        
        $h=$m/60;
        //if($h<2) return (int)($h).' '.$labels['hour'].' '.(int)($m/60).' '.$labels['minutes'];
        //else
        if($h<2) return (int)($h).' '.$labels['hour'];
        else
            if($h<48) return (int)($h).' '.$labels['hours'];
        
        $d=$h/24;
        if($d<24) return (int)($d).' '.$labels['days'];
        
        //
        $default=$labels['never'];
        
        $len=strlen($timestamp);
        
        $t=@strtotime($timestamp)+$TIMEOFFSET;
        
        $s="";
        {
            $m=date("m", $t);
            $d=date("d", $t);
            $y=date("Y", $t);
            $h=date("G", $t);
            $min=date("i", $t);
        }
        
        if($m>=1&&$m<=12) $s.=$this->printable_month($m);
        $s.="&nbsp;";
        if($d>=1&&$d<=32) $s.=sprintf('%02d', $d);
        $s.="&nbsp;";
        if($y>=0) $s.=sprintf('%04d', $y);
        
        $s.="&nbsp;";
        
        //
        if($h>=1&&$h<=12) $s.=sprintf('%02d', $h);
        else if($h>12&&$h<=24) $s.=sprintf('%02d', $h-12);
        else $s.="12";
            $s.=":";
        if($min>=0&&$min<=59) $s.=sprintf('%02d', $min);
        else $s.="60";
            
        $s.="&nbsp;";
        if($h>=0&&$h<=11) $s.="AM";
        else $s.="PM";
            
        return $s;
    }
    // --------------------------------------------------------------------
    /**
    *
    *
    @author: Lewis A. Sellers <lasellers@gmail.com>
    @date: 8/2012
    */
    private function is_null_date($timestamp)
    {
        if(is_object($timestamp)&&get_class($timestamp)=='Carbon\Carbon')
        {
            $obj=$timestamp;
            $timestamp=$obj->timestamp;
            $tz=$obj->tz;
        }
        $t=is_numeric($timestamp)?$timestamp:strtotime($timestamp);
        
        if(
        $timestamp==NULL
        ||$timestamp=='NULL'
        ||$t<=1
        ) return true;
        return false;
    }
    // --------------------------------------------------------------------
    /**
    *
    *
    @author: Lewis A. Sellers <lasellers@gmail.com>
    @date: 8/2012
    */
    private function printable_month($month)
    {
        if($this->language=='es')
        $months=array(
        "Enero","Febrero","Marzo","Abril","Mayo","Junio​​","Julio","Agosto","Septiembre","Octubre","Noviembre","Diciembre"
        );
        else
            $months=array(
        "January","February","March","April","May","June","July","August","September","October","November","December"
        );
        
        $month=(int)$month;
        if($month<1||$month>12) return '';
        return $months[$month-1];
    }
    // --------------------------------------------------------------------
    
    public static function get_string($timestamp)
    {
        $ts=new \Timesince();
        return $ts->get($timestamp);
    }
    // --------------------------------------------------------------------
    
    
    public static function valid_date_or_blank($date)
    {
        $t=strtotime($date);
        return $t<=1?'':date("Y-m-d",$t);
    }
    // --------------------------------------------------------------------
}