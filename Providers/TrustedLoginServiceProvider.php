<?php

namespace Modules\TrustedLogin\Providers;

use App\Mailbox;
use App\Misc\Helper;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;
use Illuminate\Support\ServiceProvider;
use Illuminate\Database\Eloquent\Factory;

define('TRUSTEDLOGIN_MODULE', 'trustedlogin');

class TrustedLoginServiceProvider extends ServiceProvider
{
    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = false;

    /**
     * Boot the application events.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerConfig();
        $this->registerViews();
        $this->registerFactories();
        $this->loadMigrationsFrom(__DIR__ . '/../Database/Migrations');
        $this->hooks();
    }

    /**
     * Module hooks.
     */
    public function hooks()
    {
        // Add module's css file to the application layout
        \Eventy::addFilter('stylesheets', function ($styles) {
            $styles[] = \Module::getPublicPath(TRUSTEDLOGIN_MODULE).'/css/module.css';
            return $styles;
        });

        // Add item to settings sections.
        \Eventy::addFilter('settings.sections', function($sections) {
            $sections[TRUSTEDLOGIN_MODULE] = ['title' => __('TrustedLogin'), 'icon' => 'lock', 'order' => 900];

            return $sections;
        }, 20);

        // Section settings
        \Eventy::addFilter('settings.section_settings', function($settings, $section) {
            if ($section == TRUSTEDLOGIN_MODULE) {
                $settings['trustedlogin.callbackUrl'] = \Option::get('trustedlogin.callbackUrl');
                $settings['trustedlogin.secretKey'] = \Option::get('trustedlogin.secretKey');
                $settings['trustedlogin.debugMode'] = \Option::get('trustedlogin.debugMode');
            }
            return $settings;
        }, 20, 2);

        // Settings view name
        \Eventy::addFilter('settings.view', function($view, $section) {
            if ($section == TRUSTEDLOGIN_MODULE) {
                $view = 'trustedlogin::settings';
            }
            return $view;
        }, 20, 2);

        // Mailbox Settings
        \Eventy::addAction('mailboxes.settings.menu', function($mailbox){
            echo view('trustedlogin::partials.mailboxsettings', ['mailbox' => $mailbox])->render();
        }, 20);

        \Eventy::addAction('conversation.after_prev_convs', function($customer, $conversation, $mailbox) {

            if ( ! $mailbox->getMeta('trustedlogin_enabled', false) ) {
                return;
            }

            $callbackUrl = \Option::get('trustedlogin.callbackUrl');
            $secretKey = \Option::get('trustedlogin.secretKey');
            $debugMode = \Option::get('trustedlogin.debugMode');

            // Parse the URL and return its components
            $parts = parse_url( $callbackUrl );

            // Parse the query string into an array
            parse_str( $parts['query'], $query );

            // Get the ak_account_id value from the array
            $ak_account_id = $query['ak_account_id'] ?? '';

            $payload['customer'] = [
                'emails' => $customer->emails_cached()->pluck('email')->toArray(),
                'account_id' => $ak_account_id
            ];

            $signature = base64_encode(hash_hmac('sha1', json_encode($payload), $secretKey, true));

            $client = new Client();

            $request = new Request('POST', $callbackUrl, [
                'Content-Type' => 'application/json',
                'X-FreeScout-Signature' => $signature
            ], json_encode($payload));

            try {
                $response = $client->send($request);
            } catch (\Exception $e) {
                $response = $e->getResponse();
            }

            $decodedResponse = json_decode( $response->getBody()->getContents(), true );

            $responseHtml = $decodedResponse['html'] ?? '';
            $html = '<h2 class="c-sb-heading">TrustedLogin</h2>';

            // Check if the response came back 200ish and if not put the error message in the html
            if ( $response->getStatusCode() >= 200 && $response->getStatusCode() < 300 ) {
                if ( $debugMode ) {
                    $html .= $this->debugMode($request, $response);
                }
                $html .= $responseHtml;
            } else {
                if ( $debugMode ) {
                    $html .= $this->debugMode($request, $response);
                }
                $html .= '<div class="alert alert-danger" role="alert">';
                $html .= '<h4 class="alert-heading">TrustedLogin</h4>';
                $html .= '<p>Response: ' . $response->getStatusCode() . ' ' . $response->getReasonPhrase() . '</p>';
                $html .= '<hr>';
                $html .= '<p class="mb-0">' . $responseHtml . '</p>';
                $html .= '</div>';
            }

            echo \View::make(TRUSTEDLOGIN_MODULE . '::partials/sidebar', [
                'html' => $html
            ])->render();
        }, -1, 3);
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->registerTranslations();
    }

