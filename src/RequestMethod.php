<?php
namespace Diagro\API;

enum RequestMethod: string
{

    case GET        = "get";

    case POST       = "post";

    case PUT        = "put";

    case DELETE     = "delete";

}