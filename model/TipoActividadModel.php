<?php
require_once 'ConexionModel.php';

class TipoActividadModel
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    private function generarNuevoId()
    {
        $sql = "SELECT idTipoActividad FROM tipoActividad ORDER BY idTipoActividad DESC LIMIT 1";
        $stmt = $this->db->query($sql);
        $ultimoId = $stmt->fetchColumn();

        if (!$ultimoId) {
            return "TA0001";
        }

        $numero = substr($ultimoId, 2);
        $nuevoNumero = intval($numero) + 1;

        return "TA" . str_pad($nuevoNumero, 4, "0", STR_PAD_LEFT);
    }

    public function listarTiposActividad()
    {
        $sql = "SELECT * FROM tipoActividad ORDER BY idTipoActividad ASC";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function registrarTipoActividad($nombre, $descripcion)
    {
        $nuevoId = $this->generarNuevoId();

        $sql = "INSERT INTO tipoActividad (idTipoActividad, nomTipoActividad, descTipoActividad) 
            VALUES (:id, :nombre, :desc)";
        $stmt = $this->db->prepare($sql);

        $resultado = $stmt->execute([
            'id' => $nuevoId,
            'nombre' => $nombre,
            'desc' => $descripcion
        ]);

        return $resultado ? $nuevoId : false;
    }

    public function eliminarTipoActividad($id)
    {
        try {
            $sql = "DELETE FROM tipoActividad WHERE idTipoActividad = ?";
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

    public function obtenerTipoActividadPorId($id)
    {
        $sql = "SELECT * FROM tipoActividad WHERE idTipoActividad = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function actualizarTipoActividad($id, $nombre, $descripcion)
    {
        $sql = "UPDATE tipoActividad SET nomTipoActividad = :nombre, descTipoActividad = :desc WHERE idTipoActividad = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            'id' => $id,
            'nombre' => $nombre,
            'desc' => $descripcion
        ]);
    }
}