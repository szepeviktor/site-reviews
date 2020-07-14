<?php

namespace GeminiLabs\SiteReviews\Modules;

use GeminiLabs\SiteReviews\Modules\Html\Builder;
use GeminiLabs\SiteReviews\Modules\Html\Field;

class Honeypot
{
    /**
     * @param string $formId
     * @return string
     */
    public function build($formId)
    {
        $honeypot = new Field([
            'class' => 'glsr-field-control',
            'label' => esc_html__('Your review', 'site-reviews'),
            'name' => $this->hash($formId),
            'required' => true,
            'type' => 'text',
        ]);
        $honeypot->id = $honeypot->id.'-'.$formId;
        return glsr(Builder::class)->div([
            'class' => 'glsr-field glsr-required',
            'style' => 'display:none;',
            'text' => $honeypot->getFieldLabel().$honeypot->getField(),
        ]);
    }

    /**
     * @param string $formId
     * @return string
     */
    public function hash($formId)
    {
        return substr(wp_hash($formId, 'nonce'), -12, 8);
    }

    /**
     * @param string $hash
     * @param string $formId
     * @return bool
     */
    public function verify($hash, $formId)
    {
        return hash_equals($this->hash($formId), $hash);
    }
}