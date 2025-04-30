<?php
class PagesController extends Controller {

    public function __construct(){
        // Models can be loaded here if needed for all methods
        // Example: $this->pageModel = $this->model('Page');
    }

    // Default method called by router if no specific method in URL
    public function index(){
        // Data to pass to the view
        $data = [
            'title' => 'Welcome',
            'description' => 'Barangay Business Registration System - Home'
        ];

        // Load the view (e.g., pages/index.php) using the 'app' layout
        // For now, let's use the 'auth' layout as we haven't built 'app' yet
        // Or create a very simple index view without a layout first.
        // Let's try loading a simple view directly for initial test.

         $viewPath = APPROOT . '/views/pages/index.php';
         if (file_exists($viewPath)) {
             extract($data); // Make $title, $description available
             require_once $viewPath;
         } else {
             echo "<h1>Welcome</h1><p>Home page view file not found.</p>";
         }

         // Once layouts are ready, use:
         // $this->view('pages/index', $data, 'app'); // Or 'auth' if it's a public landing page
    }

    // Example of another page
    public function about(){
         $data = [
            'title' => 'About Us',
            'description' => 'Information about the system.'
        ];
         // $this->view('pages/about', $data, 'app'); // Use appropriate layout
         echo "<h1>About Us</h1><p>This is the about page.</p>"; // Simple output for now
    }
}
?>