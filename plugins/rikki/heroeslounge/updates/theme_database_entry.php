<?php namespace Rikki\Heroeslounge\Updates;

use Db;
use October\Rain\Database\Updates\Migration;

class ThemeDatabaseEntry extends Migration
{
    public function up()
    {
        Db::table('cms_theme_data')->where('theme','HeroesLounge-Theme')->update(['data'=>'{"0":"","site_title":"Heroes Lounge","site_locale":"en","color":"blue","header_email":"contact@heroeslounge.com","footer_email":"contact@heroeslounge.com","footer_facebook":"https:\/\/www.facebook.com\/heroeslounge.gg\/","social_facebook":"https:\/\/www.facebook.com\/heroeslounge.gg\/","social_twitter":"https:\/\/twitter.com\/hotslounge","copyright_first":"Copyright 2017","copyright_second":"Heroes Lounge","intro_fullscreen":"0","header_phone":"","footer_address":"","footer_phone":"","social_instagram":"","social_google":"","social_linkedin":""}']);
    }
    
    public function down()
    {
       
    }
}