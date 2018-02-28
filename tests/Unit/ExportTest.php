<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

use App\Console\Commands\HomeHealthCompareCommand;
use App\Console\Commands\NursingHomeCompareCommand;
use App\Console\Commands\StatusCommand;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Application as ConsoleApplication;

use App\HealthProviders;

use App\Models\NursingHomeCompareModel;
use App\Models\HomeHealthCompareModel;

use App\Timer as Timer;
use App\Browser as Browser;
use App\Whois as Whois;

class ExportTest extends TestCase
{
    /**
    *
    * @return void
    */
	public function testTrue()
	{
		$this->assertTrue(true);
	}

    public function testGetHome()
    {
        $response = $this->get('/');
        $response->assertStatus(200);
    }
    
    public function testGetNHCCSV()
    {
        $this->assertTrue(file_exists(public_path().'/nhc.csv'));
   }

    public function testGetHHCCSV()
    {
        $this->assertTrue(file_exists(public_path().'/hhc.csv'));
    }
    
    public function testGetNHCHtml()
    {
        $this->assertTrue(file_exists(public_path().'/nhc.html'));
    }

    public function testGetHHCHtml()
    {
        $this->assertTrue(file_exists(public_path().'/hhc.html'));
    }

}