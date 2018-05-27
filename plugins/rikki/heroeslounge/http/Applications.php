<?php namespace Rikki\Heroeslounge\Http;

use Backend\Classes\Controller;

/**
 * Applications Back-end Controller
 */
class Applications extends Controller
{
    public $implement = [
        'Mohsin.Rest.Behaviors.RestController'
    ];

    public $restConfig = 'config_rest.yaml';

}
