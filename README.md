# README

This repository contains a sample setup for REST APIs implemented with the Symfony framework. It uses the [NelmioApiDocBundle](https://github.com/nelmio/NelmioApiDocBundle) to generate an API documentation in the OpenAPI format, uses this information to validate incoming data with the help of the [SwaggerResolverBundle](https://github.com/adrenalinkin/swagger-resolver-bundle) and shows how the Symfony serializer can be used to avoid manual data transformations.

Feel free to take a look at the [ContactController](https://github.com/sabinebaer/symfony-apis/blob/master/src/Controller/ContactController.php) - it is the centrepiece of this setup.