    /**
     * Register config.
     *
     * @return void
     */
    protected function registerConfig()
    {
        $this->publishes([
            __DIR__.'/../Config/config.php' => config_path('trustedlogin.php'),
        ], 'config');
        $this->mergeConfigFrom(
            __DIR__.'/../Config/config.php', 'trustedlogin'
        );
    }

    /**
     * Register views.
     *
     * @return void
     */
    public function registerViews()
    {
        $viewPath = resource_path('views/modules/trustedlogin');

        $sourcePath = __DIR__.'/../Resources/views';

        $this->publishes([
            $sourcePath => $viewPath
        ],'views');

        $this->loadViewsFrom(array_merge(array_map(function ($path) {
            return $path . '/modules/trustedlogin';
        }, \Config::get('view.paths')), [$sourcePath]), 'trustedlogin');
    }

    /**
     * Register translations.
     *
     * @return void
     */
    public function registerTranslations()
    {
        $this->loadJsonTranslationsFrom(__DIR__ .'/../Resources/lang');
    }

    /**
     * Register an additional directory of factories.
     * @source https://github.com/sebastiaanluca/laravel-resource-flow/blob/develop/src/Modules/ModuleServiceProvider.php#L66
     */
    public function registerFactories()
    {
        if (! app()->environment('production')) {
            app(Factory::class)->load(__DIR__ . '/../Database/factories');
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

    /**
     * Output the debug modal.
     *
     * @param $request
     * @param $response
     *
     * @return string
     */
    private function debugMode( $request, $response ): string
    {
        $goodResponse = $response->getStatusCode() >= 200 && $response->getStatusCode() < 300;

        $color = $goodResponse ? 'success' : 'danger';

        $requestHeader = print_r($request->getHeaders(), true);
        $requestBody = $request->getBody();
        $responseHeader = print_r($response->getHeaders(), true);
        $responseBody = e($response->getBody());
        Helper::log('TrustedLogin', 'debugMode', [
            'Request Header' => $requestHeader,
            'Request Body' => print_r($requestBody, true),
            'Response Header' => $responseHeader,
            'Response Body' => print_r($responseBody, true),
        ]);

        $debugId = 'tl-debugModal';

        $html = '<a class="debugger-link" href="#' . $debugId . '">View Debugger</a>';

        $html .= '<div id="' . $debugId . '" class="modal">';
        $html .= '<div class="alert alert-'. $color .'" role="alert">';
        $html .= '<a href="#" class="close">&times;</a>';
        $html .= '<h4 class="alert-heading">TrustedLogin</h4>';
        $html .= '<p>Response: ' . $response->getStatusCode() . ' ' . $response->getReasonPhrase() . '</p>';
        $html .= '<hr>';

        // Adding request headers
        $html .= '<h5>Request Headers:</h5>';
        $html .= '<pre>' . $requestHeader . '</pre>';

        // Adding request body
        $html .= '<h5>Request Body:</h5>';
        $html .= '<pre>' . $requestBody . '</pre>';

        // Adding response headers
        $html .= '<h5>Response Headers:</h5>';
        $html .= '<pre>' . $responseHeader . '</pre>';

        // Adding response body
        $html .= '<h5>Response Body:</h5>';
        $html .= '<pre>' . $responseBody . '</pre>';

        $html .= '</div>';
        $html .= '</div>';

        return $html;
    }
}
