<?php

namespace GeminiLabs\SiteReviews\Commands;

use GeminiLabs\SiteReviews\Application;
use GeminiLabs\SiteReviews\Contracts\CommandContract as Contract;
use GeminiLabs\SiteReviews\Database\OptionManager;
use GeminiLabs\SiteReviews\Defaults\ValidationStringsDefaults;
use GeminiLabs\SiteReviews\Modules\Style;

class EnqueuePublicAssets implements Contract
{
    /**
     * @return void
     */
    public function handle()
    {
        $this->enqueueAssets();
        $this->enqueuePolyfillService();
        $this->enqueueRecaptchaScript();
        $this->inlineScript();
        $this->inlineStyles();
    }

    /**
     * @return void
     */
    public function enqueueAssets()
    {
        if (glsr()->filterBool('assets/css', true)) {
            wp_enqueue_style(
                Application::ID,
                $this->getStylesheet(),
                [],
                glsr()->version
            );
        }
        if (glsr()->filterBool('assets/js', true)) {
            $dependencies = glsr()->filterBool('assets/polyfill', true)
                ? [Application::ID.'/polyfill']
                : [];
            $dependencies = glsr()->filterArray('enqueue/public/dependencies', $dependencies);
            wp_enqueue_script(
                Application::ID,
                glsr()->url('assets/scripts/'.Application::ID.'.js'),
                $dependencies,
                glsr()->version,
                true
            );
        }
    }

    /**
     * @return void
     */
    public function enqueuePolyfillService()
    {
        if (!glsr()->filterBool('assets/polyfill', true)) {
            return;
        }
        wp_enqueue_script(Application::ID.'/polyfill', add_query_arg([
            'features' => 'Array.prototype.findIndex,CustomEvent,Element.prototype.closest,Element.prototype.dataset,Event,XMLHttpRequest,MutationObserver',
            'flags' => 'gated',
        ], 'https://polyfill.io/v3/polyfill.min.js'));
    }

    /**
     * @return void
     */
    public function enqueueRecaptchaScript()
    {
        // wpforms-recaptcha
        // google-recaptcha
        // nf-google-recaptcha
        if (!glsr(OptionManager::class)->isRecaptchaEnabled()) {
            return;
        }
        $language = glsr()->filterString('recaptcha/language', get_locale());
        wp_enqueue_script(Application::ID.'/google-recaptcha', add_query_arg([
            'hl' => $language,
            'render' => 'explicit',
        ], 'https://www.google.com/recaptcha/api.js'));
    }

    /**
     * @return void
     */
    public function inlineScript()
    {
        $variables = [
            'action' => Application::PREFIX.'action',
            'ajaxpagination' => $this->getFixedSelectorsForPagination(),
            'ajaxurl' => admin_url('admin-ajax.php'),
            'nameprefix' => Application::ID,
            'urlparameter' => glsr(OptionManager::class)->getBool('settings.reviews.pagination.url_parameter'),
            'validationconfig' => glsr(Style::class)->validation,
            'validationstrings' => glsr(ValidationStringsDefaults::class)->defaults(),
        ];
        $variables = glsr()->filterArray('enqueue/public/localize', $variables);
        wp_add_inline_script(Application::ID, $this->buildInlineScript($variables), 'before');
    }

    /**
     * @return void
     */
    public function inlineStyles()
    {
        $inlineStylesheetPath = glsr()->path('assets/styles/inline-styles.css');
        if (!glsr()->filterBool('assets/css', true)) {
            return;
        }
        if (!file_exists($inlineStylesheetPath)) {
            glsr_log()->error('Inline stylesheet is missing: '.$inlineStylesheetPath);
            return;
        }
        $inlineStylesheetValues = glsr()->config('inline-styles');
        $stylesheet = str_replace(
            array_keys($inlineStylesheetValues),
            array_values($inlineStylesheetValues),
            file_get_contents($inlineStylesheetPath)
        );
        wp_add_inline_style(Application::ID, $stylesheet);
    }

    /**
     * @return string
     */
    protected function buildInlineScript(array $variables)
    {
        $script = 'window.hasOwnProperty("GLSR")||(window.GLSR={});';
        foreach ($variables as $key => $value) {
            $script.= sprintf('GLSR.%s=%s;', $key, json_encode($value, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
        }
        $pattern = '/\"([^ \-\"]+)\"(:[{\[\"])/'; // removes unnecessary quotes surrounding object keys
        $optimizedScript = preg_replace($pattern, '$1$2', $script);
        return glsr()->filterString('enqueue/public/inline-script', $optimizedScript, $script, $variables);
    }

    /**
     * @return array
     */
    protected function getFixedSelectorsForPagination()
    {
        $selectors = ['#wpadminbar', '.site-navigation-fixed'];
        return glsr()->filterArray('enqueue/public/localize/ajax-pagination', $selectors);
    }

    /**
     * @return string
     */
    protected function getStylesheet()
    {
        $currentStyle = glsr(Style::class)->style;
        return file_exists(glsr()->path('assets/styles/custom/'.$currentStyle.'.css'))
            ? glsr()->url('assets/styles/custom/'.$currentStyle.'.css')
            : glsr()->url('assets/styles/'.Application::ID.'.css');
    }
}
