<?php
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
        $sql = "SELECT e.*, l.nomLugarActividad 
                FROM espacioUtilizar e 
                LEFT JOIN lugarActividad l ON e.idLugarActividad = l.idLugarActividad 
                ORDER BY e.idEspacioUtilizar ASC";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function listarLugaresActividad()
    {
        $sql = "SELECT idLugarActividad, nomLugarActividad FROM lugarActividad ORDER BY nomLugarActividad ASC";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function registrarEspacio($nombre, $descripcion, $capacidad, $idLugarActividad)
    {
        $nuevoId = $this->generarNuevoId();

        $sql = "INSERT INTO espacioUtilizar (idEspacioUtilizar, nombreEspacioUtilizar, descEspacio, capacidad, idLugarActividad) 
            VALUES (:id, :nombre, :desc, :capacidad, :idLugarActividad)";
        $stmt = $this->db->prepare($sql);

        $resultado = $stmt->execute([
            'id' => $nuevoId,
            'nombre' => $nombre,
            'desc' => $descripcion,
            'capacidad' => $capacidad,
            'idLugarActividad' => $idLugarActividad
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
        $sql = "SELECT e.*, l.nombreLugarActividad 
                FROM espacioUtilizar e 
                LEFT JOIN lugarActividad l ON e.idLugarActividad = l.idLugarActividad 
                WHERE e.idEspacioUtilizar = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function actualizarEspacio($id, $nombre, $descripcion, $capacidad, $idLugarActividad)
    {
        $sql = "UPDATE espacioUtilizar 
                SET nombreEspacioUtilizar = :nombre, descEspacio = :desc, capacidad = :capacidad, idLugarActividad = :idLugarActividad 
                WHERE idEspacioUtilizar = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            'id' => $id,
            'nombre' => $nombre,
            'desc' => $descripcion,
            'capacidad' => $capacidad,
            'idLugarActividad' => $idLugarActividad
        ]);
    }
}