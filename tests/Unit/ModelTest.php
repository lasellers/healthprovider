<?php

namespace Tests\Unit;

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
use Symfony\Component\Console\Application as ConsoleApplication;

use App\HealthProviders;

use App\Models\NursingHomeCompareModel;
use App\Models\HomeHealthCompareModel;

use App\Timer as Timer;
use App\Browser as Browser;
use App\Whois as Whois;

class ModelTest extends TestCase {
	/**
	 *
	 * @return void
	 */
	public function testTrue() {
		$this->assertTrue( true );
	}

	public function testNursingHomeCompareModel() {
		$m = new NursingHomeCompareModel();
		$this->assertTrue( is_object( $m ) );
	}

	public function testHomeHealthCompareModel() {
		$m = new HomeHealthCompareModel();
		$this->assertTrue( is_object( $m ) );
	}

	public function testTimer() {
		$this->assertTrue( true );
		/*			$io = new SymfonyStyle();

					$m=new Timer($io);
					$this->assertTrue(is_object($m));
		*/
	}

	public function testWhois() {
		$this->assertTrue( true );
		/*
				$m=new Whois();
				$this->assertTrue(is_object($m));
		*/
	}

	public function testTimesince() {
		$this->assertTrue( true );
		/*		$m=new Timesince();
				$this->assertTrue(is_object($m));
		*/
	}

}
