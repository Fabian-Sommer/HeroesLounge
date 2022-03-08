<?php namespace Rikki\Heroeslounge\Components;

 
use Cms\Classes\ComponentBase;
use Rikki\Heroeslounge\Models\Match;
use Rikki\Heroeslounge\Models\Team as Teams;
use Rikki\Heroeslounge\Models\Season as Seasons;
use Rikki\Heroeslounge\Models\Division as Divisions;
use Rikki\Heroeslounge\Models\Match as Matches;
use Rikki\Heroeslounge\Models\Sloth as Sloths;
use Rikki\Heroeslounge\Classes\Helpers\TimezoneHelper;

use Input;
use Request;
use Carbon\Carbon;
use Log;
use RainLab\User\facades\Auth;

class UpcomingMatches extends ComponentBase
{
    public $user = null;
    public $timezone = null;
    public $timezoneOffset = null;
    public $timeFormat = null;
    public $datesToMatches = null;
    public $showLogo = false;
    public $showName = false;
    public $showCasters = false;
    public $idApp = "";
    public $eid = 0;

    public function componentDetails()
    {
        return [
            'name'        => 'Upcoming Matches',
            'description' => 'Allows users to view upcoming matches'
        ];
    }

    public function init()
    {
        $this->addJs('/plugins/rikki/heroeslounge/assets/js/ResizeSensor.js');
        $this->addJs('/plugins/rikki/heroeslounge/assets/js/ElementQueries.js');
        $this->addCss('/plugins/rikki/heroeslounge/assets/css/heroeslounge.css');
    }

    public function onRender()
    {
        $this->showLogo = ($this->property('showLogo') == true) ? true : false;
        $this->showName = ($this->property('showName') == true) ? true : false;
        $this->showCasters = ($this->property('showCasters') == true) ? true : false;
        $this->idApp = $this->property("casterFilter");
        $this->eid = $this->property('id');

        $this->user = Auth::getUser();
        $this->timezone = TimezoneHelper::getTimezone();
        $this->timezoneOffset = TimezoneHelper::getTimezoneOffset();
        $this->timeFormat = TimezoneHelper::getTimeFormatString();
        $this->collectMatches($this->timezone, $this->eid);
    }

    public function collectMatches($timezoneName, $id)
    {
        $type = $this->property('type');
        $daysInFuture = $this->property('daysInFuture');
        $matches = null;
        $myEntity = null;
        switch ($type) {
            case 'team':
                $myEntity = Teams::find($id);
                break;
            case 'season':
                $myEntity = Seasons::find($id);
                break;
            case 'division':
                $myEntity = Divisions::find($id);
                break;
            case 'caster':
                $myEntity = Sloths::find($id);
                break;
            case 'all':
                $myEntity = 1;
                break;
        }
        if ($myEntity) {
            if ($type == 'all') {
                $matches = Matches::with('teams', 'teams.logo', 'casters', 'channels', 'division')->where('winner_id', null)->where('wbp', '>=', Carbon::today())->where('wbp', '<=', Carbon::today()->addDays($daysInFuture))->orderBy('wbp', 'asc')->get();
            } elseif ($type == 'caster') {
                if ($this->idApp == 'denied') {
                    $matches = $myEntity->castMatches()->where('rikki_heroeslounge_match_caster.approved', '=', '2')->where('winner_id', null)->orderBy('wbp', 'asc')->where('wbp', '>=', Carbon::today())->where('wbp', '<=', Carbon::today()->addDays($daysInFuture))->get();
                } elseif ($this->idApp == 'accepted') {
                    $matches = $myEntity->castMatches()->where('rikki_heroeslounge_match_caster.approved', '=', '1')->where('winner_id', null)->orderBy('wbp', 'asc')->where('wbp', '>=', Carbon::today())->where('wbp', '<=', Carbon::today()->addDays($daysInFuture))->get();
                } elseif ($this->idApp == 'pending') {
                    $matches = $myEntity->castMatches()->where('rikki_heroeslounge_match_caster.approved', '=', '0')->where('winner_id', null)->orderBy('wbp', 'asc')->where('wbp', '>=', Carbon::today())->where('wbp', '<=', Carbon::today()->addDays($daysInFuture))->get();
                } else {
                    $matches = $myEntity->castMatches()->orderBy('wbp', 'asc')->where('winner_id', null)->where('wbp', '>=', Carbon::today())->where('wbp', '<=', Carbon::today()->addDays($daysInFuture))->get();
                }
            } else {
                $matches = $myEntity->matches()->with('teams', 'teams.logo', 'casters', 'channels')->orderBy('wbp', 'asc')->where('wbp', '>=', Carbon::today())->where('wbp', '<=', Carbon::today()->addDays($daysInFuture))->get();
            }
            $this->datesToMatches = $matches->groupBy(
                function ($match) use ($timezoneName) {
                    return Carbon::parse($match->wbp)->setTimezone($timezoneName)->format('d-M-y');
                }
            );
        }
    }

