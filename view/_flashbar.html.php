<?php if ($vars['flash']['success']) { ?>
<p><strong><?php echo $vars['flash']['success']; ?></strong></p>
<?php  } ?>
<?php if ($vars['flash']['warning']) { ?>
<p><em><?php echo $vars['flash']['warning']; ?></em></p>
<?php  } ?>
<?php if ($vars['flash']['error']) { ?>
<p><u><?php echo $vars['flash']['error']; ?></u></p>
<?php  } ?>
<?php if ($vars['flash']['info']) { ?>
<p><?php echo $vars['flash']['info']; ?></p>
<?php  } ?>

