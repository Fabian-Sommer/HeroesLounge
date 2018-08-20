<?php namespace Rikki\Heroeslounge\Components;

 
use Cms\Classes\ComponentBase;
use Rikki\Heroeslounge\Models\RssEntry as RssEntry;
use Carbon\Carbon;

class RssFeed extends ComponentBase
{
    public function componentDetails()
    {
        return [
            'name'        => 'RssFeed',
            'description' => 'Display Rssfeed'
        ];
    }

    public $entries = null;
    public function onRender()
    {
        $this->entries = RssEntry::where('publication_date', '<=', Carbon::now())->orderBy('publication_date', 'desc')->get();
    }
}
