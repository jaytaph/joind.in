<tr class="row1">
    <td><input type=checkbox checked name=talk[] value="<?php echo escape($talk_id); ?>"></td>
    <td>
        <?php $type = !empty($talk['cat_title']) ? $talk['cat_title'] : 'Talk'; ?>
        <span class="talk-type talk-type-<?php echo strtolower(str_replace(' ', '-', $type)); ?>" title="<?php echo escape($type); ?>"><?php echo escape(strtoupper($type)); ?></span>
    </td>
    <td><?php echo escape($talk['talk_title']) . " (".date('d M Y h:i',$talk['date_given']).")"; ?></td>
    <td><?php echo escape(join(',', $talk['speakers'])); ?></td>
    <td><?php echo escape($talk['lang_name']); ?></td>
</tr>
<tr class="row1">
    <td>&nbsp;</td>
    <td colspan=4>
        <?php echo escape($talk['talk_desc']); ?>
    </td>
</tr>
