<?php

class Badge_model extends Model {

    function Badge_model() {
        parent::Model();
    }

    /**
     * Return all known badges in the system
     *
     * @return mixed
     */
    function getBadges() {
        $q = $this->db->get('badges');
        $result = $q->result();
        return $result;
    }

    /**
     * Adds the specified badge to the specified user
     *
     * @param $badge_id
     * @param $user_id
     */
    function addBadge($badge_id, $user_id, $level) {
        $arr = array('badge_id' => $badge_id,
                     'user_id'  => $user_id,
                     'level'    => $level,
                    );
        $this->db->insert('user_badges', $arr);
    }

    /**
     * Updates the level of the specfied badge
     *
     * @param $badge_id
     * @param $user_id
     * @param $level
     */
    function levelupBadge($badge_id, $user_id, $level) {
        $sql = sprintf("UPDATE user_badges SET level=%d WHERE user_id = %d AND badge_id = %d", $level, $user_id, $badge_id);
        $this->db->query($sql);
    }

    /**
     * Removes a badge from the user
     *
     * @param $badge_id
     * @param $user_id
     */
    function removeBadge($badge_id, $user_id) {
        $this->db->delete('user_badges', array('user_id' => $user_id, 'badge_id' => $badge_id));
    }

    /**
     * Returns true when the user $user_id has the specified badge
     *
     * @param $badge_id
     * @param $user_id
     * @return bool
     */
    function hasBadge($badge_id, $user_id) {
        $q = $this->db->get_where('user_badges', array('user_id' => $user_id, 'badge_id' => $badge_id));
        $result = $q->result();
        return isset($result[0]);
    }

