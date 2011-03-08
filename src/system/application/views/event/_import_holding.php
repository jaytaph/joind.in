<div class="row">
    <p>
      Here you can find all your uploaded talks. Note that these talks are NOT yet committed. To commit these talks
      into the actual event, select the talks and press "Commit Selected Talks".
    </p>
</div>
<a name="holding"></a>
<?php echo form_open_multipart('event/import/'.$details[0]->ID, array('id'=>'holdingform')); ?>

<table summary="" cellpadding="0" cellspacing="0" border="1" width="100%" class="list">
<?php
foreach($talks as $k => $v){
	$this->load->view('event/_import_row', array('talk_id' => $k, 'talk'=>$v));	 }
?>
</table>
<div class="row">
	<a href=# onclick='javascript:checkSelection("holdingform", true); return false;'>Select all</a> -
    <a href=# onclick='javascript:checkSelection("holdingform", false); return false;'>Select none</a> -
    <a href=# onclick='javascript:toggleSelection("holdingform"); return false;'>Toggle selection</a>
</div>

<div class="row">
	<?php echo form_submit('commit','Commit selected talks'); ?>
</div>
<?php echo form_close(); ?>