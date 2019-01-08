<?php namespace Rikki\Heroeslounge\Components;

 
use Cms\Classes\ComponentBase;
use Rikki\Heroeslounge\Models\Match;
use Rikki\Heroeslounge\Models\Team as Teams;
use Rikki\Heroeslounge\Models\Season as Seasons;
use Rikki\Heroeslounge\Models\Division as Divisions;
use Rikki\Heroeslounge\Models\Match as Matches;
use Rikki\Heroeslounge\Models\Sloth as Sloths;

use Input;
use Request;
use Carbon\Carbon;
use Log;
use RainLab\User\facades\Auth;

class UpcomingMatches extends ComponentBase
{
    public $groupMatches = null;
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
        
    }

    public function collectMatches($timezoneName, $id)
    {
        $this->showLogo = ($this->property('showLogo') == true) ? true : false;
        $this->showName = ($this->property('showName') == true) ? true : false;
        $this->showCasters = ($this->property('showCasters') == true) ? true : false;
        $this->idApp = $this->property("casterFilter");
        $type = $this->property('type');
        
        $daysInFuture = $this->property('daysInFuture');
        $casterFilter = $this->property('casterFilter');
        $myData = null;
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
                $myData = Matches::with('teams', 'teams.logo', 'casters')->where('winner_id', null)->where('wbp', '>=', Carbon::today())->where('wbp', '<=', Carbon::today()->addDays($daysInFuture))->orderBy('wbp', 'asc')->get();
            } elseif ($type == 'caster') {
                if ($casterFilter == 'denied') {
                    $myData = $myEntity->castMatches()->where('rikki_heroeslounge_match_caster.approved', '=', '2')->where('winner_id', null)->orderBy('wbp', 'asc')->where('wbp', '>=', Carbon::today())->where('wbp', '<=', Carbon::today()->addDays($daysInFuture))->get();
                } elseif ($casterFilter == 'accepted') {
                    $myData = $myEntity->castMatches()->where('rikki_heroeslounge_match_caster.approved', '=', '1')->where('winner_id', null)->orderBy('wbp', 'asc')->where('wbp', '>=', Carbon::today())->where('wbp', '<=', Carbon::today()->addDays($daysInFuture))->get();
                } elseif ($casterFilter == 'pending') {
                    $myData = $myEntity->castMatches()->where('rikki_heroeslounge_match_caster.approved', '=', '0')->where('winner_id', null)->orderBy('wbp', 'asc')->where('wbp', '>=', Carbon::today())->where('wbp', '<=', Carbon::today()->addDays($daysInFuture))->get();
                } else {
                    $myData = $myEntity->castMatches()->orderBy('wbp', 'asc')->where('winner_id', null)->where('wbp', '>=', Carbon::today())->where('wbp', '<=', Carbon::today()->addDays($daysInFuture))->get();
                }
            } else {
                $myData = $myEntity->matches()->with('teams', 'teams.logo', 'casters')->orderBy('wbp', 'asc')->where('wbp', '>=', Carbon::today())->where('wbp', '<=', Carbon::today()->addDays($daysInFuture))->get();
            }
            $matches = $myData->groupBy(
                                function ($match) use ($timezoneName) {
                                    $x = Carbon::parse($match->wbp)->setTimezone($timezoneName);
                                    return $x->format('d-M-y');
                                }
            );
            $this->groupMatches = $matches;
        }
    }

    public function onMyRender()
    {
        $timezoneoffset = (int)$_POST['time'];
        $timezoneName = "Europe/Berlin";
        if (isset($_POST['timezone'])) {
            $timezoneName = $_POST['timezone'];
        }

        $id = 0;
        if (isset($_POST['id'])) {
            $id = $_POST['id'];
        }

        if (!in_array($timezoneName, timezone_identifiers_list())) {
            $timezoneName = "Europe/Berlin";
        }
        $this->idApp = $this->property("casterFilter");
        if ($this->idApp != "accepted" && $this->idApp != "denied") {
            $this->collectMatches($timezoneName, $id);

        
            $containerId = "#upcomingMatches".$this->idApp;
            return [
                $containerId => $this->renderPartial('@calendar', ['user' => Auth::getUser(), 'groupMatches' => $this->groupMatches, 'timezone' => $timezoneName])
            ];
        }
    }

    public function onMyRenderAcceptedCasts()
    {
        $timezoneoffset = (int)$_POST['time'];
        $timezoneName = "Europe/Berlin";
        if (isset($_POST['timezone'])) {
            $timezoneName = $_POST['timezone'];
        }

        if (!in_array($timezoneName, timezone_identifiers_list())) {
            $timezoneName = "Europe/Berlin";
        }
        $this->idApp = $this->property("casterFilter");
        if ($this->idApp == "accepted") {
            $this->collectMatches($timezoneName, $_POST['id']);
        
        
            $containerId = "#upcomingMatches".$this->idApp;
            return [
                $containerId => $this->renderPartial('@calendar', ['user' => Auth::getUser(), 'groupMatches' => $this->groupMatches, 'timezone' => $timezoneName])
            ];
        }
    }

    public function onMyRenderDeniedCasts()
    {
        $timezoneoffset = (int)$_POST['time'];
        $timezoneName = "Europe/Berlin";
        if (isset($_POST['timezone'])) {
            $timezoneName = $_POST['timezone'];
        }

        if (!in_array($timezoneName, timezone_identifiers_list())) {
            $timezoneName = "Europe/Berlin";
        }
        $this->idApp = $this->property("casterFilter");

        if ($this->idApp == "denied") {

            $this->collectMatches($timezoneName, $_POST['id']);
        
            $containerId = "#upcomingMatches".$this->idApp;
            return [
                $containerId => $this->renderPartial('@calendar', ['user' => Auth::getUser(), 'groupMatches' => $this->groupMatches, 'timezone' => $timezoneName])
            ];
        }
    }

    public function onCastRequest()
    {
        $match = Match::findOrFail(input('match_id'));
        $match->casters()->attach(Sloths::findOrFail(input('caster_id')));
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
                'description' => 'Decides whether or not to show Team Names',
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
