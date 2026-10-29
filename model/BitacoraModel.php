<?php
// Modelo de bitácora



require_once 'ConexionModel.php';

class BitacoraModel
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }


    private function generarNuevoId()
    {
        $sql = "SELECT idBitacora FROM bitacora ORDER BY LENGTH(idBitacora) DESC, idBitacora DESC LIMIT 1";
        $stmt = $this->db->query($sql);
        $ultimoId = $stmt->fetchColumn();

        if (!$ultimoId) {
            return "BIT0001";
        }

        $numero = intval(substr($ultimoId, 3));
        return "BIT" . str_pad($numero + 1, 4, "0", STR_PAD_LEFT);
    }



    public function registrar($datos)
    {
        $sql = "INSERT INTO bitacora
                (idBitacora, usuario_id, usuario_nombre, accion, modulo, registro_id, descripcion, ip)
                VALUES
                (:id, :usuario_id, :usuario_nombre, :accion, :modulo, :registro_id, :descripcion, :ip)";

        $maxIntentos = 3;

        for ($intento = 1; $intento <= $maxIntentos; $intento++) {
            try {
                $nuevoId = $this->generarNuevoId();

                $stmt = $this->db->prepare($sql);


                $stmt->bindValue(':id', $nuevoId);
                $stmt->bindValue(':usuario_id', $datos['usuario_id'],
                    $datos['usuario_id'] === null ? PDO::PARAM_NULL : PDO::PARAM_STR);
                $stmt->bindValue(':usuario_nombre', $datos['usuario_nombre']);
                $stmt->bindValue(':accion', $datos['accion']);
                $stmt->bindValue(':modulo', $datos['modulo']);
                $stmt->bindValue(':registro_id', $datos['registro_id']);
                $stmt->bindValue(':descripcion', $datos['descripcion']);
                $stmt->bindValue(':ip', $datos['ip']);
                $stmt->execute();

                return $nuevoId;

            } catch (PDOException $e) {
                $esDuplicado = stripos($e->getMessage(), 'Duplicate') !== false
                            || stripos($e->getMessage(), 'duplicada') !== false;

                if ($esDuplicado && $intento < $maxIntentos) {
                    continue;
                }

                throw $e;
            }
        }
    }


    public function listar($filtros = [], $pagina = 1, $porPagina = 15)
    {
        list($where, $params) = $this->construirWhere($filtros);

        $offset = (int)(($pagina - 1) * $porPagina);
        $limit  = (int)$porPagina;

        $sql = "SELECT * FROM bitacora" . $where . " ORDER BY fecha_hora DESC LIMIT $limit OFFSET $offset";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function contar($filtros = [])
    {
        list($where, $params) = $this->construirWhere($filtros);

        $sql = "SELECT COUNT(*) FROM bitacora" . $where;
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchColumn();
    }


    public function contarPorAccion($filtros = [])
    {
        $filtrosSinAccion = $filtros;
        $filtrosSinAccion['accion'] = '';

        list($where, $params) = $this->construirWhere($filtrosSinAccion);

        $sql = "SELECT accion, COUNT(*) AS total
                FROM bitacora" . $where . "
                GROUP BY accion";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        $conteo = [];
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $fila) {
            $conteo[$fila['accion']] = (int) $fila['total'];
        }
        return $conteo;
    }


    public function obtenerUsuariosConRegistros()
    {
        $sql = "SELECT DISTINCT usuario_nombre FROM bitacora ORDER BY usuario_nombre";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    private function construirWhere($filtros)
    {
        $where  = [];
        $params = [];

        if (!empty($filtros['accion'])) {
            $where[] = "accion = :accion";
            $params['accion'] = $filtros['accion'];
        }

        if (!empty($filtros['usuario'])) {
            $where[] = "usuario_nombre LIKE :usuario";
            $params['usuario'] = "%{$filtros['usuario']}%";
        }

        if (!empty($filtros['desde'])) {
            $where[] = "DATE(fecha_hora) >= :desde";
            $params['desde'] = $filtros['desde'];
        }

        if (!empty($filtros['hasta'])) {
            $where[] = "DATE(fecha_hora) <= :hasta";
            $params['hasta'] = $filtros['hasta'];
        }

        if (!empty($filtros['buscar'])) {
            $where[] = "(descripcion LIKE :buscar OR modulo LIKE :buscar_mod)";
            $params['buscar'] = "%{$filtros['buscar']}%";
            $params['buscar_mod'] = "%{$filtros['buscar']}%";
        }

        $whereSql = $where ? " WHERE " . implode(" AND ", $where) : "";
        return [$whereSql, $params];
    }
}
