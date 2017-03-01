<?php namespace App\Models;
/**

12262 + 15565 = 27827
config settings:
scraper.cache_only
scraper.min_sleep_between_runs
scraper.sleep_between_runs

@author: Lewis A. Sellers <lasellers@gmail.com>
@date: 8/2015

**/

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Output\ConsoleOutput;

class SimpleCurl extends \App\Console\Commands\DebugCommand
{
    private $config=null;
    private $cookie_file="";
    private $min_sleep=0;
    private $sleep=0;
    private $redirect=true;
    private $send_header=[];
    private $cookies=null;
    private $domain='';
    private $user_agent='';
    private $referer='';
    private $verbose=true;
    
    public $info=null;
    public $log="";
    public $timeout=15; /*30;*/
    public $min_raw_size=256;
    public $max_raw_size=4000000;
    
    // --------------------------------------------------------------------
    /*
    
    @author: Lewis A. Sellers <lasellers@gmail.com>
    @date: 10/2013-12/2013
    */
    
    public function __construct($config=null)
    {
        $this->config=$config;
        
        $this->min_sleep=\Config::get('simplecurl.min_sleep_between_runs');
        $this->sleep=\Config::get('simplecurl.sleep_between_runs');
        
        if(isset($config['min_sleep'])) $this->min_sleep=$config['min_sleep'];
        if(isset($config['sleep'])) $this->sleep=$config['sleep'];
        
        if(isset($config['redirect'])) $this->redirect=$config['redirect'];
        if(isset($config['domain'])) $this->domain=$config['domain'];
        
        $hash=hash("sha256",$this->domain);
        $this->cookie_file=sys_get_temp_dir().DIRECTORY_SEPARATOR."SimpleCurl.cookies.$hash.txt";
        $this->log="";
    }
    
    
    /**
    */
    public function get_path() : string
    {
        $path=sys_get_temp_dir().DIRECTORY_SEPARATOR ."cache".DIRECTORY_SEPARATOR;
        if(!file_exists($path))
        {
            mkdir($path);
        }
        return $path;
    }
    
    public function get_log_path() : string
    {
        $path=sys_get_temp_dir().DIRECTORY_SEPARATOR ."logs".DIRECTORY_SEPARATOR;
        if(!file_exists($path))
        {
            mkdir($path);
        }
        return $path;
    }
    
    // --------------------------------------------------------------------
    
    public function sleep_between_calls()
    {
        $sleep=rand($this->min_sleep,$this->sleep);
        if($sleep>0)
        {
            \App\CLI::info(" --- Sleeping...".$sleep." ---");
            sleep($sleep);
        }
    }
    // --------------------------------------------------------------------
    
    public function set_verbose($verbose)
    {
        $this->verbose=$verbose;
    }
    
    // --------------------------------------------------------------------
    public function set_sleep($sleep)
    {
        $this->sleep=$sleep;
    }
    
    // --------------------------------------------------------------------
    
    public function get_sleep()
    {
        return $this->sleep;
    }
    // --------------------------------------------------------------------
    
    public function set_redirect($redirect)
    {
        $this->redirect=($redirect===true);
    }
    
    // --------------------------------------------------------------------
    
    public function get_redirect()
    {
        return $this->redirect;
    }
    
    // --------------------------------------------------------------------
    
    public function set_send_header($header)
    {
        $this->send_header=$header;
    }
    
    // --------------------------------------------------------------------
    
    public function set_user_agent($user_agent)
    {
        $this->user_agent=$user_agent;
    }
    
    // --------------------------------------------------------------------
    
    public function set_referer($referer)
    {
        $this->referer=$referer;
    }
    
    // --------------------------------------------------------------------
    public function set_timeout($timeout)
    {
        $this->timeout=$timeout;
    }
    
    // --------------------------------------------------------------------
    
    public function get_timeout()
    {
        return $this->timeout;
    }
    
