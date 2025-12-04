<?php

namespace App\Http\Controllers\Install;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class InstallController extends Controller
{
    public function index(Request $request)
    {
        // Merge POST/GET data into $_REQUEST for installer.php to use
        if ($request->isMethod('post')) {
            $_POST = array_merge($_POST, $request->all());
            $_REQUEST = array_merge($_REQUEST, $request->all());
        }
        
        // Include the standalone installer which handles its own view/output
        ob_start();
        include base_path('installer/index.php');
        $content = ob_get_clean();
        return response($content)->header('Content-Type', 'text/html');
    }

    public function checkEnvironment(Request $request) { return $this->index($request); }
    public function setupDatabase(Request $request) { return $this->index($request); }
    public function createAdmin(Request $request) { return $this->index($request); }
    public function setupSettings(Request $request) { return $this->index($request); }
    public function complete(Request $request) { return $this->index($request); }
}
