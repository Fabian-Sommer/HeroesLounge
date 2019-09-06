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
        if ($user == null) {
            return [];
        }
        $sloth = $user->sloth;
        $retVal = array();
        if (isset($sloth)) {
            // NotificationHelper::checkHotSLogsProfileStatus($sloth, $retVal);
            NotificationHelper::checkApplications($user, $retVal);
            NotificationHelper::checkParticipation($sloth, $retVal);
            foreach ($sloth->teams as $key => $team) {
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
                'message' => 'The roster of '. $team->title .' consists of less than 5 members.',
                'entity' => $team
                ];
        }
    }

    public static function checkMatches($user, $team, &$retVal)
    {
        if ($user->sloth->isCaptainOfTeam($team) == true) {
            NotificationHelper::generateScheduleMatchMessages($user->sloth, $team->matches()->whereNull('wbp')->whereNull('is_played')->get(), $retVal);
            $repMatches = $team->matches()->where(function ($q) {
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
        if($user->sloth->isCaptain())
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
        foreach ($user->sloth->teams as $key => $team) {
            $applicationsBySloth = Apps::where('approved', 0)->where('accepted', 0)->where('withdrawn', 0)->whereHas('team', function ($q) use ($user, $team) {
                $q->where('team_id', $team->id);
            });
            NotificationHelper::generateApplicationMessages($applicationsBySloth, $user->sloth, $retVal);
        }
        $applicationsToSloth = Apps::where('approved', 1)->where('accepted', 0)->where('withdrawn', 0)->whereHas('sloth', function ($q) use ($user) {
            $q->where('user_id', $user->id);
        });
        NotificationHelper::generateApplicationMessages($applicationsToSloth, $user->sloth, $retVal);
    }

    private static function generateApplicationMessages($a, $sloth, &$retVal)
    {
        foreach ($a as $application) {
            if ($sloth->isCaptainOfTeam($application->team)) {
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
        foreach ($sloth->teams as $key => $team) {
            $s = Seasons::where('is_active', 1)->where('reg_open', 1)->where('region_id', $team->region_id)->whereDoesntHave('teams', function ($q) use ($sloth, $team) {
                $q->where('team_id', $team->id);
            })->get();
            NotificationHelper::generateParticipationMessages($s, $team, $sloth, $retVal);
        }
    }

    private static function generateParticipationMessages($s, $team, $sloth, &$retVal)
    {
        foreach ($s as $season) {
            if ($sloth->isCaptainOfTeam($team)) {
                $retVal[] = [
                    'type' => 'warning',
                    'message' => 'Your team ' . $team->title . ' isn\'t listed to participate in '.$season->title.'. You can sign up your team on the season page!',
                    'entity' => $season->toArray()
                    ];
            } else {
                $retVal[] = [
                    'type' => 'warning',
                    'message' => 'Your team ' . $team->title . ' isn\'t listed to participate in '.$season->title.'. Your captain can sign up the team for it.',
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