    // --------------------------------------------------------------------
    public function download_file($url,$local_file)
    {
        if(\Config::get('simplecurl.cache_only')) return null;
        
        \App\CLI::error(" >>> download_file: url=$url local_file=$local_file");
        
        try {
            $curl = curl_init($url);
        }
        catch(Exception $e)
        {
            return ['error'=>'curl_init failure: '.$e->getMessage()];
        }
        
        curl_setopt ($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl,CURLOPT_TIMEOUT,$this->timeout);
        if($this->redirect)
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
        $data = curl_exec ($curl);
        file_put_contents($local_file, $data);
        unset($data);
        
        $filetime = curl_getinfo($curl, CURLINFO_FILETIME);
        if($filetime==-1) $filetime=time();
        touch($local_file,$filetime);
        
        curl_close($curl);
        
        \App\CLI::error(" >>> download_file: url=$url file time=$filetime size=".filesize($local_file));
        
        $this->sleep_between_calls();
    }
    
    // --------------------------------------------------------------------
    public function get_file($url)
    {
        if(\Config::get('simplecurl.cache_only')) return null;
        
        \App\CLI::line("get_file: url=$url");
        
        $curl = curl_init($url);
        curl_setopt ($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl,CURLOPT_TIMEOUT,$this->timeout);
        if($this->redirect)
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
        
        try {
            \App\CLI::error(" <<< get_file: fetching... $url");
            
            $data = curl_exec ($curl);
        } catch(Exception $e)
        {
            \App\CLI::error(" error: ".curl_error($curl));
            return ['error'=>'curl_exec failure: '.$e->getMessage()];
        }
        if(curl_errno($curl))
        {
            \App\CLI::error(" error: ".curl_error($curl));
            return ['error'=>'Curl error: '.curl_error($curl)];
        }
        
        curl_close($curl);
        
        $this->comment("get_file: url=$url size=".strlen($data));
        
        $this->sleep_between_calls();
        return $data;
    }
    
