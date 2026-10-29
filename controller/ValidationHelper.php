<?php
// Validaciones del servidor




class ValidationHelper
{

    const MAX_TEXTO = 300;

    // Reglas de validación

    public static function requerido($valor)
    {
        return $valor !== null && trim((string) $valor) !== '';
    }

    public static function maxLongitud($valor, $max = self::MAX_TEXTO)
    {
        return mb_strlen(trim((string) $valor), 'UTF-8') <= $max;
    }

    public static function fecha($valor)
    {
        $valor = (string) $valor;
        if ($valor === '') {
            return false;
        }
        $dt = DateTime::createFromFormat('Y-m-d', $valor);
        return $dt && $dt->format('Y-m-d') === $valor;
    }


    public static function rangoFechas($fechaInicio, $fechaFin)
    {
        return self::fecha($fechaInicio)
            && self::fecha($fechaFin)
            && $fechaInicio <= $fechaFin;
    }


    public static function cedula($valor)
    {
        return preg_match('/^[VE]?\d{6,9}$/i', trim((string) $valor)) === 1;
    }


    public static function telefono($valor)
    {
        $t = preg_replace('/\s/', '', (string) $valor);
        if ($t === '') {
            return true;
        }
        return preg_match('/^(0(412|414|416|422|424|426))-?\d{7}$/', $t) === 1;
    }

    public static function correo($valor)
    {
        if (trim((string) $valor) === '') {
            return true;
        }
        return filter_var(trim((string) $valor), FILTER_VALIDATE_EMAIL) !== false;
    }


    public static function enteroMin($valor, $min = 0)
    {
        if (!is_numeric($valor)) {
            return false;
        }
        return (int) $valor >= $min;
    }


    public static function soloLetras($valor)
    {
        return preg_match("/^[A-Za-zÁáÉéÍíÓóÚúÜüÑñ' .-]+$/u", trim((string) $valor)) === 1;
    }


    public static function exigirRequerido($valor, $mensaje)
    {
        if (!self::requerido($valor)) {
            self::error($mensaje);
        }
    }


    public static function exigirMaxLongitud($valor, $mensaje, $max = self::MAX_TEXTO)
    {
        if (!self::maxLongitud($valor, $max)) {
            self::error($mensaje . " (máximo $max caracteres).");
        }
    }

    // Validar duplicados


    public static function esDuplicado(array $lista, $columna, $valor, $columnaId = null, $excluirId = null, array $adicional = [])
    {
        $buscado = mb_strtolower(trim((string) $valor), 'UTF-8');
        foreach ($lista as $fila) {
            if ($columnaId !== null && $excluirId !== null
                && (string) ($fila[$columnaId] ?? '') === (string) $excluirId) {
                continue;
            }
            if (mb_strtolower(trim((string) ($fila[$columna] ?? '')), 'UTF-8') !== $buscado) {
                continue;
            }
            $coincide = true;
            foreach ($adicional as $colAdicional => $valorAdicional) {
                if ((string) ($fila[$colAdicional] ?? '') !== (string) $valorAdicional) {
                    $coincide = false;
                    break;
                }
            }
            if ($coincide) {
                return true;
            }
        }
        return false;
    }


    public static function errorDuplicado($entidad, $campo, $valor)
    {
        self::error("Ya existe {$entidad} con {$campo} '" . trim((string) $valor)
            . "'. Verifique e inténtelo con otro.");
    }

    // Respuestas json estándar del sistema


    public static function error($mensaje)
    {
        if (ob_get_length()) {
            ob_clean();
        }
        header('Content-Type: application/json');
        echo json_encode(['status' => 'error', 'message' => $mensaje]);
        exit();
    }


    public static function exito($mensaje, $extra = [])
    {
        if (ob_get_length()) {
            ob_clean();
        }
        header('Content-Type: application/json');
        echo json_encode(array_merge(['status' => 'success', 'message' => $mensaje], $extra));
        exit();
    }


    public static function exigirPost()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            self::error('Método no permitido. Recarga la página e inténtalo de nuevo.');
        }
    }
}
