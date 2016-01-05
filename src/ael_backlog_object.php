<?php

class ael_backlog_object
{
    var $command;
    var $bundle;
    var $entity;
    var $limit;
    var $feedback;
    var $entity_array = array();
    var $ael_config; //= array();
    var $ael_config_pattern; //= array();
    var $ael_config_php; //= array();
    var $nid_array = array();
    var $mask;
    var $mask_config_array = array();
    var $mask_field_array = array();
    var $mask_join_array = array();
    var $mask_sql_smarty;
    var $mask_rendered = array();
    var $update_sql_smarty;
    var $update_sql_rendered = array();
    var $output_string = 'OUTPUT PENDING OR ERROR';//'';
    var $output_message = 'OUTPUT MESSAGE PENDING OR ERROR';//'';
    var $output_message_type = 'success';//assume the best
    var $stack = array();
    var $stack_type = array();
    var $crlf = "\r\n";
    var $tab = "    ";
    var $space = " ";
    var $temp_ouput;

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
        $this->unpack_all_entities_method();
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
        $this->unpack_mask_config();
        if ($this->output_message_type != 'success') {
            return;
        }
        $this->unpack_mask_fields();
        if ($this->output_message_type != 'success') {
            return;
        }
        $this->unpack_update_sql_smarty();
        if ($this->output_message_type != 'success') {
            return;
        }
        // $this->dev_method();
        // if ($this->output_message_type != 'success') {
        //     return;
        // }
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

    public function unpack_all_entities_method()
    {
        // $variable_name = 'entityreference:base-tables';
        // $variable_default = 'MISSING:' . $this->entity . '_' . $this->bundle;
        $this->entity_array = unpack_all_entities();
    }

