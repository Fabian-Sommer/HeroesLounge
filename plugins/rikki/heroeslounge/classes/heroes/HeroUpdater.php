<?php namespace Rikki\Heroeslounge\classes\Heroes;

 

use Rikki\Heroeslounge\Models\Hero;
use Rikki\Heroeslounge\Models\Map;
use Rikki\Heroeslounge\Models\Talent;
use Cms\Classes\Theme;
use October\Rain\Database\Attach\Resizer;
use Log;

class HeroUpdater
{
    public static function updateHeroes()
    {
        set_time_limit(600);
        $theme = Theme::getActiveTheme();
        $theme_path = $theme->getPath();
        defined('DS') or define('DS', DIRECTORY_SEPARATOR);
        $hero_image_path = $theme_path.DS.'assets'.DS.'img'.DS.'heroes';
        if (!file_exists($hero_image_path)) {
            mkdir($hero_image_path, 0777, true);
        }
        
        $ch = curl_init("https://api.hotslogs.com/Public/Data/Heroes");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $hero_list_json = curl_exec($ch);
        curl_close($ch);
        $hero_list = json_decode($hero_list_json, true);
        foreach ($hero_list as $hero_entry) {
            if (Hero::where('title', $hero_entry['PrimaryName'])->count() == 0) {
                $ch2 = curl_init("https://d1i1jxrdh2kvwy.cloudfront.net/Images/Heroes/Portraits/".urlencode($hero_entry['ImageURL']).".png");
                curl_setopt($ch2, CURLOPT_RETURNTRANSFER, true);
                $hero_portrait = curl_exec($ch2);
                curl_close($ch2);
                $file = fopen($hero_image_path.DS.$hero_entry['ImageURL'].".png", "w+");
                fputs($file, $hero_portrait);
                fclose($file);
                $hero = new Hero();
                $hero->title = $hero_entry['PrimaryName'];
                $hero->image_url = $hero_entry['ImageURL'];
                $hero->attribute_name = $hero_entry['AttributeName'];
                $hero->translations = $hero_entry['Translations'];
                $hero->save();
            } else {
                $hero = Hero::where('title', $hero_entry['PrimaryName'])->firstOrFail();
                $hero->translations = $hero_entry['Translations'];
                $hero->save();
            }
        }
        HeroUpdater::updateTalents();
    }

    //deprecated?
    public static function updateTalentsHotslogs($hero)
    {
        set_time_limit(60);
        defined('htmldom') or (include('simple_html_dom.php'));
        defined('htmldom') or define('htmldom',0);
        $ch = curl_init("https://www.hotslogs.com/Sitewide/HeroDetails?Hero=".urlencode($hero->title));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $html_response = curl_exec($ch);
        curl_close($ch);
        $html = str_get_html($html_response);
        $c = 0;
        $talents = [];
        foreach($html->find('div#talentDetails div#ctl00_MainContent_ctl00_MainContent_RadGridHeroTalentStatisticsPanel div table tbody tr') as $tab) {
            $c++;
            $tier = 0;
            $choice = 0;
            //skip first (header)
            if ($c == 1) {
                continue;
            }
            //now, we have either a talent or a level x subheader
            if ($tab->find('td span.rgGroupHeaderText')) {
                //level x subheader
                $tier++;
                $choice = 0;
            } else {
                $choice++;
                $image_url = null;
                foreach ($tab->find('img') as $imag) {
                    $image_url = 'https:'.$imag->src;
                }
                $talent_title = $tab->find('td')[3]->innertext;
                $talents[] = ['tier' => $tier, 'choice' => $choice, 'name' => $talent_title, 'icon' => ['small' => $image_url]];
                //trigger_error(json_encode(['tier' => $tier, 'choice' => $choice, 'name' => $talent_title, 'icon' => ['small' => $image_url]]));
            }
        }
        //trigger_error($talents[8]['icon']['small']);
        return ['talents' => $talents];
    }

