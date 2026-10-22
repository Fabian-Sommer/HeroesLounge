<?php namespace Rikki\Heroeslounge\Http;

use App;
use Backend\Classes\Controller;
use Rikki\Heroeslounge\Models\MatchModel;
use Rikki\Heroeslounge\Models\Map;
use Rikki\Heroeslounge\Models\MapBan as BanModel;
use Rikki\Heroeslounge\Models\Team;
use Carbon\Carbon;
/**
 * MapBan Back-end Controller
 */
class MapBan extends Controller
{
    public $implement = [
        'Mohsin.Rest.Behaviors.RestController'
    ];

    public $restConfig = 'config_rest.yaml';

    public function indexAll()
    {
        return BanModel::all();
    }

    public function getForMatch($match_id) {
        $match = MatchModel::findOrFail($match_id);
        return $match->mapBans;
    }

    public function addMapBan($match_id, $team_id, $map_id) {
        $map = Map::findOrFail($map_id);
        $match = MatchModel::findOrFail($match_id);
        $team = $match->teams->where('id', '=', $team_id)->first();
        if ($team == null) {
            App::abort(400);
        }
        $MapBan = new BanModel;
        $MapBan->map = $map;
        $MapBan->match = $match;
        $MapBan->team = $team;
        $MapBan->save();
    }

    public function delete($id) {
        BanModel::findOrFail($id)->delete();
    }
}
