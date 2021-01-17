CREATE DEFINER=`zalz`@`localhost` PROCEDURE `hl_main_copy`.`RemovePersonalData`()
BEGIN
    DECLARE n INT DEFAULT 0;
    DECLARE i INT DEFAULT 0;
    DECLARE tit varchar(255);
    DECLARE myid INT;
    START TRANSACTION;
    SELECT COUNT(*) FROM hl_main_copy.rikki_heroeslounge_sloths INTO n;
   	WHILE i<n DO 
      SELECT id, title FROM hl_main_copy.rikki_heroeslounge_sloths LIMIT i,1 INTO myid, tit;
      SET i = i + 1;
      UPDATE hl_main_copy.rikki_heroeslounge_sloths SET battle_tag = CONCAT(tit, "#9999"), discord_tag = CONCAT(tit, "#9999"), twitch_url = "", twitter_url = "", facebook_url = "", website_url="", youtube_url="", timezone="Europe/Berlin", discord_id = i, newsletter_subscription = 0 WHERE id = myid;
    END WHILE;
   
    SELECT COUNT(*) FROM hl_main_copy.users INTO n;
    SET i = 0;
    WHILE i<n DO 
      SELECT id, username FROM hl_main_copy.users LIMIT i,1 INTO myid, tit;
      SET i = i + 1;
      UPDATE hl_main_copy.users SET email = CONCAT(tit, i, "@fakegmail.com"), password = "$2y$10$vmfUt5DDesQTfQk5bRWwj.mE220Eph.p1M31V2klsGa2fsiIU2eWq", persist_code = "$2y$10$oZn4vHQObwChE7lL81bddeKMS/T7tMwfDpSfXxUhbfuS78jVk/4Ku", country_id = 3 WHERE id = myid;
    END WHILE;
    COMMIT;
END

// Also look at rikki_heroeslounge_api_keys (change keys), backend_users (drop all but one), backend_access_log (drop)