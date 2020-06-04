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
        $hero_list = SELF::getHeroesList();
        foreach($hero_list as $hero_entry) {
            if (Hero::where('title', $hero_entry['name'])->count() == 0) {
                $hero_data = Self::getHero($hero_entry['short_name']);
                $hero = new Hero();
                $hero->title = $hero_entry['name'];
                $hero->attribute_name = $hero_data['attributeId'];
                $hero->translations = implode(",", $hero_entry['translations']);
                $hero->save();
                Self::setHeroImage($hero, $hero_entry['short_name']);
            }
        }

        Self::updateTalents();
    }

    public static function updateTalents()
    {
        $hero_list = SELF::getHeroesList();
        foreach($hero_list as $hero_entry) {
            $hero_model = Hero::where('title', $hero_entry['name'])->first();
            if ($hero_model) {
                Self::updateTalentsForHero($hero_model, $hero_entry['short_name']);
            } else {
                Log::error('Could not find hero '. $hero_entry['name'] .' while updating talents!');
            }
        }
    }

    public static function updateTalentsForHero($hero_model, $hero_short_name)
    {
        $hero = Self::getHero($hero_short_name);
        foreach($hero['talents'] as $talent_tier) {
            foreach($talent_tier as $talent_data) {
                if (Talent::where('title', 'IS NOT', $talent_data['name'])->where('hero_id', $hero_model->id)->where('replay_title', $talent_data['talentTreeId'])->first()) {
                    // We encountered this talent earlier during replay parsing, but weren't able to populate all of it's data at the time.
                    $talent = Talent::where('replay_title', $talent_data['talentTreeId'])->where('hero_id', $hero_model->id)->firstOrFail();
                    $talent->title = $talent_data['name'];
                    $talent->save();
                    Self::setTalentImage($talent, $talent_data['icon']);
                    Log::info('Talent information updated: ' . $talent_data['name']);
                } elseif (Talent::where('title', $talent_data['name'])->where('hero_id', $hero_model->id)->count() == 0) {
                    $talent = new Talent;
                    $talent->hero = $hero_model;
                    $talent->title = $talent_data['name'];
                    $talent->replay_title = $talent_data['talentTreeId'];
                    $talent->save();
                    Self::setTalentImage($talent, $talent_data['icon']);
                    Log::info('New talent added: '.$talent_data['name']);
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

    public static function getHero($hero_short_name)
    {
        $hpn_hero_name = Self::getHeroNameForHeroesPatchNotes($hero_short_name);
        $ch = curl_init("https://heroespatchnotes.github.io/heroes-talents/hero/" . urlencode($hpn_hero_name) . ".json");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $hero_json = curl_exec($ch);
        curl_close($ch);
        $hero = json_decode($hero_json, true);

        return $hero;
    }

    public static function setTalentImage($talent_model, $icon_url)
    {
        $theme = Theme::getActiveTheme();
        $theme_path = $theme->getPath();
        defined('DS') or define('DS', DIRECTORY_SEPARATOR);
        $talent_image_path = $theme_path.DS.'assets'.DS.'img'.DS.'talents';
        if (!file_exists($talent_image_path)) {
            mkdir($talent_image_path, 0777, true);
        }

        $ch = curl_init("https://heroespatchnotes.github.io/heroes-talents/images/talents/" . urlencode($icon_url));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $talent_icon = curl_exec($ch);
        $content_type = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
        curl_close($ch);
        if ($content_type != 'application/xml') {
            $file = fopen($talent_image_path.DS.$icon_url, "w+");
            fputs($file, $talent_icon);
            fclose($file);
            Resizer::open($talent_image_path.DS.$icon_url)
                ->resize(32, 32)
                ->save($talent_image_path.DS.$icon_url, 100);

            $talent_model->image_url = $icon_url;
            $talent_model->save();
        } else {
            Log::error('Failed to get image for talent icon '. $icon_url);
        }
    }

    public static function setHeroImage($hero_model, $hero_short_name)
    {
        $theme = Theme::getActiveTheme();
        $theme_path = $theme->getPath();
        defined('DS') or define('DS', DIRECTORY_SEPARATOR);
        $hero_image_path = $theme_path.DS.'assets'.DS.'img'.DS.'heroes';
        if (!file_exists($hero_image_path)) {
            mkdir($hero_image_path, 0777, true);
        }

        $hpn_hero_name = Self::getHeroNameForHeroesPatchNotes($hero_short_name);
        $ch = curl_init("https://heroespatchnotes.github.io/heroes-talents/images/heroes/" . urlencode($hpn_hero_name) . ".png");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $hero_portrait = curl_exec($ch);
        $content_type = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
        curl_close($ch);
        if ($content_type != 'application/xml') {
            $portrait_url = ucfirst($hero_short_name);
            $file = fopen($hero_image_path.DS.$portrait_url, "w+");
            fputs($file, $hero_portrait);
            fclose($file);
            Resizer::open($hero_image_path.DS.$portrait_url)
                ->resize(75, 75)
                ->save($hero_image_path.DS.$portrait_url, 100);

            $hero_model->image_url = $portrait_url;
            $hero_model->save();
        } else {
            Log::error('Failed to get image for hero portrait '. $hero_short_name);
        }
    }

    public static function getHeroNameForHeroesPatchNotes($hero_short_name)
    {
        if ($hero_short_name == 'thelostvikings') {
            return 'lostvikings';
        } else if ($hero_short_name == 'cho') {
            return 'chogall';
        }

        return $hero_short_name;
    }
}
