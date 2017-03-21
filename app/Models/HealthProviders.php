<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Symfony\Component\Console\Output\ConsoleOutput;

use Monolog\Logger;
use Monolog\Handler\StreamHandler;

/**

Main utility model for NursingHomeCompare,HomeHealthCompare, etc.

@author: Lewis Sellers lasellers@gmail.com
@date: 8/2015

**/

class HealthProviders {
    private $domain_rejects=[
    "skillednursingfacilities.org",
    "skillednursingfacilities.com",
    "yelp.com",
    "facebook.com",
    "plus.google.com",
    "google.com",
    "bing.com",
    "imdb.com",
    "hospital-data.com",
    "health.usnews.com",
    "bbb.org",
    "indeed.com",
    "yellowpages.com",
    "nursinghomesite.com",
    "ourparents.com",
    "healthcare.com",
    "seniorcarehomes.com",
    "nursing-homes.healthgrove.com",
    "cms.com",
    "money.usnews.com",
    "bestlawfirms.usnews.com",
    "local-nursing-homes.com",
    "retirenet.com",
    "npidb.org",
    "health.usnews.com",
    "homefacts.com",
    "nursinghomerating.org",
    "myseniorcare.com",
    "anikawellness.org",
    "ucomparehealthcare.com",
    "manta.com",
    "api.citygridmedia.com",
    "citygridmedia.com",
    "local-nursing-homes.com",
    "local.yahoo.com",
    "bbb.com",
    "ourparents.com",
    "ucomparehealthcare.com",
    "homehealthcareagencies.com",
    "hipaaspace.com",
    "indeed.com",
    "yellowpages.com",
    "agingcare.com",
    "carebulletin.com",
    "superpages.com",
    "carepathways.com",
    "domains.googlesyndication.com",
    "providerdata.com"
    ];
    
    private $email_rejects=[
    "junkmailprevention.org",
    "networksolutionsprivateregistration.com",
    "domaindiscreet.com",
    "findyourdomain.com",
    "domainsbyproxy.com",
    "worldnic.com",
    "bluehost.com",
    "whoisguard.com",
    "domainlistingagent.com",
    "privacyprotect.org",
    "yp.com",
    "domainprivacygroup.com",
    "bizapedia@whoisproxy.org",
    "contactprivacy.com",
    "protecteddomainservices.com",
    "1and1-private-registration.com",
    "hostmonster.com",
    "whois.gkg.net",
    "whois.tigertech.net",
    "myprivateregistration.com",
    "whoisprivacyprotect.com",
    "privacy.no-ip.com",
    "whoisproxy.org",
    "dns-protect.net",
    "proxy.dreamhost.com",
    "myprivacy.net",
    "nameprivacy.com",
    "network-support.com",
    "hugedomains.com",
    //"gandi.net"
    ];
    
    private $email_prefix_rejects=[
    "whois"
    ];
    
    private $SERP_MAX_LISTINGS=3;
    private $SCRAPER_TIMEOUT=20;
    private $SCRAPER_LONG_TIMEOUT=480;
    
    // --------------------------------------------------------------------
    
    private $user_agent;
    
    // --------------------------------------------------------------------
    
    public $id;
    public $name;
    public $address;
    public $city;
    public $zip;
    public $state;
    public $phone;
    public $found_phone;
    public $url;
    public $domain;
    public $email;
    
    public $domains;
    public $emails;
    
    public $ordered_domains;
    public $ordered_emails;
    
    public $home_url;
    public $aux_urls;
    
    private $scraper;
    private $dom;
    private $dom2;
    private $whois;
    private $xpath;
    private $xpath2;
    
    // we allow mass assignment of the following fields...
    protected $fillable = array(
    'id',
    'name',
    'address',
    'city',
    'zip',
    'state',
    'phone',
    'found_phone',
    'url',
    'domain',
    'email',
    'domains',
    'emails',
    'ordered_domains',
    'ordered_emails',
    'home_url',
    'aux_urls',
    );
    
    public function get_invalid_domains_path()
    {
        return public_path().DIRECTORY_SEPARATOR."invalid_domains.txt";
    }
    public function get_data_path()
    {
        $path=sys_get_temp_dir().DIRECTORY_SEPARATOR."healthprovider_data";
        if(!file_exists($path))
        {
            mkdir($path);
        }
        
        return $path;
    }
    
    /**
    
    **/
    public static function pack_tsv($a)
    {
        return '"'.implode('"'."\t".'"',$a).'"'."\n";
    }
    public static function pack_html($a)
    {
        return '<a href=\"'.$a[2].'\">'.$a[2].'</a> '.implode(' | ',$a).'<br>';
    }
    
    /**
    
    **/
    function __construct($command=null)
    {
        $this->reset();
        $this->command=$command;
        
        $path=$this->get_data_path();
        
        $this->scraper=new \App\Models\SimpleCurl;
        $this->dom = new \DOMDocument();
        $this->dom2 = new \DOMDocument();
        $this->whois=new Whois();
        $this->whois->command=$this->command;
    }
    
    /**
    
    **/
    /*
    public static function adjust_memory()
    {
    $memory_limit = ini_get('memory_limit');
    if (preg_match('/^(\d+)(.)$/', $memory_limit, $matches))
    {
    if ($matches[2] == 'M')
    {
    $memory_limit = $matches[1] * 1024 * 1024;
    }
    else if ($matches[2] == 'K')
    {
    $memory_limit = $matches[1] * 1024;
    }
    }
    $memory_limit/=(1024*1024);
    if($memory_limit<1024)
    ini_set('memory_limit','1024M');
    }*/
    
    /**
    
    Reset data.
    
    **/
    public function reset()
    {
        foreach($this->fillable as $key)
        {
            $this->$key="";
        }
        
        unset($this->domains);
        unset($this->emails);
        unset($this->ordered_domains);
        unset($this->ordered_emails);
        unset($this->aux_urls);
        
        $this->domains=[];
        $this->emails=[];
        $this->ordered_domains=[];
        $this->ordered_emails=[];
        $this->aux_urls=[];
    }
    
