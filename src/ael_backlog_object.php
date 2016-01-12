<?php

class ael_backlog_object
{
    var $command;
    var $bundle;
    var $entity;
    var $limit;
    var $feedback;
    var $entity_array = array();
    var $bundle_array = array();
    var $entity_id_array = array();
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
    var $crlf = "zCRLFz";
    var $tab = "zTABz";
    var $space = "zSPACEz";
    var $temp_ouput;

    public function  __construct($command = 'compose', $bundle, $additional_option_array = array())
    {
        $this->command = $command;
        $this->bundle = $bundle;
        // $this->entity = $entity;
        // $this->limit = $limit;
        // $this->feedback = $feedback;
    }

    public function unpack()
    {

        $this->unpack_command();
        if (1 == 2 && $this->output_message_type != 'success') {
            return;
        }
        $this->unpack_all_entities_method();
        if (1 == 2 && $this->output_message_type != 'success') {
            return;
        }
        $this->unpack_bundle();
        if (1 == 2 && $this->output_message_type != 'success') {
            return;
        }
        $this->unpack_ael_config();
        if (1 == 2 && $this->output_message_type != 'success') {
            return;
        }
        $this->unpack_mask_pattern();
        if (1 == 2 && $this->output_message_type != 'success') {
            return;
        }
        $this->unpack_mask_php();
        if (1 == 2 && $this->output_message_type != 'success') {
            return;
        }
        $this->unpack_mask_config();
        if (1 == 2 && $this->output_message_type != 'success') {
            return;
        }
        $this->unpack_mask_fields();
        if (1 == 2 && $this->output_message_type != 'success') {
            return;
        }
        $this->unpack_update_sql_smarty();
        if (1 == 2 && $this->output_message_type != 'success') {
            return;
        }
        $this->unpack_entity_id_array();
        if (1 == 2 && $this->output_message_type != 'success') {
            return;
        }
        $this->unpack_update_sql_rendered();
        if (1 == 2 && $this->output_message_type != 'success') {
            return;
        }
        $this->gather_output();
        return;
    }

