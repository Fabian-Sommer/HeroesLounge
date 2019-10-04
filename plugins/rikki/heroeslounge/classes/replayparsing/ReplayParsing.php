<?php namespace Rikki\Heroeslounge\classes\ReplayParsing;

 

use Cms\Classes\Theme;

use October\Rain\Support\Collection;

use Rikki\Heroeslounge\Models\Map;
use Rikki\Heroeslounge\Models\Hero;
use Rikki\Heroeslounge\Models\Sloth;
use Rikki\Heroeslounge\Models\GameParticipation;
use Rikki\Heroeslounge\Models\Game;
use Rikki\Heroeslounge\Models\Match;
use Rikki\Heroeslounge\Models\Talent;
use Rikki\Heroeslounge\classes\Heroes\HeroUpdater;
use Rikki\Heroeslounge\classes\deployment\Deployment;
use Log;
use Db;

class ReplayParsing
{
    public $replay = null;
    public $match = null;
    public $game = null;
    public $uploadingTeam = null;
    private $decodedDetails = null;
    private $decodedAttributeEvents = null;
    private $decodedHeader = null;
    private $decodedTrackerEvents = null;
    private $allSlothNames = null;
    private $participationList = null;
    private $talentTierCounters = null;
    private $indizesToSlotId = null;
    private static $pythonPath = 'python2.7 ';//'python '

    public static function parseAllReplays($modify_winners = false)
    {
        $allGames = Game::all();
        foreach ($allGames as $game) {
            if ($game->replay != null) {
                Log::info('Parsing replay for game '. $game->id .' with disk name '. $game->replay->getLocalPath());
                ReplayParsing::parseReplayAndSave($game, $modify_winners);
            } else {
                Log::error('Game '.$game->id.' has no replay.');
            }
        }
    }

    public static function addGameDurationToAllReplays()
    {
        $allGames = Game::all();
        foreach ($allGames as $game) {
            set_time_limit(10);
            if ($game->replay != null) {
                Log::info('Parsing replay for game '. $game->id .' with disk name '. $game->replay->getLocalPath());
                $parser = new ReplayParsing;
                $parser->game = $game;
                $parser->replay = $game->replay;
                $parser->addGameDuration();
            } else {
                Log::error('Game '.$game->id.' has no replay.');
            }
        }
    }

    //deprecated
    public function parseReplay()
    {
        $this->parseDetails();
        $this->parseAttributeEvents();
    }

    public function parseDetails()
    {
        if ($this->replay == null) {
            return;
        }
        defined('DS') or define('DS', DIRECTORY_SEPARATOR);
        @chdir('public_html'); //working directory may already be here depending from where this is called, the @ suppresses the error in that case
        exec(ReplayParsing::$pythonPath.'plugins'.DS.'rikki'.DS.'heroeslounge'.DS.'classes'.DS.'replayparsing'.DS.'parseReplay.py --details --json ' . $this->replay->getLocalPath(), $output);
        if (is_array($output) && array_key_exists(0, $output)) {
            $this->decodedDetails = json_decode($output[0], true);
        } else {
            //get newest version and try again
            Deployment::updateHeroprotocol();
            exec(ReplayParsing::$pythonPath.'plugins'.DS.'rikki'.DS.'heroeslounge'.DS.'classes'.DS.'replayparsing'.DS.'parseReplay.py --details --json ' . $this->replay->getLocalPath(), $output);
            if (is_array($output) && array_key_exists(0, $output)) {
                $this->decodedDetails = json_decode($output[0], true);
            } else {
                if ($this->game) {
                    Log::error("Replay for game id ". $this->game->id ." could not be parsed (Part: Details).");
                } else {
                    Log::error("Replay for match id ".$this->match->id." could not be parsed (Part: Details).");
                }
            }
        }
    }

    public function parseAttributeEvents()
    {
        if ($this->replay == null) {
            return;
        }
        defined('DS') or define('DS', DIRECTORY_SEPARATOR);
        @chdir('public_html'); //working directory may already be here depending from where this is called, the @ suppresses the error in that case
        exec(ReplayParsing::$pythonPath.'plugins'.DS.'rikki'.DS.'heroeslounge'.DS.'classes'.DS.'replayparsing'.DS.'parseReplay.py --attributeevents --json ' . $this->replay->getLocalPath(), $output);
        if (is_array($output) && array_key_exists(0, $output)) {
            $this->decodedAttributeEvents = json_decode($output[0], true);
        } else {
            if ($this->game) {
                Log::error("Replay for game id ". $this->game->id ." could not be parsed (Part: Attributeevents).");
            } else {
                Log::error("Replay for match id ".$this->match->id." could not be parsed (Part: Attributeevents).");
            }
        }
    }

