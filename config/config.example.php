<?php

return [
    'db' => [
        'host' => 'localhost',
        'name' => 'crud_api_lab8',
        'user' => 'root',
        'password' => ''
    ],
    'jwt' => [
        'secret' => 'cambia_esta_clave_por_una_clave_larga_y_privada_de_32_caracteres_o_mas',
        'expiration' => 3600,
        'issuer' => 'http://localhost/Software7/Laboratorios/CrudAPI-lab8'
    ]
];
