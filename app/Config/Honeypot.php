<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

class Honeypot extends BaseConfig
{
    /**
     * Makes Honeypot visible or not to human
     */
    public bool $hidden = true;

    /**
     * Honeypot Label Content
     */
    public string $label = '';

    /**
     * Honeypot Field Name
     */
    public string $name = 'honeypot';

    /**
     * Honeypot HTML Template
     *
     * No label: the framework default ("Fill This Field") sits in the page source
     * of every form and reads as leftover template text to crawlers/reviewers.
     */
    public string $template = '<input type="text" name="{name}" value="" tabindex="-1" autocomplete="off" aria-hidden="true">';

    /**
     * Honeypot container
     *
     * If you enabled CSP, you can remove `style="display:none"`.
     */
    public string $container = '<div style="display:none">{template}</div>';

    /**
     * The id attribute for Honeypot container tag
     *
     * Used when CSP is enabled.
     */
    public string $containerId = 'hpc';
}