    public function parseHeader()
    {
        if ($this->replay == null) {
            return;
        }
        defined('DS') or define('DS', DIRECTORY_SEPARATOR);
        @chdir('public_html'); //working directory may already be here depending from where this is called, the @ suppresses the error in that case
        exec(ReplayParsing::$pythonPath.'plugins'.DS.'rikki'.DS.'heroeslounge'.DS.'classes'.DS.'replayparsing'.DS.'parseReplay.py --header --json ' . $this->replay->getLocalPath(), $output);
        if (is_array($output) && array_key_exists(0, $output)) {
            $this->decodedHeader = json_decode($output[0], true);
        } else {
            if ($this->game) {
                Log::error("Replay for game id ". $this->game->id ." could not be parsed (Part: Header).");
            } else {
                Log::error("Replay for match id ".$this->match->id." could not be parsed (Part: Header).");
            }
        }
    }

    public function parseTrackerEvents()
    {
        if ($this->replay == null) {
            return;
        }
        defined('DS') or define('DS', DIRECTORY_SEPARATOR);
        @chdir('public_html'); //working directory may already be here depending from where this is called, the @ suppresses the error in that case
        exec(ReplayParsing::$pythonPath.'plugins'.DS.'rikki'.DS.'heroeslounge'.DS.'classes'.DS.'replayparsing'.DS.'parseReplay.py --trackerevents --json ' . $this->replay->getLocalPath(), $output);
        if (is_array($output) && array_key_exists(0, $output)) {
            $this->decodedTrackerEvents = [];
            foreach ($output as $jsonTrackerEvent) {
                $this->decodedTrackerEvents[] = json_decode($jsonTrackerEvent, true);
            }
        } else {
            if ($this->game) {
                Log::error("Replay for game id ". $this->game->id ." could not be parsed (Part: Trackerevents).");
            } else {
                Log::error("Replay for match id ".$this->match->id." could not be parsed (Part: Trackerevents).");
            }
        }
    }

    public function validateResult()
    {
        if ($this->decodedDetails == null) {
            $this->parseDetails();
            if ($this->decodedDetails == null) {
                return [true, 'Replay could not be parsed.'];
            }
        }

        if ($this->replayIsOld()) {
            //Replay was recorded before March 16th, 2017
            return [true, 'This replay is too old.'];
        }

        $map = Map::where('title', $this->decodedDetails["m_title"])->first();
        if (!$map) {
            //map name might be localized, try translations
            $map = Map::where('translations', 'LIKE', '%'.$this->decodedDetails["m_title"].'%')->first();
        }

        if (!$map) {
            return [true, 'Map could not be recognized: ' . $this->decodedDetails["m_title"]];
        }

        $mapPlayedBefore = $this->match->games->contains(function ($game) use ($map) {
            return $game->map->id == $map->id;
        });
        if ($mapPlayedBefore && $map->title != "Lost Cavern") {
            return [true, 'This map was already played in this game.'];
        }

        //identify teams - uploading team has to field 3 core members, opponent has to have 1 core player
        $teamOnePlayers = 0;
        $teamTwoPlayers = 0;
        $getBattleTagNames = function ($sloth) {
            return strtolower(explode('#', $sloth->battle_tag)[0]);
        };
        $firstTeamNames = $this->match->teams[0]->sloths->map($getBattleTagNames)->toArray();
        $secondTeamNames = $this->match->teams[1]->sloths->map($getBattleTagNames)->toArray();
        $teamIdentity = 0;
        foreach ($this->decodedDetails["m_playerList"] as $playerDetails) {
            if (in_array(strtolower($playerDetails["m_name"]), $firstTeamNames)) {
                $teamIdentity += $playerDetails["m_teamId"]*2-1;
                $teamOnePlayers++;
            }
            if (in_array(strtolower($playerDetails["m_name"]), $secondTeamNames)) {
                $teamIdentity -= $playerDetails["m_teamId"]*2-1;
                $teamTwoPlayers++;
            }
        }
        if (($this->uploadingTeam->id == $this->match->teams[0]->id && $teamOnePlayers < 3)
            || ($this->uploadingTeam->id == $this->match->teams[1]->id && $teamTwoPlayers < 3)
            ) {
            //less than 3 players of core roster
            return [true, 'Your team could not be recognized. At least 3 players have to be on your roster.'];
        }

        if ($teamOnePlayers < 1 || $teamTwoPlayers < 1) {
            return [true, 'Your opponent could not be recognized.'];
        }

        //absolute value of $teamIdentity indicates confidence with which teams could be recognized
        if ($teamIdentity >= -3 && $teamIdentity <= 3) {
            return [true, 'Teams could not be identified.'];
        }

        return [false, ''];
    }

