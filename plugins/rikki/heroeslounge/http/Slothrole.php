<?php namespace Rikki\Heroeslounge\Http;

use Backend\Classes\Controller;
use Rikki\Heroeslounge\Models\SlothRole as SlothRoleModel;
use Input;
/**
 * Map Back-end Controller
 */
class SlothRole extends Controller
{
    public $implement = [
        'Mohsin.Rest.Behaviors.RestController'
    ];

    public $restConfig = 'config_rest.yaml';

    public function indexAll()
    {
        return SlothRoleModel::all();
    }
    
}
