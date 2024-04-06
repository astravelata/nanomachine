# nanomachine

![Nanomachines, son.](./docs/nanomachines_son.webp)

> **Warning: pre-release software: this is a prototype version, distilled and universalized from more complex code run on my personal website, not yet ready for production use. This version may break in strange ways, and/or may not even run.**

Nanomachine is a small framework built to run a personal website in a way that simply is a fancy exposed directory index. It assumes all of your personal content exists in `data`. It prizes being fast and simple over any real extensibility, and is really opinionated. It _might_ be for you if you want to run a nicer version of a 2000s-style fancy_index driven site. If that doesn't make any sense to you, it is probably not for you.

## Installation

Nanomachine expects that you will be setting up something like PHP-FPM and a webserver that handles static content on its own, and then routes requests it can't find in `_assets` or `src/templates/static/` to `src/index.php`. To develop and try nanomachine, you can use the built-in PHP development server with `src/index.php` as the router:

```bash
php -S localhost:8000 src/index.php
```

You will also need to install the dependencies for this project with [Composer](https://getcomposer.org/), edit `config/config.ini.sample` and change it to `config/config.ini`. Add your content to `data/` and watch things go.

## Requirements

Dependencies of Nanomachine assume PHP 8.x.

## LICENSE

GNU GPL-3.0.