<?php
// Modelo de espacios



require_once 'ConexionModel.php';

class EspacioUtilizarModel
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    private function generarNuevoId()
    {
        $sql = "SELECT idEspacioUtilizar FROM espacioUtilizar ORDER BY idEspacioUtilizar DESC LIMIT 1";
        $stmt = $this->db->query($sql);
        $ultimoId = $stmt->fetchColumn();

        if (!$ultimoId) {
            return "EU0001";
        }

        $numero = substr($ultimoId, 2);
        $nuevoNumero = intval($numero) + 1;

        return "EU" . str_pad($nuevoNumero, 4, "0", STR_PAD_LEFT);
    }

    public function listarEspacios()
    {
        $sql = "SELECT e.*, l.nomLugarActividad AS nomSede
                FROM espacioUtilizar e
                LEFT JOIN lugarActividad l ON e.idLugarActividad = l.idLugarActividad
                ORDER BY e.idEspacioUtilizar ASC";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Sedes (lugares institucionales) a las que puede pertenecer un espacio
    public function obtenerSedes()
    {
        $sql = "SELECT idLugarActividad, nomLugarActividad
                FROM lugarActividad
                WHERE esSede = 1
                ORDER BY nomLugarActividad";
        return $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    public function esSedeValida($idLugar)
    {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM lugarActividad WHERE idLugarActividad = :id AND esSede = 1");
        $stmt->execute(['id' => $idLugar]);
        return $stmt->fetchColumn() > 0;
    }

    public function registrarEspacio($nombreEspacio, $descEspacio, $capacidad, $idLugar = null)
    {
        $nuevoId = $this->generarNuevoId();

        $sql = "INSERT INTO espacioUtilizar (idEspacioUtilizar, nombreEspacioUtilizar, descEspacio, capacidad, idLugarActividad) 
                VALUES (:id, :nombre, :descripcion, :capacidad, :lugar)";
        $stmt = $this->db->prepare($sql);

        $resultado = $stmt->execute([
            'id' => $nuevoId,
            'nombre' => $nombreEspacio,
            'descripcion' => $descEspacio,
            'capacidad' => $capacidad,
            'lugar' => $idLugar !== '' ? $idLugar : null
        ]);

        return $resultado ? $nuevoId : false;
    }

    public function eliminarEspacio($id)
    {
        try {
            $sql = "DELETE FROM espacioUtilizar WHERE idEspacioUtilizar = ?";
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

    public function obtenerEspacioPorId($id)
    {
        $sql = "SELECT * FROM espacioUtilizar WHERE idEspacioUtilizar = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function actualizarEspacio($id, $nombreEspacio, $descEspacio, $capacidad, $idLugar = null)
    {
        $sql = "UPDATE espacioUtilizar 
                SET nombreEspacioUtilizar = :nombre, descEspacio = :descripcion, capacidad = :capacidad,
                    idLugarActividad = :lugar
                WHERE idEspacioUtilizar = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            'id' => $id,
            'nombre' => $nombreEspacio,
            'descripcion' => $descEspacio,
            'capacidad' => $capacidad,
            'lugar' => $idLugar !== '' ? $idLugar : null
        ]);
    }
}