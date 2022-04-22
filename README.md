<p align="center"><a href="https://www.diagro.be" target="_blank"><img src="https://diagro.be/assets/img/diagro-logo.svg" width="400"></a></p>

<p align="center">
<img src="https://img.shields.io/badge/project-lib_laravel_api-yellowgreen" alt="Diagro API facade helpers">
<img src="https://img.shields.io/badge/type-library-informational" alt="Diagro service">
<img src="https://img.shields.io/badge/php-8.1-blueviolet" alt="PHP">
<img src="https://img.shields.io/badge/laravel-9.0-red" alt="Laravel framework">
</p>

## Beschrijving

Deze bibliotheek wordt gebruikt als basis voor alle backend API facades. 

## Development

* Composer: `diagro/lib_laravel_api: "^1.9"`

## Production

* Composer: `diagro/lib_laravel_api: "^1.9"`

## Example

```php
<?php
class Backend extends \Diagro\API\API
{
    public static function endpoint(int $id)
    {
        return self::cache(
            self::concatToString(__FUNCTION__, $id),
            fn() => self::get(self::url("/$id"))->json()
        );
    }
    
    public static function create(array $data)
    {
        return self::post('/', $data)
            ->deleteCache()
            ->deleteCache(class_name: OtherSection::class)
            ->json();
    }
}
```

## Changelog

### V1.13

* **Update**: API#backend(...) om interne API calls te maken met diagro backends onderling.

### V1.13

* **Update**: timeout voor request is standaad 30 seconden en instelbaar via `DIAGRO_API_REQUEST_TIMEOUT` env in seconden.

### V1.12

* **Update**: withHeaders method om extra headers in te stellen bij een API endpoint
* **Update**: fields method om fields te kiezen bij endpoint

### V1.11

* **Update**: mogelijkheid om cache van andere applicatie secties te verwijderen.

### V1.10

* **Update**: env variabele `DIAGRO_API_CACHE_TTL` om standaard ttl in te stellen van de cache per app in seconden.
* **Update**: cache wordt bijgehouden per user/bedrijf ipv user niveau. Per cache item zijn nu vier tags gekoppeld.

### V1.8

* **Update**: Verwijderen van cache na een update, delete of create

### V1.7

* **Update**: Json response weg en cache ingebouwd.

### V1.6
* **Bugfix**: getValue van queued cookie

### V1.5
* **Bugfix**: Token moet eerst uit queued cookies halen

### V1.3
* **Update**: default headers request via backend of frontend

### V1.2
* **Update**: static abstract functie kan niet in abstract class. Gooit `RuntimeException` als subclass niet implementeerd.

### V1.1
* **Feature**: upgrade naar PHP8.1 and Laravel 9.0

### V1.0

* **Feature**: API abstract class per API facade
* **Feature**: Response classes
* **Feature**: JSON response handler
