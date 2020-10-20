 <?php 
 $baseurl = strtok($_SERVER["REQUEST_URI"],'?'); 
 $key = (isset($vars['_pagevar'])) ? $vars['_pagevar'] : 'page'; ?>
 <ul class="pagination">
  <?php if ($vars[$key]->getCurrentPage() > 1) { ?>
    <li><a href="<?php echo $baseurl."?".build_new_querystring($_GET, array($key => ($vars[$key]->getCurrentPage() - 1))); ?>" class="page-link"><?php echo ($vars[$key]->getCurrentPage() - 1); ?></a></li>
  <?php } ?>
  <li class="page-item active"><a href="#" class="page-link"><?php echo $vars[$key]->getCurrentPage(); ?></a></li>
  <?php if ($vars[$key]->getCurrentPage() < $vars[$key]->getTotalPages()) { ?>
    <li><a href="<?php echo $baseurl."?".build_new_querystring($_GET, array($key => ($vars[$key]->getCurrentPage() + 1))); ?>" class="page-link"><?php echo ($vars[$key]->getCurrentPage() + 1); ?></a></li>
  <?php } ?>
 </ul>  