    public static function updateTalentsHotsAPI()
    {
        $theme = Theme::getActiveTheme();
        $theme_path = $theme->getPath();
        defined('DS') or define('DS', DIRECTORY_SEPARATOR);
        $talent_image_path = $theme_path.DS.'assets'.DS.'img'.DS.'talents';
        if (!file_exists($talent_image_path)) {
            mkdir($talent_image_path, 0777, true);
        }
        $ch = curl_init("http://hotsapi.net/api/v1/heroes");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        curl_close($ch);
        $decoded_heroes = json_decode($response, true);
        foreach ($decoded_heroes as $hero) {
            set_time_limit(30);
            $heroModel = Hero::where('title', $hero['name'])->first();
            if ($heroModel != null) {
                foreach ($hero['talents'] as $talent) {
                    if (Talent::where('title', $talent['title'])->where('hero_id', $heroModel->id)->count() == 0) {
                        $tal = new Talent;
                        $tal->hero = $heroModel;
                        $tal->title = $talent['title'];
                        $temp_string_array = explode('/', $talent['icon_url']['64x64']);
                        $tal->image_url = end($temp_string_array);
                        $talent_url = "https://cdn.hotstat.us/images/" . end($temp_string_array);
                        $ch2 = curl_init($talent_url);
                        curl_setopt($ch2, CURLOPT_RETURNTRANSFER, true);
                        $talent_icon = curl_exec($ch2);
                        $contentType = curl_getinfo($ch2, CURLINFO_CONTENT_TYPE);
                        curl_close($ch2);
                        if ($contentType != 'application/xml') {
                            $file = fopen($talent_image_path.DS.$tal->image_url, "w+");
                            fputs($file, $talent_icon);
                            fclose($file);
                            Resizer::open($talent_image_path.DS.$tal->image_url)
                                ->resize(32, 32)
                                ->save($talent_image_path.DS.$tal->image_url, 100);
                        } else {
                            Log::error('Failed to get image for talent '.$talent['title']);
                        }
                        
                        $tal->suspected_replay_title = preg_replace("/[^A-Za-z0-9]/", '', $tal->title);
                        $tal->replay_title = $talent['name'];
                        $tal->save();
                        Log::info('New talent added: '.$talent['title']);
                    } elseif (Talent::where('title', $talent['title'])->where('hero_id', $heroModel->id)->where('replay_title', 'IS NOT', 'NULL')->count() == 0) {
                        $tal = Talent::where('title', $talent['title'])->where('hero_id', $heroModel->id)->firstOrFail();
                        $tal->replay_title = $talent['name'];
                        $tal->save();
                    }
                }
            } else {
                Log::error('Could not find hero '. $hero['name'] .' while updating talents!');
            }
        }
    }

    public static function getImages($hid)
    {
        $theme = Theme::getActiveTheme();
        $theme_path = $theme->getPath();
        defined('DS') or define('DS', DIRECTORY_SEPARATOR);
        $talent_image_path = $theme_path.DS.'assets'.DS.'img'.DS.'talents';
        if (!file_exists($talent_image_path)) {
            mkdir($talent_image_path, 0777, true);
        }
        $ch = curl_init("http://hotsapi.net/api/v1/heroes");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        curl_close($ch);
        $decoded_heroes = json_decode($response, true);
        foreach ($decoded_heroes as $hero) {
            set_time_limit(30);
            $heroModel = Hero::where('title', $hero['name'])->first();
            if ($heroModel != null && $heroModel->id == $hid) {
                foreach ($hero['talents'] as $talent) {
                    if (Talent::where('title', $talent['title'])->where('hero_id', $heroModel->id)->count() == 0) {
                        $tal = new Talent;
                        $tal->hero = $heroModel;
                        $tal->title = $talent['title'];
                        $tal->suspected_replay_title = preg_replace("/[^A-Za-z0-9]/", '', $tal->title);
                        $tal->replay_title = $talent['name'];
                        $temp_string_array = explode('/', $talent['icon_url']['64x64']);
                        $tal->image_url = end($temp_string_array);
                        $talent_url = "https://cdn.hotstat.us/images/" . end($temp_string_array);
                        $ch2 = curl_init($talent_url);
                        curl_setopt($ch2, CURLOPT_RETURNTRANSFER, true);
                        $talent_icon = curl_exec($ch2);
                        $contentType = curl_getinfo($ch2, CURLINFO_CONTENT_TYPE);
                        curl_close($ch2);
                        if ($contentType != 'application/xml') {
                            $file = fopen($talent_image_path.DS.$tal->image_url, "w+");
                            fputs($file, $talent_icon);
                            fclose($file);
                            Resizer::open($talent_image_path.DS.$tal->image_url)
                                ->resize(32, 32)
                                ->save($talent_image_path.DS.$tal->image_url, 100);
                        } else {
                            //Log::error('Failed to get image for talent '.$talent['title']);
                            $hotslogs_title = substr($talent['name'], strlen($heroModel->title));
                            HeroUpdater::fetchHotslogsImage($hotslogs_title, $talent['title'], end($temp_string_array), $tal->suspected_replay_title);
                        }
                        
                        $tal->save();
                        Log::info('New talent added: '.$talent['title']);
                    } else {
                        //just update image
                        $temp_string_array = explode('/', $talent['icon_url']['64x64']);
                        $talent_url = "https://cdn.hotstat.us/images/" . end($temp_string_array);
                        $ch2 = curl_init($talent_url);
                        curl_setopt($ch2, CURLOPT_RETURNTRANSFER, true);
                        $talent_icon = curl_exec($ch2);
                        $contentType = curl_getinfo($ch2, CURLINFO_CONTENT_TYPE);
                        curl_close($ch2);
                        if ($contentType != 'application/xml') {
                            $file = fopen($talent_image_path.DS.end($temp_string_array), "w+");
                            fputs($file, $talent_icon);
                            fclose($file);
                            Resizer::open($talent_image_path.DS.end($temp_string_array))
                                ->resize(32, 32)
                                ->save($talent_image_path.DS.end($temp_string_array), 100);
                        } else {
                            //Log::error('Failed to get image for talent '.$talent['title']);
                            $hotslogs_title = substr($talent['name'], strlen($heroModel->title));
                            HeroUpdater::fetchHotslogsImage($hotslogs_title, $talent['title'], end($temp_string_array), preg_replace("/[^A-Za-z0-9]/", '', $talent['title']));
                        }
                    }  
                }
            }
        }
    }

