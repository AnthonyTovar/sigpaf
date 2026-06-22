<?php
require_once 'ConexionModel.php';

class VerticeModel
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    private function generarNuevoId()
    {
        $sql = "SELECT idVertice FROM vertice ORDER BY idVertice DESC LIMIT 1";
        $stmt = $this->db->query($sql);
        $ultimoId = $stmt->fetchColumn();

        if (!$ultimoId) {
            return "VER0001";
        }

        $numero = substr($ultimoId, 3);
        $nuevoNumero = intval($numero) + 1;

        return "VER" . str_pad($nuevoNumero, 4, "0", STR_PAD_LEFT);
    }

    public function listarVertice()
    {
        $sql = "SELECT v.*, a.nomAreaE 
                FROM vertice v 
                LEFT JOIN areaEspecifica a ON v.idAreaE = a.idAreaE 
                ORDER BY v.idVertice ASC";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function listarAreasEspecificas()
    {
        $sql = "SELECT idAreaE, nomAreaE FROM areaEspecifica ORDER BY nomAreaE ASC";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function registrarVertice($nombre, $descripcion, $idAreaE)
    {
        $nuevoId = $this->generarNuevoId();

        $sql = "INSERT INTO vertice (idVertice, nombreVertice, descVertice, idAreaE) 
                VALUES (:id, :nombre, :desc, :idAreaE)";
        $stmt = $this->db->prepare($sql);

        $resultado = $stmt->execute([
            'id' => $nuevoId,
            'nombre' => $nombre,
            'desc' => $descripcion,
            'idAreaE' => $idAreaE
        ]);

        return $resultado ? $nuevoId : false;
    }

    public function eliminarVertice($id)
    {
        try {
            $sql = "DELETE FROM vertice WHERE idVertice = ?";
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

    public function obtenerVerticePorId($id)
    {
        $sql = "SELECT v.*, a.nomAreaE 
                FROM vertice v 
                LEFT JOIN areaEspecifica a ON v.idAreaE = a.idAreaE 
                WHERE v.idVertice = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function actualizarVertice($id, $nombre, $desc, $idAreaE)
    {
        $sql = "UPDATE vertice 
                SET nombreVertice = :nombre, descVertice = :desc, idAreaE = :idAreaE 
                WHERE idVertice = :id";
        $stmt = $this->db->prepare($sql);

        return $stmt->execute([
            'id' => $id,
            'nombre' => $nombre,
            'desc' => $desc,
            'idAreaE' => $idAreaE
        ]);
    }
}