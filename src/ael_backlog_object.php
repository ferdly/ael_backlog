<?php

class ael_backlog_object
{
    var $command;
    var $bundle;
    var $entity;
    var $limit;
    var $feedback;
    var $ael_config = array();
    var $nid_array = array();
    var $mask;
    var $mask_sql_smarty = array();
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
            $this->output_message_type = __METHOD__ . ': ' . basename(__FILE__) . ' - line '. __LINE__;
        }
        return;//no change to $command
    }

    public function unpack_bundle()
    {
        //skeleton
        //no validation
        $this->entity = 'node';
        $this->mask = 'drupal_get_variable';
    }

    public function unpack_mask ()
    {
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
        return;
    }



}