    public function saveResult($modify_winner = true)
    {
        if ($this->replay == null) {
            return;
        }
        if ($this->decodedDetails == null) {
            $this->parseDetails();
            if ($this->decodedDetails == null) {
                return;
            }
        }

        if ($this->replayIsOld()) {
            //Replay was recorded before March 16th, 2017
            $this->replay->delete();
            return;
        }
        $this->participationList = new Collection;
        $this->talentTierCounters = new Collection;
        $this->indizesToSlotId = new Collection;

        //get map from details
        $map = Map::where('title', $this->decodedDetails["m_title"])->first();
        if (!$map) {
            //map name might be localized, try translations
            $map = Map::where('translations', 'LIKE', '%'.$this->decodedDetails["m_title"].'%')->first();
        }
        $this->game->map = $map;

        //identify teams - replay has teams 0 and 1, website has teams 0 and 1, we need to match them
        $getBattleTagNames = function ($sloth) {
            return strtolower(explode('#', $sloth->battle_tag)[0]);
        };
        $firstTeamNames = $this->match->teams[0]->sloths->map($getBattleTagNames)->toArray();
        $secondTeamNames = $this->match->teams[1]->sloths->map($getBattleTagNames)->toArray();
        if ($this->allSlothNames == null) {
            $this->allSlothNames = Sloth::all()->map($getBattleTagNames)->toArray();
        }
        $teamIdentity = 0;
        foreach ($this->decodedDetails["m_playerList"] as $playerDetails) {
            if (in_array(strtolower($playerDetails["m_name"]), $firstTeamNames)) {
                $teamIdentity += $playerDetails["m_teamId"]*2-1;
            }
            if (in_array(strtolower($playerDetails["m_name"]), $secondTeamNames)) {
                $teamIdentity -= $playerDetails["m_teamId"]*2-1;
            }
        }

        //$teamIdentity is now a value between -10 and +10, indicating which team in the replay is which team in the website
        //positive: team 0 in replay is team 1 in site
        //negative: team 0 in replay is team 0 in site
        $firstReplayTeam = 0;
        $secondReplayTeam = 0;
        if ($teamIdentity < 0) {
            $secondReplayTeam = 1;
        } elseif ($teamIdentity > 0) {
            $firstReplayTeam = 1;
            $tmp = $firstTeamNames;
            $firstTeamNames = $secondTeamNames;
            $secondTeamNames = $tmp;
        } else {
            //this should never happen
            $secondReplayTeam = 1;
        }

        $this->game->teamOne = $this->match->teams[$firstReplayTeam];
        $this->game->teamTwo = $this->match->teams[$secondReplayTeam];
        $prevGameParts = GameParticipation::where('game_id', $this->game->id)->get();
        foreach ($prevGameParts as $pgp) {
        	$pgp->talents()->detach();
        }
        GameParticipation::where('game_id', $this->game->id)->delete();
        //get details for each player, build GameParticipation for each
        foreach ($this->decodedDetails["m_playerList"] as $playerDetails) {
            if ($playerDetails["m_observe"] == 0) {
                $participation = new GameParticipation();
                $participation->game_id = $this->game->id;
                $participation->title = $playerDetails["m_name"];
                $hero = Hero::where('title', $playerDetails["m_hero"])->first();
                if (!$hero) {
                    //game client might be localized, try translations
                    if ($playerDetails["m_hero"] == "Ана") {
                        $hero = Hero::where('title', "Ana")->first(); //otherwise, this would match Sylvanas instead of Ana
                    } else {
                        $hero = Hero::where('translations', 'LIKE', '%'.$playerDetails["m_hero"].'%')->first();
                    }
                }
                if (!$hero) {
                    Log::error("Hero not found: " . $playerDetails["m_hero"]);
                }
                $participation->hero = $hero;
                if ($playerDetails["m_teamId"] == 0) {
                    $participation->team_id = $this->match->teams[$firstReplayTeam]->id;
                    // Retrieve all the sloths with matching Heroes Profile ID and then filter on battletag name.
                    $participatingSloth = Sloth::where('heroesprofile_id', $playerDetails["m_toon"]["m_id"])->get()->first(function ($sloth) use ($playerDetails) {
                        return strtolower(explode('#', $sloth->battle_tag)[0]) == strtolower($playerDetails["m_name"]);
                    });

                    if (isset($participatingSloth)) {
                        $participation->sloth = $participatingSloth;
                    } else {
                        $teamKey = array_search(strtolower($playerDetails["m_name"]), $firstTeamNames);
                        if ($teamKey !== false) {
                            $participation->sloth = $this->match->teams[$firstReplayTeam]->sloths[$teamKey];
                        } else {
                            //player might be a sub, we got to check all sloths now
                            $allKey = array_search(strtolower($playerDetails["m_name"]), $this->allSlothNames);
                            if ($allKey !== false) {
                                $participation->sloth = Sloth::all()[$allKey];
                            }
                        }
                    }
                } elseif ($playerDetails["m_teamId"] == 1) {
                    $participation->team_id = $this->match->teams[$secondReplayTeam]->id;
                    // Retrieve all the sloths with matching Heroes Profile ID and then filter on battletag name.
                    $participatingSloth = Sloth::where('heroesprofile_id', $playerDetails["m_toon"]["m_id"])->get()->first(function ($sloth) use ($playerDetails) {
                        return strtolower(explode('#', $sloth->battle_tag)[0]) == strtolower($playerDetails["m_name"]);
                    });

                    if (isset($participatingSloth)) {
                        $participation->sloth = $participatingSloth;
                    } else {
                        $teamKey = array_search(strtolower($playerDetails["m_name"]), $secondTeamNames);
                        if ($teamKey !== false) {
                            $participation->sloth = $this->match->teams[$secondReplayTeam]->sloths[$teamKey];
                        } else {
                            //player might be a sub, we got to check all sloths now
                            $allKey = array_search(strtolower($playerDetails["m_name"]), $this->allSlothNames);
                            if ($allKey !== false) {
                                $participation->sloth = Sloth::all()[$allKey];
                            }
                        }
                    }
                }
                if ($playerDetails["m_result"] == 1 && $modify_winner) {
                    if ($playerDetails["m_teamId"] == 0) {
                        $this->game->winner_id = $this->match->teams[$firstReplayTeam]->id;
                    } elseif ($playerDetails["m_teamId"] == 1) {
                        $this->game->winner_id = $this->match->teams[$secondReplayTeam]->id;
                    }
                }
                $participation->save();
                $this->participationList[$playerDetails["m_workingSetSlotId"]] = $participation;
                $this->talentTierCounters[$playerDetails["m_workingSetSlotId"]] = 0;
                $this->indizesToSlotId[] = $playerDetails["m_workingSetSlotId"];
            }
        }
        
        if ($this->game->map->title != "Lost Cavern") {
            if ($this->decodedAttributeEvents == null) {
                $this->parseAttributeEvents();
                if ($this->decodedAttributeEvents == null) {
                    return;
                }
            }

            //get bans from attributeevents
            $this->game->teamOneFirstBan = Hero::where('attribute_name', $this->decodedAttributeEvents["scopes"]["16"]["4023"][0]["value"])->first();
            $this->game->teamOneSecondBan = Hero::where('attribute_name', $this->decodedAttributeEvents["scopes"]["16"]["4025"][0]["value"])->first();
            $this->game->teamTwoFirstBan = Hero::where('attribute_name', $this->decodedAttributeEvents["scopes"]["16"]["4028"][0]["value"])->first();
            $this->game->teamTwoSecondBan = Hero::where('attribute_name', $this->decodedAttributeEvents["scopes"]["16"]["4030"][0]["value"])->first();
            if (array_key_exists("4043", $this->decodedAttributeEvents["scopes"]["16"])) {
                $this->game->teamOneThirdBan = Hero::where('attribute_name', $this->decodedAttributeEvents["scopes"]["16"]["4043"][0]["value"])->first();
                $this->game->teamTwoThirdBan = Hero::where('attribute_name', $this->decodedAttributeEvents["scopes"]["16"]["4045"][0]["value"])->first();
            } else {
                $this->game->teamOneThirdBan = null;
                $this->game->teamTwoThirdBan = null;
            }
        }
        $this->game->save();
    }

