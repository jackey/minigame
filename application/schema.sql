CREATE TABLE IF NOT EXISTS  `user` (
  `uid` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Primary Key: Unique user ID.',
  `name` varchar(60) NOT NULL DEFAULT '' COMMENT 'Unique user name.',
  `phone` varchar(60) NOT NULL DEFAULT '' COMMENT 'User’s phone.',
  `pass` varchar(128) NOT NULL DEFAULT '' COMMENT 'User’s password (hashed).',
  `mail` varchar(254) DEFAULT '' COMMENT 'User’s e-mail address.',
  `created` int(11) NOT NULL DEFAULT '0' COMMENT 'Timestamp for when user was created.',
  `access` int(11) NOT NULL DEFAULT '0' COMMENT 'Timestamp for previous time user accessed the site.',
  `login` int(11) NOT NULL DEFAULT '0' COMMENT 'Timestamp for user’s last login.',
  `status` tinyint(4) NOT NULL DEFAULT '0' COMMENT 'Whether the user is active(1) or blocked(0).',
  `real_name` tinyint(4) NOT NULL DEFAULT '0' COMMENT 'True name of user.',
  `delivery_address` varchar(200) NOT NULL DEFAULT '0' COMMENT '.',
  `weibo_screen_name` varchar(200) NOT NULL DEFAULT '0' COMMENT '.',
  `receive_newsletter` varchar(1) NOT NULL DEFAULT '0' COMMENT '',
  `played_game`  int(2) NOT NULL DEFAULT 0 COMMENT '',
  PRIMARY KEY (`uid`),
  UNIQUE KEY `name` (`name`),
  KEY `access` (`access`),
  KEY `created` (`created`),
  KEY `mail` (`mail`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Stores user data.';

CREATE TABLE IF NOT EXISTS  `game` (
  `gid` int(10) unsigned AUTO_INCREMENT COMMENT 'Primary Key: Unique game ID.',
  `name` varchar(60) NOT NULL DEFAULT '' COMMENT 'Unique game name.',
  `uuid` varchar(60) NOT NULL DEFAULT '' COMMENT 'Game UUID.',
  `created` int(11) NOT NULL DEFAULT '0' COMMENT 'Timestamp for when game was created.',
  `access` int(11) NOT NULL DEFAULT '0' COMMENT 'Timestamp for previous time game accessed the site.',
  PRIMARY KEY (`gid`),
  UNIQUE KEY `name` (`name`),
  KEY `access` (`access`),
  KEY `created` (`created`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Stores game data.';

CREATE TABLE IF NOT EXISTS  `user_game` (
  `id` int(10) unsigned AUTO_INCREMENT COMMENT 'Primary Key: Unique user game ID.',
  `gid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'Primary Key: Unique game ID.',
  `uid` varchar(60) NOT NULL DEFAULT '' COMMENT 'Unique game name.',
  `started` int(11) NOT NULL DEFAULT '0' COMMENT 'Timestamp for when game was created.',
  `finished` int(11) NOT NULL DEFAULT '0' COMMENT 'Timestamp for previous time game accessed the site.',
  `score` int(11) NOT NULL DEFAULT '0' COMMENT 'Find out account.',
  `shared_status` varchar(500) NOT NULL DEFAULT '' COMMENT '.',
  'map' varchar(500) NOT NULL DEFAULT '' COMMENT '',
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_game` (`uid`, `gid`),
  KEY `started` (`started`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Stores user palyed with game data.';

CREATE TABLE IF NOT EXISTS  `ci_sessions` (
	session_id varchar(40) DEFAULT '0' NOT NULL,
	ip_address varchar(45) DEFAULT '0' NOT NULL,
	user_agent varchar(120) NOT NULL,
	last_activity int(10) unsigned DEFAULT 0 NOT NULL,
	user_data text NOT NULL,
	PRIMARY KEY (session_id),
	KEY `last_activity_idx` (`last_activity`)
);

CREATE TABLE IF NOT EXISTS `newsletter` (
  `id` int(10) unsigned AUTO_INCREMENT,
  `email` varchar(100) DEFAULT '',
  `created` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY(`id`),
  KEY (`created`)
);


查询一个用户所玩游戏最高分的记录:
SELECT MAX( max_user_game.score ) AS max_score, max_user_game.uid AS max_uid, base_user_game. * , user . * 
FROM user_game max_user_game
LEFT JOIN user_game base_user_game ON base_user_game.uid = max_user_game.uid
LEFT JOIN user ON user.uid = base_user_game.uid
GROUP BY max_uid
