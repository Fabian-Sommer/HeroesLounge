<?php namespace Rikki\Heroeslounge\classes\helpers;

use Flash;
use Cms\Classes\Page;
use Cms\Classes\ComponentBase;
use Exception;

use Rikki\Heroeslounge\Models\Season as Seasons;
use Rikki\Heroeslounge\Models\Apps;
use Rikki\Heroeslounge\Models\Bans;


class NotificationHelper
{
    public static function generateMessages($user)
    {
        $sloth = $user->sloth;
        $team = $sloth->team;
        $retVal = array();
        if (isset($sloth)) {
            NotificationHelper::checkHotSLogsProfileStatus($sloth, $retVal);
            NotificationHelper::checkApplications($user, $retVal);
            NotificationHelper::checkParticipation($sloth, $retVal);

            if (isset($team)) {
                NotificationHelper::checkRoster($team, $retVal);
                NotificationHelper::checkMatches($user, $team, $retVal);
                NotificationHelper::checkBans($user,$retVal);
            }
        }
        return $retVal;
    }


    public static function checkRoster($team, &$retVal)
    {
        if ($team->sloths->count() < 5) {
            $retVal[]=  [
                'type' => 'info',
                'message' => 'Your current roster consists of less than 5 members.',
                'entity' => $team
                ];
        }
    }

    public static function checkMatches($user, $team, &$retVal)
    {
        if ($user->sloth->is_captain == true) {
            NotificationHelper::generateScheduleMatchMessages($user->sloth, $team->matches()->whereNull('wbp')->whereNull('is_played')->get(), $retVal);
            $repMatches = $user->sloth->team->matches()->where(function ($q) {
                $q->whereNotNull('wbp')->where('winner_id', null);
            })->get();
            NotificationHelper::generateReportMatchMessages($user->sloth, $repMatches, $retVal);
        }
    }


    private static function generateScheduleMatchMessages($sloth, $matches, &$retVal)
    {
        if ($matches->count() > 0) {
            $retVal[] = [
                'type' => 'warning',
                'message' => 'You have matches which still needs to be scheduled, else you will be flagged inactive!',
                'entity' => $matches->toArray()
            ];
        }
    }

    private static function generateReportMatchMessages($sloth, $matches, &$retVal)
    {
        if ($matches->count() > 0) {
            $retVal[] = [
                'type' => 'warning',
                'message' => 'You have matches where you still need to report the score for, else you will be flagged inactive!',
                'entity' => $matches->toArray()
            ];
        }
    }

    public static function checkBans($user,&$retVal)
    {
        if($user->sloth->is_captain)
        {
            NotificationHelper::generateBanMessages(Bans::with('hero','talent')->get(),$retVal);
        }
    }

    public static function generateBanMessages($bans,&$retVal)
    {
        foreach($bans as $ban)
        {
            $m = 'BANNED: ';
            if(!empty($ban->literal))
            {
                $m .= $ban->literal;
            }
            else
            {
                if(isset($ban->hero))
                {
                    $m .= $ban->hero->title.' ';
                }
                if(isset($ban->talent))
                {
                    $m .= $ban->talent->title.' ';
                }
                if(isset($ban->round_start))
                {
                    $m .= '[Round '.$ban->round_start;
                    if(isset($ban->round_length))
                    {
                        $m.= ' - '.($ban->round_start + $ban->round_length);
                    }
                    $m.=' ]';
                }
            }
            
            $retVal[] = [
                'type' => 'warning',
                'message' => $m,
                'entity' => $ban->toArray()
                ];
        }


    }
    

    public static function checkApplications($user, &$retVal)
    {
        $a = null;
        if ($user->sloth->team_id > 0) {
            //Only happens if some sloth applied for a team
            $a = Apps::where('approved', 0)->where('accepted', 0)->where('withdrawn', 0)->whereHas('team', function ($q) use ($user) {
                $q->where('team_id', $user->sloth->team_id);
            });
        } else {
            //only happens if someone invited sloth for a team
            $a = Apps::where('approved', 1)->where('accepted', 0)->where('withdrawn', 0)->whereHas('sloth', function ($q) use ($user) {
                $q->where('user_id', $user->id);
            });
        }
        NotificationHelper::generateApplicationMessages($a, $user->sloth, $retVal);
    }

    private static function generateApplicationMessages($a, $sloth, &$retVal)
    {
        foreach ($a as $application) {
            if ($sloth->is_captain == true) {
                $retVal[] = [
                    'type' => 'info',
                    'message' => $application->sloth->title.' applied for your team!',
                    'entity' => $application->toArray()
                    ];
            } else {
                $retVal[] = [
                    'type' => 'info',
                    'message' => 'You\'ve recieved an application from '.$application->team->title,
                    'entity' => $application->toArray()
                    ];
            }
        }
    }


    public static function checkParticipation($sloth, &$retVal)
    {
        $s = null;
        if ($sloth->team_id > 0) {
            //Only get seasons which are activated, not already in progress and where team is not registered as participating
            $s = Seasons::where('is_active', 1)->where('reg_open', 1)->where('region_id', $sloth->region_id)->whereDoesntHave('teams', function ($q) use ($sloth) {
                $q->where('team_id', $sloth->team_id);
            })->get();
        } else {
            //Only get seasons which are activated, not already in progress and where sloth is not registered as Free agents
            $s = Seasons::where('is_active', 1)->where('reg_open', 1)->where('region_id', $sloth->region_id)->whereDoesntHave('free_agents', function ($q) use ($sloth) {
                $q->where('sloth_id', $sloth->id);
            })->get();
        }
        NotificationHelper::generateParticipationMessages($s, $sloth, $retVal);
    }

    private static function generateParticipationMessages($s, $sloth, &$retVal)
    {
        foreach ($s as $season) {
            if ($sloth->is_captain == true) {
                $retVal[] = [
                    'type' => 'warning',
                    'message' => 'Your team isn\'t listed to participate in '.$season->title.'. You can sign up your team on the team management page!',
                    'entity' => $season->toArray()
                    ];
            } elseif ($sloth->team_id > 0) {
                $retVal[] = [
                    'type' => 'warning',
                    'message' => 'Your team isn\'t listed to participate in '.$season->title.'. Your captain can sign up the team for it.',
                    'entity' => $season->toArray()
                    ];
            } else {
                $retVal[] = [
                    'type' => 'warning',
                    'message' => 'You are currently not listed to participate in '.$season->title.'. Join a team or sign up as free agent in your profile!',
                    'entity' => $season->toArray()
                    ];
            }
        }
    }

    public static function checkHotSLogsProfileStatus($sloth, &$retVal)
    {
        if ($sloth->hotslogs_id == null) {
            $retVal[]=  [
                'type' => 'info',
                'message' => 'Your HotSLogs profile is currently hidden. Please set it to public. This will be required for the next season.',
                'entity' => $sloth->toArray()
            ];
        }
    }
}
