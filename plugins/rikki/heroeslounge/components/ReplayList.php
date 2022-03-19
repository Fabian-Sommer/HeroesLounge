<?php namespace Rikki\Heroeslounge\Components;

use Cms\Classes\ComponentBase;

use Rikki\Heroeslounge\models\Hero;
use Rikki\Heroeslounge\models\Talent;
use Rikki\Heroeslounge\models\Sloth;
use Rikki\Heroeslounge\models\Team;
use Rikki\Heroeslounge\models\Game;
use Rikki\Heroeslounge\models\Match;
use Rikki\Heroeslounge\models\Season;
use Rikki\Heroeslounge\models\Division;

use Log;
use ZipArchive;

class ReplayList extends ComponentBase
{
  public function componentDetails()
  {
    return [
      'name'        => 'Replay List',
      'description' => 'Lists all replays of a division'
    ];
  }

  public $division = null;
  public $path = null;
  public function init()
  {
    Log::info($this->property('division_id'));
    $this->division = Division::find($this->property('division_id'));
    if (!$this->division) {
      return;
    }
    $this->division->load('matches', 'matches.games', 'matches.games.map', 'matches.games.replay');

    $zipFileName = 'replays.zip';
    $zip = new ZipArchive;
    $this->path = '/storage/temp/public/' . $zipFileName;
    $filepath = public_path() . $this->path;
    // Delete any previously existing file with the same name - otherwise, we would just extend the existing zipfile.
    if (is_file($filepath)) {
      unlink($filepath);
    }

    if (!$zip->open($filepath, ZipArchive::CREATE) === TRUE) {
      return;
    }
    foreach ($this->division->matches as $match) {
      foreach ($match->games as $game) {
        if ($game->replay) {
          $replaypatharray = explode('/', $game->replay->getPath());
          $replayname = end($replaypatharray);
          $zip->addFile($game->replay->getLocalPath(), $replayname);
        }
      }
    }
    $zip->close();
  }

  public function defineProperties()
  {
    return [
      'division_id' => [
        'title' => 'Division ID',
        'description' => 'Division ID to grab data from',
        'default' => 0,
        'type' => 'string',
      ]
    ];
  }
}