    public function addGameDuration()
    {
        if ($this->decodedHeader == null) {
            $this->parseHeader();
            if ($this->decodedHeader == null) {
                return;
            }
        }
        $seconds = $this->decodedHeader['m_elapsedGameLoops'] / 16;
        $time = (string) floor($seconds / 3600) . ':' . (string) (floor($seconds / 60) % 60) . ':' . (string) ($seconds % 60);
        $this->game->duration = $time;
        $this->game->save();
    }

    //gets draft order, damage done and other statistics
    //assumpution: Details were already parsed and saved (saveResult())
    public function addTrackerDetails()
    {
        if ($this->decodedTrackerEvents == null) {
            $this->parseTrackerEvents();
            if ($this->decodedTrackerEvents == null) {
                return;
            }
        }
        if ($this->decodedDetails == null) {
            $this->parseDetails();
            if ($this->decodedDetails == null) {
                return;
            }
        }
        if ($this->decodedHeader == null) {
        	$this->parseHeader();
        	if ($this->decodedHeader == null) {
                return;
            }
        }

        $pickIndex = 0;
        $swappingPlayer1 = -1;

        foreach ($this->decodedTrackerEvents as $decodedTrackerEvent) {
            if ($decodedTrackerEvent['_event'] == 'NNet.Replay.Tracker.SHeroPickedEvent') {
                $pickIndex++;
                $this->participationList[$decodedTrackerEvent['m_controllingPlayer']]->draft_order = $pickIndex;
            } elseif ($decodedTrackerEvent['_event'] == 'NNet.Replay.Tracker.SHeroSwappedEvent') {
                if ($swappingPlayer1 == -1) {
                    $swappingPlayer1 = $decodedTrackerEvent['m_newControllingPlayer'];
                } else {
                    $swappingPlayer2 = $decodedTrackerEvent['m_newControllingPlayer'];
                    $temp = $this->participationList[$swappingPlayer1]->draft_order;
                    $this->participationList[$swappingPlayer1]->draft_order = $this->participationList[$swappingPlayer2]->draft_order;
                    $this->participationList[$swappingPlayer2]->draft_order = $temp;
                    $swappingPlayer1 = -1;
                }
            } elseif ($decodedTrackerEvent['_event'] == 'NNet.Replay.Tracker.SStatGameEvent') { 
                //talent picked
                if ($decodedTrackerEvent['m_eventName'] == 'TalentChosen') {
                	$listIndex = $this->indizesToSlotId[$decodedTrackerEvent['m_intData'][0]['m_value']-1];
                	$talentTierCounters[$listIndex] = $this->talentTierCounters[$listIndex] + 1;
                	$hid = $this->participationList[$listIndex]->hero->id;
                	$talent = Talent::where('hero_id', $hid)->where('replay_title', $decodedTrackerEvent['m_stringData'][0]['m_value'])->first();
                	if ($talent == null) {
                		$t = Db::select("SELECT * FROM `rikki_heroeslounge_talents` WHERE '".$decodedTrackerEvent['m_stringData'][0]['m_value']."' LIKE CONCAT('%', suspected_replay_title, '%') AND replay_title IS NULL AND hero_id = ".$hid, []);
                		if (array_key_exists(0, $t) && !array_key_exists(1, $t)) {
                			$talent = Talent::find($t[0]->id);
                		}
                		if ($talent != null) {
                			$talent->replay_title = $decodedTrackerEvent['m_stringData'][0]['m_value'];
                			$talent->save();
                            Log::error('Added new talent replay title: '.$talent->replay_title.' assigned to '.$talent->title);
                		}
                	}
                	if ($talent != null) {
                		$pivotData = ['talent_tier' => $this->talentTierCounters[$listIndex]];
                		$this->participationList[$listIndex]->talents()->add($talent, $pivotData);
                	} else {
                        Log::error('Could not recognize talent: '.$decodedTrackerEvent['m_stringData'][0]['m_value']. ' in game '. $this->game->id);
                    }
                }
            } elseif ($decodedTrackerEvent['_event'] == 'NNet.Replay.Tracker.SScoreResultEvent') {
                foreach ($decodedTrackerEvent['m_instanceList'] as $stats) {
                    $data = new Collection($stats['m_values']);
                    switch ($stats['m_name']) {
                        case 'Deaths':
                            $data->each(function ($value, $key) use (&$participationList) {
                                if (!empty($value)) {
                                    $this->participationList[$key]->deaths = $value[0]['m_value'];
                                }
                            });
                            break;
                        case 'SoloKill':
                            $data->each(function ($value, $key) use (&$participationList) {
                                if (!empty($value)) {
                                    $this->participationList[$key]->kills = $value[0]['m_value'];
                                }
                            });
                            break;
                        case 'Assists':
                            $data->each(function ($value, $key) use (&$participationList) {
                                if (!empty($value)) {
                                    $this->participationList[$key]->assists = $value[0]['m_value'];
                                }
                            });
                            break;
                        case 'ExperienceContribution':
                            $data->each(function ($value, $key) use (&$participationList) {
                                if (!empty($value)) {
                                    $this->participationList[$key]->experience_contribution = $value[0]['m_value'];
                                }
                            });
                            break;
                        case 'Healing':
                        case 'SelfHealing':
                            $data->each(function ($value, $key) use (&$participationList) {
                                if (!empty($value)) {
                                    $this->participationList[$key]->healing = max($value[0]['m_value'], $this->participationList[$key]->healing);
                                }
                            });
                            break;
                        case 'SiegeDamage':
                            $data->each(function ($value, $key) use (&$participationList) {
                                if (!empty($value)) {
                                    $this->participationList[$key]->siege_damage = $value[0]['m_value'];
                                }
                            });
                            break;
                        case 'HeroDamage':
                            $data->each(function ($value, $key) use (&$participationList) {
                                if (!empty($value)) {
                                    $this->participationList[$key]->hero_damage = $value[0]['m_value'];
                                }
                            });
                            break;
                        case 'DamageTaken':
                            $data->each(function ($value, $key) use (&$participationList) {
                                if (!empty($value)) {
                                    $this->participationList[$key]->damage_taken = $value[0]['m_value'];
                                }
                            });
                            break;
                        case 'Level':
                            $data->each(function ($value, $key) use (&$participationList) {
                                if (!empty($value)) {
                                    if ($this->participationList[$key]->team_id == $this->game->team_one_id) {
                                        $this->game->team_one_level = $value[0]['m_value'];
                                    } else {
                                        $this->game->team_two_level = $value[0]['m_value'];
                                    }
                                }
                            });
                            break;
                        
                        default:
                            break;
                    }
                }
            }
        }
        $this->game->save();
        $this->participationList->each(function ($p) {
            $p->save();
        });
    }

    public static function parseReplayAndSave($game, $modify_winner = true)
    {
        if ($game->replay == null) {
            return;
        }
        set_time_limit(30);
        $replayParser = new ReplayParsing;
        $replayParser->game = $game;
        $replayParser->replay = $game->replay;
        $replayParser->match = $game->match;
        //used from backend, so skip validation
        $replayParser->saveResult($modify_winner);
        $replayParser->addGameDuration();
        $replayParser->addTrackerDetails();
    }

    public static function countOldReplays()
    {
        $allGames = Game::all();
        $oldReplays = 0;
        foreach ($allGames as $game) {
            $replayParser = new ReplayParsing;
            $replayParser->replay = $game->replay;
            $replayParser->game = $game;
            $replayParser->match = $game->match;
            $replayParser->parseDetails();
            if ($replayParser->replayIsOld()) {
                $oldReplays++;
            }
        }
        Log::info($oldReplays . " old replays were found.");
    }

    public function replayIsOld()
    {
        return $this->decodedDetails != null && $this->decodedDetails["m_timeUTC"] < 131341401114845000;
    }
}
