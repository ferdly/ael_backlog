<?php

/**
 * Implements hook_drush_command().
 */
function ael_backlog_drush_command() {

  $items['ael-backlog-command'] = array(
    'description' => 'Demonstrate how AEL Backlog command works.',
    'aliases' => array('label'), //anagram of AEL BL
    'arguments' => array(
      'command' => 'The type of statement (compose or preview).',
    ),
    'options' => array(
      'limit' => 'The number of items to execute for the command (smart defaults).',
    ),
    'examples' => array(
      'drush ddc error' => 'Prints the statement once with the error flag.',
      'drush ddc success --limit=10' => 'Prints the statement 10 times with the success flag.',
    ),
  );

  return $items;
}

/**
 * Callback for the drush-demo-command command
 */
function drush_ael_backlog_command($command = FALSE) {

  // Check for existence of argument
    $options = _bundle_options();
    if (!$command) {
        $command = drush_choice($options, dt('For which Bundle would you like to generate \'Auto Enity-Label Back-Log\' SQL Code?'));
    }

  // Check for correct argument
  // $correct_args = array('compose', 'preview');
  $correct_args = array_keys($options);
  $correct_args_count = count($correct_args);
  $first_args = $correct_args;
  $last_arg = array_pop($first_args);
  $first_args_list = implode(", ", $first_args);
  $zero = 'Please ask for assistance, there are currently no auto_entitylabel configurations in use';
  $non_zero = 'Please select';
  $grammar = $correct_args_count > 2?' among: ':': ';
  $grammar = $correct_args_count == 2?' between: ':$grammar;
  $list = '';
  $list = $correct_args_count == 1?$last_arg:$list;
  $list = $correct_args_count > 1?$first_args_list. ' and ' . $last_arg:$list;
  if ($correct_args_count == 0) {
    $string = $zero;
  }else{
    $string = $non_zero . $grammar . $list . '. (Or supply no bundle and select from promted values.)';
  }
  if (!in_array($command, $correct_args)) {
    // return drush_set_error(dt('"@type" is not a valid code type. Please choose between "success" and "error".', array('@type' => $command)));
    return drush_set_error(dt('"@type" is not a valid bundle (content type). @string', array('@type' => $command,'@string' => $string)));
  }

  // Option
  $limit = drush_get_option('repeat', 'EEMPTY');
  if (strtolower($limit) == 'all') {
        $limit = strtolower($limit);
        _drush_execute_command($command, $limit);
  }
  elseif ($limit >= 0 && is_numeric($limit)) {
        $limit = (int) floor($limit);
        //FIND OUT WHAT migrate CALLS THE COUNT PRAMETER
        _drush_execute_command($command, $limit);
    }
    else {
        _drush_execute_command($command);
    }

}

function _bundle_options(){
  $entity_bundle_array = field_info_bundles();
  foreach ($entity_bundle_array as $entity => $bundle_array) {
    foreach ($bundle_array as $bundle => $value_array) {
      $check_vars[$bundle] = $entity;
      $is_ael = _check_ael_bundle($bundle, $entity);
      if ($is_ael == 1) {
        $content_type = $entity == 'node'?' (content type)':'';
        $string = "'{$bundle}' [bundle{$content_type} of '{$entity}']";
        $options[$bundle] = dt($string);
      }
    }
  }

  return $options;
}
function _check_ael_bundle($bundle, $entity){
  $var_name = 'auto_entitylabel_' . $entity . '_' . $bundle;
  $is_ael = variable_get($var_name, 0) + 0;
  return $is_ael;
}
