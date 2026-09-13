<?php namespace Rikki\LoungeStatistics\Http;

use Backend\Classes\Controller;
use Rikki\Heroeslounge\Models\Sloth as SlothModel;
use Rikki\HeroesLounge\Models\Season;

/**
 * Season Back-end Controller
 */
class Sloth extends Controller
{
    public $implement = [
        'Mohsin.Rest.Behaviors.RestController'
    ];

    public $restConfig = 'config_rest.yaml';

    public function herostatistics($id)
    {
        return SlothModel::findOrFail($id)->herostatistics(null);
    }

    public function detailedHerostatistics($id)
    {
        return SlothModel::findOrFail($id)->detailedHerostatistics(null);
    }

    public function seasonHerostatistics($id, $seasonId)
    {
        $season = Season::findOrFail($seasonId);
        return SlothModel::findOrFail($id)->herostatistics($season);
    }

    public function seasonDetailedHerostatistics($id, $seasonId)
    {
        $season = Season::findOrFail($seasonId);
        return SlothModel::findOrFail($id)->detailedHerostatistics($season);
    }
}