    public function unpack_bundle()
    {
        $entity_default = 'node';
        $bundle_default = 'player_standing';
        /**
         * @circleback get the parameter from the drush command
         */
        $this->entity = $entity_default;
        $this->bundle = $bundle_default;
        /**
         * @todo get_var(entityreference-base-tables)
         */
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
        $pattern = str_replace('[', '|[', str_replace(']', ']|', $pattern));
        $pattern_array = explode('|', $pattern);
        foreach ($pattern_array as $index => $chunk) {
            $bracket_count = substr_count ( $chunk , '[') + substr_count ( $chunk , '[');
            if ($bracket_count === 2) {
                $field_array[$index] = $chunk;
                $join_array[$index] = $chunk;
            }elseif($bracket_count !== 0){
                $this->output_message = '"pattern chunk" contains an invalid number of square-bracket characters. Workaround is pending';
                $this->output_message_type = __FUNCTION__ . ': ' . basename(__FILE__) . ' - line '. __LINE__;
                // $chunk_sql = $chunk;
            }else{
                $chunk = '"' . $chunk. '"';
                $chunk = str_replace('zSPACEz', ' ', $chunk);
                $chunk = str_replace('zPIPEz', '|', $chunk);
            }
            $pattern_array[$index] = $chunk;
        }
        $this->mask = $pattern_array;
        $this->mask_config_array = $field_array;
        $this->mask_field_array = $field_array;
        $this->mask_join_array = $join_array;
        return;
        /**
         * @circleback code below could be useful at composition phase
         * @todo remove return above when is end of function
         *
         * @var        Function
         */
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

    public function unpack_mask_config () {
        $config_array = $this->mask_config_array;
        foreach ($config_array as $index => $string) {
            $config_singleton = unpack_mask_config_singleton($string, $index);
            unset($this->mask_config_array[$index]);
            $this->mask_config_array[$index] = $config_singleton;
        }
    }



    public function unpack_mask_fields ()
    {
        /**
         * @circleback - all steps regarding php are left for later
         */
        $mask_base_table = $this->entity_array[$this->entity]['table'];
        $mask_base_alias = $this->entity_array[$this->entity]['alias'];
        $mask_base_primary = $this->entity_array[$this->entity]['primary'];
        $mask_base_smarty = '{' . $mask_base_alias . '.' . $mask_base_primary . '}';
        $field_array = $this->mask_field_array;
        $i = 0;
        foreach ($field_array as $index => $chunk) {
            $config = $this->mask_config_array[$index];

            if (count($config[reference_array]) == 0) {
                $chunk_sql = $this->unpack_mask_field_direct ($config);
            }elseif(count($config[reference_array]) == 1)
            {
                $chunk_sql = $this->unpack_mask_field_reference ($config);
            }else
            {
                $chunk_sql = 'EERROR';
                $this->output_message = "\"$command\" is NOT a supported command.";
                $this->output_message_type = __FUNCTION__ . ': ' . basename(__FILE__) . ' - line '. __LINE__;
            }
            unset($this->mask_field_array[$index]);
            $this->mask_field_array[$index]['string'] = $chunk;
            $this->mask_field_array[$index]['sql'] = $chunk_sql;
        }
        $mask_sql_smarty = '';
        $concat_string = 'CONCAT( ';
        $comma_string = '';
        foreach ($this->mask as $index => $chunk) {
            $concat_string .= $comma_string;
           if (!empty($this->mask_field_array[$index]['sql'])) {
                $concat_string .= '(' . $this->mask_field_array[$index]['sql'] . ')';
            }else{
                $concat_string .= $chunk;
            }
            $comma_string = ', ';
        }
        $concat_string .= ')';
        $mask_sql_smarty = 'SELECT ' . $concat_string . ' FROM ' . $mask_base_table . ' ' . $mask_base_alias . ' WHERE ' . $mask_base_alias . '.' . $mask_base_primary . ' = ' . $mask_base_smarty;


        $this->mask_sql_smarty = $mask_sql_smarty;
       return;
    }

    public function unpack_mask_field_direct ($config)
    {
        $entity = $this->entity_array[$config['entity']];
        $field_sql = $entity['alias'] . '.' . $config['field'];
        return $field_sql;
    }

    public function unpack_mask_field_reference ($config)
    {
        /**
         * @todo determine whether part of base entity table or bundle field
         */

        $target_type = $config['reference_array'][0]['data']['target_type'];
        $target_entity = $this->entity_array[$target_type];
        $from_bundle = $config['reference_array'][0]['data']['from_bundle'] + 0;
        if ($from_bundle == 1) {
            $field = $config['field'];
            $outer_alias = implode(array_map('upmfr_left_init', explode('_', $field)));
            $outer_table_name = 'field_data_' . $field;
            $outer_field_name = $field . '_value';
            $outer_primary = 'entity_id';
        }else{
            $outer_alias = $target_entity['alias'];
            $outer_table_name = $target_entity['table'];
            $outer_field_name = $config['field'];
            $outer_primary = $target_entity['primary'];
        }
        $target_table_name = $config['reference_array'][0]['data']['target_table_name'];
        $target_field_name = $config['reference_array'][0]['data']['target_field_name'];
        $entity = $this->entity_array[$config['entity']];
        $target_alias = $config['reference_array'][0]['alias'];
        $base_entity = $this->entity_array[$config['entity']];
        $base_smarty = '{' . $base_entity['alias'] . '.' . $base_entity['primary'] . '}';
        $outer_entity_id_smarty = $outer_alias . '.' . $outer_primary;
        $target_entity_id_sql = "SELECT {$target_alias}.{$target_field_name}
                    FROM {$target_table_name} {$target_alias}
                    WHERE {$target_alias}.entity_id = {$base_smarty}";
        $outer_field_sql = "SELECT {$outer_alias}.{$outer_field_name}
                            FROM {$outer_table_name} {$outer_alias}
                            WHERE {$outer_alias}.{$outer_primary} = ({inner_sql})";
        $field_sql = str_replace('{inner_sql}', $target_entity_id_sql, $outer_field_sql);
        return $field_sql;
    }

    public function unpack_mask_joins ()
    {
        /**
         * @todo build joins rewrite smarty as:
         *
 UPDATE node n
 SET n.title = (
   SELECT
   CONCAT('Week ', w.field_nfl_sequence_value, ' Standing for ', p.name, ' (', n.nid, ')')
   FROM node_revision nr
   LEFT JOIN (
     SELECT
     wd.entity_id
     , wd.field_week_target_id
     , wt.field_nfl_sequence_value
     FROM field_data_field_week wd
     LEFT JOIN field_data_field_nfl_sequence wt
     ON wd.field_week_target_id = wt.entity_id
   ) w
   ON w.entity_id = nr.nid
   LEFT JOIN (
     SELECT
     pd.entity_id
     , pd.field_player_target_id
     , pt.name
     FROM field_data_field_player pd
     LEFT JOIN users pt
     ON pd.field_player_target_id = pt.uid
   ) p
   ON p.entity_id = nr.nid
   WHERE nr.nid IN (58)
 )
 WHERE n.nid IN (58)
 ;
         */
    }

    public function unpack_update_sql_smarty()
    {
        $ael_this = 'SET @ael_this = (' . $this->mask_sql_smarty . ');';
        $entity = $this->entity_array[$this->entity];
        $table = $entity['table'];
        $primary = $entity['primary'];
        $primary_smarty = '{' . $entity['alias'] . '.' . $primary . '}';
        $alias = $entity['alias'] . $entity['alias'];
        $update_sql_smarty = 'UPDATE ' . $table . ' ' . $alias . ' SET ' . $alias . '.title = (' . '@ael_this' . ') WHERE ' . $alias . '.' . $primary . ' = ' . $primary_smarty . ';';
        $this->update_sql_smarty = $ael_this . $this->space . $this->crlf . $update_sql_smarty;
    }

    public function dev_method($options = NULL) {
        $field_name = 'field_player';
        // $this->temp_ouput = $field_name;

        /**
         * @comment in db for table field_config for column field_name: 'The name of this field. Non-deleted field names are unique, but multiple deleted fields can have the same name.'
         */
        $config =
            db_query('SELECT
                id
                , field_name
                , type
                , module
                , active
                , storage_type
                , storage_module
                , storage_active
                , locked
                , data
                , cardinality
                , translatable
                , deleted
                FROM {field_config}
                WHERE field_name = :field_name AND deleted = :deleted',
                array(':field_name' => $field_name, ':deleted'=> 0))->fetchAssoc();
        $config_data = unserialize($config['data']);
        $config['data'] = $config_data;
        $this->temp_ouput = $config;
        if ($config['type'] != 'entityreference') {
                $this->output_message = '"field_config[type]" is NOT \'entityreference\'';
                $this->output_message_type = __FUNCTION__ . ': ' . basename(__FILE__) . ' - line '. __LINE__;
        }
        elseif ($config['module'] != 'entityreference') {
                $this->output_message = '"field_config[module]" is NOT \'entityreference\'';
                $this->output_message_type = __FUNCTION__ . ': ' . basename(__FILE__) . ' - line '. __LINE__;
        }
        elseif ($config['storage_type'] != 'field_sql_storage') {
                $this->output_message = '"field_config[storage_type]" is NOT \'field_sql_storage\'';
                $this->output_message_type = __FUNCTION__ . ': ' . basename(__FILE__) . ' - line '. __LINE__;
        }
        elseif ($config['storage_module'] != 'field_sql_storage') {
                $this->output_message = '"field_config[storage_module]" is NOT \'field_sql_storage\'';
                $this->output_message_type = __FUNCTION__ . ': ' . basename(__FILE__) . ' - line '. __LINE__;
        }
        elseif ($config['storage_active'] != 1) {
                $this->output_message = '"field_config[storage_active]" is NOT \'1\'';
                $this->output_message_type = __FUNCTION__ . ': ' . basename(__FILE__) . ' - line '. __LINE__;
        }
        elseif ($config['active'] != 1) {
                $this->output_message = '"field_config[active]" is NOT \'1\'';
                $this->output_message_type = __FUNCTION__ . ': ' . basename(__FILE__) . ' - line '. __LINE__;
        }
        elseif ($config['cardinality'] != 1) {
                $this->output_message = '"field_config[cardinality]" is NOT \'1\'. Cardinality greater than 1 is not supported at this time.';
                $this->output_message_type = __FUNCTION__ . ': ' . basename(__FILE__) . ' - line '. __LINE__;
        }
        elseif ($config['deleted'] != 0) {
                $this->output_message = '"field_config[deleted]" is NOT \'0\'';
                $this->output_message_type = __FUNCTION__ . ': ' . basename(__FILE__) . ' - line '. __LINE__;
        }
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
        $this->output_string = "=====================================";
        $attribute_array = array(
         // 'command',
        // 'bundle',
        'entity',
        // 'limit',
        // 'feedback',
        // 'entity_array',
        // 'ael_config',
        // 'ael_config_pattern',
        // 'ael_config_php',
        // 'nid_array',
        // 'mask',
        'mask_config_array',
        // 'mask_field_array',
        'mask_join_array',
        'mask_sql_smarty',
        // 'mask_rendered',
        'update_sql_smarty',
        // 'update_sql_rendered',
        // 'output_string',
        'output_message',
        'output_message_type',
        // 'stack',
        // 'stack_type',
        'temp_ouput',
        );
        if (count($attribute_array) > 0) {
            foreach ($attribute_array as $index => $attribute) {
                $this->output_string .= "\r\n" . $attribute . ":\r\n    " . print_r($this->$attribute, TRUE);
            } //END foreach()
        }else{
            $this->output_string .= "\r\n" . print_r($this, TRUE);
        }
        $this->output_string .= "\r\n=====================================\r\n";

        return;
    }

} //END Class ael_backlog_object