    /* -------------------------------------------------------------------- */
    public function get_url($url,$cache=null,$options=null)
    {
        /* */
        if($cache!=null)
        {
            try {
                $cache_file=$this->get_path()."$cache";
                
                if(is_array($options)&&isset($options['headers']))
                {
                    $a=explode(".",$cache_file);
                    $ext=array_pop($a);
                    $cache_header_file=implode(".",$a).".header.$ext";
                    \App\CLI::line("  get_url with header: cache_file $cache_file  cache_header_file=$cache_header_file ...");
                    if(file_exists($cache_header_file)&&file_exists($cache_file))
                    {
                        \App\CLI::info(" <<< get_url: [cached] reading url=$url cache_file=$cache_file cache_header_file=$cache_header_file ...");
                        if(filesize($cache_file)>$this->max_raw_size) return [null,null,[]];
                        if(filesize($cache_file)>$this->min_raw_size)
                        {
                            return array(
                            file_get_contents($cache_header_file),
                            file_get_contents($cache_file),
                            array()
                            );
                        }
                    }
                }
                else
                {
                    \App\CLI::line("  get_url: cache_file $cache_file ...");
                    if(file_exists($cache_file))
                    {
                        if(filesize($cache_file)>$this->max_raw_size) return null;
                        if(filesize($cache_file)>$this->min_raw_size)
                        {
                            \App\CLI::info(" <<< get_url: [cached] reading url=$url cache_file $cache_file ... size=".filesize($cache_file));
                            return file_get_contents($cache_file);
                        }
                    }
                }
                
            } catch(Exception $e)
            {
                
                return ['error'=>'get_url cache failure: '.$e->getMessage()];
            }
        }
        
        /* */
        if(\Config::get('simplecurl.cache_only')) return null;
        
        /* */
        try {
            $curl = curl_init($url);
        }
        catch(Exception $e)
        {
            return ['error'=>'curl_init failure: '.$e->getMessage()];
        }
        
        curl_setopt ($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl,CURLOPT_TIMEOUT,$this->timeout);
        
        if($this->redirect)
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
        
        /*  curl_setopt($curl, CURLOPT_VERBOSE, 1);*/
        curl_setopt($curl, CURLOPT_HEADER, 1);
        
        /* */
        curl_setopt ($curl, CURLOPT_COOKIEJAR,  $this->cookie_file);
        curl_setopt ($curl, CURLOPT_COOKIEFILE,  $this->cookie_file);
        
        /* */
        if($this->user_agent!=''&&$this->user_agent!=NULL)
        {
            curl_setopt($curl, CURLOPT_USERAGENT, $this->user_agent);
        }
        
        if($this->referer!=''&&$this->referer!=NULL)
        {
            curl_setopt($curl, CURLOPT_REFERER, $this->referer);
        }
        curl_setopt($curl, CURLOPT_AUTOREFERER, true);
        
        /* */
        if(is_array($this->send_header))
        {
            $send_header=$this->send_header;
            $send_header[]='Expect:';
            curl_setopt($curl, CURLOPT_HTTPHEADER, $send_header);
        }
        
        /* */
        try {
            \App\CLI::error(" <<< get_url: fetching... $url");
            $response = curl_exec ($curl);
        } catch(Exception $e)
        {
            \App\CLI::error(" error(1): ".curl_error($curl));
            return ['error'=>'curl_exec failure: '.$e->getMessage()];
        }
        /* */
        if(curl_errno($curl))
        {
            $total_time = curl_getinfo($curl, CURLINFO_TOTAL_TIME);
            
            \App\CLI::line(" # total_time:  $total_time");
            
            if($total_time>$this->timeout-1)
            {
                \App\CLI::line("");
                \App\CLI::line(" ##################################### ");
                \App\CLI::line(" ##################################### ");
                \App\CLI::line(" ##################################### ");
                \App\CLI::info(" *** slow namelookup ***");
                \App\CLI::line(" ##################################### ");
                \App\CLI::line(" ##################################### ");
                \App\CLI::line(" ##################################### ");
                \App\CLI::line("");
                sleep(5);
                return ['error'=>'Slow name lookup','status'=>'namelookup'];
            }
            
            \App\CLI::error(" error(2): ".curl_error($curl));
            return ['error'=>'Curl error: '.curl_error($curl),'status'=>'error'];
        }
        
        /* */
        $header_size = curl_getinfo($curl, CURLINFO_HEADER_SIZE);
        
        $total_time = curl_getinfo($curl, CURLINFO_TOTAL_TIME);
        $namelookup_time = curl_getinfo($curl, CURLINFO_NAMELOOKUP_TIME);
        $connect_time = curl_getinfo($curl, CURLINFO_CONNECT_TIME);
        $pretransfer_time = curl_getinfo($curl, CURLINFO_PRETRANSFER_TIME);
        $starttransfer_time = curl_getinfo($curl, CURLINFO_STARTTRANSFER_TIME);
        
        /* */
        curl_close($curl);
        
        /* */
        \App\CLI::line(" # total_time:  $total_time");
        \App\CLI::line(" # namelookup_time:  $namelookup_time");
        \App\CLI::line(" # connect_time:  $connect_time");
        \App\CLI::line(" # pretransfer_time:  $pretransfer_time");
        \App\CLI::line(" # starttransfer_time:  $starttransfer_time");
        
        if($total_time>$this->timeout-1)
        {
            \App\CLI::line("");
            \App\CLI::line("");
            \App\CLI::info(" *** slow transfer ***");
            \App\CLI::line("");
            \App\CLI::line("");
            return "";
        }
        
        /* */
        $header = substr($response, 0, $header_size);
        $body = substr($response, $header_size);
        
        /* */
        if($cache!=null&&isset($cache_file))
        {
            file_put_contents($cache_file, $body);
        }
        if(is_array($options)&&isset($options['headers']))
        {
            if($cache!=null&&isset($cache_header_file))
            {
                file_put_contents($cache_header_file, $header);
            }
        }
        \App\CLI::error(" >>> get_url: writing $url size=".strlen($body));
        
        /* */
        $this->sleep_between_calls();
        
        if(is_array($options)&&isset($options['headers']))
        {
            return [$header,$body,$headers];
        }
        else
        {
            return $body;
        }
        /* */
    }
    
    // --------------------------------------------------------------------
    
    public function retrieve_remote_file_last_modified_time($url)
    {
        if(\Config::get('simplecurl.cache_only')) return null;
        
        $ch = curl_init($url);
        
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($curl, CURLOPT_HEADER, TRUE);
        curl_setopt($curl, CURLOPT_NOBODY, TRUE);
        curl_setopt($curl, CURLOPT_FILETIME, TRUE);
        curl_setopt($curl, CURLOPT_TIMEOUT,$this->timeout);
        
        $data = curl_exec($ch);
        $filetime = curl_getinfo($ch, CURLINFO_FILETIME);
        
        curl_close($ch);
        return $filetime;
    }
    // --------------------------------------------------------------------
    