    public function unpack_command()
    {
        $command = $this->command;
        $this->bundle = $command;
        $command = 'compose';
        $this->command = $command;
        /**
         * @circleback get command dynamically
         *
         * @var        array
         */
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
        $entity_bundle_array = field_info_bundles();
        $i = 1;
        foreach ($entity_bundle_array as $entity => $bundle_array) {
            foreach ($bundle_array as $bundle => $value_array) {
                // $this->temp_ouput[$entity][$bundle] = $i;
                $result[$bundle] = $entity;
                $i++;
            }
        }
        $this->entity = $result[$this->bundle];
        $this->bundle_array = $result;
        $supported = array_keys($result);
        if (!in_array($this->bundle, $supported)) {
            $this->output_message = "\"{$this->bundle}\" is NOT a supported bundle.";
            $this->output_message_type = __FUNCTION__ . ': ' . basename(__FILE__) . ' - line '. __LINE__;
        }
        return;//no change to $command

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
        $crlf = $this->crlf;
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
        $crlf = $this->crlf;
        $tab = $this->tab;
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
        $concat_string = 'CONCAT( '. $crlf . $tab;
        $comma_string = '';
        foreach ($this->mask as $index => $chunk) {
            $concat_string .= $comma_string;
           if (!empty($this->mask_field_array[$index]['sql'])) {
                $concat_string .= $this->mask_field_array[$index]['sql'];
            }else{
                $concat_string .= $chunk;
            }
            $comma_string = ', ' . $crlf . $tab;
        }
        $concat_string .= $crlf . $tab . ')' . $crlf . $tab;
        $mask_sql_smarty = 'SELECT ' . $crlf . $tab . $concat_string . $space . $crlf . 'FROM ' . $mask_base_table . ' ' . $mask_base_alias . $space . $crlf . 'WHERE ' . $mask_base_alias . '.' . $mask_base_primary . ' = ' . $mask_base_smarty;


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
        $crlf = $this->crlf;
        $tab = $this->tab;
        $space = $this->space;
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
        $target_entity_id_sql = "{$crlf}{$tab}SELECT {$target_alias}.{$target_field_name}{$crlf}{$tab}FROM {$target_table_name} {$target_alias}{$crlf}{$tab}WHERE {$target_alias}.entity_id = {$base_smarty}{$crlf}{$tab}";
        $outer_field_sql = "{$crlf}{$tab}SELECT {$outer_alias}.{$outer_field_name}{$crlf}{$tab}FROM {$outer_table_name} {$outer_alias} {$crlf}{$tab}WHERE {$outer_alias}.{$outer_primary} = ({inner_sql})";
        $target_entity_id_sql = $this->utility_ztring_replace($target_entity_id_sql, 3);
        $field_sql = str_replace('{inner_sql}', $target_entity_id_sql, $outer_field_sql);
        $field_sql = $this->utility_ztring_replace($field_sql, 2);
        $field_sql = '(' . $field_sql . $crlf . $tab . ')';
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
        $crlf = $this->crlf;
        $tab = $this->tab;
        $space = $this->space;
        $ael_this = 'SET @ael_this = (' . $this->mask_sql_smarty . ');';
        $entity = $this->entity_array[$this->entity];
        $table = $entity['table'];
        $primary = $entity['primary'];
        $primary_smarty = '{' . $entity['alias'] . '.' . $primary . '}';
        $alias = $entity['alias'] . $entity['alias'];
        $ael_this = $this->utility_ztring_replace($ael_this);
        switch ($this->command) {
            case 'compose':
                $update_sql_smarty = 'UPDATE ' . $table . ' ' . $alias . ' SET ' . $alias . '.title = (' . '@ael_this' . ') WHERE ' . $alias . '.' . $primary . ' = ' . $primary_smarty . ';';
                $update_sql_smarty = $ael_this . $space . $crlf . $update_sql_smarty;
                $update_sql_smarty = $this->utility_ztring_replace($update_sql_smarty);
                break;
            case 'preview':
                $update_sql_smarty = 'SELECT ' . '@ael_this' . ';';
                $update_sql_smarty = $ael_this . $space . $crlf . $update_sql_smarty;
                $update_sql_smarty = $this->utility_ztring_replace($update_sql_smarty);
                break;

            default:
                #\_ default is 'mask' since upack_command already validated
                $update_sql_smarty = $ael_this;
                break;
        }
        $this->update_sql_smarty = $update_sql_smarty;
    }
    public function utility_ztring_replace($string, $tab_level = 1) {
        /**
         * Method instead of Function so that $attributes can be consistent
         * Could make replacement string $attributes, but makes sense to make them local here
         */
        $crlf_z = $this->crlf;
        $tab_z = $this->tab;
        $space_z = $this->space;
        $crlf = "\r\n";
        $tab_level = $tab_level + 0;
        $i = 0;
        $tab = '';
        while ($i < $tab_level) {
            $tab .= "    ";
            $i++;
        }
        $space = " ";
        $string = str_replace($crlf_z, $crlf, $string);
        $string = str_replace($tab_z, $tab, $string);
        $string = str_replace($space_z, $space, $string);
        return $string;
    }
    public function unpack_entity_id_array() {
        if ($this->command == 'mask') {
            return;
        }
        $entity_id_array = array(68,70);
        /**
         * @todo evaluate limit, list, rand, (other options)
         */
        $this->entity_id_array = $entity_id_array;
    }
    public function unpack_update_sql_rendered() {
        #\_ well, render
        if ($this->command == 'mask') {
            $this->update_sql_rendered = $this->update_sql_smarty;
            return;
        }
        $entity = $this->entity_array[$this->entity];
        $smarty_search = '{' . $entity['alias'] . '.' . $entity['primary'] . '}';
        $update_sql_smarty = $this->update_sql_smarty;
        $update_sql_rendered = '';
        foreach ($this->entity_id_array as $index => $entity_id) {
            $singleton = str_replace($smarty_search, $entity_id, $update_sql_smarty) . "\r\n";
            $update_sql_rendered .= $singleton;
        }
        $this->update_sql_rendered = $update_sql_rendered;
        return;
    }
    public function gather_output ()
    {
        $crlf = "\r\n"; //$this->crlf; // use ztring_replace() method of this gets too hairy
        $tab = "    "; //$this->tab;
        $space = " "; //$this->space;
        $attribute_array = array();
        $leading_output_string = "=====================================";
        $trailing_output_string = "\r\n=====================================";
        #\_ overload either above below
        switch ($this->command) {
            case 'compose':
                $leading_output_string = '/*======= SQL Code Block Start ========*/';
                $trailing_output_string = $crlf . '/*======== SQL Code Block End =========*/';
                $attribute_array[] = 'update_sql_rendered';
                $this->output_message = 'Okay, I will compose the SQL';
                break;
            case 'preview':
                $attribute_array[] = 'update_sql_rendered';
                $this->output_message = 'Okay, I will compose a preview of the SQL and some results';
                break;
            case 'mask':
                $attribute_array[] = 'update_sql_rendered';
                $this->output_message = 'Okay, I will compose the mask SQL and generate and example';
                break;

            default:
                $this->output_message = 'The "' . $command . '" command is not supported, something went very wrong. Please asks for assistance.';
                $this->output_message_type = 'OOAOC should be caught before output.';
                break;
        }
        $attribute_title_array = array();
        $att_count = count($attribute_array);
        if ($att_count > 0) {
            $this->output_string = '';
            $this->output_string .= $leading_output_string;
            $pre_block = $att_count > 0?"\r\n=====\r\n":'';
            $attribute_title = empty($attribute_title_array[$attribute])?$attribute:$attribute_title_array[$attribute];
            foreach ($attribute_array as $index => $attribute) {
                // $this->output_string .= "\r\n=====\r\n" . $attribute . ":";//Maybe Not
                $this->output_string .= "\r\n" . print_r($this->$attribute, TRUE);
            } //END foreach()
            $this->output_string .= $trailing_output_string;
            return;
        }
        $entity_bundle_array = field_info_bundles();
        $i = 1;
        foreach ($entity_bundle_array as $entity => $bundle_array) {
            foreach ($bundle_array as $bundle => $value_array) {
                // $this->temp_ouput[$entity][$bundle] = $i;
                $this->temp_ouput[] = $bundle;
                $i++;
            }
        }
        // $this->temp_ouput = array_keys(field_info_bundles());
        $this->output_string = "=====================================";
        $attribute_array = array(
         'command',
        'bundle',
        'entity',
        // 'limit',
        // 'feedback',
        'entity_id_array',
        'entity_array',
        'bundle_array',
        // 'ael_config',
        // 'ael_config_pattern',
        // 'ael_config_php',
        // 'nid_array',
        // 'mask',
        // 'mask_config_array',
        // 'mask_field_array',
        // 'mask_join_array',
        // 'mask_sql_smarty',
        // 'mask_rendered',
        // 'update_sql_smarty',
        // 'update_sql_rendered',
        // 'output_string',
        'output_message',
        'output_message_type',
        // 'stack',
        // 'stack_type',
        // 'temp_ouput',
        );
        if (count($attribute_array) > 0) {
            foreach ($attribute_array as $index => $attribute) {
                $this->output_string .= "\r\n=====\r\n" . $attribute . ":\r\n" . print_r($this->$attribute, TRUE);
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
