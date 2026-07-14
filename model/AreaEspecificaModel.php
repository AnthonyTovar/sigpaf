<?php
require_once 'ConexionModel.php';

class AreaEspecificaModel
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    private function generarNuevoId()
    {
        $sql = "SELECT idAreaE FROM areaEspecifica ORDER BY idAreaE DESC LIMIT 1";
        $stmt = $this->db->query($sql);
        $ultimoId = $stmt->fetchColumn();

        if (!$ultimoId) {
            return "AE0001";
        }

        $numero = substr($ultimoId, 2);
        $nuevoNumero = intval($numero) + 1;

        return "AE" . str_pad($nuevoNumero, 4, "0", STR_PAD_LEFT);
    }

    public function listarAreas()
    {
        $sql = "SELECT idAreaE, nomAreaE FROM areaEspecifica ORDER BY idAreaE ASC";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function registrarArea($nombre)
    {
        $nuevoId = $this->generarNuevoId();

        $sql = "INSERT INTO areaEspecifica (idAreaE, nomAreaE) 
                VALUES (:id, :nombre)";
        $stmt = $this->db->prepare($sql);

        $resultado = $stmt->execute([
            'id' => $nuevoId,
            'nombre' => $nombre
        ]);

        return $resultado ? $nuevoId : false;
    }

    public function eliminarArea($id)
    {
        try {
            $sql = "DELETE FROM areaEspecifica WHERE idAreaE = ?";
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

    public function obtenerAreaPorId($id)
    {
        $sql = "SELECT idAreaE, nomAreaE 
                FROM areaEspecifica 
                WHERE idAreaE = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function actualizarArea($id, $nombre)
    {
        $sql = "UPDATE areaEspecifica 
                SET nomAreaE = :nombre 
                WHERE idAreaE = :id";
        $stmt = $this->db->prepare($sql);

        return $stmt->execute([
            'id' => $id,
            'nombre' => $nombre
        ]);
    }
}