<h1>New Contact</h1>

<?php renderTemplate("_flashbar", $vars); ?>

<form method="POST">
  <?php renderTemplate("contacts/_contactform", $vars); ?>
  <br /><br />
  <input type="submit" value="create" />
</form>

<a href="<?php echo link_url('contacts', 'listing'); ?>">Back to listing</a>
