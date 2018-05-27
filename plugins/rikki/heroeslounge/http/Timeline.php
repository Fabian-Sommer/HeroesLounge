<?php namespace Rikki\Heroeslounge\Http;

use Backend\Classes\Controller;

/**
 * Timeline Back-end Controller
 */
class Timeline extends Controller
{
    public $implement = [
        'Mohsin.Rest.Behaviors.RestController'
    ];

    public $restConfig = 'config_rest.yaml';

}
