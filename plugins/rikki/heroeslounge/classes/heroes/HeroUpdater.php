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
        $theme = Theme::getActiveTheme();
        $theme_path = $theme->getPath();
        defined('DS') or define('DS', DIRECTORY_SEPARATOR);
        $hero_image_path = $theme_path.DS.'assets'.DS.'img'.DS.'heroes';
        if (!file_exists($hero_image_path)) {
            mkdir($hero_image_path, 0777, true);
        }

        $hero_list = SELF::getHeroesList();
        foreach($hero_list as $hero_entry) {
            if (Hero::where('title', $hero_entry['name'])->count() == 0) {
                $heroData = Self::getHero($hero_entry['short_name']);
                $hero_portrait = Self::getHeroImage($hero_entry['short_name']);

                $localImageUrl = ucfirst($hero_entry['short_name']);
                $file = fopen($hero_image_path.DS.$localImageUrl.".png", "w+");
                fputs($file, $hero_portrait);
                fclose($file);

                $hero = new Hero();
                $hero->title = $hero_entry['name'];
                $hero->image_url = $localImageUrl;
                $hero->attribute_name = $hero_data['attributeId'];
                $hero->translations = implode(",", $hero_entry['translations']);
                $hero->save();
            }
        }

        Self::updateTalents();
    }

    public static function updateTalents()
    {
        $hero_list = SELF::getHeroesList();
        foreach($hero_list as $hero_entry) {
            $heroModel = Hero::where('title', $hero_entry['name'])->first();
            if ($heroModel) {
                Self::updateTalentsForHero($heroModel, $hero_entry['short_name']);
            } else {
                Log::error('Could not find hero '. $hero_entry['name'] .' while updating talents!');
            }
        }
    }

    public static function updateTalentsForHero($heroModel, $heroShortName)
    {
        $theme = Theme::getActiveTheme();
        $theme_path = $theme->getPath();
        defined('DS') or define('DS', DIRECTORY_SEPARATOR);
        $talent_image_path = $theme_path.DS.'assets'.DS.'img'.DS.'talents';
        if (!file_exists($talent_image_path)) {
            mkdir($talent_image_path, 0777, true);
        }

        $hero = Self::getHero($heroShortName);
        foreach($hero['talents'] as $talentTier) {
            foreach($talentTier as $talent_data) {
                if (Talent::where('title', $talent_data['name'])->where('hero_id', $heroModel->id)->count() == 0) {
                    $talent = new Talent;
                    $talent->hero = $heroModel;
                    $talent->title = $talent_data['name'];
                    $talent->image_url = $talent_data['icon'];
    
                    $ch = curl_init("https://heroespatchnotes.github.io/heroes-talents/images/talents/" . urlencode($talent_data['icon']));
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                    $talent_icon = curl_exec($ch);
                    $contentType = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
                    curl_close($ch);
                    if ($contentType != 'application/xml') {
                        $file = fopen($talent_image_path.DS.$talent->image_url, "w+");
                        fputs($file, $talent_icon);
                        fclose($file);
                        Resizer::open($talent_image_path.DS.$talent->image_url)
                            ->resize(32, 32)
                            ->save($talent_image_path.DS.$talent->image_url, 100);
                    } else {
                        Log::error('Failed to get image for talent '.$talent_data['name']);
                    }
                    
                    $talent->replay_title = $talent_data['name'];
                    $talent->save();
                    Log::info('New talent added: '.$talent_data['name']);
                } elseif (Talent::where('title', $talent_data['name'])->where('hero_id', $heroModel->id)->where('replay_title', 'IS NOT', 'NULL')->count() == 0) {
                    // Check if the talent has replay_title data, which we need to identify talents during replay parsing.
                    $talent = Talent::where('title', $talent_data['name'])->where('hero_id', $heroModel->id)->firstOrFail();
                    $talent->replay_title = $talent_data['talentTreeId'];
                    $talent->save();
                }
            }
        }
    }

    public static function addTranslationsToHeroes()
    {
        $hero_list = Self::getHeroesList();
        foreach ($hero_list as $hero_entry) {
            $hero = Hero::where('title', $hero_entry['name'])->first();
            if ($hero) {
                $hero->translations = implode(",", $hero_entry['translations']);
                $hero->save();
            }
        }
    }

    public static function getHeroesList()
    {
        $ch = curl_init("https://api.heroesprofile.com/openApi/Heroes");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $hero_list_json = curl_exec($ch);
        curl_close($ch);
        $hero_list = json_decode($hero_list_json, true);

        return $hero_list;
    }

    public static function getHero($hero_name)
    {
        $hero_name = Self::getHeroNameForHeroesPatchNotes($hero_name);
        $ch = curl_init("https://heroespatchnotes.github.io/heroes-talents/hero/" . urlencode($hero_name) . ".json");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $hero_json = curl_exec($ch);
        curl_close($ch);
        $hero = json_decode($hero_json, true);

        return $hero;
    }

    public static function getHeroImage($hero_name)
    {
        $hero_name = Self::getHeroNameForHeroesPatchNotes($hero_name);
        $ch = curl_init("https://heroespatchnotes.github.io/heroes-talents/images/heroes/" . urlencode($hero_name) . ".png");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $hero_portrait = curl_exec($ch);
        curl_close($ch);
        
        return $hero_portrait;
    }

    public static function getHeroNameForHeroesPatchNotes($hero_name)
    {
        if ($hero_name == 'thelostvikings') {
            $hero_name = 'lostvikings';
        } else if ($hero_name == 'cho') {
            $hero_name = 'chogall';
        }

        return $hero_name;
    }
}
