<?php

class evoCaptcha
{
    const NAMESPACE='evocaptcha';
    public $modx;
    public $authenticated = false;
    public $errors = array();

    function __construct(modX &$modx, array $config = array())
    {
        $this->modx =& $modx;
        
        $localPath='components/'.static::NAMESPACE.'/';
        $corePath = $this->modx->getOption(static::NAMESPACE.'.core_path', $config, $this->modx->getOption('core_path') . $localPath);
        $assetsPath = $this->modx->getOption(static::NAMESPACE.'.assets_path', $config, $this->modx->getOption('assets_path') . $localPath);
        $assetsUrl = $this->modx->getOption(static::NAMESPACE.'.assets_url', $config, $this->modx->getOption('assets_url') . $localPath);
        $connectorUrl = $assetsUrl . 'connector.php';
        $context_path = $this->modx->context->get('key')=='mgr'?'mgr':'web';

        $this->config = array_merge(array(
            'assetsUrl' => $assetsUrl,
            'cssUrl' => $assetsUrl . $context_path . '/css/',
            'jsUrl' => $assetsUrl . $context_path . '/js/',
            'jsPath' => $assetsPath . $context_path . '/js/',
            'imagesUrl' => $assetsUrl . $context_path . '/img/',
            'connectorUrl' => $connectorUrl,
            'corePath' => $corePath,
            'modelPath' => $corePath . 'model/',
            'servicePath' => $corePath . 'model/'.static::NAMESPACE.'/',
            'chunksPath' => $corePath . 'elements/chunks/',
            'templatesPath' => $corePath . 'elements/templates/',
            'chunkSuffix' => '.chunk.tpl',
            'snippetsPath' => $corePath . 'elements/snippets/',
            'processorsPath' => $corePath . 'processors/',
            'noisesDir' => $assetsPath . 'noises/',
            'ttfDir' => $assetsPath . 'ttf/',
        ), $config);

        $this->modx->lexicon->load(static::NAMESPACE.':default');
        $this->authenticated = $this->modx->user->isAuthenticated($this->modx->context->get('key'));
        $this->loadModel();
        
        spl_autoload_register(array($this,'autoload'));
    }

    public function initialize($scriptProperties = array(),$ctx = 'web'){
        $this->config['ctx'] = $ctx;
        $this->properties = array_merge(array(
            'width' => 148,
            'height' => 60,
            'text_size' => 30,
            'sessionPrefix' => 'vericode',
            'noisesDir' => $this->config['noisesDir'],
            'ttfDir' => $this->config['ttfDir'],
            'connector' => $this->config['connectorUrl'],
        ), $scriptProperties);
        
        $this->properties['sessionKey']=$this->properties['sessionPrefix'].'_'.static::NAMESPACE;
        if(!isset($_SESSION[$this->properties['sessionKey']]))$_SESSION[$this->properties['sessionKey']]=array();
        $_SESSION[$this->properties['sessionKey']]['config']=$this->properties;
        return true;
    }
    
    public function autoload($class){
        $class = explode('/',str_replace("\\", "/", $class));
        $className = array_pop($class);
        $classPath = strtolower(implode('/',$class));
        
        $path = $this->config['modelPath'].'/'.$classPath.'/'.$className.'.php';
        if(!file_exists($path))return false;
        include $path;
    }
    
    public function loadAssets($ctx){
        if(!$this->modx->controller)return false;
        $this->modx->controller->addLexiconTopic(static::NAMESPACE.':default');
        switch($ctx){
            case 'mgr':{
                $this->modx->controller->addJavascript($this->config['assetsUrl'].'mgr/js/'.static::NAMESPACE.'.js');
            }
        }
    }
    
    public function loadModel(){
        //Ищем файл metadata
        $metadata=$this->config['servicePath']."metadata.".$this->modx->config['dbtype'].'.php';
        if(file_exists($metadata))$this->modx->addPackage(static::NAMESPACE, $this->config['modelPath']);
    }
    
    /**************************************************************************/
    /**************************************************************************/
    /**************************************************************************/
    
