<?php namespace App\Console\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Style\SymfonyStyle;

use Illuminate\Support\Facades\Log;

use App\Models\HomeHealthCompareModel as HomeHealthCompareModel;
use App\Models\NursingHomeCompareModel as NursingHomeCompareModel;

use App\HealthProviders as HealthProviders;

use App\Timer as Timer;
use App\Browser as Browser;
use App\Whois as Whois;


class StatusCommand extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'healthprovider:status';
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Status';
    // --------------------------------------------------------------------

    /**
     * Get the console command arguments.
     *
     * @return array
     */
    protected function getArguments()
    {
        return array(
            array('options', InputArgument::OPTIONAL, 'blank or name'),
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
        return array();
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
        $options = $this->argument("options");

        $this->call_command($options);
    }
    // --------------------------------------------------------------------
    /*
    *
    */
    public function call_command($options = '')
    {
        $this->output->title($this->description);

        $rows = HomeHealthCompareModel::all()->toArray();

        $this->output->table(
            array_keys($rows[0]),
            array_values($rows)
        );

    }

    // --------------------------------------------------------------------

}