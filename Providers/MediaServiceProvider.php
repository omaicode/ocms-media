<?php
namespace Modules\Media\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Event;
use Illuminate\Routing\Events\RouteMatched;

use Modules\Media\Conversions\Commands\RegenerateCommand;
use Modules\Media\MediaCollections\Commands\CleanCommand;
use Modules\Media\MediaCollections\Commands\ClearCommand;
use Modules\Media\MediaCollections\Filesystem;
use Modules\Media\MediaCollections\MediaRepository;
use Modules\Media\MediaCollections\Models\Observers\MediaObserver;
use Modules\Media\ResponsiveImages\TinyPlaceholderGenerator\TinyPlaceholderGenerator;
use Modules\Media\ResponsiveImages\WidthCalculator\WidthCalculator;

use Menu;

class MediaServiceProvider extends ServiceProvider
{
    /**
     * @var string $moduleName
     */
    protected $moduleName = 'Media';

    /**
     * @var string $moduleNameLower
     */
    protected $moduleNameLower = 'media';

    /**
     * Boot the application events.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerTranslations();
        $this->registerConfig();
        $this->registerViews();
        $this->loadMigrationsFrom(module_path($this->moduleName, 'Database/Migrations'));
        $this->loadMenus();

        $mediaClass = config('media.media_model');
        $mediaClass::observe(new MediaObserver());        
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->app->register(RouteServiceProvider::class);
        $this->app->scoped(MediaRepository::class, function () {
            $mediaClass = config('media.media_model');

            return new MediaRepository(new $mediaClass());
        });

        $this->registerCommands();
    }

    /**
     * Register config.
     *
     * @return void
     */
    protected function registerConfig()
    {
        $this->publishes([
            module_path($this->moduleName, 'Config/config.php') => config_path($this->moduleNameLower . '.php'),
        ], 'config');
        $this->mergeConfigFrom(
            module_path($this->moduleName, 'Config/config.php'), $this->moduleNameLower
        );
    }

    /**
     * Register views.
     *
     * @return void
     */
    public function registerViews()
    {
        $viewPath = resource_path('views/modules/' . $this->moduleNameLower);

        $sourcePath = module_path($this->moduleName, 'Resources/views');

        $this->publishes([
            $sourcePath => $viewPath
        ], ['views', $this->moduleNameLower . '-module-views']);

        $this->loadViewsFrom(array_merge($this->getPublishableViewPaths(), [$sourcePath]), $this->moduleNameLower);
    }

    /**
     * Register translations.
     *
     * @return void
     */
    public function registerTranslations()
    {
        $langPath = resource_path('lang/modules/' . $this->moduleNameLower);

        if (is_dir($langPath)) {
            $this->loadTranslationsFrom($langPath, $this->moduleNameLower);
        } else {
            $this->loadTranslationsFrom(module_path($this->moduleName, 'Resources/lang'), $this->moduleNameLower);
        }
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return [];
    }

    private function getPublishableViewPaths(): array
    {
        $paths = [];
        foreach (\Config::get('view.paths') as $path) {
            if (is_dir($path . '/modules/' . $this->moduleNameLower)) {
                $paths[] = $path . '/modules/' . $this->moduleNameLower;
            }
        }
        return $paths;
    }

    public function loadMenus()
    {
        Event::listen(RouteMatched::class, function() {
            $menu_path = module_path($this->moduleName, 'Config/menu.php');

            if(file_exists($menu_path)) {
                $menus = include $menu_path;

                foreach($menus as $menu) {
                    Menu::add($menu);
                }
            }
        });        
    }    

    protected function registerCommands(): void
    {
        $this->app->bind(Filesystem::class, Filesystem::class);
        $this->app->bind(WidthCalculator::class, config('media.responsive_images.width_calculator'));
        $this->app->bind(TinyPlaceholderGenerator::class, config('media.responsive_images.tiny_placeholder_generator'));

        $this->app->bind('command.media:regenerate', RegenerateCommand::class);
        $this->app->bind('command.media:clear', ClearCommand::class);
        $this->app->bind('command.media:clean', CleanCommand::class);

        $this->commands([
            'command.media:regenerate',
            'command.media:clear',
            'command.media:clean',
        ]);
    }    
}