    public function getConnector()
    {
        return $this->properties['connector'].'?prefix='.$this->properties['sessionPrefix'];
    }
    
    public function setWord()
    {
        $_SESSION[$this->properties['sessionKey']]['old_word'] = $_SESSION[$this->properties['sessionKey']]['word'];
        $_SESSION[$this->properties['sessionKey']]['word'] = $this->createWord();
    }
    
    public function createWord()
    {
        $words="MODX,Access,Better,BitCode,Chunk,Cache,Desc,Design,Excell,Enjoy,URLs,TechView,Gerald,Griff,Humphrey,Holiday,Intel,Integration,Joystick,Join(),Oscope,Genetic,Light,Likeness,Marit,Maaike,Niche,Netherlands,Ordinance,Oscillo,Parser,Phusion,Query,Question,Regalia,Righteous,Snippet,Sentinel,Template,Thespian,Unity,Enterprise,Verily,Veri,Website,WideWeb,Yap,Yellow,Zebra,Zygote";
        $words = $this->modx->getOption(static::NAMESPACE.'.words',null,$words,true);
        $arr_words = array_filter(array_map('trim', explode(',', $words)));
        return (string) $arr_words[array_rand($arr_words)].rand(10,999);
    }
    
    public function checkWord($value)
    {
        return $_SESSION[$this->properties['sessionKey']]['old_word']==$value;
    }
    
    public function imageOut()
    {
        $this->imageDraw();
        header("Content-type: image/jpeg");
        imagejpeg($this->image);
    }
    
    public function imageDraw()
    {
        $bg_file = $this->properties['noisesDir']."noise".rand(1,4).".jpg";
        $bg_img = @imagecreatefromjpeg ($bg_file);
        $bg_width = imagesx($bg_img); 
        $bg_height = imagesy($bg_img); 
        
        $this->image = imagecreatetruecolor($this->properties['width'],$this->properties['height']); 
        imagecopyresampled
        (
            $this->image, 
            $bg_img, 
            0, 0, 0, 0, 
            $this->properties['width'],
            $this->properties['height'], 
            $bg_width, 
            $bg_height
        );
        imagecopymerge
        (
            $this->image, 
            $this->drawWord(), 
            0, 0, 0, 0, 
            $this->properties['width'], 
            $this->properties['height'], 
            70
        );
        return $this->image;
    }
    
    public function drawWord()
    {
        $word = $_SESSION[$this->properties['sessionKey']]['word'];

        $text = array
        (
            'angle' => rand(-9,9),
            'size' => $this->properties['text_size'],
            'font' => $this->getFont()
        );
        $box = imagettfbbox($text['size'],$text['angle'],$text['font'],$word);
        $text['width'] = $box[2]-$box[0];
        $text['height'] = $box[5]-$box[3];

        $text['size'] = round((20*$this->properties['width'])/$text['width']);  
        $box = imagettfbbox ($text['size'],$text['angle'],$text['font'],$word);
        $text['width'] = $box[2]-$box[0];
        $text['height'] = $box[5]-$box[3];

        $text['x'] = ($this->properties['width'] - $text['width'])/2;
        $text['y'] = ($this->properties['height'] - $text['height'])/2;
        
        $image = imagecreate ($this->properties['width'], $this->properties['height']); 
        $text['bg'] = imagecolorallocate($image,255,255,255); 
        $text['color'] = imagecolorallocate ($image, 0, 51, 153);

        imagettftext
        (
            $image,
            $text['size'],
            $text['angle'],
            $text['x'],
            $text['y'],
            $text['color'], 
            $text['font'], 
            $word
        );

        imagecolortransparent($image,$text['bg']);
        return $image;
        imagedestroy($image);
    }
    
    public function getFont()
    {
        $dir = dir($this->properties['ttfDir']);
        $fontstmp = array();
        while (false !== ($file = $dir->read())) {
            if(substr($file, -4) == '.ttf') {
                $fontstmp[] = $this->properties['ttfDir'].$file;
            }
        }
        $dir->close();
        return (string) $fontstmp[array_rand($fontstmp)];
    }
    
    public function imageDestroy()
    {
        imagedestroy($this->image);

    }
}
