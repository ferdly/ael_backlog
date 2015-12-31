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
    $options = array(
      'compose' => dt('Compose SQL'),
      'preview' => dt('Preview Result'),
      'mask' => dt('Preview The Mask and its SQL'),
      );
    if (!$command) {
        $command = drush_choice($options, dt('What kind of code would you like to generate?'));
    }

  // Check for correct argument
  // $correct_args = array('compose', 'preview');
  $correct_args = array_keys($options);
  $correct_args_count = count($correct_args);
  $first_args = $correct_args;
  $last_arg = array_pop($first_args);
  $correct_args_grammer = '';
  $correct_args_grammer = $correct_args_count > 1?'between ':$correct_args_grammer;
  $correct_args_grammer = $correct_args_count > 2?'among ':$correct_args_grammer;
  $correct_args_string = '"' . implode('&quot;, &quot;', $first_args) . '"';
  // $correct_args_string = $correct_args_grammer .  $correct_args_string;
  $correct_args_string .= $correct_args_count > 1?' and "' . $last_arg . '"':' "' . $last_arg . '"';
  // $correct_args_string = $last_arg;
  if (1 == 2 && !in_array($command, $correct_args)) {
    // return drush_set_error(dt('"@type" is not a valid code type. Please choose between "success" and "error".', array('@type' => $command)));
    return drush_set_error(dt('"@type" is not a valid code type. Please choose @arg_string.', array('@type' => $command,'@arg_string' => $correct_args_string)));
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