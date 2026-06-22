<?php
require_once 'ConexionModel.php';

class DocenteModel
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    private function generarNuevoId()
    {
        $sql = "SELECT idDocente FROM docente ORDER BY idDocente DESC LIMIT 1";
        $stmt = $this->db->query($sql);
        $ultimoId = $stmt->fetchColumn();

        if (!$ultimoId) {
            return "DC0001";
        }

        $numero = substr($ultimoId, 2);
        $nuevoNumero = intval($numero) + 1;

        return "DC" . str_pad($nuevoNumero, 4, "0", STR_PAD_LEFT);
    }

    public function listarDocentes()
    {
        $sql = "SELECT * FROM docente ORDER BY idDocente ASC";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function registrarDocente($cedula, $nombres, $apellidos, $telefono)
    {
        $nuevoId = $this->generarNuevoId();

        $sql = "INSERT INTO docente (idDocente, cedDocente, nombreDocente, apellidoDocente, telfDocente) 
            VALUES (:id, :cedula, :nombres, :apellidos, :telefono)";
        $stmt = $this->db->prepare($sql);

        $resultado = $stmt->execute([
            'id' => $nuevoId,
            'cedula' => $cedula,
            'nombres' => $nombres,
            'apellidos' => $apellidos,
            'telefono' => $telefono
        ]);

        return $resultado ? $nuevoId : false;
    }

    public function eliminarDocente($id)
    {
        try {
            $sql = "DELETE FROM docente WHERE idDocente = ?";
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

    public function obtenerDocentePorId($id)
    {
        $sql = "SELECT * FROM docente WHERE idDocente = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function actualizarDocente($id, $cedula, $nombres, $apellidos, $telefono)
    {
        $sql = "UPDATE docente 
                SET cedDocente = :cedula, nombreDocente = :nombres, apellidoDocente = :apellidos, telfDocente = :telefono
                WHERE idDocente = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            'id' => $id,
            'cedula' => $cedula,
            'nombres' => $nombres,
            'apellidos' => $apellidos,
            'telefono' => $telefono
        ]);
    }
}