<?php

namespace App\Http\Controllers;

use OpenApi\Attributes as OA;

#[OA\Info(
    version: '1.0.0',
    title: 'IMD (Inisiasi Menyusui Dini) API',
    description: 'API untuk sistem manajemen data Inisiasi Menyusui Dini dengan fitur dashboard analytics, autentikasi, dan CRUD operations.',
    contact: new OA\Contact(
        name: 'API Support',
        email: 'support@wafik.net'
    )
)]
#[OA\Server(
    url: 'http://localhost:8000',
    description: 'Development Server'
)]
#[OA\SecurityScheme(
    securityScheme: 'bearerAuth',
    type: 'http',
    scheme: 'bearer',
    bearerFormat: 'JWT',
    description: 'Laravel Sanctum Bearer Token'
)]
abstract class Controller
{
    //
}