    /**
    
    Main entry function for this class.
    
    **/
    public function get_data()
    {
        $this->command->line(" * Querying $this->name $this->address $this->phone");
        
        $this->domains=[];
        $this->emails=[];
        $this->ordered_domains=[];
        $this->ordered_emails=[];
        $this->aux_urls=[];
        
        $this->get_invalid_domains();
        
        /* try yellow pages for domains first */
        $data=$this->yellowpages($this->name,$this->city,$this->state);
        if($data['size']>256)
        {
            $data=$this->yellowpages_detail_page($data['detail_url']);
            $this->domains=array_merge($this->domains,[$data['domain']]);
            $this->domains=array_merge($this->domains,$data['domains']);
            $this->reject_domains();
            $this->url=$data['url'];
        }
        
        /* if no domains yet, try bing maps */
        if(count($this->domains)==0)
        {
            $data=$this->bing_map();
            $this->domains=array_merge($this->domains,$data['domains']);
            if(count($this->domains)>0)
            {
                $this->url=$data['urls'][0];
            }
            
            /* if still no domains try bing search */
            if(count($this->domains)==0)
            {
                list($domains,$url)=$this->bing();
                if(count($domains)>0)
                {
                    $this->url=$url;
                    $this->domains=array_merge($this->domains,$domains);
                    $this->reject_domains();
                }
            }
            
        }
        
        /* clean up the domains lists and get the best best */
        $this->reject_domains();
        
        $this->guess_domain(); /* guess is placed here temporarily so we don't have to redownload everything. */
        
        /* */
        if($this->domain=="http://www.gentiva.com"||$this->domain=="http://gentiva.com")
        {
            $city=preg_replace("/[^A-Za-z0-9]/", '', strtolower($this->city));
            $url="http://".$city.".gentivahomehealth.com";
            
            $data=$this->crawl_page($url);
            if(strlen($data['raw'])>512)
            {
                $this->url=$url;
                $this->domain=$url;
                array_unshift($this->domains,$url);
                $this->command->line("\t ============ adjusted(1) ".$url);
            }
            else
            {
                $url="http://".$city.".gentivahospice.com";
                $data=$this->crawl_page($url);
                if(strlen($data['raw'])>512)
                {
                    $this->url=$url;
                    $this->domain=$url;
                    array_unshift($this->domains,$url);
                    $this->command->line("\t ============ adjusted(2) ".$url);
                }
            }
            
        }
        
        /* */
        $this->strip_to_domains();
        $this->order_domains();
        /*	$this->guess_ordered_domain(); */ /* but in future full runs it should be here */
        
        /* get the home page and if we can find them the about and contact pages */
        /* the page crawling also collects email addresses as it goes */
        $domains=[$this->domain];
        $this->home_url="";
        if(count($domains)>0)
        //        foreach($domains as $url)
        {
            $url=$domains[0];
            $this->command->line(" Looking for additional about/contact pages on $url...");
                
            $tag='home';
            $data=$this->crawl_page($url,$tag);
            $this->domains=array_merge($this->domains,$data['domains']);
            $this->emails=array_merge($this->emails,$data['emails']);
            $this->home_url=$url;
            
            $this->aux_urls=$this->find_auxillary_pages($this->home_url,$data['raw']);
            foreach($this->aux_urls as $tag=>$url)
            {
                $data=$this->crawl_page($url,$tag);
                $this->domains=array_merge($this->domains,$data['domains']);
                $this->emails=array_merge($this->emails,$data['emails']);
                unset($data);
            }
            
            unset($data);
        }
        
        /* if no emails yet, try doing a whois lookup of the domain and get those */
        if(count($this->emails)==0)
        {
            $data=$this->whois($this->domain);
            $this->emails=array_merge($this->emails,$data['emails']);
        }
        unset($data);
        
        /* cleanup the emails and order them */
        $this->reject_emails();
        $this->order_emails();
        $this->guess_ordered_email();
        
        $this->guess_ordered_domain(); /* use here only because we commented it above for compatibility wth first run */
        
        /* */
        /* display results to CLI */
        
        $this->command->line("");
        
        $this->command->line("\t name=".$this->name);
        $this->command->line("\t domain=".$this->domain);
        $this->command->line("\t email=".$this->email);
        $this->command->line("\t url=".$this->url);
        
        $this->command->line("\t phone=".$this->phone);
        $this->command->line("\t found phone=".$this->found_phone);
        $this->command->line("\t address=".$this->address);
        $this->command->line("\t city=".$this->city);
        $this->command->line("\t state=".$this->state);
        $this->command->line("\t zip=".$this->zip);
        
        $this->command->line("\t home_url=".$this->home_url);
        foreach($this->aux_urls as $tag=>$url)
        {
            $this->command->line("\t $tag url=".$url);
        }
        
        foreach($this->ordered_domains as $domain=>$count)
        {
            $this->command->line("\t\t > domain=".$domain." (".$count.")");
        }
        foreach($this->ordered_emails as $email=>$count)
        {
            $this->command->line("\t\t\t > email=".$email." (".$count.")");
        }
        
        $this->command->line("");
        $this->command->line("");
        $this->command->line("");
        
        gc_collect_cycles();
    }
    
    
    
    
    // --------------------------------------------------------------------
    
