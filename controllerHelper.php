<?php

$BASE_PATH = explode($_SERVER['DOCUMENT_ROOT'], __DIR__)[1];
if ($BASE_PATH[0] !== '/' && $BASE_PATH != "") { $BASE_PATH = "/".$BASE_PATH; }

function replace_keys($target, $keymap) {
  foreach ($keymap as $old => $new) {
    $target[$new] = $target[$old];
    unset($target[$old]);
  }
  return $target;
}

function convert_to_arrays($target, $array_keys) {
  foreach($array_keys as $key) {
    $target[$key] = explode(",", $target[$key]);
  }
  return $target;
}

function merge_checkbox($options, $submitted) {
  if (!is_array($submitted)) { return $options; }
  foreach ($options as $i => $val) {
    if (in_array($val["id"], $submitted)) {
      $options[$i]["checked"] = true;
    }
  }
  return $options;
}
