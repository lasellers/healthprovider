<?php namespace App\Console\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Output\ConsoleOutput;

use Illuminate\Support\Facades\Log;

/**

Command to process https://data.medicare.gov/views/bg9k-emty/files/69v7QYRkrGAO5T0UZq6wA_vB85PYg5RWxUNb0dkM3w0?content_type=application%2Fzip%3B%20charset%3Dbinary&filename=HHCompare_Revised_FlatFiles.zip
//DMG_CSV_DOWNLOAD20150701

https://www.medicare.gov/download/downloaddb.asp

@author: Lewis Sellers lasellers@gmail.com
@date: 8/2015

**/

class HomeHealthCompare extends \App\Console\Commands\DebugCommand
{
    private $download_url="https://data.medicare.gov/views/bg9k-emty/files/69v7QYRkrGAO5T0UZq6wA_vB85PYg5RWxUNb0dkM3w0?content_type=application%2Fzip%3B%20charset%3Dbinary&filename=HHCompare_Revised_FlatFiles.zip";
    private $download_zip="HHCompare_Revised_FlatFiles.zip";
    private $unzipped_folder="HHCompare_Revised_FlatFiles/";
    private $input_file="HHCompare_Revised_FlatFiles/HHC_SOCRATA_PRVDR.csv";
    private $output_file="HHC.csv";
    private $dup_file="HHC.dup.csv";
    private $excel_file="HHC.xls";
    private $html_file="HHC.html";
    
    private $t=null;
    
