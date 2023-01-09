<?php

//Prevent direct access to this file
if ( ! defined('WPINC')) {
    die();
}

if (DAIMMA_PHP_VERSION > 50300) {

    require __DIR__ . '/vendor/autoload.php';

    //parsedown
    $daimma_parsedown       = new Parsedown();
    $daimma_parsedown_extra = new ParsedownExtra();

}

if (DAIMMA_PHP_VERSION > 50400) {

    //cebe markdown
    global $daimma_cebe_markdown;
    global $daimma_cebe_markdown_github_flavored;
    global $daimma_cebe_markdown_extra;
    $daimma_cebe_markdown                 = new \cebe\markdown\Markdown();
    $daimma_cebe_markdown_github_flavored = new \cebe\markdown\GithubMarkdown();
    $daimma_cebe_markdown_extra           = new \cebe\markdown\MarkdownExtra();

}