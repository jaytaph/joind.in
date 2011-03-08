<h2>Import Talks</h2>
<?php if (!empty($msg)): ?>
<?php $this->load->view('msg_info', array('msg' => $msg)); ?>
<?php endif; ?>

<?php if (!empty($error_msg)): ?>
<?php $this->load->view('msg_error', array('msg' => $error_msg)); ?>
<?php endif; ?>

<?php
  if (count($talks) > 0) :
    $this->load->view('event/_import_holding', array('talks' => $talks));
  else :
    $this->load->view('event/_import_upload');
  endif;
?>