    public function onCastRequest()
    {
        $match = Match::findOrFail(input('match_id'));
        $match->casters()->syncWithoutDetaching(Sloths::findOrFail(input('caster_id')));
        $divId = "#divCasterRequests" . input('match_id');
        return [
            $divId => $this->renderPartial('@casterRequests', ['user' => Auth::getUser(), 'match' => $match])
        ];
    }

    public function onCastRetract()
    {
        $match = Match::findOrFail(input('match_id'));
        $match->casters()->detach(Sloths::findOrFail(input('caster_id')));
        $divId = "#divCasterRequests" . input('match_id');
        return [
            $divId => $this->renderPartial('@casterRequests', ['user' => Auth::getUser(), 'match' => $match])
        ];
    }

    public function getIdOptions()
    {
        $type = Request::input('type');
        $myData = [];
        switch ($type) {
                case 'team':
                    $myData = Teams::all();
                    break;
                case 'season':
                    $myData = Seasons::all();
                    break;
                case 'division':
                    $myData = Divisions::all();
                    break;
                case 'caster':
                    $myData = Sloths::isCaster()->get();
                    break;
                /* CURRENTLY NOT IMPLEMENTED */
                /*case 'playoff':
                    $myData = Playoffs::find($id);
                    break;*/
                case 'all':
                    $myData[0] = 'All';
                    return $myData;
            }
        $retOptions = [];
        foreach ($myData as $entity) {
            if ($type == 'caster') {
                $retOptions[$entity->id] = $entity->slothtitle;
            } else {
                $retOptions[$entity->id] = $entity->title;
            }
        }
        return $retOptions;
    }

    public function defineProperties()
    {
        return [
            'daysInFuture' => [
                'title' => 'Days in Future',
                'description' => 'Number of days to grab Matches from',
                'default' => 7,
                'type' => 'string',
                'validationPattern' => '^[0-9]+$',
                'validationMessage' => 'The daysInFuture property can contain only numeric symbols'
            ],
            'showLogo' => [
                'title' => 'Show Logo',
                'description' => 'Decides whether or not to show Team Logos',
                'default' => false,
                'type' => 'checkbox'
            ],
            'showName' => [
                'title' => 'Show TeamName',
                'description' => 'Decides whether or not to show Region Name',
                'default' => true,
                'type' => 'checkbox'
            ],
            'showCasters' => [
                'title' => 'Show Casters',
                'description' => 'Decides whether or not to show Casters',
                'default' => true,
                'type' => 'checkbox'
            ],
            'type' => [
                'title' => 'Type',
                'description' => 'Entity type of which upcoming Matches be shown',
                'type' => 'dropdown',
                'placeholder' => 'Select Entity',
                'required' => 'true',
                'default' => 'all',
                'options' => ['all' => 'All','team' => 'Team','season' => 'Season','division' => 'Division', 'caster' => 'Caster', 'playoff'=>'Playoff']
            ],
            'id' => [
                'title' => 'Entity',
                'description' => 'The specific Entity to take data from - not needed for All',
                'default' => 'all',
                'type' => 'dropdown',
                'depends' => ['type'],
                'placeholder' => 'Select specific Entity'
            ],
            'casterFilter' => [
                'title' => 'Caster Filter',
                'description' => 'Used to display matches to casters',
                'type' => 'dropdown',
                'required' => 'false',
                'default' => 'off',
                'options' => ['off' => 'Off','pending' => 'Pending','accepted' => 'Accepted','denied' => 'Denied']
            ],
        ];
    }
}
