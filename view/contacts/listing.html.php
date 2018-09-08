<?php renderTemplate("_flashbar", $vars); ?>

<h1>Your contacts</h1>

<?php if (count($vars['contact_list']) < 1) { ?>
<p>(You don't have any)</p>
<?php } ?>

<?php foreach($vars['contact_list'] as $contact) { ?>

<p>
  <?php echo $contact['first_name'].' '.$contact['last_name']; ?>, email: <?php echo $contact['email']; ?>. From <?php echo $contact['country']; ?>.
  <a href="<?php echo link_url('contacts', 'destroy', 'id='.$contact['id']); ?>">Delete me</a> |
  <a href="<?php echo link_url('contacts', 'update', 'id='.$contact['id']); ?>">Update me</a> 
</p>

<?php } ?>

<a href="<?php echo link_url('contacts', 'create'); ?>">Add new</a>
