<h1>Dashboard</h1>

<?php renderTemplate("_flashbar", $vars); ?>

<p>Hello <?php echo $CURRENT_USER['email']; ?></p>

<a href="<?php echo link_url('contacts', 'listing'); ?>">Contacts</a>

<a href="<?php echo link_url('pages', 'logout'); ?>">Logout</a>
