# HeroesLounge

Core plugins and theme for https://heroeslounge.gg.

To be used with https://octobercms.com/ 


## Installation

Install Virtualbox and Vagrant:

On Windows, install with Chocolatey: https://chocolatey.org/

    choco install virtualbox vagrant

On OS X, install with Brew: https://brew.sh/

    brew cask install virtualbox vagrant

On Linux you should be able to figure this out yourself. ;)

Now run the Virtualbox UI and make sure that the Virtualbox Guest Additions are up to date with the Virtualbox version (it should prompt you automaticallly if not).


Download the database zip from the link you should have received and put it in this directory.

Run Vagrant:

    vagrant up && vagrant ssh

Once in, run `/vagrant/install_october.sh`

Select y, y, enter, y (developer files, correct, /opt/lampp, continue)

Then open the October install URL:

http://localhost:8080/hl/install.php

All checks should pass so read the license agreement, "Agree & Continue",
 "Database Name" `hl`, "MySQL Login" `root`, no password, "Administrator >",
 "Admin Password" [whatever you want], "Continue", "Start from scratch"

(This admin user and password will be replaced when you import the db dump later on, but you need them for now.)

Then login to the backend with the user "admin" and the password you selected:

http://localhost:8080/hl/backend/system/updates

Then click "Attach Project" (small link just above the "Check for updates" big blue button)
 and enter the Project ID you should have received, then click "Attach to Project"

Wait until the project has finished attaching, then run `/vagrant/install_hl.sh`


## Usage

Login to the backend with the credentials you should have received:

http://localhost:8080/hl/backend

There are a few warnings about missing json files scattered across the backend dashboard, but these are all fine to ignore.

Login to the frontend with your real website username, assuming the dump was made after you registered, with password 1234:

http://localhost:8080/hl/user

All other personally identifying information (PII) has been randomized in the db dump: email addresses, battletags, discordtags.

You can't login from the front page of the site currently, it fails without displaying an error.

You can't register a new user locally because the dicord integration will not work locally to verify registered users as having joined the discord channel.  You can modify existing users directly in the db:

    /opt/lampp/bin/mysql -u root hl

Verify your own user information like this:

    SELECT * FROM users AS u LEFT JOIN rikki_heroeslounge_sloths AS s ON s.user_id=u.id WHERE u.username='YOUR_USERNAME';
