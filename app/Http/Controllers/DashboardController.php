<?php
// app/Http/Controllers/DashboardController.php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Controller for handling the dashboard view.
 * Requires authentication.
 */
class DashboardController extends Controller
{
    /**
     * Constructor.
     * Applies the 'auth' middleware to ensure only authenticated users can access.
     */
    public function __construct()
    {
        // Apply the 'auth' middleware to all methods in this controller
        $this->middleware('auth');
    }

    /**
     * Display the dashboard view.
     *
     * @param Request $request The incoming request.
     * @return View
     */
    public function index(Request $request): View
    {
        // You can fetch data here to display on the dashboard
        // For example: count of products, categories, users (based on user's role/permissions)
        // $productCount = \App\Models\Product::count();
        // $categoryCount = \App\Models\Category::count();
        // $userCount = \App\Models\User::count(); // Only if user has permission

        // Return the dashboard view
        return view('dashboard'); // Pass data to the view if fetched: ->with(compact('productCount', 'categoryCount'))
    }
}
