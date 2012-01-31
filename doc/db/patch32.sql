CREATE TABLE IF NOT EXISTS `badges` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  `description` varchar(200) NOT NULL,
  `level_badge` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;

INSERT INTO `badges` (`id`, `name`, `description`, `level_badge`) VALUES
(1, 'newbie', 'This badge is given to all users who have registered to the joind.in website', 0),
(2, 'speaker', 'This badge is given to all users who have claimed at least one talk', 1),
(3, 'commenter', 'This badge is given to users who have commented to at least one talk', 1),
(4, 'conference admin', 'Organised / hosted at least 1 conference', 1),
(5, 'valuable commenter', '', 0),
(6, 'traveler', 'Attended conferences in at least 2 different places', 1),
(7, 'attender', 'When you attended at least 1 conferences.', 1),
(8, 'socializer', 'commented on at least one social event', 1),
(9, 'workshop', 'commented on at least one workshop', 1),
(10, 'worldtraveler', 'Attended conferences on at least 2 different continents', 1);


CREATE TABLE IF NOT EXISTS `user_badges` (
  `user_id` int(11) NOT NULL,
  `badge_id` int(11) NOT NULL,
  `level` int(11) NOT NULL,
  PRIMARY KEY (`user_id`,`badge_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- Increase patch count
INSERT INTO patch_history SET patch_number = 32;
