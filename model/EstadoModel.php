<?php
require_once 'ConexionModel.php';

class EstadoModel
{
    private $db;

    public function __construct()
    {
        // Conexión centralizada del sistema SIGPAF
        $this->db = Database::getConnection();
    }

    /**
     * Genera el ID secuencial EST0001, EST0002...
     */
    private function generarNuevoId()
    {
        $sql = "SELECT idEstado FROM estado ORDER BY idEstado DESC LIMIT 1";
        $stmt = $this->db->query($sql);
        $ultimoId = $stmt->fetchColumn();

        if (!$ultimoId) {
            return "EST0001";
        }

        $numero = substr($ultimoId, 3);
        $nuevoNumero = intval($numero) + 1;

        return "EST" . str_pad($nuevoNumero, 4, "0", STR_PAD_LEFT);
    }

    // Listado completo de Estados
    public function listarEstados()
    {
        $sql = "SELECT idEstado, nombreEstado FROM estado ORDER BY idEstado ASC";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Registro de nuevo Estado
    public function registrarEstado($nombre)
    {
        $nuevoId = $this->generarNuevoId();

        $sql = "INSERT INTO estado (idEstado, nombreEstado) VALUES (:id, :nombre)";
        $stmt = $this->db->prepare($sql);

        $resultado = $stmt->execute([
            'id'     => $nuevoId,
            'nombre' => $nombre
        ]);

        return $resultado ? $nuevoId : false;
    }

    // Consulta por ID para el Modal
    public function obtenerEstadoPorId($id)
    {
        $sql = "SELECT idEstado, nombreEstado FROM estado WHERE idEstado = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Actualización de Estado
    public function actualizarEstado($id, $nombre)
    {
        $sql = "UPDATE estado SET nombreEstado = :nombre WHERE idEstado = :id";
        $stmt = $this->db->prepare($sql);
        
        return $stmt->execute([
            'id'     => $id,
            'nombre' => $nombre
        ]);
    }

    // Eliminación con manejo de restricción por integridad referencial
    public function eliminarEstado($id)
    {
        try {
            $sql = "DELETE FROM estado WHERE idEstado = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$id]);

            return ($stmt->rowCount() > 0);

        } catch (PDOException $e) {
            // Código 23000: El estado está siendo usado en la tabla municipio
            if ($e->getCode() == '23000') {
                return "link";
            }
            return false;
        }
    }
}