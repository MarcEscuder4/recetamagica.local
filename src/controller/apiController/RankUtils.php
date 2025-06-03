<?php

class RankUtils {
    public static function obtenerRangoPorPuntos(int $puntos, string $genero = 'otro'): array {
    $rangosBase = [
        'base' => [
            ['nombre' => 'Aprendiz de Mago', 'nombre_f' => 'Aprendiz de Maga', 'nombre_x' => 'Aprendiz de Magia', 'min' => 0, 'max' => 39, 'color' => '#FFD1EA', 'icono' => 'fa-hat-wizard'],
            ['nombre' => 'Novato del éter', 'nombre_f' => 'Novata del éter', 'nombre_x' => 'Persona del éter', 'min' => 40, 'max' => 79, 'color' => '#FFB8CA', 'icono' => 'fa-wand-magic'],
            ['nombre' => 'Alquimista de elixires', 'nombre_f' => 'Alquimista de elixires', 'nombre_x' => 'Alquimista de elixires', 'min' => 80, 'max' => 119, 'color' => '#FF9FB9', 'icono' => 'fa-flask'],
            ['nombre' => 'Mago de los hechizos', 'nombre_f' => 'Maga de los hechizos', 'nombre_x' => 'Magi de los hechizos', 'min' => 120, 'max' => 179, 'color' => '#FF87A8', 'icono' => 'fa-hat-wizard'],
            ['nombre' => 'Gran Mago de las pócimas', 'nombre_f' => 'Gran Maga de las pócimas', 'nombre_x' => 'Gran Magi de las pócimas', 'min' => 180, 'max' => 200, 'color' => '#FF6F91', 'icono' => 'fa-star-and-crescent'],
        ]
    ];

    foreach ($rangosBase['base'] as $rango) {
        if ($puntos >= $rango['min'] && $puntos <= $rango['max']) {
            switch (strtolower($genero)) {
                case 'Mujer':
                    $nombre = $rango['nombre_x'];
                    break;
                case 'Hombre':
                    $nombre = $rango['nombre_f'];
                    break;
                default:
                    $nombre = $rango['nombre_f'];
                    break;
            }

            return [
                'nombre' => $nombre,
                'color' => $rango['color'],
                'icono' => $rango['icono']
            ];
        }
    }

    return [
        'nombre' => 'Sin rango',
        'color' => '#ccc',
        'icono' => 'fa-question'
    ];
}

}
