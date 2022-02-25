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

* Composer: `diagro/lib_laravel_api: "^1.5"`

## Production

* Composer: `diagro/lib_laravel_api: "^1.5"`

## Changelog

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
