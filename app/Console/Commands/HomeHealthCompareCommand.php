<?php namespace App\Console\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Output\ConsoleInput;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Style\SymfonyStyle;

use Illuminate\Support\Facades\Log;

use App\Models\HomeHealthCompareModel as HomeHealthCompareModel;
use App\Models\NursingHomeCompareModel as NursingHomeCompareModel;

use App\HealthProviders as HealthProviders;

use App\Timer as Timer;
use App\Browser as Browser;
use App\Whois as Whois;


/**

Command to process https://data.medicare.gov/views/bg9k-emty/files/69v7QYRkrGAO5T0UZq6wA_vB85PYg5RWxUNb0dkM3w0?content_type=application%2Fzip%3B%20charset%3Dbinary&filename=HHCompare_Revised_FlatFiles.zip
//DMG_CSV_DOWNLOAD20150701

https://www.medicare.gov/download/downloaddb.asp

@author: Lewis Sellers lasellers@gmail.com
@date: 8/2015

**/

class HomeHealthCompareCommand extends Command
{
    //private $download_url="https://data.medicare.gov/views/bg9k-emty/files/69v7QYRkrGAO5T0UZq6wA_vB85PYg5RWxUNb0dkM3w0?content_type=application%2Fzip%3B%20charset%3Dbinary&filename=HHCompare_Revised_FlatFiles.zip";
    private $download_url="https://data.medicare.gov/views/bg9k-emty/files/aeee9175-6b47-4b0d-8c3c-2757ebb7db89?content_type=application%2Fzip%3B%20charset%3Dbinary&filename=HHCompare_Revised_FlatFiles.zip";
    private $download_zip="HHCompare_Revised_FlatFiles.zip";
    private $unzipped_folder="HHCompare_Revised_FlatFiles/";
    private $input_file="HHCompare_Revised_FlatFiles/HHC_SOCRATA_PRVDR.csv";
    private $output_file="HHC.csv";
    private $dup_file="HHC.dup.csv";
    private $excel_file="HHC.xls";
    private $html_file="HHC.html";
    
    private $timer;
    
    /**
    * The console command name.
    *
    * @var string
    */
    protected $name='healthprovider:hhc';
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
        $start=$this->argument("start");
        $options=$this->argument("options");
        $this->call_command($start,$options);
    }

    private $io;

    // --------------------------------------------------------------------
    /*
    *
    @author: Lewis Sellers lasellers@gmail.com
    @date: 8/2015
    */
    public function call_command($start='all',$options='')
    {
        $this->output->title($this->description);

        //DB::disableQueryLog();

        //$this->io = new ConsoleOutput();
        $this->io = new SymfonyStyle($this->input, $this->output);

        //
        $this->user_agent=Browser::get_default_user_agent();
            
            $this->timer=new Timer($this->io);
            $hp=new HealthProviders($this,$this->io);
            
            $this->download_zip=$hp->get_data_path().DIRECTORY_SEPARATOR.$this->download_zip;
            $this->unzipped_folder=$hp->get_data_path().DIRECTORY_SEPARATOR.$this->unzipped_folder;
            $this->input_file=$hp->get_data_path().DIRECTORY_SEPARATOR.$this->input_file;
            
            $this->output_file=public_path().DIRECTORY_SEPARATOR.$this->output_file;
            $this->dup_file=public_path().DIRECTORY_SEPARATOR.$this->dup_file;
            $this->html_file=public_path().DIRECTORY_SEPARATOR.$this->html_file;
            
            /* */
            if($start=='dup')
            {
                $hp=new HealthProviders($this);
                $hp->dup_csv($this->output_file,$this->dup_file);
                return;
        }
        
        /* */
        if($start=='') $start='all';
        if($start!='all') $start=intval($start);
        $this->output->note("Start=".$start);
        $this->output->note("Options=".$options);
        
        /* */
        $hp->get_csv($this->download_url,$this->download_zip,$this->unzipped_folder,$this->input_file);
        
        /* */
        $lookup=[];
        $line_index=1;
        $handle = fopen($this->input_file, "r");
        if (!$handle)
        {
            $this->output->error("Error: Could not open $this->input_file\n");
            return;
        }
        else {

            while (($line = fgets($handle)) !== false) {
                $line = trim($line);
                if ($line == "") continue;

                $data = str_getcsv($line, ",", "\"");
                if ($line_index == 1) {
                    $lookup = array_flip($data);

                    if ($start == 'all') {
                        $output_data = HealthProviders::pack_tsv([
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
                        $this->io->text($output_data);
                        file_put_contents($this->output_file, $output_data);
                        file_put_contents($this->html_file, "");
                    }
                } else {
                    if ($start == 'all' || $start == $line_index) {
                        if ($options == 'skipto' && $start != 'all') {
                            $start = 'all';
                        }

                        $this->io->text("");
                        $this->io->text(" **** HHC $line_index start=$start options=$options " . $this->timer->print_elapsed_time() . " " . $this->timer->memory_used() . " ****");
                        $this->io->text("");

                        $hp->reset();

                        $hp->state = $data[$lookup['State']];
                        $hp->id = $data[$lookup['CMS Certification Number (CCN)']];
                        $hp->name = $data[$lookup['Provider Name']];
                        $hp->address = $data[$lookup['Address']];
                        $hp->city = $data[$lookup['City']];
                        $hp->zip = $data[$lookup['Zip']];
                        $hp->phone = $data[$lookup['Phone']];
                        $hp->county = "";
                        //$hp->found_phone=$hp->phone;

                        $this->output->note(" ccn=" . $hp->id);
                        Log::info(" ccn=" . $hp->id);

                        $hp->get_data();

                        $emails = array_keys($hp->ordered_emails);
                        $domains = array_keys($hp->ordered_domains);

                        $output_data = \App\HealthProviders::pack_tsv([
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
                            implode(",", $emails),
                            implode(",", $hp->aux_urls),
                            $hp->home_url,
                            implode(",", $domains)
                        ]);

                        $html_data = HealthProviders::pack_html([
                            ($line_index),
                            $hp->id,
                            $hp->domain,
                            $hp->name,
                            $hp->phone,
                            $hp->city,
                            $hp->state,
                            $hp->zip,
                            $hp->email,
                            implode(",", $emails),
                            implode(",", $hp->aux_urls),
                            $hp->home_url,
                            implode(",", $domains)
                        ]);
                        if ($start == 'all') {
                            file_put_contents($this->output_file, $output_data, FILE_APPEND);
                            file_put_contents($this->html_file, $html_data, FILE_APPEND);
                        } else {
                            $this->io->text($output_data);
                        }

                    }
                }

                $line_index++;

                if ($start != 'all' && $line_index > $start)
                    break;
            }

            fclose($handle);
        }
    
    if($start=='all')
    {
        $this->io->text("");
        $this->io->text(" **** HHC complete ".$this->timer->print_elapsed_time()." ****");
        $this->io->text("");
    }
}

// --------------------------------------------------------------------

}