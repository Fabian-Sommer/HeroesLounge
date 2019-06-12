<?php namespace Rikki\Heroeslounge\Components;

use Cms\Classes\ComponentBase;
use Auth;
use Rikki\Heroeslounge\Models\Match;
use Input;
use Redirect;
use Log;
use DateTime;
use DateTimeZone;
use Flash;
use Carbon\Carbon;
use Rikki\Heroeslounge\Classes\Helpers\TimezoneHelper;

class ScheduleMatch extends ComponentBase
{
    public function componentDetails()
    {
        return [
            'name'        => 'Schedule Match',
            'description' => 'Allows Captains to schedule a match'
        ];
    }

    public $match = null;
    public $opp = null;
    public $user = null;
    public $timezone = null;
    public $timezoneOffset = null;
    public $datetimeFormat = null;

    public function init()
    {
        $this->addCss('/plugins/rikki/heroeslounge/assets/css/jquery.datetimepicker.css');
        $this->addCss('/plugins/rikki/heroeslounge/assets/css/heroeslounge.css');
        $this->addJs('/plugins/rikki/heroeslounge/assets/js/jquery.datetimepicker.full.js');
    }

    public function onRender()
    {
        $this->user = Auth::getUser();
        $this->match = Match::find($this->property('id'));
        if ($this->match) {
            $sloth = $this->user->sloth;
            $ownTeam = $this->match->teams->filter(function ($team) use ($sloth) {
                return $team->sloths->contains($sloth);
            })->first();
            if ($ownTeam) {
                $this->opp = $this->match->teams()->where('team_id', '!=', $ownTeam->id)->first();
            }
        }
        $this->timezone = TimezoneHelper::getTimezone();
        $this->timezoneOffset = TimezoneHelper::getTimezoneOffset();
        $this->datetimeFormat = TimezoneHelper::getDateTimeFormatString();
    }

    public function onSaveDate()
    {
        $match = Match::find(post('match_id'));
        if ($match) {
            $date = post('date');
            $timezone = TimezoneHelper::getTimezone();
            if ($date != null) {
                try {
                    $x = new DateTime($date, new DateTimeZone($timezone));
                    $x->setTimezone(new DateTimeZone(TimezoneHelper::defaultTimezone()));
                    $match->wbp = $x->format('Y-m-d H:i:s');
                    if ($match->tbp != null && Carbon::parse($match->wbp) < Carbon::parse($match->tbp)) {
                        $match->save();
                        Flash::success('Match has been successfully scheduled for '.$date);
                    } else {
                        $y = new DateTime($match->tbp, new DateTimeZone(TimezoneHelper::defaultTimezone()));
                        $y->setTimezone(new DateTimeZone($timezone));
                        Flash::error('The match has to be played before ' . $y->format('d M Y H:i'));
                    }
                } catch (Exception $e) {
                    Flash::error($e->getMessage());
                    Log::info($e->getMessage());
                } finally {
                    return Redirect::refresh();
                }
            } else {
                Flash::error('Please provide a date!');
            }
        }
    }

    public function defineProperties()
    {
        return [
            'id' => [
                'title' => 'MatchID',
                'description' => 'MatchID to grab data from',
                'type' => 'string',
                'required' => true,
                'validationPattern' => '^[0-9]+$',
                'validationMessage' => 'The MatchID property can contain only numeric symbols'
            ]
        ];
    }
}
