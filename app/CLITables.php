<?php namespace App;

class CLITables
{
    private $string_array;
    private $string_array2;
    private $min_sizes_array;
    private $align;
    private $t=null;
    // --------------------------------------------------------------------
    
    public function __construct($string_array, $min_sizes_array=null,
    $align='left')
    {
        //
        if($min_sizes_array!=null&&count($string_array)!=count($min_sizes_array))
        {
            CLI::println("param count mismatch.");
            print_r($string_array); echo "\n";
            print_r($min_sizes_array); echo "\n";
            exit;
        }
        //
        if(isset($string_array[0])&&is_array($string_array[0]))
        {
            foreach($string_array as $k=>$arr)
            {
                $this->string_array[$k]=$arr[0];
                $this->string_array2[$k]=$arr[1];
            }
        }
        else
        {
            $this->string_array=$string_array;
            $this->string_array2=null;
        }
        
        //
        if(is_array($min_sizes_array))
        {
            $this->min_sizes_array=$min_sizes_array;
            foreach($this->string_array as $k=>$s)
            {
                $len=strlen($s)+2;
                if($this->min_sizes_array[$k]<$len)
                $this->min_sizes_array[$k]=$len;
            }
        }
        else
        {
            $this->min_sizes_array=[];
            foreach($string_array as $k=>$s)
            {
                $this->min_sizes_array[$k]=strlen($s)+2;
            }
        }
        
        //
        $this->t=new Timer();
        //
        $this->align=$align;
    }
    
    // --------------------------------------------------------------------
    
    public function th()
    {
        //
        foreach ($this->min_sizes_array as $k=>$min_size)
        {
            $len=$min_size-strlen($this->string_array[$k]);
            $len=($len>0)?$len:0;
            echo $this->string_array[$k].str_repeat(" ", $len).": ";
        }
        echo "\r\n";
        
        //
        if($this->string_array2!=null)
        {
            foreach ($this->min_sizes_array as $k=>$min_size)
            {
                $len=$min_size-strlen($this->string_array2[$k]);
                $len=($len>0)?$len:0;
                echo $this->string_array2[$k].str_repeat(" ", $len).": ";
            }
            echo "\r\n";
        }
    }
    
    // --------------------------------------------------------------------
    
    public function th_top()
    {
        //
        $this->th();
        $this->spacer();
    }
    // --------------------------------------------------------------------
    
    public function th_bottom()
    {
        $this->spacer();
        $this->th();
        if(is_object($this->t))
        $this->t->print_elapsed_time();
        
    }
    // --------------------------------------------------------------------
    
    public function spacer()
    {
        foreach ($this->min_sizes_array as $min_size)
        {
            echo str_repeat("-", $min_size).": ";
        }
        echo "\r\n";
    }
    // --------------------------------------------------------------------
    
    private function toObject($array)
    {
        return json_decode(json_encode($array),FALSE);
    }
    
    // --------------------------------------------------------------------
    public function td($values=null)
    {
        //
        if($values==null)  $values=$this->string_array;
        
        //
        if(is_object($values))
        {
            $attributes=$values->toArray();
            $attributes=$this->toObject($attributes);
            $arr=[];
            foreach($attributes as $attr)
            {
                $arr[]=$attr;
            }
            $values=$arr;
            unset($arr);
        }
        
        //
        foreach ($values as $k=> $s)
        {
            $min_size=$this->min_sizes_array[$k];
            if (strlen($s)<$min_size)
            {
                switch ($this->align)
                {
                    case 'left':
                        $len=$min_size-strlen($s);
                        $s=$s.str_repeat(" ", $len);
                        break;
                    case 'center':
                        $len1=intval(($min_size-strlen($s))/2);
                        $len2=$min_size-strlen($s)-$len1;
                        $s=str_repeat(" ", $len1).$s.str_repeat(" ", $len2);
                        break;
                    case 'right':
                        $len=$min_size-strlen($s);
                        $s=str_repeat(" ", $len).$s;
                        break;
            }
            echo "$s: ";
        }
        else
        {
            $s=substr($s, 0, $min_size);
            echo "$s> ";
        }
    }
    echo "\r\n";
}
// --------------------------------------------------------------------



// --------------------------------------------------------------------
public function long_list($values=null)
{
    //
    if($values==null)  $values=$this->string_array;
    
    //
    if(is_object($values))
    {
        $attributes=$values->toArray();
        $attributes=$this->toObject($attributes);
        $arr=[];
        foreach($attributes as $attr)
        {
            $arr[]=$attr;
        }
        $values=$arr;
        unset($arr);
    }
    
    //
    $min_k=0;
    $min_s=0;
    
    foreach ($values as $k=> $s)
    {
        $name=$this->string_array[$k];
        $klen=strlen($name)+1;
        $slen=strlen($s)+1;
        if($klen>$min_k) $min_k=$klen;
        if($slen>$min_s) $min_s=$slen;
    }
    if($min_s>132) $min_s=132;
    
    //
    foreach ($values as $k=> $s)
    {
        $name=$this->string_array[$k];
        if (strlen($name)<$min_k)
        {
            $len=$min_k-strlen($name);
            $name=$name.str_repeat(" ", $len);
            echo "$name : ";
        }
        else
        {
            $name=substr($name, 0, $min_k);
            echo "$name>: \n";
        }
        
        if (strlen($s)<$min_s)
        {
            $len=$min_s-strlen($s);
            $s=$s.str_repeat(" ", $len);
            echo "$s\n";
        }
        else
        {
            $s=substr($s, 0, $min_s);
            echo "$s>\n";
        }
        
    }
    
}
// --------------------------------------------------------------------

}