<?php
// Modelo de estados



require_once 'ConexionModel.php';

class EstadoModel
{
    private $db;

    public function __construct()
    {

        $this->db = Database::getConnection();
    }


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


    public function listarEstados()
    {
        $sql = "SELECT idEstado, nombreEstado FROM estado ORDER BY idEstado ASC";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }


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


    public function obtenerEstadoPorId($id)
    {
        $sql = "SELECT idEstado, nombreEstado FROM estado WHERE idEstado = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }


    public function actualizarEstado($id, $nombre)
    {
        $sql = "UPDATE estado SET nombreEstado = :nombre WHERE idEstado = :id";
        $stmt = $this->db->prepare($sql);
        
        return $stmt->execute([
            'id'     => $id,
            'nombre' => $nombre
        ]);
    }


    public function eliminarEstado($id)
    {
        try {
            $sql = "DELETE FROM estado WHERE idEstado = ?";
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
}