    public function download_file_if_modified($url,$file)
    {
        if(\Config::get('simplecurl.cache_only')) return null;
        
        $rt=SimpleCurl::retrieve_remote_file_last_modified_time($url);
        $lt=file_exists($file)?filemtime($file):0;
        
        if($lt==-1)
        {
            touch($file,$rt);
            $lt=$rt;
        }
        
        if($lt<$rt)
        {
            if($this->verbose)
            \App\CLI::line("Get file=$file url=$url");
            
            $curl = curl_init($url);
            curl_setopt ($curl, CURLOPT_URL, $url);
            
            // set to download directly to the file, so we can download an size file...
            $fp = fopen($file, 'w');
            curl_setopt($curl, CURLOPT_FILE, $fp);
            
            curl_exec ($curl);
            
            //
            touch($file,$rt);
            
            // Check if any error occurred
            if(!curl_errno($curl))
            {
                $info = curl_getinfo($curl);
                $this->info=$info;
                if($this->verbose)
                \App\CLI::line('Took ' . $info['total_time'] . ' seconds to send a request to ' . $info['url']);
                if($this->verbose)
                \App\CLI::line(" CURL >>> size=".$info['size_download']."");
            }
            
            //
            curl_close ($curl);
            fclose($fp);
            
            //
            $this->sleep_between_calls();
            return true;
        }
        
        if($this->verbose)
        \App\CLI::line("Skipped file=$file url=$url");
        return false;
    }
    
