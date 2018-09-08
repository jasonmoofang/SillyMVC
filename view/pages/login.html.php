<h1>Login</h1>

<?php renderTemplate("_flashbar", $vars); ?>

<form method="POST">
  <p>email</p>
  <input type="text" name="email" />
  <p>password</p>
  <input type="password" name="password" />
  <br /><br />
  <input type="submit" value="login" />
</form>
