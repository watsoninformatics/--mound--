<?php
    set_include_path( get_include_path().PATH_SEPARATOR.'./lib');
    require_once('smarty/libs/Smarty.class.php');
    if(!defined('REQUIRED_SMARTY_DIR')) define('REQUIRED_SMARTY_DIR','./');

    class CustomSmarty extends Smarty{

        function __construct(){
            $this->Smarty();

            /*
            You can remove this comment, if you prefer this JSP tag style
            instead of the default { and }
            $this->left_delimiter =  '<%';
            $this->right_delimiter = '%>';
            */

            $this->template_dir = REQUIRED_SMARTY_DIR.'templates';
            $this->compile_dir   = REQUIRED_SMARTY_DIR.'templates_c';
            $this->config_dir    = REQUIRED_SMARTY_DIR.'config';
            $this->cache_dir     = REQUIRED_SMARTY_DIR.'cache';
        }
    }
?>