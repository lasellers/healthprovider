<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

use App\Console\Commands\HomeHealthCompareCommand;
use App\Console\Commands\NursingHomeCompareCommand;
use App\Console\Commands\StatusCommand;

use App\HealthProviders;

use App\Models\NursingHomeCompareModel;
use App\Models\HomeHealthCompareModel;

use App\Models\Timer;
use App\Models\TimeSince;
use App\Models\Whois;

class ExportTest extends TestCase
{
    /**
    *
    * @return void
    */
    public function testGetHome()
    {
        //        $this->assertTrue(true);
        $response = $this->get('/');
        $response->assertStatus(200);
    }
    
    public function testGetNHCCSV()
    {
        $this->assertTrue(file_exists(public_path().'/nhc.csv'));
        //        $this->assertTrue(true);
        //    $response = $this->get('nhc.csv');
        //  $response->assertStatus(200);
    }
    public function testGetHHCCSV()
    {
        $this->assertTrue(file_exists(public_path().'/hhc.csv'));
        //        $this->assertTrue(true);
        //        $response = $this->get('hhc.csv');
        //      $response->assertStatus(200);
    }
    
    public function testGetNHCHtml()
    {
        $this->assertTrue(file_exists(public_path().'/nhc.html'));
        //        $this->assertTrue(true);
        //        $response = $this->get('nhc.html');
        //      $response->assertStatus(200);
    }
    public function testGetHHCHtml()
    {
        $this->assertTrue(file_exists(public_path().'/hhc.html'));
        //        $response = $this->get('hhc.html');
        //      $response->assertStatus(200);
    }
    
    public function testHomeHealthCompareCommand()
    {
        $m=new HomeHealthCompareCommand();
        if(is_object($m)) {
            $this->assertTrue(true);
        } else {
            $this->assertTrue(fals);
        }
    }
    
    public function testNursingHomeCompareCommand()
    {
        $m=new NursingHomeCompareCommand();
        if(is_object($m)) {
            $this->assertTrue(true);
        } else {
            $this->assertTrue(fals);
        }
    }
    
    public function testStatusCommand()
    {
        $m=new StatusCommand();
        if(is_object($m)) {
            $this->assertTrue(true);
        } else {
            $this->assertTrue(fals);
        }
    }
    
    public function testHealthProviders()
    {
        $m=new HealthProviders();
        if(is_object($m)) {
            $this->assertTrue(true);
        } else {
            $this->assertTrue(fals);
        }
    }
    
    
    public function testNursingHomeCompareModel()
    {
        $m=new NursingHomeCompareModel();
        if(is_object($m)) {
            $this->assertTrue(true);
        } else {
            $this->assertTrue(fals);
        }
    }
    
    public function testHomeHealthCompareModel()
    {
        $m=new HomeHealthCompareModel();
        if(is_object($m)) {
            $this->assertTrue(true);
        } else {
            $this->assertTrue(fals);
        }
    }
    
    
    public function testTimer()
    {
        $m=new Timer();
        if(is_object($m)) {
            $this->assertTrue(true);
        } else {
            $this->assertTrue(fals);
        }
    }
    /*
    public function testWhois()
    {
        $m=new Whois();
        if(is_object($m)) {
            $this->assertTrue(true);
        } else {
            $this->assertTrue(fals);
        }
    }
    
    public function testTimesince()
    {
        $m=new Timesince();
        if(is_object($m)) {
            $this->assertTrue(true);
        } else {
            $this->assertTrue(fals);
        }
    }
    */
}