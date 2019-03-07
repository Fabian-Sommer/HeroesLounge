<?php namespace Rikki\Heroeslounge\Models;

 
use October\Rain\Database\Model;
use Rikki\Heroeslounge\Models\Timeline;
use Rikki\Heroeslounge\Models\Team;
use Rikki\Heroeslounge\classes\hotslogs\IDFetcher;
use Rikki\Heroeslounge\classes\MMR\MMRFetcher;
use Rikki\Heroeslounge\classes\Discord;
use Rikki\Heroeslounge\classes\Mailchimp\MailChimpAPI;
use Flash;
/**
 * Model
 */
class Sloth extends Model
{
    use \October\Rain\Database\Traits\Validation;
    
    use \October\Rain\Database\Traits\SoftDelete;

    protected $dates = ['deleted_at'];

    /*
     * Validation
     */
    public $rules = [
       
    ];

    protected $hidden = ['user_id','user'];

    /**
     * @var string The database table used by the model.
     */
    public $table = 'rikki_heroeslounge_sloths';

    public $belongsTo = [
            'team' => [
                'Rikki\Heroeslounge\Models\Team',
                'key' => 'team_id',
                'otherKey' => 'id'
            ],
            'divs_team' => [
                'Rikki\Heroeslounge\Models\Team',
                'key' => 'divs_team_id',
                'otherKey' => 'id'
            ],
            'user' => ['RainLab\User\Models\User'],
            'role' => [
                'Rikki\Heroeslounge\Models\SlothRole'
            ],
            'region' => ['Rikki\Heroeslounge\Models\Region'],
        ];

    public $hasMany = [
        'apps' => ['Rikki\Heroeslounge\Models\Apps'],
        'gameParticipations' => ['Rikki\Heroeslounge\Models\GameParticipation'],
        'heroStats' =>
        [
            'Rikki\Heroeslounge\Models\Hero',
            'key' => 'sloth_id',
            'otherKey' => 'hero_id',
            'table' => 'rikki_loungestatistics_sloth_hero_statistics',
            'pivot' => 
            [
                'avg_kills',
                'avg_assists',
                'avg_deaths',
                'total_kills',
                'total_assists',
                'total_deaths',
                'avg_siege_dmg',
                'avg_hero_dmg',
                'avg_healing',
                'avg_dmg_taken',
                'avg_xp_contrib',
                'total_siege_dmg',
                'total_hero_dmg',
                'total_healing',
                'total_dmg_taken',
                'total_xp_contrib',
                'total_games',
                'total_wins',
                'total_losses'
            ]
        ]
    ];
    
    public $hasOne = [
    ];
  

    public $attachOne = ['banner' => 'System\Models\File'];

    public $morphToMany = [
        'timeline' => ['Rikki\Heroeslounge\Models\Timeline',
                    'name' => 'timelineable',
                    'table' => 'rikki_heroeslounge_timelineables']
    ];
    
    public $belongsToMany = [
        'castMatches' => [
            'Rikki\Heroeslounge\Models\Match',
            'key' => 'caster_id',
            'otherKey' => 'match_id',
            'table' => 'rikki_heroeslounge_match_caster',
            'pivot' => ['approved']
        ],
        'seasons' =>
        [
            'Rikki\Heroeslounge\Models\Season',
            'key' => 'sloth_id',
            'otherKey' => 'season_id',
            'table' => 'rikki_heroeslounge_season_freeagent'
        ]
    ];

    public static function getFromUser($user)
    {
        if ($user->sloth) {
            return $user->sloth;
        }

        $sloth = new static;
        $sloth->user = $user;
        $sloth->title = $user->username;
        $sloth->save();

        $user->sloth = $sloth;

        return $sloth;
    }

