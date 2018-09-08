  <p>Family Name</p>
  <input type="text" name="last_name" value="<?php echo $vars['form_item']['last_name']; ?>" />
  <p>Given Name</p>
  <input type="text" name="first_name" value="<?php echo $vars['form_item']['first_name']; ?>" />
  <p>Email</p>
  <input type="text" name="email" value="<?php echo $vars['form_item']['email']; ?>" />
  <p>Country</p>
  <select name="country_id">
    <?php foreach($vars['countryOptions'] as $option) { ?>
      <option value="<?php echo $option['id']; ?>" <?php echo $vars['form_item']['country_id'] === $option['id'] ? "SELECTED" : "" ?>>
        <?php echo $option['name']; ?>
      </option>
    <?php } ?>
  </select>
 
