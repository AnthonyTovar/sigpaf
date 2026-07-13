<?php
require_once 'ConexionModel.php';

class UnidadMedidaModel
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    private function generarNuevoId()
    {
        $sql = "SELECT idUnidadMedida FROM unidadMedida ORDER BY idUnidadMedida DESC LIMIT 1";
        $stmt = $this->db->query($sql);
        $ultimoId = $stmt->fetchColumn();

        if (!$ultimoId) {
            return "UM0001";
        }

        $numero = substr($ultimoId, 2);
        $nuevoNumero = intval($numero) + 1;

        return "UM" . str_pad($nuevoNumero, 4, "0", STR_PAD_LEFT);
    }

    public function listarUnidades()
    {
        $sql = "SELECT idUnidadMedida, nomUnidadMedida, descUnidadMedida 
                FROM unidadMedida 
                ORDER BY idUnidadMedida ASC";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function registrarUnidad($nombre, $descripcion)
    {
        $nuevoId = $this->generarNuevoId();

        $sql = "INSERT INTO unidadMedida (idUnidadMedida, nomUnidadMedida, descUnidadMedida) 
                VALUES (:id, :nombre, :descripcion)";
        $stmt = $this->db->prepare($sql);

        $resultado = $stmt->execute([
            'id' => $nuevoId,
            'nombre' => $nombre,
            'descripcion' => $descripcion
        ]);

        return $resultado ? $nuevoId : false;
    }

    public function eliminarUnidad($id)
    {
        try {
            $sql = "DELETE FROM unidadMedida WHERE idUnidadMedida = ?";
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

    public function obtenerUnidadPorId($id)
    {
        $sql = "SELECT idUnidadMedida, nomUnidadMedida, descUnidadMedida 
                FROM unidadMedida 
                WHERE idUnidadMedida = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function actualizarUnidad($id, $nombre, $descripcion)
    {
        $sql = "UPDATE unidadMedida 
                SET nomUnidadMedida = :nombre, descUnidadMedida = :descripcion 
                WHERE idUnidadMedida = :id";
        $stmt = $this->db->prepare($sql);

        return $stmt->execute([
            'id' => $id,
            'nombre' => $nombre,
            'descripcion' => $descripcion
        ]);
    }
}