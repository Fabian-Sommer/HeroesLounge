<?php
namespace Rikki\Heroeslounge\Classes\deployment;

use Log;

class Deployment
{
    public static function initHeroprotocol()
    {
        defined('DS') or define('DS', DIRECTORY_SEPARATOR);
        $x = exec('git clone https://github.com/Blizzard/heroprotocol.git plugins'.DS.'rikki'.DS.'heroeslounge'.DS.'classes'.DS.'replayparsing'.DS.'heroprotocol', $output);
        Log::info('Output: '.json_encode($output));
    }

    public static function updateHeroprotocol()
    {
        defined('DS') or define('DS', DIRECTORY_SEPARATOR);
        exec('(cd plugins'.DS.'rikki'.DS.'heroeslounge'.DS.'classes'.DS.'replayparsing'.DS.'heroprotocol && exec git pull https://github.com/Blizzard/heroprotocol.git master)', $output);
        Log::info('Attemted to update heroprotocol. Output: '.json_encode($output));
    }
}
