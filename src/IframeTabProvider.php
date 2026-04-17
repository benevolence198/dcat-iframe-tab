<?php

namespace Benevolences\DcatIframeTab;

use Dcat\Admin\Admin;
use Dcat\Admin\Grid;
use Dcat\Admin\Layout\Content;
use Illuminate\Support\ServiceProvider;

class IframeTabProvider extends ServiceProvider
{
    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        // 必须在 boot 中注册：若本包在 AdminServiceProvider 之前 register，register 阶段
        // 尚未执行 Dcat 的 admin.context 等绑定，会触发 BindingResolutionException。
        if (config('iframe_tab.enable')) {
            $this->app->resolving(Content::class, function ($content, $app) {
                $content->view('iframe-tab::full-content');
                if (strpos(request()->getUri(), 'auth/login') !== false) {
                    session()->forget('url.intended');
                    Admin::script(<<<JS
                    if (window != top)
                        top.location.href = location.href; 
JS
                    );
                }
            });
            Content::resolving(function (Content $content) {
                $content->view('iframe-tab::full-content');
                if (strpos(request()->getUri(), 'auth/login') !== false) {
                    Admin::script(<<<JS
                    if (window != top)
                        top.location.href = location.href; 
JS
                    );
                }
            });
            Grid::resolving(function (Grid $grid) {
                $grid->setDialogFormDimensions(config('iframe_tab.dialog_area_width'), config('iframe_tab.dialog_area_height'));
            });
        }

        $this->loadViewsFrom(__DIR__ . '/resource/views', 'iframe-tab');
        $this->loadRoutesFrom(__DIR__ . '/routes.php');
        $this->publishes([
            __DIR__ . '/assets/js/compress' => public_path('vendor/iframe-tab/js'),
            __DIR__ . '/assets/css' => public_path('vendor/iframe-tab/css'),
        ], 'iframe-tab');
        $this->publishes([
            __DIR__ . '/resource/views' => resource_path('views/vendor/iframe-tab'),
        ], 'iframe-tab.view');
        $this->publishes([
            __DIR__ . '/iframe_tab.php' => config_path('iframe_tab.php'),
        ], 'iframe-tab.config');
    }
}
