<?php namespace Rikki\Heroeslounge\Http;

use Backend\Classes\Controller;
use Rikki\Heroeslounge\Models\Match;
use Rikki\Heroeslounge\Models\SubstituteRegistration as SubModel;
use Rikki\Heroeslounge\Models\Sloth;
use Rikki\Heroeslounge\Models\Team;
use Carbon\Carbon;
/**
 * SubstituteRegistration Back-end Controller
 */
class SubstituteRegistration extends Controller
{
    public $implement = [
        'Mohsin.Rest.Behaviors.RestController'
    ];

    public $restConfig = 'config_rest.yaml';

    public function indexAll()
    {
        return SubModel::all();
    }

    public function getForMatch($match_id) {
        $match = Match::findOrFail($match_id);
        return $match->substituteRegistrations;
    }

    public function addSubstitute($sub_id, $registrant_id, $match_id) {
        $subSloth = Sloth::findOrFail($sub_id);
        $registrantSloth = Sloth::findOrFail($registrant_id);
        $match = Match::findOrFail($match_id);
        $team = $match->teams->filter(function ($team) use ($registrantSloth) {
            return $registrantSloth->teams->contains($team);
        })->first();
        if ($team == null) {
            App::abort(400);
        }
        $subReg = new SubModel;
        $subReg->subSloth = $subSloth;
        $subReg->registrantSloth = $registrantSloth;
        $subReg->match = $match;
        $subReg->team = $team;
        $subReg->save();
    }

    public function approveSubstitute($id) {
        SubModel::findOrFail($id)->approve();
    }

    public function delete($id) {
        SubModel::findOrFail($id)->delete();
    }
}
