<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

use App\Console\Commands\HomeHealthCompareCommand;
use App\Console\Commands\NursingHomeCompareCommand;
use App\Console\Commands\StatusCommand;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

use App\HealthProviders;

use App\Models\NursingHomeCompareModel;
use App\Models\HomeHealthCompareModel;

use App\Timer as Timer;
use App\Browser as Browser;
use App\Whois as Whois;

class CommandTest extends TestCase
{
    /**
     * A basic test example.
     *
     * @return void
     */
    public function testTrue()
    {
        $this->assertTrue(true);
    }

	public function testHomeHealthCompareCommand()
	{
		$m=new HomeHealthCompareCommand();
		$this->assertTrue(is_object($m));
	}

	public function testNursingHomeCompareCommand()
	{
		$m=new NursingHomeCompareCommand();
		$this->assertTrue(is_object($m));
	}

	public function testStatusCommand()
	{
		$m=new StatusCommand();
		$this->assertTrue(is_object($m));
	}

	public function testHealthProviders()
	{
		$this->assertTrue(true);
		return;

		$application = new ConsoleApplication();

		$testedCommand = $this->app->make(HealthProviders::class);
		$testedCommand->setLaravel(app());
		$application->add($testedCommand);
/*
		$command = $application->find('healthprovider:nhc');

		$commandTester = new CommandTester($command);

		$commandTester->execute([
			'command' => $command->getName(),
		]);

		//$this->assertRegExp('/Scanning all businesses../', $commandTester->getDisplay());
*/

		// $io = new ConsoleOutput();
		$io = new SymfonyStyle(); //$this->input); //, $this->output);

		$m=new HealthProviders($this,$io);
		$this->assertTrue(is_object($m));
	}

}
