<?php
namespace app\modules;

use Throwable;
use httpclient;
use std, gui, framework, app;


class AppModule extends AbstractModule
{
    $timer;
    
    /**
     * @event action 
     */
    function doAction(ScriptEvent $e = null)
    {    
        if (isset($GLOBALS['argv'][1]) == false)
        {
            self::exitWithError('Link not specified');
            return;
        }
        if (str::contains($GLOBALS['argv'][1],'episode-') == false
            and str::contains($GLOBALS['argv'][1],'film-') == false
            or str::contains($GLOBALS['argv'][1],'jut.su/') == false)
        {
            self::exitWithError('Invalid link');
            return;
        }
        
        app()->form('MainForm')->browser->engine->url = $GLOBALS['argv'][1];
        
        Logger::info('Waiting for the player to load to get the video URL');
        $this->timer['timeout'] = Timer::after('25s',function (){self::exitWithError('Timeout');});
        $this->timer['getUrl'] = Timer::every(200,[$this,'getUrl']);
    }
    
    function getUrl()
    {
        uiLater(function ()
        {
            try
            {
                $element = app()->form('MainForm')->browser->engine->document->getElementById('my-player_html5_api');
                $url = app()->form('MainForm')->browser->engine->document->getElementById('my-player_html5_api')->getAttribute('src');
            }
            catch (Throwable $ex){return;}
            
            if ($element != null and $url != '1' and $url != null)
            {
                $this->timer['timeout']->cancel();
                $this->timer['getUrl']->cancel();
                
                $this->download($url);
            }
        });
    }
    
    function download($url)
    {
        new Thread(function () use ($url)
        {
            if ($GLOBALS['argv'][2] != null and fs::isDir($GLOBALS['argv'][2]))
            {
                if (File::of($GLOBALS['argv'][2])->canWrite() == false)
                    self::exitWithError('No rights to write to this directory');
                
                $path = $GLOBALS['argv'][2];
            }
            elseif ($GLOBALS['argv'][2] == null)
            {
                $path = fs::abs('./');
                Logger::warn('The download path is not specified, working directory is used');
            }
            elseif (fs::isDir($GLOBALS['argv'][2]) == false)
                self::exitWithError('Destination directory not found');
            
            Logger::info('Video URL - '.$url);
            Logger::info('Path for downloading - '.$path);
            
            $videoname = str::sub($url,str::lastPos($url,'/'),str::lastPos($url,'.mp4')).'.mp4';
            
            if (fs::isFile($path.'/'.$videoname))
            {
                Logger::warn('File already exists, skipping download');
                Logger::info('OK - '.$path.'/'.$videoname);
                
                System::halt(0);
            }
            else
            {
                $downloader = new HttpDownloader;
                $downloader->client()->userAgent = app()->form('MainForm')->browser->engine->userAgent;
                $downloader->useTempFile = true;
                $downloader->destDirectory = $path;
                $downloader->urls = [$url];
                
                $downloader->on('successOne',function () use ($path,$videoname)
                {
                    Logger::info('Downloaded');
                    Logger::info('OK - '.$path.'/'.$videoname);
                    
                    System::halt(0);
                });
                $downloader->on('errorOne',function ()
                {
                    self::exitWithError('Some error was occurred when downloading video');
                });
                
                $downloader->start();
            }
        })->start();
    }
    
    static function exitWithError($message)
    {
        Logger::error($message);
        System::halt(1);
    }
}
