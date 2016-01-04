<?php

class ael_backlog_object
{
    var $command;
    var $bundle;
    var $entity;
    var $limit;
    var $feedback;
    var $ael_config; //= array();
    var $ael_config_pattern; //= array();
    var $ael_config_php; //= array();
    var $nid_array = array();
    var $mask;
    var $mask_sql_smarty;
    var $mask_sql_join_array = array();
    var $mask_rendered = array();
    var $update_sql_smarty;
    var $update_sql_rendered = array();
    var $output_string = 'OUTPUT PENDING OR ERROR';//'';
    var $output_message = 'OUTPUT MESSAGE PENDING OR ERROR';//'';
    var $output_message_type = 'success';//assume the best
    var $stack = array();
    var $stack_type = array();

    public function  __construct($command, $bundle, $entity)
    {
        $this->command = $command;
        $this->bundle = $bundle;
        $this->entity = $entity;
        $this->limit = $limit;
        $this->feedback = $feedback;
    }

    public function unpack()
    {

        $this->unpack_command();
        if ($this->output_message_type != 'success') {
            return;
        }
        $this->unpack_bundle();
        if ($this->output_message_type != 'success') {
            return;
        }
        $this->unpack_ael_config();
        if ($this->output_message_type != 'success') {
            return;
        }
        $this->unpack_mask();
        if ($this->output_message_type != 'success') {
            return;
        }
        $this->gather_output();
        return;
    }

    public function unpack_command()
    {
        $command = $this->command;
        $supported = array('compose', 'preview', 'mask');
        if (!in_array($command, $supported)) {
            $this->supported_command_array = $supported; // dynamic overload for print_r() purposes
            $this->output_message = "\"$command\" is NOT a supported command.";
            $this->output_message_type = __FUNCTION__ . ': ' . basename(__FILE__) . ' - line '. __LINE__;
        }
        return;//no change to $command
    }

    public function unpack_bundle()
    {
        //skeleton
        //no validation
        $this->entity = 'node';
        // $this->bundle = 'test_type';
        $this->bundle = 'player_standing';
        $this->mask = 'test_mask';
    }

    public function unpack_ael_config ()
    {
        $ael_var_string = 'auto_entitylabel';
        $config_string = $ael_var_string . '_' . $this->entity . '_' . $this->bundle;
        $config_pattern_string = $ael_var_string . '_pattern_' . $this->entity . '_' . $this->bundle;
        $config_php_string = $ael_var_string . '_php_' . $this->entity . '_' . $this->bundle;

        $variable_default = 'MISSING:' . $this->entity . '_' . $this->bundle;
        $this->ael_config = variable_get($config_string, $variable_default);
        $this->ael_config_pattern = variable_get($config_pattern_string, $variable_default);
        $this->ael_config_php = variable_get($config_php_string, $variable_default);
        $entity_bundle = $this->entity . '_' . $this->bundle;
        if ($this->ael_config != 1) {
            $this->output_message = "\"$entity_bundle\" is NOT active for AEL.";
            $this->output_message_type = __FUNCTION__ . ': ' . basename(__FILE__) . ' - line '. __LINE__;
        }
        if ($this->ael_config_php != 0) {
            $this->output_message = "\"$entity_bundle\" PHP is NOT yet supported by AEL_BackLog.";
            $this->output_message_type = __FUNCTION__ . ': ' . basename(__FILE__) . ' - line '. __LINE__;
        }
        return;
    }

    public function unpack_mask ()
    {
        $brk = "\r\n"; // could be cflf or lf or...
        $pattern = $this->ael_config_pattern;
        $pattern = str_replace('|', 'zPIPEz', $pattern);
        $pattern = str_replace(' ', 'zSPACEz', $pattern);
        $pipes = strpos($pattern, '|');
        $pattern = str_replace('[', '|[', str_replace(']', ']|', $pattern));
        $pattern_array = explode('|', $pattern);
        $pattern_sql_array = $pattern_array;
        foreach ($pattern_array as $index => $chunk) {
            $bracket_count = substr_count ( $chunk , '[') + substr_count ( $chunk , '[');
            if ($bracket_count === 2) {
                $chunk_sql = $this->pattern_to_sql_smarty($chunk);
            }elseif($bracket_count !== 0){
                $this->output_message = '"pattern chunk" contains an invalid number of square-bracket characters. Workaround is pending';
                $this->output_message_type = __FUNCTION__ . ': ' . basename(__FILE__) . ' - line '. __LINE__;
                $chunk_sql = $chunk;
            }else{
                $chunk_sql = '"' . $chunk. '"';
                $chunk_sql = str_replace('zSPACEz', ' ', $chunk_sql);
                $chunk_sql = str_replace('zPIPEz', '|', $chunk_sql);
            }
            $pattern_sql_array[$index] = $chunk_sql;
        }
        $this->mask = $pattern_sql_array;

        $mask_sql_smarty = implode(' + ', $pattern_sql_array);
        $mask_sql_smarty = 'SELECT ' . $brk . $mask_sql_smarty . ' ';
        $mask_sql_smarty .= $brk . 'FROM node n';
        $join_array = $this->mask_sql_join_array['join'];
        foreach ($join_array as $index => $join_singleton) {
            $mask_sql_smarty .= ' ' . $brk . $join_singleton;
        }
        $mask_sql_smarty .= ' ' . $brk . 'WHERE n.nid = {nid}';
        $this->mask_sql_smarty = $mask_sql_smarty;
        $update_sql_smarty = 'UPDATE node SET title=(' . $mask_sql_smarty . ') WHERE nid = {nid}';
        $this->update_sql_smarty = $update_sql_smarty;
        return;
    }

