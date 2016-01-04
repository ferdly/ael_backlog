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
        $this->unpack_mask_pattern();
        if ($this->output_message_type != 'success') {
            return;
        }
        $this->unpack_mask_php();
        if ($this->output_message_type != 'success') {
            return;
        }
        $this->unpack_mask_bundles();
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

    public function unpack_mask_pattern ()
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

    public function unpack_mask_php (){
        /**
         * @todo - unsupported at first juncture
         * @circleback - purposely left for later
         */
        return;
    }

    public function unpack_mask_bundles (){
        /**
         * @Todo - based on pattern and php gather the bundles required to compose SQL
         * @circleback - all steps regarding php are left for later
         * @BUG this is test of upper bug
         * @fixMe I guess this is broken but not buggy (like an editing circleback)
         * @cosmetic this will rarely exist in doxygen since php is rarely cosmetic
         */
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

}