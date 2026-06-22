<?php
require_once 'ConexionModel.php';

class EstatusModel
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    private function generarNuevoId()
    {
        $sql = "SELECT idEstatus FROM estatus ORDER BY idEstatus DESC LIMIT 1";
        $stmt = $this->db->query($sql);
        $ultimoId = $stmt->fetchColumn();

        if (!$ultimoId) {
            return "ES0001";
        }

        $numero = substr($ultimoId, 2);
        $nuevoNumero = intval($numero) + 1;

        return "ES" . str_pad($nuevoNumero, 4, "0", STR_PAD_LEFT);
    }

    public function listarEstatus()
    {
        $sql = "SELECT * FROM estatus ORDER BY idEstatus ASC";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function registrarEstatus($nombre, $descripcion)
    {
        $nuevoId = $this->generarNuevoId();

        $sql = "INSERT INTO estatus (idEstatus, nomEstatus, descEstatus) 
            VALUES (:id, :nombre, :desc)";
        $stmt = $this->db->prepare($sql);

        $resultado = $stmt->execute([
            'id' => $nuevoId,
            'nombre' => $nombre,
            'desc' => $descripcion
        ]);

        return $resultado ? $nuevoId : false;
    }

    public function eliminarEstatus($id)
    {
        try {
            $sql = "DELETE FROM estatus WHERE idEstatus = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$id]);

            return ($stmt->rowCount() > 0);

        } catch (PDOException $e) {
            if ($e->getCode() == '23000') {
                return "link";
            }
            return false;
        }
    }

    public function obtenerEstatusPorId($id)
    {
        $sql = "SELECT * FROM estatus WHERE idEstatus = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function actualizarEstatus($id, $nombre, $descripcion)
    {
        $sql = "UPDATE estatus SET nomEstatus = :nombre, descEstatus = :desc WHERE idEstatus = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            'id' => $id,
            'nombre' => $nombre,
            'desc' => $descripcion
        ]);
    }
}