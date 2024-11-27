<?php namespace Rikki\Heroeslounge\Components;

use Cms\Classes\ComponentBase;

use Rikki\Heroeslounge\models\Hero;
use Rikki\Heroeslounge\models\Talent;
use Rikki\Heroeslounge\models\Sloth;
use Rikki\Heroeslounge\models\Team;
use Rikki\Heroeslounge\models\Game;
use Rikki\Heroeslounge\models\Match;
use Rikki\Heroeslounge\models\Playoff;
use Rikki\Heroeslounge\models\Division;

use Log;
use ZipArchive;

class PlayoffReplayList extends ComponentBase
{
  public function componentDetails()
  {
    return [
      'name'        => 'Playoff Replay List',
      'description' => 'Lists all replays of a playoff'
    ];
  }

  public $playoff = null;
  public $path = null;
  public function init()
  {
    Log::info($this->property('playoff_id'));
    $this->playoff = Playoff::find($this->property('playoff_id'));
    if (!$this->playoff) {
      return;
    }
    $this->playoff->load('divisions', 'divisions.matches', 'matches', 'matches.games', 'matches.games.map', 'matches.games.replay');

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
    // Add replays from group stage
    foreach ($this->playoff->divisions as $division) {
      foreach ($division->matches as $match) {
        foreach ($match->games as $game) {
          if ($game->replay) {
            $replaypatharray = explode('/', $game->replay->getPath());
            $replayname = end($replaypatharray);
            $zip->addFile($game->replay->getLocalPath(), $replayname);
          }
        }
      }
    }
    // Add replays from bracket
    foreach ($this->playoff->matches as $match) {
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
      'playoff_id' => [
        'title' => 'Playoff ID',
        'description' => 'Playoff ID to grab data from',
        'default' => 0,
        'type' => 'string',
      ]
    ];
  }
}