    function unpack_mask_config_singleton($string, $index) {
        $return_array = array();
        $return_array['index'] = $index;
        $return_array['string'] = $string;
        $string = str_replace('[', '', str_replace(']', '', $string));
        $string_array = explode(':', $string);
        $base_entity = array_shift($string_array);
        $field = array_pop($string_array);
        $i = 0;
        foreach ($string_array as $ref_index => $reference) {
            $reference_array[$i] = unpack_reference_singleton($reference, $index, $ref_index);
            $i++;
        }
        $field_table = 'field_data_' . $field;
        $field_name = $field . '_value';
        $return_array['entity'] = $base_entity;
        $return_array['field'] = $field;
        // $return_array['field_name'] = $field_name;
        // $return_array['field_table'] = $field_table;
        $return_array['reference_array'] = $reference_array;
        return $return_array;
    }

    function unpack_reference_singleton($reference, $index, $ref_index)
    {
        $reference_array = array();
        $reference_array['alias'] = 'r' .  $index . '_' . $ref_index;
        $reference_array['string'] = $reference;
        $field_name = str_replace('-', '_', $reference);
        $reference_array['field_name'] = $field_name;
                /**
         * @comment in db for table field_config for column field_name: 'The name of this field. Non-deleted field names are unique, but multiple deleted fields can have the same name.'
         */
        $config =
            db_query('SELECT
                id
                , field_name
                , type
                , module
                , active
                , storage_type
                , storage_module
                , storage_active
                , locked
                , data
                , cardinality
                , translatable
                , deleted
                FROM {field_config}
                WHERE field_name = :field_name AND deleted = :deleted',
                array(':field_name' => $field_name, ':deleted'=> 0))->fetchAssoc();
        $config_data = unserialize($config['data']);
        $config_data_limited_array['target_type'] = $config_data['settings']['target_type'];
        $config_data_limited_array['from_bundle'] = count(@$config_data['settings']['handler_settings']['target_bundles']) > 0?1:0;
        $limited_table = key($config_data['storage']['details']['sql']['FIELD_LOAD_CURRENT']);
        $limited_field = $config_data['storage']['details']['sql']['FIELD_LOAD_CURRENT'][$limited_table]['target_id'];
        $config_data_limited_array['target_table_name'] = $limited_table;
        $config_data_limited_array['target_field_name'] = $limited_field;
        $config['data'] = $config_data;
        // $reference_array['all'] = $config_data;
        $reference_array['data'] = $config_data_limited_array;
        if ($config['type'] != 'entityreference') {
                $output_message = '"field_config[type]" is NOT \'entityreference\'';
                $output_message_type = __FUNCTION__ . ': ' . basename(__FILE__) . ' - line '. __LINE__;
        }
        elseif ($config['module'] != 'entityreference') {
                $output_message = '"field_config[module]" is NOT \'entityreference\'';
                $output_message_type = __FUNCTION__ . ': ' . basename(__FILE__) . ' - line '. __LINE__;
        }
        elseif ($config['storage_type'] != 'field_sql_storage') {
                $output_message = '"field_config[storage_type]" is NOT \'field_sql_storage\'';
                $output_message_type = __FUNCTION__ . ': ' . basename(__FILE__) . ' - line '. __LINE__;
        }
        elseif ($config['storage_module'] != 'field_sql_storage') {
                $output_message = '"field_config[storage_module]" is NOT \'field_sql_storage\'';
                $output_message_type = __FUNCTION__ . ': ' . basename(__FILE__) . ' - line '. __LINE__;
        }
        elseif ($config['storage_active'] != 1) {
                $output_message = '"field_config[storage_active]" is NOT \'1\'';
                $output_message_type = __FUNCTION__ . ': ' . basename(__FILE__) . ' - line '. __LINE__;
        }
        elseif ($config['active'] != 1) {
                $output_message = '"field_config[active]" is NOT \'1\'';
                $output_message_type = __FUNCTION__ . ': ' . basename(__FILE__) . ' - line '. __LINE__;
        }
        elseif ($config['cardinality'] != 1) {
                $output_message = '"field_config[cardinality]" is NOT \'1\'. Cardinality greater than 1 is not supported at this time.';
                $output_message_type = __FUNCTION__ . ': ' . basename(__FILE__) . ' - line '. __LINE__;
        }
        elseif ($config['deleted'] != 0) {
                $output_message = '"field_config[deleted]" is NOT \'0\'';
                $output_message_type = __FUNCTION__ . ': ' . basename(__FILE__) . ' - line '. __LINE__;
        }
        if (!empty($output_message)) {
            $reference_array['error'] = $output_message;
            $reference_array['error_debug'] = $output_message_type;
        }

        return $reference_array;
    }

