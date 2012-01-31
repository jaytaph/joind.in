<h2>Statistics</h2>
<table cellpadding="0" cellspacing="0" border="0" width=50%>

    <tr>
        <td>Conferences attended</td><td>:</td>
        <td align=right><?php echo $stats->conferences_attended; ?> </td>
    </tr>
    <tr>
        <td>Total event comments</td><td>:</td>
        <td align=right><?php echo $stats->total_event_comments; ?> </td>
    </tr>
    <tr>
        <td>Total talk comments</td><td>:</td>
        <td align=right><?php echo $stats->total_talk_comments; ?> </td>
    </tr>
    <tr>
        <td>Average rating per comment</td><td>:</td>
        <td align=right><?php echo round($stats->avg_rate_per_comment, 2); ?> </td>
    </tr>
    <tr>
        <td>Average length per comment</td><td>:</td>
        <td align=right><?php echo round($stats->avg_length_per_comment, 2); ?></td>
    </tr>

    <tr><td colspan=3><hr></td></tr>
    <tr>
        <td>Talks given</td><td>:</td>
        <td align=right><?php echo $stats->talks_given; ?></td>
    </tr>
    <tr>
        <td>Average talk rating</td><td>:</td>
        <td align=right><?php echo round($stats->avg_talk_rating, 2); ?></td>
    </tr>
    <tr>
        <td>Average comments per talk</td><td>:</td>
        <td align=right><?php echo round($stats->avg_comment_per_talk, 2); ?></td>
    </tr>

</table>
