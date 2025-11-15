<?php

class View {
    public static function render($view, $data = []) {
        extract($data);

        $viewFile = RESOURCES_PATH . '/views/' . str_replace('.', '/', $view) . '.php';

        if (file_exists($viewFile)) {
            ob_start();
            include $viewFile;
            $content = ob_get_clean();
            echo $content;
        } else {
            die("View not found: {$view}");
        }
    }

    public static function make($view, $data = []) {
        extract($data);

        $viewFile = RESOURCES_PATH . '/views/' . str_replace('.', '/', $view) . '.php';

        if (file_exists($viewFile)) {
            ob_start();
            include $viewFile;
            return ob_get_clean();
        }

        return '';
    }
}
