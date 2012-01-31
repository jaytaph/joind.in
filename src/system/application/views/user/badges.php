<?php
menu_pagetitle('Badges');
?>

<div class="box">
    <h2>Your earned badges</h2>

<?php foreach ($badges as $badge) :
    if ($badge->earned) {
        $style = 'background-color: darkblue; color: white;';
    } else {
        $style = 'background-color: lightblue; color: white;';
    }
?>

    &nbsp;<span title='<?php echo $badge->description; ?>.<?php if ($badge->level_badge && $badge->earned) : ?> You are at level: <?php echo $badge->level; ?><?php endif;?>' style='<?php echo $style;?>'>&nbsp;<?php echo $badge->name; ?>
<?php if ($badge->level_badge && $badge->earned) : ?>&nbsp;(<?php echo $badge->level; ?>)<?php endif; ?>&nbsp;</span>
<?php endforeach; ?>
</div>


