<?php
namespace app\modules;

use php\jsoup\Element;
use framework, std;
use php\jsoup\Jsoup;
use Throwable;
use httpclient;


class AppModule extends AbstractModule
{
    
    private $params;
    
    function __construct()
    {
        try 
        {
            if (!(str::contains($GLOBALS['argv'][1],'jut.su')) and !(str::contains($GLOBALS['argv'][1],'episode')) or !(str::contains($GLOBALS['argv'][1],'film')))
                self::exitWithError('Link incorrect');
                
            foreach ($GLOBALS['argv'] as $number => $value)
            {
                if ($number <= 1)
                    continue;
                    
                if (str::contains($value,'-'))
                {
                    switch (str::sub($value,1))
                    {
                        case ('p'):
                            $path = $GLOBALS['argv'][$number + 1];
                        break;
                        case ('q'):
                            $quality = $GLOBALS['argv'][$number + 1];
                        break;
                    }
                }
            }
            if ($path == null)
                $path = fs::abs('./');
            
            
            $sources = Jsoup::connect($GLOBALS['argv'][1])->userAgent('Rudi')->get()->body()->select('#my-player > source');
            
            if ($quality == 'highest' or $quality == 'lowest')
            {
                $element = $quality == 'highest' ? $sources->first() : $sources->last();
                
                $downloadLink = $element->attr('src');
                $quality = $element->attr('res');
            }
            else 
            {
                foreach ($sources as $element)
                {
                    if ($element->attr('selected') == 'true' and $quality == null or $quality == 'default')
                    {
                        $downloadLink = $element->attr('src');
                        $quality = $element->attr('res');
                    }
                    elseif ($quality == $element->attr('res'))
                        $downloadLink = $element->attr('src');
                }
            }
            if ($downloadLink == null)
                self::exitWithError("Couldn't get download link");
            if (fs::isFile($path.'/'.str::sub($downloadLink,str::lastPos($downloadLink,'/') + 1,str::pos($downloadLink,'?'))))
            {
                Logger::warn('File already exists');
                $this->successDownload($path,$downloadLink);
            }
            
            Logger::info('Downloading - quality '.$quality.', link - '.$downloadLink);
            
            $downloader = new HttpDownloader;
            
            $downloader->urls = [$downloadLink];
            $downloader->client()->userAgent = 'Rudi';
            $downloader->destDirectory = $path;
            $downloader->useTempFile = true;
            $downloader->threadCount = 8;
            
            $downloader->on('successOne',function () use ($downloadLink,$path)
            {
                $this->successDownload($path,$downloadLink);
            });
            $downloader->on('errorOne',function ()
            {
                self::exitWithError('Download failed :(');
            });
            
            $downloader->start();
        }
        catch (Throwable $ex)
            self::exitWithError($ex->getMessage());
    }
    
    function successDownload($path,$downloadLink)
    {
        Logger::info('Downloaded - '.$path.'/'.str::sub($downloadLink,str::lastPos($downloadLink,'/') + 1,str::pos($downloadLink,'?')));
        System::halt(0);
    }
    
    function getPath($path)
    {
        if ($path == null)
        {
            Logger::warn('The output path argument is detected, but its value is not set :/');
            return;
        }
        
        $path = $path;
    }
    
    function getQuality($quality)
    {
        if ($quality == null)
        {
            Logger::warn('The quality argument is detected, but its value is not set :/');
            return;
        }
        
        return $quality;
    }
    
    static function exitWithError($message)
    {
        Logger::error($message);
        System::halt(1);
    }
}
