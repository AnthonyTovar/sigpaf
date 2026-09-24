<?php
require_once 'ConexionModel.php';

class BitacoraModel
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    /**
     * Genera la secuencia alfanumerica BIT0001, BIT0002...
     */
    private function generarNuevoId()
    {
        $sql = "SELECT idBitacora FROM bitacora ORDER BY idBitacora DESC LIMIT 1";
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
        $nuevoId = $this->generarNuevoId();

        $sql = "INSERT INTO bitacora
                (idBitacora, usuario_id, usuario_nombre, accion, modulo, registro_id, descripcion, ip)
                VALUES
                (:id, :usuario_id, :usuario_nombre, :accion, :modulo, :registro_id, :descripcion, :ip)";
        $stmt = $this->db->prepare($sql);

        $stmt->execute([
            'id'             => $nuevoId,
            'usuario_id'     => $datos['usuario_id'],
            'usuario_nombre' => $datos['usuario_nombre'],
            'accion'         => $datos['accion'],
            'modulo'         => $datos['modulo'],
            'registro_id'    => $datos['registro_id'],
            'descripcion'    => $datos['descripcion'],
            'ip'             => $datos['ip']
        ]);

        return $nuevoId;
    }

    /**
     * Lista los registros con filtros y paginacion
     */
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
