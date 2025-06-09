<?php

namespace App\Constants;

class Salas
{
    public const SALAS = [
        '1' => [
           'filas' => [1, 2, 3, 4, 5, 8, 9, 10, 13, 14, 15, 16, 17],
            'columnas' => [
                   1, 2, 3, 4, 5, 7, 8, 9, 10, 
                   11, 12, 13, 14, 15, 16, 18, 19, 20, 
                   21, 22
            ],
            'estado_defecto' => 1,
            'tipo_defecto' => 1
        ],
        '2' => [
            'filas' => [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14],
            'columnas' => [
                   1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 
                   11, 12, 13, 14, 15, 16, 17, 18, 19, 20, 
                   21, 22
            ],
            'estado_defecto' => 1,
            'tipo_defecto' => 1
        ],
        '3' => [
            'filas' => [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15],
            'columnas' => [
                1, 2, /*pasillo*/ 4, 5,
                7, 8, 9, /*pasillo*/ 11, 12, 13,
                15, 16, 17, /*pasillo*/ 19, 20,
                22, 23
            ],
            'estado_defecto' => 1,
            'tipo_defecto' => 1
        ],
        '4' => [
            'filas' => [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15],
            'columnas' => [
                1, 2, 3, 4,
                7, 8, 9, 10, 11, 12,
                15, 16, 17, 18, 19, 20,
                23, 24
            ],
            'estado_defecto' => 1,
            'tipo_defecto' => 1
        ],
    ];
}