    /**
    
    Queries yellowpages with name city, state for a list of possible biz
    
    We match the phone numbers to confirm which is the correct entry then call the
    
    **/
    public function yellowpages($name,$city,$state)
    {
        $this->command->line(" ** yellowpages $name,$city,$state");
        
        $default_data=[
        'phone'=>'',
        'url'=>'',
        'name'=>'',
        'size'=>0,
        'detail_url'=>''
        ];
        $data=$default_data;
        
        /* remove LLC if found because we cant find them with it */
        //$name=self::strip_bizname_postfix($name);
        
        /* */
        $url="http://www.yellowpages.com/search?search_terms=".urlencode(strtoupper($name))."&geo_location_terms=".urlencode(strtoupper($city).", ".strtoupper($state));
        $this->command->line("url=$url");
        
        //$scraper=new \App\Models\SimpleCurl;
        //$this->scraper->command=$this->command;
        $this->scraper->set_user_agent($this->user_agent);
        $this->scraper->set_referer($url);
        $this->scraper->timeout=$this->SCRAPER_TIMEOUT;
        $cache_file="yellowpages.search.".urlencode($name).".".urlencode($city).".".urlencode($state).".txt";
        do {
            $html=$this->scraper->get_url($url,$cache_file);
        } while($html===false);
        
        if(is_array($html)&&isset($html['error']))
        $this->add_invalid_domain($url);
        
        if(is_object($html)||is_array($html)||$html=="")
        {
            return $data;
        }
        
        $data['size']=strlen($html);
        
        /* suppress errors about malformed html */
        libxml_use_internal_errors(true);
        
        /* */
        //$dom = new \DOMDocument();
        $this->dom->substituteEntities = false;
        $this->dom->loadHTML($html);
        $this->xpath = new \DOMXpath($this->dom);
        
        $found=false;
        
        /* find by phone */
        $resultList = $this->xpath->query("//*[contains(@class, 'srp-listing')]");
        if(get_class($resultList)=='DOMNodeList')
        {
            foreach ($resultList as $result)
            {
                $itemList = $this->xpath->query(".//*[contains(@class, 'phone')]",$result);
                if(get_class($itemList)=='DOMNodeList')
                {
                    if(count($itemList)>0)
                    foreach ($itemList as $item)
                    {
                        $item=$itemList[0];
                        $data['phone']=$this->toPhone($item->nodeValue);
                        //break;
                    }
                }
                
                $itemList = $this->xpath->query(".//*[contains(@class, 'business-name')]",$result);
                if(get_class($itemList)=='DOMNodeList')
                {
                    foreach ($itemList as $item)
                    {
                        $url=$this->toDomain($item->getAttribute('href'));
                        if(substr($url,0,4)!="http")
                        {
                            $data['detail_url']="http://www.yellowpages.com".$item->getAttribute('href');
                            $data['name']=$item->nodeValue;
                            break;
                    }
                }
            }
            
            if($data['phone'] == $this->phone)
            {
                $found=true;
                // break;
            }
            
        }
        
    }
    
    /* find by address if phone not found */
    /* test nhc 22 */
    $resultList = $this->xpath->query("//*[contains(@class, 'srp-listing')]");
    if(get_class($resultList)=='DOMNodeList')
    {
        $srps=[];
        
        foreach ($resultList as $i=>$result)
        {
            $srps[$i]['name']='';
            $srps[$i]['city']='';
            $srps[$i]['state']='';
            $srps[$i]['zip']='';
            $srps[$i]['url']='';
            $srps[$i]['detail_url']='';
        }
        
        foreach ($resultList as $i=>$result)
        {
            $itemList = $this->xpath->query(".//*[contains(@class, 'business-name')]",$result);
            if(get_class($itemList)=='DOMNodeList')
            {
                foreach ($itemList as $item)
                {
                    $url=($item->getAttribute('href'));
                    if(substr($url,0,4)!="http")
                    {
                        $srps[$i]['name']=$item->nodeValue;
                        $srps[$i]['detail_url']="http://www.yellowpages.com".$item->getAttribute('href');
                        break;
                }
            }
        }
    }
    
    foreach ($resultList as $i=>$result)
    {
        $city = $this->xpath->query(".//*[contains(@class, 'locality')]",$result);
        if(get_class($city)=='DOMNodeList')
        {
            if(count($city)>0)
            //            foreach ($city as $result)
            {
                $resukt=$city[0];
                $srps[$i]['city']=$result->nodeValue;
                // break;
            }
        }
    }
    
    foreach ($resultList as $i=>$result)
    {
        $state = $this->xpath->query(".//span[@itemprop='addressRegion']",$result);
        if(get_class($state)=='DOMNodeList')
        {
            if(count($state)>0)
            // foreach ($state as $result)
            {
                $srps[$i]['state']=$result->nodeValue;
                //  break;
            }
        }
    }
    
    foreach ($resultList as $i=>$result)
    {
        $zip = $this->xpath->query(".//span[@itemprop='postalCode']",$result);
        if(get_class($zip)=='DOMNodeList')
        {
            if(count($zip)>0)
            //  foreach ($zip as $result)
            {
                $srps[$i]['zip']=$result->nodeValue;
                //   break;
            }
        }
    }
    
    foreach($srps as $i=>$srp)
    {
        if($found) break;
    if(isset($srp['name'])&&isset($srp['city'])&&isset($srp['state'])&&isset($srp['zip'])&&isset($srp['url']))
    {
        //echo $srp['name']." == ".$this->name."\n";
        if(
        strtoupper($srp['city'])==strtoupper($this->city.", ")
        &&strtoupper($srp['state'])==strtoupper($this->state)
        &&$srp['zip']==$this->zip
        )
        {
            if(self::compare_bizname($srp['name'],$this->name))
            {
                $data['detail_url']=$srp['detail_url'];
                $found=true;
                //  break;
            }
            else
            {
                
                $data2=$this->yellowpages_detail_page($srp['detail_url']);
                foreach($data2['aka'] as $name)
                {
                    if(self::compare_bizname($srp['name'],$name))
                    {
                        $data['detail_url']=$srp['detail_url'];
                        $found=true;
                        break;
                }
            }
            
        }
    }
}
}

}

if($found)
{
    $ext = pathinfo($data['url'], PATHINFO_EXTENSION);
    $ext=strtolower($ext);
    if($ext=='pdf'||$ext=='jpg'||$ext=='gif'||$ext=='png'||$ext=='swf')
    $found=false;
}

if(!$found)
$data=$default_data;

return $data;
}

/**

Gets the yellowpage detail page

Returns the domain (in most cases)

**/
public function yellowpages_detail_page($url)
{
    $this->command->line("");
    $this->command->line(" ** yellowpages page=$url");
    
    $data=[
    'domain'=>'',
    'domains'=>[],
    'aka'=>[],
    'url'=>'',
    'urls'=>[]
    ];
    
    if($url=='') return $data;
    
    $this->command->line("url=$url");
    
    //$scraper=new \App\Models\SimpleCurl;
    //$this->scraper->command=$this->command;
    $this->scraper->set_user_agent($this->user_agent);
    $this->scraper->set_referer($url);
    $this->scraper->timeout=$this->SCRAPER_TIMEOUT;
    $cache_file="yellowpages.page.".hash('sha256',$url).".txt";
    do {
        $html=$this->scraper->get_url($url,$cache_file);
    } while($html===false);
    
    if(is_array($html)&&isset($html['error']))
    $this->add_invalid_domain($url);
    
    if(is_object($html)||is_array($html)||$html=="")
    {
        return $data;
    }
    
    /* suppress errors about malformed html */
    libxml_use_internal_errors(true);
    
    //$dom = new \DOMDocument();
    $this->dom->substituteEntities = false;
    $this->dom->loadHTML($html);
    $this->xpath = new \DOMXpath($this->dom);
    
    /* */
    $itemList = $this->xpath->query("//*[contains(@class, 'custom-link')]");
    if(get_class($itemList)=='DOMNodeList')
    {
        foreach ($itemList as $item)
        {
            $data['domain']=$this->toDomain($item->getAttribute('href'));
            $data['domains'][]=$data['domain'];
            $url=$item->getAttribute('href');
            $data['url']=$url;
            $data['urls'][]=$url;
        }
    }
    //is_url_not_on_reject_list
    /* */
    $resultList = $this->xpath->query("//*[contains(@class, 'weblinks')]");
    if(get_class($resultList)=='DOMNodeList')
    {
        foreach ($resultList as $result)
        {
            $itemList = $this->xpath->query("*//a",$result);
            if(get_class($itemList)=='DOMNodeList')
            {
                foreach ($itemList as $item)
                {
                    $data['domains'][]=$this->toDomain($item->getAttribute('href'));
                    $url=$item->getAttribute('href');
                    $data['urls'][]=$url;
                }
            }
        }
    }
    
    /* */
    $data['aka']=[];
    $resultList = $this->xpath->query("//*[contains(@class, 'aka')]");
    if(get_class($resultList)=='DOMNodeList')
    {
        foreach ($resultList as $result)
        {
            $itemList = $this->xpath->query("p",$result);
            if(get_class($itemList)=='DOMNodeList')
            {
                foreach ($itemList as $item)
                {
                    $data['aka'][]=$item->nodeValue;
                }
            }
        }
    }
    //echo "aka="; print_r($data['aka']);// exit;
    
    return $data;
}

// --------------------------------------------------------------------

/**

Looks for the bing biz data on the search pae (right side)

input: target {name,address,phone}
ouput: array of {'name','address','phone','domain','email'}
**/
public function bing_map()
{
    //$query=$this->name;
    $query=$this->name." ".$this->city.",".$this->state;
    
    $this->command->line("");
    $this->command->line(" ** bing_map ".$query);
    
    $data=[
    'domains'=>[],
    'emails'=>[],
    'address'=>'',
    'phone'=>'',
    'urls'=>[],
    'url'=>''
    ];
    
    $url="https://www.bing.com/search?q=".urlencode($query)."&go=Submit&qs=n&form=QBLH&pq=".urlencode(strtolower($query))."&sc=0-0&sp=-1&sk=&cvid=c22403831b494924bd32765ae242ecbb";
    
    //https://www.bing.com/search?q=AEGIS+HOMECARE%2C+INC&go=Submit&qs=n&form=QBLH&pq=aegis+homecare%2C+inc&sc=8-19&sp=-1&sk=&ghc=1&cvid=c71a7bc73d6349cdbd4e661f1010aaac
    
    //http://www.bing.com/search?q=AEGIS+HOMECARE%2C+INC+chandler+az&go=Submit&qs=n&form=QBLH&pq=aegis+homecare%2C+inc+chandler+az&sc=0-0&sp=-1&sk=&cvid=c22403831b494924bd32765ae242ecbb
    
    //$scraper=new \App\Models\SimpleCurl;
    //$this->scraper->command=$this->command;
    $this->scraper->set_user_agent($this->user_agent);
    $this->scraper->set_referer($url);
    $this->scraper->timeout=$this->SCRAPER_TIMEOUT;
    //	do {
    $html=$this->scraper->get_url($url,"bing.searchengine_map_query.".urlencode($query).".txt");
    //} while($html===false);
    
    if(is_array($html)&&isset($html['error']))
    $this->add_invalid_domain($url);
    
    if(is_object($html)||is_array($html)||$html=="")
    {
        return $data;
    }
    
    /* suppress errors about malformed html */
    libxml_use_internal_errors(true);
    
    /* */
    //$dom = new \DOMDocument();
    $this->dom->substituteEntities = false;
    $this->dom->loadHTML($html);
    $this->xpath = new \DOMXpath($this->dom);
    
    $resultList = $this->xpath->query("//*[contains(@class, 'b_hList')]");
    
    if(get_class($resultList)=='DOMNodeList')
    {
        foreach ($resultList as $result)
        {
            /* */
            $aList = $this->xpath->query("*//a",$result);
            if(get_class($aList)=='DOMNodeList')
            {
                foreach ($aList as $anode)
                {
                    $href=$anode->getAttribute('href');
                    $value=$anode->nodeValue;
                    
                    if($value=='Website')
                    {
                        $data['domains'][]=$this->toDomain($href);
                        $data['urls'][]=($href);
                    }
                }
            }
        }
        
        $resultList = $this->xpath->query("//*[contains(@class, 'b_factrow')]");
        if(get_class($resultList)=='DOMNodeList')
        {
            foreach ($resultList as $result)
            {
                //$dom2 = new \DOMDocument();
                $this->dom2->substituteEntities = false;
                $this->dom2->loadHTML($this->dom->saveHTML($result));
                $this->xpath2 = new \DOMXpath($this->dom2);
                
                /* */
                $liList = $this->xpath2->query("//span");
                if(get_class($liList)=='DOMNodeList')
                {
                    foreach ($liList as $linode)
                    {
                        $class=$linode->getAttribute('class');
                        $value=$linode->nodeValue;
                        
                        switch($class)
                        {
                            case 'b_address':
                                $data['address']=trim($linode->nodeValue);
                                break;
                            
                            case 'nowrap':
                                $data['phone']=$this->toPhone($linode->nodeValue);
                                break;
                    }
                }
            }
        }
    }
}

return $data;
}

// --------------------------------------------------------------------

/**

Last chance fallback to find a domain. A bing general search for urls...

* @input query
* @output array of {'url'}

**/
public function bing()
{
    $query=$this->name;
    
    $this->command->line("");
    $this->command->line(" ** bing ".$query);
    
    $url="https://www.bing.com/search?q=".urlencode($query)."&go=Submit&qs=n&form=QBLH&pq=".urlencode(strtolower($query))."&sc=8-19&sp=-1&sk=&ghc=1&cvid=c71a7bc73d6349cdbd4e661f1010aaac";
    
    $blank=[[],null];
    
    $this->scraper->set_user_agent($this->user_agent);
    $this->scraper->set_referer($url);
    $this->scraper->timeout=$this->SCRAPER_TIMEOUT;
    
    do
    {
        $html=$this->scraper->get_url($url,"bing.searchengine_query.".urlencode($query).".txt");
    } while($html===false);
    
    if(is_array($html)&&isset($html['error']))
    $this->add_invalid_domain($url);
    
    if(is_object($html)||is_array($html)||$html=="")
    {
        return $blank;
    }
    
    /* suppress errors about malformed html */
    libxml_use_internal_errors(true);
    /* */
    //$dom = new \DOMDocument();
    $this->dom->substituteEntities = false;
    $this->dom->loadHTML($html);
    
    /* check local first */
    /* test nhc 2446 */
    $xpath = new \DOMXpath($this->dom);
    
    $this->command->line(" -- bing local...");
    
    $resultList = $xpath->query("//*[contains(@class, 'mhc_lc')]");
    if(get_class($resultList)=='DOMNodeList')
    {
        foreach ($resultList as $result)
        {
            $itemList = $xpath->query(".//*[contains(@class, 'b_factrow')]",$result);
            if(get_class($itemList)=='DOMNodeList')
            {
                foreach ($itemList as $item)
                {
                    $aList = $xpath->query(".//a",$item);
                    if(get_class($aList)=='DOMNodeList')
                    {
                        foreach ($aList as $anode)
                        {
                            $url=$this->toDomain($anode->getAttribute('href'));
                            
                            $url=$anode->getAttribute('href');
                            $domain=$this->toDomain($url);
                            if(substr($url,0,4)=='http')
                            {
                                $this->command->line(" * bing local check: $url");
                                if($this->is_url_not_on_reject_list($url))
                                {
                                    $data=$this->crawl_page($url);
                                    if(in_array($this->phone,$data['phones']))
                                    {
                                        return [[$domain],$url];
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
    }
    
    $this->command->line(" -- bing phone...".$this->phone);
    
    /* check serps for phone */
    $xpath = new \DOMXpath($this->dom);
    $max_urls=$this->SERP_MAX_LISTINGS;
    $resultList = $xpath->query("//*[contains(@id, 'b_results')]");
    if(get_class($resultList)=='DOMNodeList')
    {
        foreach ($resultList as $result)
        {
            $itemList = $xpath->query("//*[contains(@class, 'b_algo')]",$result);
            if(get_class($itemList)=='DOMNodeList')
            {
                foreach ($itemList as $item)
                {
                    $aList = $xpath->query("*//a",$item);
                    if(get_class($aList)=='DOMNodeList')
                    {
                        foreach ($aList as $anode)
                        {
                            $url=$this->toDomain($anode->getAttribute('href'));
                            
                            if(substr($url,0,4)=='http')
                            {
                                /* test: nhc 24 */
                                $domains=$this->reject_domains([$url]);
                                if(count($domains)>0)
                                {
                                    $this->command->line(" * $max_urls bing phone check: $url");
                                    $data=$this->crawl_page($url);
                                    
                                    if(count($data['domains'])>0)
                                    {
                                        /* did we find a matching phone number? */
                                        if(in_array($this->phone,$data['phones']))
                                        {
                                            return [$domains,$url];
                                        }
                                        
                                    }
                                    $max_urls--;
                                    if($max_urls<=0) return $blank;
                                }
                            }
                        }
                    }
                }
            }
        }
    }
    
    $this->command->line(" -- bing city+zip... ".$this->city." ".$this->zip);
    
    /* check serps for city+zip */
    $xpath = new \DOMXpath($this->dom);
    $max_urls=$this->SERP_MAX_LISTINGS;
    $resultList = $xpath->query("//*[contains(@id, 'b_results')]");
    if(get_class($resultList)=='DOMNodeList')
    {
        foreach ($resultList as $result)
        {
            $itemList = $xpath->query("//*[contains(@class, 'b_algo')]",$result);
            if(get_class($itemList)=='DOMNodeList')
            {
                foreach ($itemList as $item)
                {
                    $aList = $xpath->query("*//a",$item);
                    if(get_class($aList)=='DOMNodeList')
                    {
                        foreach ($aList as $anode)
                        {
                            $url=$this->toDomain($anode->getAttribute('href'));
                            if(substr($url,0,4)=='http')
                            {
                                /* test: nhc 24 */
                                $this->command->line(" * $max_urls bing city+zip check: $url");
                                $domains=$this->reject_domains([$url]);
                                
                                if(count($domains)>0)
                                {
                                    $data=$this->crawl_page($url);
                                    
                                    /* did we find a matching city + zip?  */
                                    if(
                                    !(stripos($html," ".$this->zip)===false)
                                    && !(stripos($html,$this->city." ")===false)
                                    )
                                    {
                                        if(count($data['phones'])>0)
                                        $this->found_phone=$data['phones'][0];
                                        return [$domains,$url];
                                    }
                                }
                                
                                $max_urls--;
                                if($max_urls<=0) return $blank;
                            }
                        }
                    }
                }
            }
        }
    }
    
    return $blank;
}


// --------------------------------------------------------------------

/**

Crawls a page on the target domain website. Looks for email and phone.

* @input url
* @return {emails[]=>count,addresses[]=>count,phones[]=>count}


a) get names
b) get addresses
c) get phones
d) get domains
e) get emails

**/
public function crawl_page($url)
{
    $this->command->line(" ** crawl_page=$url");
    
    $data=[
    'domains'=>[],
    'emails'=>[],
    'phones'=>[],
    'raw'=>''
    ];
    if($this->is_invalid_domain($url)) return $data;
    
    if($url=='') return $data;
    
    //$scraper=new \App\Models\SimpleCurl;
    //$this->scraper->command=$this->command;
    $this->scraper->set_user_agent($this->user_agent);
    $this->scraper->set_referer($url);
    $this->scraper->timeout=$this->SCRAPER_TIMEOUT;
    //	do {
    $this->command->line(" ** scraper=$url");
    $html=$this->scraper->get_url($url,"page.".hash('sha512',$url).".txt");
    //} while(is_array($html)&&$html['status']!='error');
    
    $data['raw']=$html;
    
    if(is_array($html)&&isset($html['error']))
    $this->add_invalid_domain($url);
    
    if(is_object($html)||is_array($html)||$html=="")
    {
        $data['raw']="";
        return $data;
    }
    
    /* suppress errors about malformed html */
    libxml_use_internal_errors(true);
    
    /* */
    //$dom = new \App\Models\SmartDOMDocument();
    $this->dom->loadHTML($html);
    $this->xpath = new \DOMXpath($this->dom);
    
    /* find emails and telephones numbers on page */
    $nodeList = $this->xpath->query("//a");
    if(is_object($nodeList)&&get_class($nodeList)=='DOMNodeList')
    {
        foreach ($nodeList as $node)
        {
            $href=$node->getAttribute('href');
            $s=strtolower($node->nodeValue);
            
            if(!(stripos(strtolower($s),"site by")===false))
            {
                $a=explode(":",$href);
                if(count($a)>=2)
                {
                    $type=$a[0];
                    $value=trim($a[1]);
                    switch($type)
                    {
                        case 'mailto':
                            if(stripos($value,"@")>0)
                            {
                                $filtered = filter_var($value, FILTER_VALIDATE_EMAIL);
                                if($filtered || $filtered === 0)
                                {
                                    $data['emails'][]=$this->toEmail($value);
                            }
                        }
                        break;
                    
                    case 'tel':
                        $data['phones'][]=$this->toPhone($value);
                        break;
            }
        }
    }
}
}

/*  look for non-clickable phone number on page */
$phones=[$this->phone,$this->expandPhone($this->phone)];
foreach($phones as $phone)
{
    if(!(stripos($html,$phone)===false))
    {
        $data['phones'][]=$this->phone;
    }
}

/*  look for non-clickable email addresses on page */
$nodeList = $this->xpath->query("//*[contains(text(),'@')]");
if(is_object($nodeList)&&get_class($nodeList)=='DOMNodeList')
{
    foreach ($nodeList as $node)
    {
        /* test nhc 79 */
        $a=explode(" ",$node->nodeValue);
        foreach($a as $s)
        {
            if(!(stripos($s,"@")===false))
            {
                $filtered = filter_var($s, FILTER_VALIDATE_EMAIL);
                if($filtered || $filtered === 0)
                {
                    $data['emails'][]=$this->toEmail($s);
                }
            }
        }
    }
}

return $data;
}
// --------------------------------------------------------------------
/**

* @input url
* @return domain

**/
public function find_about($url,$html)
{
    $this->command->line(" ** find_about=$url");
    
    return $this->find_links_to_other_page($url,$html,[
    'about_us',
    'aboutus',
    'about',
    'mission',
    'board_of_directors',
    'visitor_information',
    'privacy',
    'privacy-notice'
    ]);
}

// --------------------------------------------------------------------
/**

* @input url
* @return domain

**/
public function find_contact($url,$html)
{
    $this->command->line(" ** find_contact=$url");
    
    return $this->find_links_to_other_page($url,$html,[
    'contact_us',
    'contactus',
    'contact',
    'feedback',
    'facilities',
    'staff',
    ]);
}

// --------------------------------------------------------------------
/**

* @input url
* @return domain

**/
private function find_links_to_other_page($url,$html,$tags)
{
    $this->command->line(" ** find_links_to_other_page=$url");
    if($url==""|is_object($html)||is_array($html)||$html=="")
    {
        return "";
    }
    
    /* suppress errors about malformed html */
    libxml_use_internal_errors(true);
    
    //$dom = new \App\Models\SmartDOMDocument();
    $this->dom->loadHTML($html);
    $this->xpath = new \DOMXpath($this->dom);
    $nodeList = $this->xpath->query("//a");
    if(is_object($nodeList)&&get_class($nodeList)=='DOMNodeList')
    { /* test hhc 376 */
        foreach ($nodeList as $node)
        {
            $href=$node->getAttribute('href');
            $href=$this->toUrl($href);
            
            /* */
            $s=strtolower(trim($href));
            if(substr($s,0,7)=='http://')
            {
                $s=substr($s,7);
                $a=explode("/",$s);
                array_shift($a);
            }
            else if(substr($s,0,8)=='https://')
            {
                $s=substr($s,8);
                $a=explode("/",$s);
                array_shift($a);
            }
            else
            {
                $a=explode("/",$s);
            }
            
            /* */
            $name=array_pop($a);
            if($name=='')
            {
                $name=array_pop($a);
            }
            
            /* */
            $b=explode(".",$name);
            if(count($b)>1)
            {
                $ext=array_pop($b);
                $name=implode(".",$b);
            }
            
            /* */
            $name=str_replace(["-",""],"_",$name);
            
            foreach($tags as $tag)
            {
                $i=stripos($href,$tag);
                if($tag==substr($name,0,strlen($tag))||!($i===false))
                {
                    $this->command->line("             page #### $href ####");
                    if($href!='')
                    {
                        $page_url="";
                        $domain=$this->host_from_url($url);
                        
                        if(substr($href,0,7)=='http://'||substr($href,0,8)=='https://')
                        {
                            $page_url=$href;
                        }
                        else
                        {
                            if(substr($href,0,1)=='/')
                            $page_url=$domain.$href;
                            else
                                $page_url=$domain."/".$href;
                        }
                        
                        if($domain==substr($page_url,0,strlen($domain)))
                        return $page_url;
                    }
                }
            }
            
        }
    }
    return "";
}


// --------------------------------------------------------------------
/**

* @input url
* @return domain

**/
public function find_auxillary_pages($url,$html)
{
    $this->command->line(" ** find_auxillary_pages=$url");
    
    $tags=[
    //'about_us',
    //'aboutus',
    'about',
    'mission',
    'board_of_directors',
    'visitor_information',
    'privacy',
    //'privacy_notice',
    //'contact_us',
    //'contactus',
    'contact',
    'feedback',
    'facilities',
    'staff',
    ];
    
    $urls=[];
    foreach($tags as $tag)
    {
        $aux_url=$this->find_auxillary_page($url,$html,$tag);
        if($aux_url!='') $urls[$tag]=$aux_url;
    }
    return $urls;
}
private function find_auxillary_page($url,$html,$tag)
{
    //$this->command->line(" ** find_auxillary_page[$tag]=$url");
    if($url==""|is_object($html)||is_array($html)||$html=="")
    {
        return "";
    }
    
    /* suppress errors about malformed html */
    libxml_use_internal_errors(true);
    
    //$dom = new \App\Models\SmartDOMDocument();
    $this->dom->loadHTML($html);
    $this->xpath = new \DOMXpath($this->dom);
    $nodeList = $this->xpath->query("//a");
    if(is_object($nodeList)&&get_class($nodeList)=='DOMNodeList')
    {
        /* test hhc 376 */
        foreach ($nodeList as $node)
        {
            $href=$node->getAttribute('href');
            $href=$this->toUrl($href);
            
            /* */
            $s=strtolower(trim($href));
            if(substr($s,0,7)=='http://')
            {
                $s=substr($s,7);
                $a=explode("/",$s);
                array_shift($a);
            }
            else if(substr($s,0,8)=='https://')
            {
                $s=substr($s,8);
                $a=explode("/",$s);
                array_shift($a);
            }
            else
            {
                $a=explode("/",$s);
            }
            
            /* */
            $name=array_pop($a);
            if($name=='')
            {
                $name=array_pop($a);
            }
            
            /* */
            $b=explode(".",$name);
            if(count($b)>1)
            {
                $ext=array_pop($b);
                $name=implode(".",$b);
            }
            
            /* */
            $name=str_replace(["-",""],"_",$name);
            
            /* */
            $i=stripos($href,$tag);
            if($tag==substr($name,0,strlen($tag))||!($i===false))
            {
                $this->command->line("\t\t\t #### find_auxillary_page $tag \t $href");
                if($href!='')
                {
                    $page_url="";
                    $domain=$this->host_from_url($url);
                    
                    if(substr($href,0,7)=='http://'
                    ||substr($href,0,8)=='https://')
                    {
                        $page_url=$href;
                    }
                    else
                    {
                        if(substr($href,0,1)=='/')
                        $page_url=$domain.$href;
                        else
                            $page_url=$domain."/".$href;
                    }
                    
                    if($domain==substr($page_url,0,strlen($domain)))
                    return $page_url;
                }
            }
            /* */
            
        }
    }
    return "";
}


// --------------------------------------------------------------------
/**

Performs a whois in an attempt to validate the domain and get more emalil addresses.

* @input domain
* @return {'domain','email','address','phone','name'}

**/
public function whois($domain)
{
    //$this->command->line(" ** whois=".$domain);
    
    $data=[
    'emails'=>[],
    'phones'=>[]
    ];
    
    if($domain=='') return $data;
    
    //$this->whois=new Whois();
    //$this->whois->$this->command=$this->command;
    $raw=$this->whois->whoislookup($domain);
    
    /* */
    $lines=explode("\n",$raw);
    foreach($lines as $line)
    {
        $a=explode(":",trim($line));
        if(count($a)>=2)
        {
            $a[1]=trim($a[1]);
            switch($a[0])
            {
                case 'Registrant Email':
                    $filtered = filter_var($a[1], FILTER_VALIDATE_EMAIL);
                    if($filtered || $filtered === 0)
                    {
                        
                        $data['emails'][]=$this->toEmail($a[1]);
                }
                break;
            
            case 'Admin Email':
                $filtered = filter_var($a[1], FILTER_VALIDATE_EMAIL);
                if($filtered || $filtered === 0)
                {
                    $data['emails'][]=$this->toEmail($a[1]);
            }
            break;
        
        case 'Registrant Phone':
            $data['phones'][]=$this->toPhone($a[1]);
            break;
        
        case 'Admin Phone':
            $data['phones'][]=$this->toPhone($a[1]);
            break;
}
}
}

return $data;
}


// --------------------------------------------------------------------
/**
**/
private function domain_from_url($url)
{
    if(substr($url,0,7)=='http://'||substr($url,0,8)=='https://')
    ;
    else
        $url="http://".$url;
    return parse_url($url,PHP_URL_HOST);
}
/**
**/
private function host_from_url($url)
{
    if(substr($url,0,7)=='http://'||substr($url,0,8)=='https://')
    ;
    else
        $url="http://".$url;
    return parse_url($url,PHP_URL_SCHEME)."://".parse_url($url,PHP_URL_HOST);
}
/**
**/
private function expandPhone($phone)
{
    $s=$this->toPhone($phone);
    $phone=substr($phone,0,3)."-".substr($phone,3,3)."-".substr($phone,6,4);
    return $phone;
}
/**
**/
private function toPhone($phone)
{
    return preg_replace("/[^0-9]/", "", $phone);
}
/**
**/
private function toEmail($email)
{
    return strtolower(trim($email));
}
/**
**/
private function toDomain($domain)
{
    return strtolower(trim($domain));
}

/**
**/
private function strip_to_domains()
{
    foreach($this->domains as $index=>$domain)
    {
        $this->domains[$index]=$this->domain_from_url($domain);
    }
}


/**
Cleans out unwanted urls

Has two modes: reading this->domains or the supplied url
**/
private function reject_domains($urls=null)
{
    if($urls==null)
    $domains=$this->domains;
    else
        $domains=$urls;
    
    /* pre clean */
    foreach($domains as $index=>$domain)
    {
        $domains[$index]=urldecode($domains[$index]);
        
        /* max domain length */
        if(strlen($domains[$index])>=255)
        {
            $domains[$index]=substr($domains[$index],0,254);
        }
        
        /* get rid of any comments */
        $a=explode("?",$domains[$index]);
        if(count($a)>1)
        $domains[$index]=$a[0];
        /* get rid of any comments */
        $a=explode("?",$domains[$index]);
        if(count($a)>1)
        $domains[$index]=$a[0];
    }
    
    /* go through reject list */
    $rejects=$this->domain_rejects;
    
    $indexes=[];
    foreach($domains as $index=>$domain)
    {
        /* */
        if($domain==""||'<script'==substr($domain,0,7))
        {
            $indexes[]=$index;
        }
        else
        {
            foreach($rejects as $reject)
            {
                $httpreject="http://".$reject;
                $httpsreject="https://".$reject;
                $wreject="www.".$reject;
                $whttpreject="http://".$wreject;
                $whttpsreject="https://".$wreject;
                
                if(
                $reject==substr($domain,0,strlen($reject)) ||
                $httpreject==substr($domain,0,strlen($httpreject)) ||
                $httpsreject==substr($domain,0,strlen($httpsreject))
                )
                {
                    $indexes[]=$index;
                }
                else if(
                $wreject==substr($domain,0,strlen($wreject)) ||
                $whttpreject==substr($domain,0,strlen($whttpreject)) ||
                $whttpsreject==substr($domain,0,strlen($whttpsreject))
                )
                {
                    $indexes[]=$index;
                }
            }
        }
    }
    foreach($indexes as $index)
    {
        unset($domains[$index]);
    }
    
    if($urls==null)
    $this->domains=$domains;
    else
        return $domains;
}

/* */
private function is_url_not_on_reject_list($url)
{
    /* go through reject list */
    $rejects=$this->domain_rejects;
    
    $domains=[$url];
    
    foreach($domains as $index=>$domain)
    {
        /* */
        if($domain==""||'<script'==substr($domain,0,7))
        {
            return false;
        }
        else
        {
            foreach($rejects as $reject)
            {
                $httpreject="http://".$reject;
                $httpsreject="https://".$reject;
                $wreject="www.".$reject;
                $whttpreject="http://".$wreject;
                $whttpsreject="https://".$wreject;
                
                if(
                $reject==substr($domain,0,strlen($reject)) ||
                $httpreject==substr($domain,0,strlen($httpreject)) ||
                $httpsreject==substr($domain,0,strlen($httpsreject))
                )
                {
                    return false;
                }
                else if(
                $wreject==substr($domain,0,strlen($wreject)) ||
                $whttpreject==substr($domain,0,strlen($whttpreject)) ||
                $whttpsreject==substr($domain,0,strlen($whttpsreject))
                )
                {
                    return false;
                }
            }
        }
    }
    
    return true;
}



/**
**/
private function reject_emails()
{
    /* pre clean */
    foreach($this->emails as $index=>$email)
    {
        $this->emails[$index]=urldecode($this->emails[$index]);
        
        /* max emails length */
        if(strlen($this->emails[$index])>=255)
        {
            $this->emails[$index]=substr($this->emails[$index],0,254);
        }
        
        /* get rid of any comments */
        $a=explode("?",$this->emails[$index]);
        if(count($a)>1)
        $this->emails[$index]=$a[0];
        /* get rid of any comments */
        $a=explode("&",$this->emails[$index]);
        if(count($a)>1)
        $this->emails[$index]=$a[0];
        
        $this->emails[$index]=preg_replace("/[^a-z0-9\@\.\-\_]/", '', $this->emails[$index]);
    }
    
    /* go through reject list */
    $rejects=$this->email_rejects;
    $prefix_rejects=$this->email_prefix_rejects;
    
    $indexes=[];
    foreach($this->emails as $index=>$email)
    {
        /* */
        $a=explode("@",$email);
        $iscount=count($a);
        $isscript=(!stripos($email,'<script')===false);
        $iscomment=(!stripos($email,'<!--')===false);
        $domain=array_pop($a);
        
        if($email==""||$iscount!=2||$isscript||$iscomment)
        {
            $indexes[]=$index;
        }
        else
        {
            foreach($rejects as $reject)
            {
                if($reject==substr($domain,strlen($domain)-strlen($reject),strlen($reject)))
                {
                    $indexes[]=$index;
                }
            }
            foreach($prefix_rejects as $reject)
            {
                if($reject==substr($email,0,strlen($reject)))
                {
                    $indexes[]=$index;
                }
            }
        }
    }
    foreach($indexes as $index)
    {
        unset($this->emails[$index]);
    }
}

/**
**/
private function guess_domain()
{
    /* */
    if(count($this->domains)>0)
    {
        foreach($this->domains as $domain)
        {
            $this->domain=$domain;
            break;
    }
}
}
/**
**/
private function guess_ordered_domain()
{
    /* */
    if(count($this->ordered_domains)>0)
    {
        foreach($this->ordered_domains as $domain=>$count)
        {
            $this->domain=$domain;
            break;
    }
}
}

/**
**/
private function guess_email()
{
    /* */
    if(count($this->emails)>0)
    {
        foreach($this->emails as $email)
        {
            $this->email=$email;
            break;
    }
}
}
/**
**/
private function guess_ordered_email()
{
    /* */
    if(count($this->ordered_emails)>0)
    {
        foreach($this->ordered_emails as $email=>$count)
        {
            $this->email=$email;
            break;
    }
}
}

/**
**/
private $invalid_domains=[];
private $invalid_domain_file="";
private function get_invalid_domains()
{
    $this->invalid_domain_file=$this->get_invalid_domains_path();
    if(file_exists($this->invalid_domain_file))
    {
        $s=file_get_contents($this->invalid_domain_file);
        $this->invalid_domains=explode("\n",$s);
    }
    else
    {
        $this->invalid_domains=[];
    }
}

/**
**/
private function add_invalid_domain($url)
{
    $domain=$this->domain_from_url($url);
    //echo "add_invalid_domain domain=$domain url=$url\n";
    if(!isset($invalid_domains[$domain]))
    {
        $invalid_domains[]=$domain;
        file_put_contents($this->invalid_domain_file, "$domain\n",FILE_APPEND);
    }
}

/**
**/
private function is_invalid_domain($url)
{
    $domain=$this->domain_from_url($url);
    //print_r($this->invalid_domains);
    //	echo "domain=$domain url=$url\n";// exit;
    //	if(in_array($domain,$this->invalid_domains))
    foreach($this->invalid_domains as $i=>$reject)
    {
        //echo "x i=$i reject=$reject domain=$domain\n";
        if($reject==$domain)
        {
            //echo "found\n\n\n";
            return true;
        }
    }
    //exit;
    return false;
}

/**
**/
private function order_domains()
{
    $this->ordered_domains=[];
    
    /* */
    if(count($this->domains)>0)
    {
        foreach($this->domains as $domain)
        {
            if(!isset($this->ordered_domains[$domain]))
            $this->ordered_domains[$domain]=1;
            else
                $this->ordered_domains[$domain]++;
        }
    }
}

/**
**/
private function order_emails()
{
    $this->ordered_emails=[];
    
    /* */
    if(count($this->emails)>0)
    {
        foreach($this->emails as $email)
        {
            if(!isset($this->ordered_emails[$email]))
            $this->ordered_emails[$email]=1;
            else
                $this->ordered_emails[$email]++;
        }
    }
    /* sorts the domain or email lists by count, leaving them otherwise in ther original order */
    $a=$this->ordered_emails;
    $this->ordered_emails=[];
    $max_count=0;
    foreach($a as $s=>$count)
    {
        if($count>$max_count) $max_count=$count;
    }
    do
    {
        foreach($a as $s=>$count)
        {
            if($count==$max_count)
            {
                $this->ordered_emails[$s]=$count;
            }
        }
        $max_count--;
    } while($max_count>0);
}

/**
**/
private function toUrl($url)
{
    
    $url=strtolower(trim($url));
    $a=explode(" ",$url);
    
    if(count($a)>0) $url=$a[0];
    
    if(!(stripos($url,":")===false))
    {
        return "";
    }
    
    return $url;
}

/**
**/
public function get_csv($url,$zip_file,$output_folder,$csv_file)
{
    /* */
    $this->command->line("get_csv()");
    $this->command->line(" url=$url");
    $this->command->line(" zip_file=$zip_file");
    $this->command->line(" output_folder=$output_folder");
    $this->command->line(" csv_file=$csv_file");
    
    if(file_exists($csv_file)) return;
    
    /* */
    if(!file_exists($zip_file)||filesize($zip_file)==0)
    {
        //$scraper=new \App\Models\SimpleCurl;
        //$this->scraper->command=$this->command;
        $this->scraper->set_user_agent($this->user_agent);
        $this->scraper->set_referer($url);
        $this->scraper->timeout=$this->SCRAPER_LONG_TIMEOUT;
        $this->scraper->download_file($url,$zip_file);
        $this->command->info("\t\t > Download $url size=".filesize($zip_file));
    }
    
    /* */
    $zip = new \ZipArchive;
    $res = $zip->open($zip_file);
    if ($res === TRUE)
    {
        $zip->extractTo($output_folder);
        $zip->close();
        
        $this->command->info(" * Unzipped $zip_file size=".filesize($zip_file));
    }
    else
    {
        $this->command->error(" * Failed to unzipped $zip_file size=".filesize($zip_file));
    }
    
    $this->command->info(" * Extracted to output_folder ($csv_file)");
}

/* */
public function dup_csv($in_file,$dup_file)
{
    $lookup=[];
    $line_index=0;
    $handle = fopen($in_file, "r");
    if (!$handle)
    {
        $this->error("Error: Could not open $in_file\n");
        return;
    }
    
    while (($line = fgets($handle)) !== false)
    {
        $line=trim($line);
        if($line=="") continue;
        
        $data=explode("\t",$line);
        $id=$data[0];
        if($line_index==0)
        {
            file_put_contents($dup_file, "$line\n");
        }
        else
        {
            if(!isset($lookup[$id]))
            {
                $lookup[$id]=1;
                file_put_contents($dup_file, "$line\n",FILE_APPEND);
            }
            else
            {
                $lookup[$id]++;
                $this->command->error("Dup $id (".$lookup[$id].")");
            }
        }
        
        $line_index++;
    }
    
    fclose($handle);
    
    $this->command->info("Last $line_index");
}

/* remove LLC if found because we cant find them with it */
public static function strip_bizname_postfix($name)
{
    $a=explode(" ",$name);
    $last=array_pop($a);
    if($last!="LLC"&&$last!="INC")
    {
        array_push($a,$last);
    }
    return implode(" ",$a);
}
/* */
public static function strip_bizname_abbrevs($name)
{
    $a=explode(" ",$name);
    
    $b=[];
    foreach($a as $s)
    {
        $s=strtoupper($s);
        $s= preg_replace("/[^A-Z0-9]/", '', $s);
        if($s=='CENTER') $s='CNTR';
        if($s=='PARTNERS') $s='PRTNRS';
        if($s=='HOSPICE') $s='HOSP';
        if($s=='AND') $s='&';
        //echo "s=$s\n";
        $b[]=$s;
    }
    
    return implode(" ",$b);
}
public static function strip_bizname_abbrevs2($name)
{
    $a=explode(" ",$name);
    
    $b=[];
    foreach($a as $s)
    {
        $s=strtoupper($s);
        $s= preg_replace("/[^A-Z0-9]/", '', $s);
        if($s=='HEALTHCARE') $s='HC';
        if($s=='CENTER') $s='CNTR';
        if($s=='PARTNERS') $s='';
        if($s=='HOSPICE') $s='HOSP';
        if($s=='AND') $s='&';
        //echo "s=$s\n";
        $b[]=$s;
    }
    
    return implode(" ",$b);
}
public static function strip_bizname_abbrevs3($name)
{
    $a=explode(" ",$name);
    
    $b=[];
    foreach($a as $s)
    {
        $s=strtoupper($s);
        $s= preg_replace("/[^A-Z0-9]/", '', $s);
        if($s=='CENTER') $s='CNTR';
        if($s=='PARTNERS') $s='';
        if($s=='PRTNRS') $s='';
        if($s=='HOSPICE') $s='HOSP';
        if($s=='AND') $s='&';
        //echo "s=$s\n";
        $b[]=$s;
    }
    
    return implode(" ",$b);
}
/* */
public static function strip_bizname_location($name)
{
    $org_name=$name;
    
    $a=explode(" ",$name);
    $len=count($a);
    
    $b=[];
    foreach($a as $s)
    {
        if(strtoupper($s)=='OF')
        {
            break;
    }
    $b[]=$s;
}

return implode(" ",$b);
}
/* */
public static function strip_to_slug($name)
{
    return preg_replace("/[^a-z0-9]/", '', strtolower($name));
}

/* */
public static function compare_bizname($s1,$s2)
{
    $os1=$s1;
    $os2=$s2;
    //echo "original s1=$s1 s2=$s2\n";
    /* try as is */
    $s1=$os1;
    $s2=$os2;
    $s1=self::strip_to_slug($s1);
    $s2=self::strip_to_slug($s2);
    //echo "s1=$s1 s2=$s2\n";
    $t=$s1==$s2;
    if($t) return $t;
    
    /* try without llc,inc */
    $s1=$os1;
    $s2=$os2;
    $s1=self::strip_bizname_postfix($s1);
    $s2=self::strip_bizname_postfix($s2);
    $s1=self::strip_to_slug($s1);
    $s2=self::strip_to_slug($s2);
    //echo "s1=$s1 s2=$s2\n";
    $t=$s1==$s2;
    if($t) return $t;
    
    /* try with abbrevs */
    $s1=$os1;
    $s2=$os2;
    $s1=self::strip_bizname_abbrevs($s1);
    $s2=self::strip_bizname_abbrevs($s2);
    $s1=self::strip_to_slug($s1);
    $s2=self::strip_to_slug($s2);
    //	echo "a1 s1=$s1 s2=$s2\n";
    $t=$s1==$s2;
    if($t) return $t;
    
    /* try with abbrevs with llc,inc */
    $s1=$os1;
    $s2=$os2;
    $s1=self::strip_bizname_postfix($s1);
    $s2=self::strip_bizname_postfix($s2);
    $s1=self::strip_bizname_abbrevs($s1);
    $s2=self::strip_bizname_abbrevs($s2);
    $s1=self::strip_to_slug($s1);
    $s2=self::strip_to_slug($s2);
    //echo "a2 s1=$s1 s2=$s2\n";
    $t=$s1==$s2;
    if($t) return $t;
    
    /* try with abbrevs with llc,inc wo partners */
    $s1=$os1;
    $s2=$os2;
    $s1=self::strip_bizname_postfix($s1);
    $s2=self::strip_bizname_postfix($s2);
    $s1=self::strip_bizname_abbrevs2($s1);
    $s2=self::strip_bizname_abbrevs2($s2);
    $s1=self::strip_to_slug($s1);
    $s2=self::strip_to_slug($s2);
    //echo "a3 s1=$s1 s2=$s2\n";
    $t=$s1==$s2;
    if($t) return $t;
    
    /* try with abbrevs with llc,inc wo partners 3 */
    $s1=$os1;
    $s2=$os2;
    $s1=self::strip_bizname_postfix($s1);
    $s2=self::strip_bizname_postfix($s2);
    $s1=self::strip_bizname_abbrevs3($s1);
    $s2=self::strip_bizname_abbrevs3($s2);
    $s1=self::strip_to_slug($s1);
    $s2=self::strip_to_slug($s2);
    //echo "a4 s1=$s1 s2=$s2\n";
    $t=$s1==$s2;
    if($t) return $t;
    //echo "end\n";
    //exit;
    return false;
}

/* end class */
}