    public static function updateTalents()
    {
        HeroUpdater::updateTalentsHotsAPI();
    }

    public static function getHeroHotsAPI($hero)
    {
        $ch = curl_init("http://hotsapi.net/api/v1/heroes/".urlencode($hero->title));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        curl_close($ch);
        return json_decode($response, true);
    }

    public static function updateTalentsForHero($hero, $version = null)
    {
    	$theme = Theme::getActiveTheme();
        $theme_path = $theme->getPath();
        defined('DS') or define('DS', DIRECTORY_SEPARATOR);
        $talent_image_path = $theme_path.DS.'assets'.DS.'img'.DS.'talents';
        if (!file_exists($talent_image_path)) {
            mkdir($talent_image_path, 0777, true);
        }
        set_time_limit(60);
        $decoded_hero = HeroUpdater::getHeroHotsAPI($hero);
        if (array_key_exists('talents', $decoded_hero)) {
            foreach ($decoded_hero['talents'] as $talent) {
                if (Talent::where('title', $talent['title'])->where('hero_id', $hero->id)->count() == 0) {

                    $ch2 = curl_init($talent['icon_url']['64x64']);
                    curl_setopt($ch2, CURLOPT_RETURNTRANSFER, true);
                    $talent_icon = curl_exec($ch2);
                    $contentType = curl_getinfo($ch2, CURLINFO_CONTENT_TYPE);
                    curl_close($ch2);

                    $tal = new Talent;
                    $tal->hero = $hero;
                    $tal->title = $talent['title'];
                    $tal->suspected_replay_title = preg_replace("/[^A-Za-z0-9]/", '', $tal->title);
                    $tal->replay_title = $talent['name'];
                    $temp_string_array = explode('/', $talent['icon_url']['64x64']);
                    $tal->image_url = end($temp_string_array);
                    if ($contentType != 'application/xml') {
                        $file = fopen($talent_image_path.DS.$tal->image_url, "w+");
                        fputs($file, $talent_icon);
                        fclose($file);
                        Resizer::open($talent_image_path.DS.$tal->image_url)
                            ->resize(32, 32)
                            ->save($talent_image_path.DS.$tal->image_url, 100);
                    } else {
                        //Log::error('Failed to get image for talent '.$talent['title']);
                        $hotslogs_title = substr($talent['name'], strlen($heroModel->title));
                        HeroUpdater::fetchHotslogsImage($hotslogs_title, $talent['title'], end($temp_string_array), preg_replace("/[^A-Za-z0-9]/", '', $talent['title']));
                    }
                    
                    
                    $tal->save();
                    Log::info('New talent added: '.$talent['title']);
                } elseif (Talent::where('title', $talent['title'])->where('hero_id', $hero->id)->where('replay_title', 'IS NOT', 'NULL')->count() == 0) {
                    $tal = Talent::where('title', $talent['title'])->where('hero_id', $hero->id)->firstOrFail();
                    $tal->replay_title = $talent['name'];
                    $tal->save();
                }
            }
            return $decoded_hero;
        } else {
            Log::error('No talents found during talent update: '.json_encode($decoded_hero));
            return null;
        }
    }