    function unpack_all_entities()
    {
        $variable_name = 'entityreference:base-tables';
        $variable_default = 'MISSING: ' . $variable_name;
        $entity_raw_array = variable_get($variable_name, $variable_default);
        $used_alias_array = array();
        $i = 0;
        function left_init($value) {return substr($value, 0, 1);}
        foreach ($entity_raw_array as $key => $value) {
            $alias_final = '';
            $initials_try =  implode(array_map('left_init', explode('_', $key)));
            $alias_try = $initials_try;
            if (!in_array($alias_try, $used_alias_array)) {
                $alias_final = $alias_try;
            }
            if (strlen($alias_final) < 1) {
                $try_i = 1;
                $init_len = strlen($initials_try) + 0;
                while ($try_i <= $init_len) {
                    $alias_try = substr($initials_try, 0, $try_i);
                    if (!in_array($alias_try, $used_alias_array)) {
                        $alias_final = $alias_try;
                        break;
                    }
                    $try_i++;
                }
            }
            if (strlen($alias_final) < 1) {
                $try_i = 1;
                $key_len = strlen($key) + 0;
                while($try_i < $key_len){
                    $alias_try = substr($key, 0, $try_i);
                    if (!in_array($alias_try, $used_alias_array)) {
                        $alias_final = $alias_try;
                        break;
                    }
                    $try_i++;
                }

            }
            $alias_final = strlen($alias_final) < 1?$key:$alias_final;
            // $alias_final = $initials_try;
            $used_alias_array[] = $alias_final;
            $entity_array[$key]['name'] = $key;
            $entity_array[$key]['table'] = $value[0];
            $entity_array[$key]['alias'] = $alias_final;
            $entity_array[$key]['primary'] = $value[1];

            $i++;
        }
        return $entity_array;
    }

    function upmfr_left_init($value) {return substr($value, 0, 1);}