    public function leaveTeam($team)
    {
        if($this->isCaptain($team) == false)
        {
            if ($team->type == 1) {
                if($this->team_id > 0)
                {
                    $title = $team->title;
                    $this->team_id = 0;
                    $this->save();
                    Flash::success('Succesfully left '.$title);
                }
                else
                {
                     Flash::error('You don\'t belong to this team!');
                }
            } else {
                if($this->divs_team_id > 0)
                {
                    $title = $team->title;
                    $this->divs_team_id = 0;
                    $this->save();
                    Flash::success('Succesfully left '.$title);
                }
                else
                {
                     Flash::error('You don\'t belong to this team!');
                }
            }
            
        }
        else
        {
            Flash::error('You are the captain of this team and cannot leave it');
        }
    }


    public function getTitleAttribute($value)
    {
        return $this->user->username;
    }

    public function isCaptain($team)
    {
        if ($team->type == 1) {
            return $this->is_captain;
        } else {
            return $this->is_divs_captain;
        }
    }
    
    public function afterCreate()
    {
        $this->_saveTimelineEntry('Sloth.Created');
        if (!empty($this->team_id)) {
            $this->_saveTimelineEntry('Sloth.Joins.Team');
        }
    }
    
    public function beforeUpdate()
    {
        if ($this->isDirty('team_id')) {
            if ($this->team_id == 0) {
                $this->is_captain = false;
            }
            if ($this->divs_team_id == 0) {
                $this->is_divs_captain = false;
            }
            MailChimpAPI::patchExistingUser($this->user);
        }
        if ($this->isDirty('discord_tag') && !isset($this->discord_id)) {
            $this->discord_id = Discord\Attendance::GetDiscordUserId($this->discord_tag);
            $this->save();
        }
        if ($this->isDirty('region_id') && isset($this->discord_id)) {
            if ($this->region_id == 1) {
                Discord\RoleManagement::UpdateUserRole("DELETE", $this->discord_id, "NA");
                Discord\RoleManagement::UpdateUserRole("PUT", $this->discord_id, "EU");
            } elseif ($this->region_id == 2) {
                Discord\RoleManagement::UpdateUserRole("DELETE", $this->discord_id, "EU");
                Discord\RoleManagement::UpdateUserRole("PUT", $this->discord_id, "NA");
            }
            
        }
    }

    public function afterSave()
    {
        if ($this->isDirty('is_captain') || $this->isDirty('is_divs_captain')) {
            if ($this->is_captain || $this->is_divs_captain) {
                $this->addDiscordCaptainRole();
            } else {
                $this->removeDiscordCaptainRole();
            }
        }
    }

    public function beforeDelete()
    {
        $this->_saveTimelineEntry('Sloth.Deleted');
    }

    public function scopeIsCaster($query)
    {
        return $query
                    ->join('users', 'rikki_heroeslounge_sloths.user_id', '=', 'users.id')
                    ->join('users_groups', 'users.id', '=', 'users_groups.user_id')
                    ->join('user_groups', 'users_groups.user_group_id', '=', 'user_groups.id')
                    ->where('user_groups.name', '=', 'Casters')
                    ->select('rikki_heroeslounge_sloths.id as id', 'users.username as slothtitle');
    }

    private function _saveTimelineEntry($entryType)
    {
        $canRun = true;
        if ($entryType = 'Sloth.Left.Team') {
            $tModel = Team::find($this->getOriginal('team_id'));
            if ($tModel == null) {
                $canRun = false;
            }
        }

        if ($canRun) {
            $timeline = new Timeline();
            $timeline->type = $entryType;
            $timeline->save();
            $timeline->sloths()->add($this);
            switch ($entryType) {
                case 'Sloth.Joins.Team':
                    $timeline->teams()->add($this->team);
                    $this->seasons()->detach();
                    break;
                case 'Sloth.Left.Team':
                    $tModel = Team::find($this->getOriginal('team_id'));
                    if ($tModel != null) {
                        $timeline->teams()->add($tModel);
                    }
                    break;
                case 'Sloth.Deleted':
                case 'Sloth.Created':
            }
        }
    }

    public function addDiscordCaptainRole()
    {
        Discord\RoleManagement::UpdateUserRole("PUT", $this->discord_id, "Captains");
    }

    public function removeDiscordCaptainRole()
    {
        Discord\RoleManagement::UpdateUserRole("DELETE", $this->discord_id, "Captains");
    }
}
