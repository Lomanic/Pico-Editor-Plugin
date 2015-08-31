<?php

/**
 * Editor plugin for Pico
 *
 * @author Gilbert Pellegrom
 * @link http://pico.dev7studios.com
 * @license http://opensource.org/licenses/MIT
 * @version 1.1
 */
class Pico_Editor {

    private $is_admin;
    private $is_logout;
    private $plugin_path;
    private $password;
    private $url;

    public function __construct()
    {
        $this->is_admin = false;
        $this->is_logout = false;
        $this->plugin_path = dirname(__FILE__);
        $this->password = '';
        $this->url = 'admin';
        session_start();

        if(file_exists($this->plugin_path .'/pico_editor_config.php')){
            global $pico_editor_password;
            global $pico_editor_url;
            include_once($this->plugin_path .'/pico_editor_config.php');
            $this->password = $pico_editor_password;
            $this->url = $pico_editor_url;
        }
    }

    public function request_url(&$url)
    {
        // Are we looking for /admin?
        if($url == $this->url) $this->is_admin = true;
        if($url == $this->url.'/new') $this->do_new();
        if($url == $this->url.'/open') $this->do_open();
        if($url == $this->url.'/save') $this->do_save();
        if($url == $this->url.'/delete') $this->do_delete();
        if($url == $this->url.'/logout') $this->is_logout = true;
    }

    public function before_render(&$twig_vars, &$twig)
    {
        if($this->is_logout){
            session_destroy();
            header('Location: '. $twig_vars['base_url'] .'/'.$this->url);
            exit;
        }

        if($this->is_admin){
            header($_SERVER['SERVER_PROTOCOL'].' 200 OK'); // Override 404 header
            $loader = new Twig_Loader_Filesystem($this->plugin_path);
            $twig_editor = new Twig_Environment($loader, $twig_vars);
            if(!$this->password){
                $twig_vars['login_error'] = 'No password set for the Pico Editor.';
                echo $twig_editor->render('login.html', $twig_vars); // Render login.html
                exit;
            }

            if(!isset($_SESSION['pico_logged_in']) || !$_SESSION['pico_logged_in']){
                if(isset($_POST['password'])){
                    if(hash('sha512',$_POST['password']) == $this->password){
                        $_SESSION['pico_logged_in'] = true;
                        $_SESSION['pico_config'] = $twig_vars['config'];
                    } else {
                        $twig_vars['login_error'] = 'Invalid password.';
                        echo $twig_editor->render('login.html', $twig_vars); // Render login.html
                        exit;
                    }
                } else {
                    echo $twig_editor->render('login.html', $twig_vars); // Render login.html
                    exit;
                }
            }
            
            $twig_vars['editor_url'] = $this->url;
            echo $twig_editor->render('editor.html', $twig_vars); // Render editor.html
            exit; // Don't continue to render template
        }
    }

    /**
     * Returns real file name to be edited.
     *
     * @param string $file_url the file URL to be edited
     * @return string
     */
    private static function get_real_filename($file_url)
    {

        $file_components = parse_url($file_url); // inner
        $base_components = parse_url($_SESSION['pico_config']['base_url']);
        $file_path = rtrim($file_components['path'], '/');
        $base_path = rtrim($base_components['path'], '/');

        if(empty($file_path) || $file_path === $base_path)
        {
            return 'index';
        }
        else
        {
            $file_path = strip_tags(substr($file_path, strlen($base_path)));
            if(is_dir(CONTENT_DIR . $file_path))
                $file_path .= "/index";

            return $file_path;
        }
    }


    private function do_new()
    {
        if(!isset($_SESSION['pico_logged_in']) || !$_SESSION['pico_logged_in']) die(json_encode(array('error' => 'Error: Unauthorized')));
        $title = isset($_POST['title']) && $_POST['title'] ? strip_tags($_POST['title']) : '';
        $dir = isset($_POST['dir']) && $_POST['dir'] ? strip_tags($_POST['dir']) : '';

        $contentDir = CONTENT_DIR . $dir;
        if($contentDir[strlen($contentDir)-1] != '/') $contentDir .= '/';

        if(!is_dir($contentDir)) {
            if (!mkdir($contentDir, 0777, true)) {
                die(json_encode(array('error' => 'Can\'t create directory...')));
            }
        }

        $file = $this->slugify(basename($title));
        if(!$file) die(json_encode(array('error' => 'Error: Invalid file name')));

        $error = '';
        $file .= CONTENT_EXT;
        $content = '/*
Title: '. $title .'
Author:
Date: '. date('Y/m/d') .'
*/';
        if(file_exists($contentDir . $file))
        {
            $error = 'Error: A post already exists with this title';
        }
        else
        {
            if(strlen($content) !== file_put_contents($contentDir . $file, $content))
                $error = 'Error: can not create the post ... ';
            chmod($contentDir . $file, 0777);
        }

        $file_url = $dir .'/'. str_replace(CONTENT_EXT, '', $file);

        die(json_encode(array(
            'title' => $title,
            'content' => $content,
            'file' => $file_url,
            'error' => $error
        )));
    }

    private function do_open()
    {
        if(!isset($_SESSION['pico_logged_in']) || !$_SESSION['pico_logged_in']) die(json_encode(array('error' => 'Error: Unauthorized')));
        $file_url = isset($_POST['file']) && $_POST['file'] ? $_POST['file'] : '';
        $file = self::get_real_filename($file_url);
        if(!$file) die('Error: Invalid file');

        $file .= CONTENT_EXT;
        if(file_exists(CONTENT_DIR . $file)) die(file_get_contents(CONTENT_DIR . $file));
        else die('Error: Invalid file');
    }

    private function do_save()
    {
        if(!isset($_SESSION['pico_logged_in']) || !$_SESSION['pico_logged_in']) die(json_encode(array('error' => 'Error: Unauthorized')));
        $file_url = isset($_POST['file']) && $_POST['file'] ? $_POST['file'] : '';
        $file = self::get_real_filename($file_url);
        if(!$file) die('Error: Invalid file');
        $content = isset($_POST['content']) && $_POST['content'] ? $_POST['content'] : '';
        if(!$content) die('Error: Invalid content');

        $file .= CONTENT_EXT;
        $error = '';
        if(strlen($content) !== file_put_contents(CONTENT_DIR . $file, $content))
            $error = 'Error: can not save changes ... ';

        die(json_encode(array(
            'content' => $content,
            'file' => $file_url,
            'error' => $error
        )));
    }

    private function do_delete()
    {
        if(!isset($_SESSION['pico_logged_in']) || !$_SESSION['pico_logged_in']) die(json_encode(array('error' => 'Error: Unauthorized')));
        $file_url = isset($_POST['file']) && $_POST['file'] ? $_POST['file'] : '';
        $file = self::get_real_filename($file_url);
        if(!$file) die('Error: Invalid file');

        $file .= CONTENT_EXT;
        if(file_exists(CONTENT_DIR . $file)) die(unlink(CONTENT_DIR . $file));
    }

    private function slugify($text)
    {
        // replace non letter or digits by -
        $text = preg_replace('~[^\\pL\d]+~u', '-', $text);
        // trim
        $text = trim($text, '-');
        // transliterate
        $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);
        // lowercase
        $text = strtolower($text);
        // remove unwanted characters
        $text = preg_replace('~[^-\w]+~', '', $text);

        if (empty($text))
            {
                return 'n-a';
            }
        return $text;
    }
}
?>
