<?php
require_once 'ConexionModel.php';

class HorarioModel
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    private function generarNuevoId()
    {
        $sql = "SELECT idHorario FROM horario ORDER BY idHorario DESC LIMIT 1";
        $stmt = $this->db->query($sql);
        $ultimoId = $stmt->fetchColumn();

        if (!$ultimoId) {
            return "HO0001";
        }

        $numero = substr($ultimoId, 2);
        $nuevoNumero = intval($numero) + 1;

        return "HO" . str_pad($nuevoNumero, 4, "0", STR_PAD_LEFT);
    }

    public function listarHorarios()
    {
        $sql = "SELECT * FROM horario ORDER BY idHorario ASC";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function registrarHorario($nombre)
    {
        $nuevoId = $this->generarNuevoId();

        $sql = "INSERT INTO horario (idHorario, nomHorario) VALUES (:id, :nombre)";
        $stmt = $this->db->prepare($sql);

        $resultado = $stmt->execute([
            'id' => $nuevoId,
            'nombre' => $nombre
        ]);

        return $resultado ? $nuevoId : false;
    }

    public function eliminarHorario($id)
    {
        try {
            $sql = "DELETE FROM horario WHERE idHorario = ?";
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

    public function obtenerHorarioPorId($id)
    {
        $sql = "SELECT * FROM horario WHERE idHorario = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function actualizarHorario($id, $nombre)
    {
        $sql = "UPDATE horario SET nomHorario = :nombre WHERE idHorario = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            'id' => $id,
            'nombre' => $nombre
        ]);
    }
}