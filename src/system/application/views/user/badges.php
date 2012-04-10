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

    &nbsp;<span style='<?php echo $style;?>'>&nbsp;<?php echo $badge->name; ?>
<?php if ($badge->levels && $badge->earned) : ?>&nbsp;(level <?php echo $badge->level; ?>)<?php endif; ?>&nbsp;</span>
<?php endforeach; ?>
</div>


<hr>

<?php foreach ($badges as $badge) : if (! $badge->earned) continue; ?>

    <div class="row">
            <h3>'<?php print $badge->name; ?>' badge</h3>
            <?php print $badge->description; ?>
        <?php if ($badge->levels && $badge->earned) : ?>
            <br><br>
            You are at level <?php echo $badge->level; ?>
        <?php endif; ?>
    </div>
<?php endforeach; ?>
