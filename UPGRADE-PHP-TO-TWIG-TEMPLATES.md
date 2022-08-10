# Migrating PHP templates to Twig

Tip: if you're using VS Code, install this plugin: `bajdzis.vscode-twig-pack`

## Basic migration

```PHP
<?php
$view->extend('MauticCoreBundle:Default:content.html.php');
$view['slots']->set('mauticContent', 'mauticWebhook');
$view['slots']->set('headerTitle', $view['translator']->trans('mautic.webhook.webhooks'));
?>

<a href="<?php echo $view['router']->path('/emails', ['objectAction' => 'batchDelete']); ?">Hello world!</a>

<?php echo $view['form']->start($form); ?>

// TODO add more examples
```

Becomes

```Twig
{% extends 'MauticCoreBundle:Default:content.html.twig' %}

{% block headerTitle %}{% trans %}mautic.webhook.webhooks{% endtrans %}{% endblock %}
{% block mauticContent %}mauticWebhook{% endblock %}

<a href="{{ path('/emails', {objectAction: 'batchDelete'}) }}">Hello world!</a>

{{ form_start(form) }}

{# TODO add more examples #}
```

## Random notes

- `strict_variables` is enabled in dev mode (`index_dev.php`) to help you prevent bugs in your code. TODO should we enable this in prod too???
- If you extend `MauticCoreBundle:Default:content.html.twig`, everything HAS to be in blocks. Trying to put any HTML elements outside a block will fail with the following error:

    > A template that extends another one cannot include content outside Twig blocks.
