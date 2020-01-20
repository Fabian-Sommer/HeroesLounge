<?php namespace Rikki\Heroeslounge\Models;

use Model;
use Carbon\Carbon;

/**
 * Model
 */
class ApiKeys extends Model
{
    
    public $timestamps = true;

    /**
     * @var string The database table used by the model.
     */
    public $table = 'rikki_heroeslounge_api_keys';

    public static function getAccessForKey($key) {
        $api_key = ApiKeys::where('key', $key)->first();
        if ($api_key) {
            return $api_key->getAccess();
        }
        return false;
    }

    public function calculateUsed() {
        $seconds_since_last_access = Carbon::now()->diffInSeconds($this->updated_at);
        $this->used = max(0,$this->used - floor($this->limit*$seconds_since_last_access/$this->seconds_duration));
    }

    public function belowLimit() {
        return $this->used < $this->limit;
    }

    public function getAccess() {
        $this->calculateUsed();
        if ($this->belowLimit()) {
            $this->total_used++;
            $this->used++;
            $this->updated_at = Carbon::now();
            $this->save();
            return true;
        }
        return false;
    }
}
