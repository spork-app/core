## core

A Spork Plugin

```
composer require spork/core
```

Publish your assets

```
php artisan vendor:publish --provider=Spork\Core\\SporkServiceProvider
```

You'll need to run `artisan migrate` to ensure your database gets the new repeating events schema

Lastly, register the Service Provider in your Spork App's `config/app.php` file.

----

## Using UI Components

### Required packages
 - cli-spinners@>=0.1 - why? Ascii art loading spinners :smile: mostly for a bit of fun.