    public function gather_output ()
    {
        //skeleton
        switch ($this->command) {
            case 'compose':
                $this->output_message = 'Okay, I will compose the SQL';
                break;
            case 'preview':
                $this->output_message = 'Okay, I will compose a preview of the SQL and some results';
                break;
            case 'mask':
                $this->output_message = 'Okay, I will compose the mask SQL and generate and example';
                break;

            default:
                $this->output_message = 'The "' . $command . '" command is not supported, something went very wrong. Please asks for assistance.';
                $this->output_message_type = 'OOAOC should be caught before output.';
                break;
        }
        $this->output_string = '<pre>' . print_r($this, TRUE) . '</pre>';
        return;
    }
/* <called methods> prefixed with underscore to indicate this */
public function pattern_to_sql_smarty($chunk) {
    $bracket_count = substr_count ( $chunk , '[') + substr_count ( $chunk , '[');
    if($bracket_count !== 2){
    #\_ OOAAOC re-testing that bracket count === 2
        $this->output_message = '"pattern chunk" contains an invalid number of square-bracket characters. Workaround is pending';
        $this->output_message_type = __FUNCTION__ . ': ' . basename(__FILE__) . ' - line '. __LINE__;
        return $chunk;
    }
    $is_node_based = substr($chunk, 0, 6);
    if($is_node_based !== '[node:'){
    #\_ OOAAOC re-testing that bracket count === 2
        $this->output_message = '"pattern chunk" is NOT node-based. Additional Entities are pending';
        $this->output_message_type = __FUNCTION__ . ': ' . basename(__FILE__) . ' - line '. __LINE__;
        return $chunk;
    }
    $new_chunk = str_replace($is_node_based, 'n.', $chunk);
    $new_chunk = str_replace(']', '', $new_chunk);
    $colon_count = substr_count ( $new_chunk , ':');
    if ($colon_count == 1 ) {
        $new_chunk = $this->reference_join($chunk); //ORIGINAL
    }elseif($colon_count > 1){
        $this->output_message = '"pattern chunk" has more than 1 colons. Additional Entity Reference Chain Support is pending';
        $this->output_message_type = __FUNCTION__ . ': ' . basename(__FILE__) . ' - line '. __LINE__;
        return $new_chunk;
    }
    return $new_chunk;
}

public function reference_join($chunk){
        #\_ only node for now, see calling script
        $break = ''; // maybe crlf or lf or whatever, but nothing for now, so always include necessary spaces
        $reference_join = array();
        $new_chunk = $chunk;
        $new_chunk = str_replace('[node:', 'n.', $chunk);
        $new_chunk = str_replace('.', '_', $new_chunk);
        $new_chunk = str_replace('-', '_', $new_chunk);
        $new_chunk = str_replace(':', '.', $new_chunk);
        $new_chunk = str_replace(']', '', $new_chunk);
        $dot_position = strpos($new_chunk, '.');
        $alias = substr($new_chunk, 0, $dot_position);
        $tablename = $new_chunk;
        $dot_position = strpos($tablename, '.') + 1;
        $tablename = substr($tablename, $dot_position);
        // $dot_position = strpos($tablename, ':');
        // $tablename = substr($tablename, 0, $dot_position);
        $tablename = 'field_data_' . $tablename;
        $join = 'LEFT JOIN ';
        $declaration = $tablename . ' ' . $alias;
        $join .= $declaration . ' ' . $break;
        $join .= 'ON n.nid = ' . $alias . '.nid';
        $tablename_array = $this->mask_sql_join_array['tablename'];
        if (1 == 1 && !in_array($tablename, $tablename_array)) {
            $index = count($tablename_array);
            $this->mask_sql_join_array['tablename'][$index] = $tablename;
            $this->mask_sql_join_array['alias'][$index] = $alias;
            $this->mask_sql_join_array['join'][$index] = $join;
        }
        return $new_chunk;
}

/* <called methods> */


}