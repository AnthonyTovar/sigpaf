<?php
/**
 * VALIDACIONES UNIFICADAS DEL LADO DEL SERVIDOR — SIGPAF
 *
 * Espejo del helper del cliente (view/public/js/validaciones.js).
 * Todos los controladores deben usar estas reglas y responder con
 * ValidationHelper::error() / ValidationHelper::exito() para que el
 * formato de respuesta sea IDENTICO en todo el sistema:
 *
 *   { "status": "error",   "message": "..." }
 *   { "status": "success", "message": "..." }
 *
 * El JavaScript muestra esos mensajes con la alerta unificada (Validar.aviso),
 * por lo que el usuario ve siempre el mismo efecto en cualquier pantalla.
 */
class ValidationHelper
{
    // Máximo de caracteres para objetivo, descripción y observación
    const MAX_TEXTO = 300;

    // ===== REGLAS (devuelven TRUE si el valor es VÁLIDO) =====

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

    // El inicio no puede ser posterior al fin
    public static function rangoFechas($fechaInicio, $fechaFin)
    {
        return self::fecha($fechaInicio)
            && self::fecha($fechaFin)
            && $fechaInicio <= $fechaFin;
    }

    // Cédula venezolana: prefijo V/E opcional + 6 a 9 dígitos
    public static function cedula($valor)
    {
        return preg_match('/^[VE]?\d{6,9}$/i', trim((string) $valor)) === 1;
    }

    // Teléfono venezolano 04XX-XXXXXXX (opcional en la mayoría de módulos)
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
            return true; // opcional
        }
        return filter_var(trim((string) $valor), FILTER_VALIDATE_EMAIL) !== false;
    }

    // Número entero mayor o igual a un mínimo
    public static function enteroMin($valor, $min = 0)
    {
        if (!is_numeric($valor)) {
            return false;
        }
        return (int) $valor >= $min;
    }

    // Texto sin números (nombres, apellidos, descripciones cortas de catálogo)
    public static function soloLetras($valor)
    {
        return preg_match("/^[A-Za-zÁáÉéÍíÓóÚúÜüÑñ' .-]+$/u", trim((string) $valor)) === 1;
    }

    // Atajo: valida un campo requerido y responde error JSON si falla
    public static function exigirRequerido($valor, $mensaje)
    {
        if (!self::requerido($valor)) {
            self::error($mensaje);
        }
    }

    // Atajo: valida longitud máxima y responde error JSON si falla
    public static function exigirMaxLongitud($valor, $mensaje, $max = self::MAX_TEXTO)
    {
        if (!self::maxLongitud($valor, $max)) {
            self::error($mensaje . " (máximo $max caracteres).");
        }
    }

    // ===== VALIDACIÓN DE DUPLICADOS (FORMATO ESTÁNDAR DEL SISTEMA) =====

    /**
     * Detecta si $valor ya existe en $columna dentro del listado de registros.
     * - Comparación sin distinguir mayúsculas ni espacios extremos.
     * - Si se pasa $columnaId y $excluirId, omite el propio registro (uso en editar).
     * - $adicional permite acotar la comparación a un ámbito (p. ej. municipio
     *   dentro del mismo estado): ['idEstado' => $idEstado].
     */
    public static function esDuplicado(array $lista, $columna, $valor, $columnaId = null, $excluirId = null, array $adicional = [])
    {
        $buscado = mb_strtolower(trim((string) $valor), 'UTF-8');
        foreach ($lista as $fila) {
            if ($columnaId !== null && $excluirId !== null
                && (string) ($fila[$columnaId] ?? '') === (string) $excluirId) {
                continue; // al editar, el propio registro no cuenta como duplicado
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

    /**
     * Responde el mensaje estándar de duplicado y termina la petición.
     * Mismo formato en todos los módulos, p. ej.:
     *   "Ya existe un cargo con el nombre 'Profesor'. Verifique e inténtelo con otro."
     */
    public static function errorDuplicado($entidad, $campo, $valor)
    {
        self::error("Ya existe {$entidad} con {$campo} '" . trim((string) $valor)
            . "'. Verifique e inténtelo con otro.");
    }

    // ===== RESPUESTAS JSON ESTÁNDAR DEL SISTEMA =====

    /**
     * Responde un error en el formato unificado y termina la petición.
     * El JavaScript lo muestra con la alerta estándar del sistema.
     */
    public static function error($mensaje)
    {
        if (ob_get_length()) {
            ob_clean();
        }
        header('Content-Type: application/json');
        echo json_encode(['status' => 'error', 'message' => $mensaje]);
        exit();
    }

    /**
     * Responde un éxito en el formato unificado y termina la petición.
     */
    public static function exito($mensaje, $extra = [])
    {
        if (ob_get_length()) {
            ob_clean();
        }
        header('Content-Type: application/json');
        echo json_encode(array_merge(['status' => 'success', 'message' => $mensaje], $extra));
        exit();
    }

    /**
     * Exige que la petición sea POST (todos los guardar del sistema).
     */
    public static function exigirPost()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            self::error('Método no permitido. Recarga la página e inténtalo de nuevo.');
        }
    }
}