    /**
     * Get all badges for specified user
     *
     * @param $user_id
     * @param bool $show_all When true, it will return ALL badges, including the ones the user doesn't have (check earned=1)
     * @return mixed
     */
    function getUserBadges($user_id, $show_all = false) {
        $sql = sprintf("SELECT badges.*,IF(user_id, 1, 0) AS earned, user_badges.level FROM badges
                        LEFT JOIN user_badges ON user_badges.badge_id = badges.id
                        WHERE user_id = %d".($show_all ? " OR user_id IS NULL" : ""), $user_id);
        $query = $this->db->query($sql);
        $result = $query->result();
        return $result;
    }


    /**
     * This method will check all existing badges and sees if the user is eligible for new badges. If so, the badge
     * would be added to the user, or the level of a badge will be upgraded.
     *
     * @param $user_id
     */
    function checkBadges($user_id) {
        $userBadges = $this->getUserBadges($user_id, true);
        foreach ($userBadges as $badge) {
            // We already got the badge and it's not a level badge
            if ($badge->earned == 1 && $badge->levels == 0) continue;

            $level = $this->_checkBadge($user_id, $badge);
            if ($level != 0) {
                if ($this->hasBadge($badge->id, $user_id)) {
                    $this->levelupBadge($badge->id, $user_id, $level);
                } else {
                    $this->addBadge($badge->id, $user_id, $level);
                }
            }
        }
    }


    /**
     * Returns a level for a number. Either the levels can be specified, or a default level-scheme is used.
     *
     * @param $i
     * @param null $arr
     * @return int
     */
    protected function _getLevel($i, $levels) {
        if ($i == 0) return 0;

        $ret = 0;
        $levels = explode(",", $levels);
        foreach ($levels as $level) {
            $level = trim($level);
            list ($tmp_level, $tmp_items) = explode(":", $level);
            if ($i >= $tmp_items) $ret = $tmp_level;
        }
        return $ret;
    }


    /**
     * Calls the correct method for the specified badge
     *
     * @param $user_id
     * @param $badge
     * @return bool|mixed
     */
    protected function _checkBadge($user_id, $badge) {
        $method = "_checkBadge_".strtolower(str_replace(" ", "", $badge->name));
        if (method_exists($this, $method)) {
            return call_user_func(array($this, $method), $user_id, $badge);
        }
        return false;
    }


    /*
     *
     *  Badges checkers. Returns the level (or 1 when for non-level badges) of the badge.
     *
     */

    protected function _checkBadge_newbie ($user_id, $badge) {
        // This badge is always available.
        return 1;
    }

    protected function _checkBadge_speaker ($user_id, $badge) {
        $sql = sprintf("SELECT COUNT(*) AS cnt FROM talk_speaker WHERE speaker_id = %d", $user_id);
        $query = $this->db->query($sql);
        $result = $query->result();

        return $this->_getLevel(isset ($result[0]) ? $result[0]->cnt : 0, $badge->levels);
    }

    protected function _checkBadge_commenter ($user_id, $badge) {
        $sql = sprintf("SELECT COUNT(*) AS cnt FROM talk_comments WHERE user_id = %d", $user_id);
        $query = $this->db->query($sql);
        $result = $query->result();

        return $this->_getLevel(isset ($result[0]) ? $result[0]->cnt : 0, $badge->levels);
    }

    protected function _checkBadge_conferenceadmin ($user_id, $badge) {
        $sql = sprintf("SELECT COUNT(*) AS cnt FROM user_admin WHERE uid = %d AND rtype='event'", $user_id);
        $query = $this->db->query($sql);
        $result = $query->result();

        return $this->_getLevel(isset ($result[0]) ? $result[0]->cnt : 0, $badge->levels);
    }

    protected function _checkBadge_traveler ($user_id, $badge) {
        $sql = sprintf("SELECT COUNT(DISTINCT e.event_tz_place) AS cnt FROM events AS e LEFT JOIN user_attend AS ua ON ua.eid = e.id WHERE ua.uid = %d", $user_id);
        $query = $this->db->query($sql);
        $result = $query->result();

        return $this->_getLevel(isset ($result[0]) ? $result[0]->cnt : 0, $badge->levels);
    }

    protected function _checkBadge_worldtraveler ($user_id, $badge) {
        $sql = sprintf("SELECT COUNT(DISTINCT e.event_tz_cont ) AS cnt FROM events AS e LEFT JOIN user_attend AS ua ON ua.eid = e.id WHERE ua.uid = %d", $user_id);
        $query = $this->db->query($sql);
        $result = $query->result();

        return $this->_getLevel(isset ($result[0]) ? $result[0]->cnt : 0, $badge->levels);
    }

    protected function _checkBadge_attender ($user_id, $badge) {
        $sql = sprintf("SELECT COUNT(*) AS cnt FROM user_attend WHERE uid = %d", $user_id);
        $query = $this->db->query($sql);
        $result = $query->result();

        return $this->_getLevel(isset ($result[0]) ? $result[0]->cnt : 0, $badge->levels);
    }

    protected function _checkBadge_socializer ($user_id, $badge) {
        $sql = sprintf("SELECT COUNT(*) AS cnt FROM talk_comments AS tc
                        LEFT JOIN talk_cat AS talkcat USING(talk_id)
                        LEFT JOIN categories AS cat ON cat.ID = talkcat.cat_id
                        WHERE tc.user_id = %d AND cat.cat_title LIKE 'Social';", $user_id);
        $query = $this->db->query($sql);
        $result = $query->result();

        return $this->_getLevel(isset ($result[0]) ? $result[0]->cnt : 0, $badge->levels);
    }

    protected function _checkBadge_workshop ($user_id, $badge) {
        $sql = sprintf("SELECT COUNT(*) AS cnt FROM talk_comments AS tc
                        LEFT JOIN talk_cat AS talkcat USING(talk_id)
                        LEFT JOIN categories AS cat ON cat.ID = talkcat.cat_id
                        WHERE tc.user_id = %d AND cat.cat_title LIKE 'Workshop';", $user_id);
        $query = $this->db->query($sql);
        $result = $query->result();

        return $this->_getLevel(isset ($result[0]) ? $result[0]->cnt : 0, $badge->levels);
    }

}
?>
