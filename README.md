<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://diagro.be/assets/img/diagro-logo.svg" width="400"></a></p>

<p align="center">
<img src="https://img.shields.io/badge/library-laravel_api-yellowgreen" alt="Diagro API facade helpers">
<a href="https://github.com/diagro-git/service_auth"><img src="https://img.shields.io/badge/type-library-informational" alt="Diagro service"></a>
<a href="https://php.net"><img src="https://img.shields.io/badge/php-8.0-blueviolet" alt="PHP"></a>
<a href="https://laravel.com/docs/8.x/"><img src="https://img.shields.io/badge/laravel-8.67-red" alt="Laravel framework"></a>
</p>

## Beschrijving

Deze bibliotheek wordt gebruikt als basis voor alle backend API facades. 

## Development

* Composer: `diagro/lib_laravel_api: "dev-development"`

## Production

* Composer: `diagro/lib_laravel_api: "1.0.0"`

## Changelog

### V1.1.1

* **Bugfix**: Kon geen path meegeven aan de URL.

### V1.1.0

* **Update**: $url parameter weg in de API en url() methode die subclasses moeten implementeren.

### V1.0.0

* **Feature**: API abstract class per API facade
* **Feature**: Response classes
* **Feature**: JSON response handler
