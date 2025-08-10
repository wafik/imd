<?php

namespace App\Http\Controllers\Api;

use OpenApi\Attributes as OA;

#[OA\Info(
    version: '1.0.0',
    description: 'API documentation for IMD (Inisiasi Menyusu Dini) Management System',
    title: 'IMD Management API',
    contact: new OA\Contact(
        email: 'admin@imd.com'
    )
)]
#[OA\Server(
    url: '/',
    description: 'API Server'
)]
#[OA\SecurityScheme(
    securityScheme: 'bearerAuth',
    type: 'http',
    name: 'Authorization',
    in: 'header',
    scheme: 'bearer',
    bearerFormat: 'JWT'
)]
#[OA\Schema(
    schema: 'User',
    required: ['id', 'name', 'email', 'username'],
    properties: [
        new OA\Property(property: 'id', type: 'string', example: '01HXYZ123456789'),
        new OA\Property(property: 'name', type: 'string', example: 'John Doe'),
        new OA\Property(property: 'email', type: 'string', example: 'john@example.com'),
        new OA\Property(property: 'username', type: 'string', example: 'johndoe'),
        new OA\Property(property: 'phone', type: 'string', example: '081234567890'),
        new OA\Property(property: 'avatar', type: 'string', nullable: true, example: 'avatars/avatar.jpg'),
        new OA\Property(property: 'created_at', type: 'string', format: 'date-time'),
        new OA\Property(property: 'updated_at', type: 'string', format: 'date-time')
    ]
)]
#[OA\Schema(
    schema: 'Imd',
    required: ['id', 'nama_pasien', 'alamat', 'no_rm', 'tanggal_lahir', 'cara_persalinan', 'tanggal_imd', 'waktu_imd', 'nama_petugas'],
    properties: [
        new OA\Property(property: 'id', type: 'string', example: '01HXYZ123456789'),
        new OA\Property(property: 'nama_pasien', type: 'string', example: 'Siti Aminah'),
        new OA\Property(property: 'alamat', type: 'string', example: 'Jl. Merdeka No. 123, Jakarta'),
        new OA\Property(property: 'no_rm', type: 'string', example: 'RM001234'),
        new OA\Property(property: 'tanggal_lahir', type: 'string', format: 'date', example: '1990-05-15'),
        new OA\Property(property: 'cara_persalinan', type: 'string', enum: ['SC', 'Spontan'], example: 'Spontan'),
        new OA\Property(property: 'tanggal_imd', type: 'string', format: 'date', example: '2024-01-15'),
        new OA\Property(property: 'waktu_imd', type: 'string', enum: ['15 menit', '30 menit', '45 menit', '60 menit'], example: '30 menit'),
        new OA\Property(property: 'nama_petugas', type: 'string', example: 'Bidan Sarah'),
        new OA\Property(property: 'created_at', type: 'string', format: 'date-time'),
        new OA\Property(property: 'updated_at', type: 'string', format: 'date-time')
    ]
)]
#[OA\Schema(
    schema: 'ImdRequest',
    required: ['nama_pasien', 'alamat', 'no_rm', 'tanggal_lahir', 'cara_persalinan', 'tanggal_imd', 'waktu_imd', 'nama_petugas'],
    properties: [
        new OA\Property(property: 'nama_pasien', type: 'string', example: 'Siti Aminah'),
        new OA\Property(property: 'alamat', type: 'string', example: 'Jl. Merdeka No. 123, Jakarta'),
        new OA\Property(property: 'no_rm', type: 'string', example: 'RM001234'),
        new OA\Property(property: 'tanggal_lahir', type: 'string', format: 'date', example: '1990-05-15'),
        new OA\Property(property: 'cara_persalinan', type: 'string', enum: ['SC', 'Spontan'], example: 'Spontan'),
        new OA\Property(property: 'tanggal_imd', type: 'string', format: 'date', example: '2024-01-15'),
        new OA\Property(property: 'waktu_imd', type: 'string', enum: ['15 menit', '30 menit', '45 menit', '60 menit'], example: '30 menit'),
        new OA\Property(property: 'nama_petugas', type: 'string', example: 'Bidan Sarah')
    ]
)]
class SwaggerController
{
    // This class is used only for Swagger documentation annotations
}
