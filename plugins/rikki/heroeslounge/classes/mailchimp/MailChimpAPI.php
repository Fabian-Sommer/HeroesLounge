<?php namespace Rikki\Heroeslounge\classes\Mailchimp;

use Log;
use Rikki\Heroeslounge\models\Season;
use Rikki\Heroeslounge\models\Sloth;

class MailChimpAPI
{
	/*
	groups: 4a5a3da9d8 - captain
			67a0ed3b0a - free agent
			b2198bf809 - not participating
			45123ca490 - teammember
	*/
	public static function initialSignup()
	{
		set_time_limit(1000000);
		
		$activeUsers = Season::where('id', 3)->firstOrFail()->divisions
		    ->map(function($division) {return $division->teams;})->flatten()
		    ->map(function($division) {return $division->sloths;})->flatten()
		    ->sortBy('id')
		    ->map(function($sl) { return $sl->user;})
		    ->each(function ($user) {MailChimpAPI::subscribeNewUser($user); MailChimpAPI::subscribeExistingUser($user); Log::info(json_encode($user));});
		  
		//$activeUsers = Sloth::where('newsletter_subscription', 1)->get()->map(function($sl) { return $sl->user;})
		//->each(function ($user) {MailChimpAPI::subscribeNewUser($user); Log::info(json_encode($user));});;
	    $inactiveUsers = Sloth::where('newsletter_subscription', 0)->get()->map(function($sl) { return $sl->user;})
	    ->each(function ($user) {MailChimpAPI::unsubscribeNewUser($user); MailChimpAPI::unsubscribeExistingUser($user); Log::info(json_encode($user));});;
	    //Log::info(json_encode($inactiveUsers));
	}

	public static function subscribeNewUser($user)
	{
	    $request_url = "https://us17.api.mailchimp.com/3.0/lists/8877904eb6/members";
	    $data = array('email_address'=>$user->email,'status'=>'subscribed', 'merge_fields' => array('FNAME' => $user->sloth->title), 'interests' => array('4a5a3da9d8' => ($user->sloth->is_captain == true), '45123ca490' => ($user->sloth->team_id != null || $user->sloth->team_id > 0)));
	    $data_json = json_encode($data);

	    $ch = curl_init($request_url);
	    curl_setopt($ch, CURLOPT_POST, true);
	    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
	    curl_setopt($ch, CURLOPT_POSTFIELDS, $data_json);
	    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	    curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
	    curl_setopt($ch, CURLOPT_USERPWD, "anystring:".AuthCode::getMailchimpAPIKey());

	    $output = curl_exec($ch);
	    curl_close($ch);

	    $user->sloth->newsletter_subscription = true;
	    $user->sloth->save();
	}

	public static function unsubscribeNewUser($user)
	{
	    $request_url = "https://us17.api.mailchimp.com/3.0/lists/8877904eb6/members";
	    $data = array('email_address'=>$user->email,'status'=>'unsubscribed', 'merge_fields' => array('FNAME' => $user->sloth->title), 'interests' => array('4a5a3da9d8' => ($user->sloth->is_captain == true), '45123ca490' => ($user->sloth->team_id != null || $user->sloth->team_id > 0)));
	    $data_json = json_encode($data);

	    $ch = curl_init($request_url);
	    curl_setopt($ch, CURLOPT_POST, true);
	    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
	    curl_setopt($ch, CURLOPT_POSTFIELDS, $data_json);
	    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	    curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
	    curl_setopt($ch, CURLOPT_USERPWD, "anystring:".AuthCode::getMailchimpAPIKey());

	    $output = curl_exec($ch);

	    curl_close($ch);

	    $user->sloth->newsletter_subscription = false;
	    $user->sloth->save();
	}

	public static function unsubscribeExistingUser($user)
	{
		$email_hash = md5(strtolower($user->email));

		$request_url = "https://us17.api.mailchimp.com/3.0/lists/8877904eb6/members/".$email_hash;
		$data = array('status'=>'unsubscribed', 'merge_fields' => array('FNAME' => $user->sloth->title), 'interests' => array('4a5a3da9d8' => ($user->sloth->is_captain == true), '45123ca490' => ($user->sloth->team_id != null || $user->sloth->team_id > 0)));
		$data_json = json_encode($data);

		$ch = curl_init($request_url);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PATCH");
		curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
		curl_setopt($ch, CURLOPT_POSTFIELDS, $data_json);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
		curl_setopt($ch, CURLOPT_USERPWD, "anystring:".AuthCode::getMailchimpAPIKey());

		$output = curl_exec($ch);

		curl_close($ch);
		$user->sloth->newsletter_subscription = false;
	    $user->sloth->save();
	}

	public static function subscribeExistingUser($user)
	{
		$email_hash = md5(strtolower($user->email));

		$request_url = "https://us17.api.mailchimp.com/3.0/lists/8877904eb6/members/".$email_hash;
		$data = array('status'=>'subscribed', 'merge_fields' => array('FNAME' => $user->sloth->title), 'interests' => array('4a5a3da9d8' => ($user->sloth->is_captain == true), '45123ca490' => ($user->sloth->team_id != null || $user->sloth->team_id > 0)));
		$data_json = json_encode($data);

		$ch = curl_init($request_url);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PATCH");
		curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
		curl_setopt($ch, CURLOPT_POSTFIELDS, $data_json);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
		curl_setopt($ch, CURLOPT_USERPWD, "anystring:".AuthCode::getMailchimpAPIKey());

		$output = curl_exec($ch);
		curl_close($ch);
		$user->sloth->newsletter_subscription = true;
	    $user->sloth->save();
	}

	public static function patchExistingUser($user)
	{
		$email_hash = md5(strtolower($user->email));

		$request_url = "https://us17.api.mailchimp.com/3.0/lists/8877904eb6/members/".$email_hash;
		$data = array('interests' => array('4a5a3da9d8' => ($user->sloth->is_captain == true), '45123ca490' => ($user->sloth->team_id != null || $user->sloth->team_id > 0)));
		$data_json = json_encode($data);

		$ch = curl_init($request_url);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PATCH");
		curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
		curl_setopt($ch, CURLOPT_POSTFIELDS, $data_json);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
		curl_setopt($ch, CURLOPT_USERPWD, "anystring:".AuthCode::getMailchimpAPIKey());

		$output = curl_exec($ch);

		curl_close($ch);
	}
}