    /**
    * The console command name.
    *
    * @var string
    */
    protected $name='homehealthcompare';
    /**
    * The console command description.
    *
    * @var string
    */
    protected $description='HomeHealthCompare';
    // --------------------------------------------------------------------
    /**
    * Get the console command arguments.
    *
    * @return array
    */
    protected function getArguments()
    {
        return array(
        array('start',InputArgument::OPTIONAL,'blank, n or all (or dup)'),
        array('options',InputArgument::OPTIONAL,'blank or skipto'),
        );
    }
    // --------------------------------------------------------------------
    /**
    * Get the console command options.
    *
    * @return array
    */
    protected function getOptions()
    {
        return array(
        );
    }
    // --------------------------------------------------------------------
    /**
    * Create a new command instance.
    *
    * @return void
    */
    public function __construct()
    {
        parent::__construct();
        
    }
    // --------------------------------------------------------------------
    public function fire()
    {
        $this->print_args();
        $start=$this->argument("start");
        $options=$this->argument("options");
        $this->call_command($start,$options);
    }
    // --------------------------------------------------------------------
    /*
    *
    @author: Lewis Sellers lasellers@gmail.com
    @date: 8/2015
    */
    public function call_command($start='all',$options='')
    {
        //DB::disableQueryLog();
        
        $stdout = new ConsoleOutput();
        
        //\App\Models\HealthProviders::adjust_memory();
        
        //
        $this->user_agent=\App\Models\Browser::get_default_user_agent();
            
            $this->print_system();
            
            
            $this->t=new \App\Models\Timer();
            $hp=new \App\Models\HealthProviders($this);
            
            
            $this->download_zip=$hp->get_data_path().DIRECTORY_SEPARATOR.$this->download_zip;
            $this->unzipped_folder=$hp->get_data_path().DIRECTORY_SEPARATOR.$this->unzipped_folder;
            $this->input_file=$hp->get_data_path().DIRECTORY_SEPARATOR.$this->input_file;
            
            $this->output_file=public_path().DIRECTORY_SEPARATOR.$this->output_file;
            $this->dup_file=public_path().DIRECTORY_SEPARATOR.$this->dup_file;
            $this->html_file=public_path().DIRECTORY_SEPARATOR.$this->html_file;
            
            /* */
            if($start=='dup')
            {
                $hp=new \App\Models\HealthProviders($this);
                $hp->dup_csv($this->output_file,$this->dup_file);
                return;
        }
        
        /* */
        if($start=='') $start='all';
        if($start!='all') $start=intval($start);
        $this->info("Start=".$start);
        $this->info("Options=".$options);
        
        
        /* */
        $hp->get_csv($this->download_url,$this->download_zip,$this->unzipped_folder,$this->input_file);
        
        /* */
        $lookup=[];
        $line_index=1;
        $handle = fopen($this->input_file, "r");
        if (!$handle)
        {
            $this->error("Error: Could not open $this->input_file\n");
            return;
        }
        
        while (($line = fgets($handle)) !== false)
        {
            $line=trim($line);
            if($line=="") continue;
            
            $data=str_getcsv($line, ",","\"");
            if($line_index==1)
            {
                $lookup=array_flip($data);
                
                if($start=='all')
                {
                    $output_data=\App\Models\HealthProviders::pack_tsv([
                    "#",
                    "CCN",
                    "name",
                    "phone",
                    //"found_phone",
                    "city",
                    "state",
                    "zip",
                    "url",
                    "domain",
                    "email",
                    "emails",
                    "aux_urls",
                    "home_url",
                    "domains"
                    ]);
                    $this->line($output_data);
                    file_put_contents($this->output_file, $output_data);
                    file_put_contents($this->html_file, "");
                }
            }
            else
            {
                if($start=='all'||$start==$line_index)
                {
                    if($options=='skipto'&&$start!='all')
                    {
                        $start='all';
                    }
                    
                    $stdout->writeln("");
                    $stdout->writeln(" **** HHC $line_index start=$start options=$options ".$this->t->print_elapsed_time()." ".$this->t-> memory_used()." ****");
                    $stdout->writeln("");
                    
                    $hp->reset();
                    
                    $hp->state=$data[$lookup['State']];
                    $hp->id=$data[$lookup['CMS Certification Number (CCN)']];
                    $hp->name=$data[$lookup['Provider Name']];
                    $hp->address=$data[$lookup['Address']];
                    $hp->city=$data[$lookup['City']];
                    $hp->zip=$data[$lookup['Zip']];
                    $hp->phone=$data[$lookup['Phone']];
                    $hp->county="";
                    //$hp->found_phone=$hp->phone;
                    
                    $this->info(" ccn=".$hp->id);
                    Log::info(" ccn=".$hp->id);
                    
                    $hp->get_data();
                    
                    $emails=array_keys($hp->ordered_emails);
                    $domains=array_keys($hp->ordered_domains);
                    
                    $output_data=\App\Models\HealthProviders::pack_tsv([
                    ($line_index),
                    $hp->id,
                    $hp->name,
                    $hp->phone,
                    //$hp->found_phone,
                    $hp->city,
                    $hp->state,
                    $hp->zip,
                    $hp->url,
                    $hp->domain,
                    $hp->email,
                    implode(",",$emails),
                    implode(",",$hp->aux_urls),
                    $hp->home_url,
                    implode(",",$domains)
                    ]);
                    
                    $html_data=\App\Models\HealthProviders::pack_html([
                    ($line_index),
                    $hp->id,
                    $hp->domain,
                    $hp->name,
                    $hp->phone,
                    $hp->city,
                    $hp->state,
                    $hp->zip,
                    $hp->email,
                    implode(",",$emails),
                    implode(",",$hp->aux_urls),
                    $hp->home_url,
                    implode(",",$domains)
                    ]);
                    if($start=='all')
                    {
                        file_put_contents($this->output_file, $output_data,FILE_APPEND);
                        file_put_contents($this->html_file, $html_data,FILE_APPEND);
                    }
                    else
                    {
                        $this->line($output_data);
                    }
                    
                }
            }
            
            $line_index++;
            
            if($start!='all'&&$line_index>$start)
            break;
    }
    
    fclose($handle);
    
    if($start=='all')
    {
        $stdout->writeln("");
        $stdout->writeln(" **** HHC complete ".$this->t->print_elapsed_time()." ****");
        $stdout->writeln("");
    }
}

// --------------------------------------------------------------------

}