    public static function fetchHotslogsImage($talent_name, $talent_title, $image_url, $secondTalentName) {
        $theme = Theme::getActiveTheme();
        $theme_path = $theme->getPath();
        defined('DS') or define('DS', DIRECTORY_SEPARATOR);
        $talent_image_path = $theme_path.DS.'assets'.DS.'img'.DS.'talents';

        $ch2 = curl_init("https://d1i1jxrdh2kvwy.cloudfront.net/Images/Talents/".$talent_name.".png");
        curl_setopt($ch2, CURLOPT_RETURNTRANSFER, true);
        $talent_icon = curl_exec($ch2);
        $contentType = curl_getinfo($ch2, CURLINFO_CONTENT_TYPE);
        curl_close($ch2);
        if ($contentType != 'application/xml') {
            $file = fopen($talent_image_path.DS.$image_url, "w+");
            fputs($file, $talent_icon);
            fclose($file);

            Resizer::open($talent_image_path.DS.$image_url)
                ->resize(32, 32)
                ->save($talent_image_path.DS.$image_url, 100);
        } else {
            $ch3 = curl_init("https://d1i1jxrdh2kvwy.cloudfront.net/Images/Talents/".$secondTalentName.".png");
            curl_setopt($ch3, CURLOPT_RETURNTRANSFER, true);
            $talent_icon = curl_exec($ch3);
            $contentType = curl_getinfo($ch3, CURLINFO_CONTENT_TYPE);
            curl_close($ch3);
            if ($contentType != 'application/xml') {
                $file = fopen($talent_image_path.DS.$image_url, "w+");
                fputs($file, $talent_icon);
                fclose($file);

                Resizer::open($talent_image_path.DS.$image_url)
                    ->resize(32, 32)
                    ->save($talent_image_path.DS.$image_url, 100);
            } else {
                //final try
                //capitalize
                $final_name = preg_replace_callback('/(?<=( |-))./',
                      function ($m) { return strtoupper($m[0]); },
                      $talent_title);
                //remove spaces
                $final_name = preg_replace("/[^A-Za-z0-9]/", '', $final_name);

                $ch4 = curl_init("https://d1i1jxrdh2kvwy.cloudfront.net/Images/Talents/".$final_name.".png");
                curl_setopt($ch4, CURLOPT_RETURNTRANSFER, true);
                $talent_icon = curl_exec($ch4);
                $contentType = curl_getinfo($ch4, CURLINFO_CONTENT_TYPE);
                curl_close($ch4);
                if ($contentType != 'application/xml') {
                    $file = fopen($talent_image_path.DS.$image_url, "w+");
                    fputs($file, $talent_icon);
                    fclose($file);

                    Resizer::open($talent_image_path.DS.$image_url)
                        ->resize(32, 32)
                        ->save($talent_image_path.DS.$image_url, 100);
                } else {
                    Log::error('Failed to get image for talent '.$talent_name);
                }
            }
        }
    }

    public static function getHeroesList()
    {
        sleep(1);
    	$ch = curl_init("https://api.masterleague.net/heroes/?format=json");
    	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    	$hero_list_json = curl_exec($ch);
    	curl_close($ch);
    	$decoded = json_decode($hero_list_json, true);
    	$hero_list = $decoded['results'];
    	while (array_key_exists('next', $decoded) && $decoded['next'] != null) {
    		sleep(1);
    		$ch = curl_init($decoded['next']);
    		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    		$hero_list_json = curl_exec($ch);
    		curl_close($ch);
    		$decoded = json_decode($hero_list_json, true);
    		$hero_list = array_merge($hero_list, $decoded['results']);
    	}
    	return $hero_list;
    }

    public static function addMasterleagueIds()
    {
    	$hero_list = HeroUpdater::getHeroesList();
    	foreach ($hero_list as $hero_entry) {
    		$hero = Hero::where('title', $hero_entry['name'])->first();
    		if ($hero) {
    			$hero->masterleague_id = $hero_entry['id'];
    		} else {
    			$hero = Hero::where('translations', 'LIKE', '%'.$hero_entry['name'].'%')->first();
    			if ($hero) {
    				$hero->masterleague_id = $hero_entry['id'];
    			} else {
    				trigger_error($hero_entry['name']);
    			}
    		}
    		$hero->save();
    	}
    }

    public static function addTranslationsToHeroes()
    {
        set_time_limit(600);

        $ch = curl_init("https://api.hotslogs.com/Public/Data/Heroes");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $hero_list_json = curl_exec($ch);
        curl_close($ch);
        $hero_list = json_decode($hero_list_json, true);

        foreach ($hero_list as $hero_entry) {
            $hero = Hero::where('title', $hero_entry['PrimaryName'])->first();
            if ($hero) {
                $hero->translations = $hero_entry['Translations'];
                $hero->save();
            }
        }
    }

    public static function addTranslationsToMaps()
    {
        set_time_limit(600);

        $ch = curl_init("https://api.hotslogs.com/Public/Data/Maps");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $map_list_json = curl_exec($ch);
        curl_close($ch);
        $map_list = json_decode($map_list_json, true);

        foreach ($map_list as $map_entry) {
            $map = Map::where('title', $map_entry['PrimaryName'])->first();
            if ($map) {
                $map->translations = $map_entry['Translations'];
                $map->save();
            }
        }
    }
}
