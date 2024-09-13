<?php

namespace App\Providers;

use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class MenuServiceProvider extends ServiceProvider
{
  /**
   * Register services.
   */
  public function register(): void
  {
    //
  }

  /**
   * Bootstrap services.
   */
  public function boot(): void
  {
    $verticalMenuJson = file_get_contents(base_path('resources/menu/verticalMenu.json'));
    $verticalMenuData = json_decode($verticalMenuJson);
    $horizontalMenuJson = file_get_contents(base_path('resources/menu/horizontalMenu.json'));
    $horizontalMenuData = json_decode($horizontalMenuJson);
	$verticalUserMenuJson = file_get_contents(base_path('resources/menu/verticalUserMenu.json'));
    $verticalUserMenuData = json_decode($verticalUserMenuJson);   

    // Share all menuData to all the views
    \View::share('menuData', [$verticalMenuData, $horizontalMenuData, $verticalUserMenuData]);
  }
}