    // --------------------------------------------------------------------
    public function post_form($url,$fields,$cache=null,$options=null)
    {
        if(\Config::get('simplecurl.cache_only')) return null;
        
        if($this->verbose)
        \App\CLI::line("post_form: url=$url");
        
        $curl = curl_init($url);
        curl_setopt ($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        if($this->redirect)
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($curl,CURLOPT_TIMEOUT,$this->timeout);
        
        //curl_setopt($curl, CURLOPT_VERBOSE, 1);
        curl_setopt($curl, CURLOPT_HEADER, 1);
        
        curl_setopt ($curl, CURLOPT_COOKIEJAR, $this->cookie_file);
        curl_setopt ($curl, CURLOPT_COOKIEFILE, $this->cookie_file);
        
        $fields_string=http_build_query($fields);
        //
        curl_setopt($curl,CURLOPT_POST, true);
        curl_setopt($curl,CURLOPT_POSTFIELDS, $fields_string);
        
        //
        if($this->user_agent!=''&&$this->user_agent!=NULL)
        {
            curl_setopt($curl, CURLOPT_USERAGENT, $this->user_agent);
        }
        
        if($this->referer!=''&&$this->referer!=NULL)
        {
            curl_setopt($curl, CURLOPT_REFERER, $this->referer);
        }
        curl_setopt($curl, CURLOPT_AUTOREFERER, true);
        
        //
        if(is_array($this->send_header))
        {
            $send_header=$this->send_header;
            $send_header[]='Expect:';
            \CLI::print_r($send_header,"send_header");
            curl_setopt($curl, CURLOPT_HTTPHEADER, $send_header);
        }
        
        //
        $response = curl_exec ($curl);
        
        //
        if(curl_errno($curl))
        {
            $this->abort('Curl error: '.curl_error($curl));
            return null;
        }
        
        //
        $header_size = curl_getinfo($curl, CURLINFO_HEADER_SIZE);
        $header = substr($response, 0, $header_size);
        $body = substr($response, $header_size);
        
        //
        curl_close($curl);
        
        $this->comment("post_form($url,$cache) size=".strlen($body));
        $this->sleep_between_calls();
        
        if(is_array($options)&&isset($options['headers']))
        {
            //$this->debug_print_r($headers,"headers");
            return array($header,$body,$headers);
        }
        else
            return $body;
    }
    
    // --------------------------------------------------------------------
    
    public function uncache($cache=null)
    {
        if($cache!=null&&\Config::get("app.debug")===true)
        {
            $cache_file=$this->get_path()."$cache";
            
            $a=explode(".",$cache_file);
            $ext=array_pop($a);
            $cache_header_file=implode(".",$a).".header.$ext";
            
            if(file_exists($cache_file))
            {
                unlink($cache_file);
            }
            if(file_exists($cache_header_file))
            {
                unlink($cache_header_file);
            }
        }
    }
    
    // --------------------------------------------------------------------
    
    public function unpack($unpack_folder,$file)
    {
        if($this->verbose)
        \App\CLI::line(" unpack($unpack_folder,$file)");
        
        $ext = pathinfo($file, PATHINFO_EXTENSION);
        if($this->verbose)
        \App\CLI::line(" UNPACK ($ext) $unpack_folder $file...");
        if ('zip'==$ext)
        {
            $cmd="unzip -o \"$unpack_folder/$file\" -d \"$unpack_folder/\"";
        }
        else if('gz'==$ext)
        {
            $cmd="gunzip -v -d -f \"$unpack_folder/$file\"";
        }
        if(isset($cmd))
        {
            if($this->verbose)
            \App\CLI::line(" Shell command >> $cmd");
            $output=system($cmd, $status);
            if($this->verbose)
            \App\CLI::line(" UNPACK output >>> $output");
        }
    }
    
    // --------------------------------------------------------------------
    
    public static function save_log($file,$data)
    {
        $base=$this->get_log_path();
        if(is_array($data)||is_object($data))
        file_put_contents($base.$file, var_export($data, true));
        else
            file_put_contents($base.$file, $data);
    }
    
    // --------------------------------------------------------------------
    
    public static function save_array_as_config($file,$config)
    {
        file_put_contents($file,  "<"."?php\n"."return ".var_export($config, true)."; ?".">");
    }
    
    
    // --------------------------------------------------------------------
    /* Low-memory version of save_array_as_config for simple arrays */
    public static function save_simple_array_as_config($file,&$config)
    {
        file_put_contents($file,  "<"."?"."php\n"."return array(");
        foreach($config as $k=>$v)
        {
            file_put_contents($file,  "$k=>'$v',",FILE_APPEND);
        }
        file_put_contents($file,  "); ?".">",FILE_APPEND);
    }
    
    // --------------------------------------------------------------------
    
    public static function get_headers($header)
    {
        $headers[]=array();
        
        $ha=explode("\n",$header);
        foreach($ha as $line)
        {
            $a=explode(":",$line);
            if(count($a)<=1)
            {
                $headers[]=$line;
            }
            else
            {
                $headers[]=$line;
                $name=trim(array_shift($a));
                $headers[$name]=trim(implode(":",$a));
            }
        }
        return $headers;
    }
    // --------------------------------------------------------------------
    
    public static function get_location()
    {
        $location=$self::get_header_key('Location');
        
        return urldecode($location);
    }
    
    // ------------------------------------------------------------------------
    
    public static function get_header_key($search_key='Location',$header)
    {
        $lines=explode("\n",$header);
        
        if(count($lines)==0) return null;
        foreach($lines as $line)
        {
            $a=explode(":",$line);
            if(count($a)>=2)
            {
                $key=trim(array_shift($a));
                $value=trim(implode(":",$a));
                if(strtolower($search_key)==strtolower($key))
                {
                    return $value;
                }
            }
        }
        return null;
    }
    // ------------------------------------------------------------------------
    
    
    public static function remove_protocol($url)
    {
        return str_replace(array('http://','https://'), '', $url);
    }
    // --------------------------------------------------------------------
    
    public function extract_cookies_from_header_lines($lines)
    {
        $this->cookies=array();
        if(!is_array($lines)) $lines=explode("\n",$lines);
        foreach($lines as $line)
        {
            $a=explode(":", $line);
            if(count($a)>=2)
            {
                $k=array_shift($a);
                $v=trim(implode(":",$a));
                if($k=='Set-Cookie'||$k=='Cookie')
                {
                    list($c)=explode(";", $v);
                    list($ck,$cv)=explode("=", trim($c), 2);
                    $this->cookies[trim($ck)]=trim($cv);
                }
            }
        }
        return $this->cookies;
    }
    
    // ------------------------------------------------------------------------
    
    public function get_cookies()
    {
        if(!isset($this->cookies))
        return null;
        return $this->cookies;
    }
    // ------------------------------------------------------------------------
    public function reset_cookies()
    {
        if(file_exists($this->cookie_file)) unlink($this->cookie_file);
    }
    
    // ------------------------------------------------------------------------
    
}